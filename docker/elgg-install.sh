#!/bin/bash
set -e

# Per-plugin Elgg 5.x install + activation script.
# PLUGIN_ID must be set in the container environment (passed by docker-compose
# from <plugin>/docker/.env). Only that one plugin is activated — no fleet
# activation, no plugin-order.txt, no cross-plugin side effects.

if [ -z "${PLUGIN_ID:-}" ]; then
    echo "ERROR: PLUGIN_ID environment variable is required." >&2
    echo "Set it in docker/.env before starting the stack." >&2
    exit 1
fi

echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=${ELGG_DB_HOST:-db}', '${ELGG_DB_USER:-elgg}', '${ELGG_DB_PASS:-elgg}');" 2>/dev/null; do
    sleep 1
done
echo "MySQL is ready."

cd /var/www/html

if [ ! -f /var/www/html/.elgg-installed ]; then
    echo "Installing Elgg 5.x..."

    mkdir -p elgg-config
    cat > elgg-config/settings.php <<'SETTINGS_TEMPLATE'
<?php
global $CONFIG;
if (!isset($CONFIG)) {
    $CONFIG = new \stdClass;
}
SETTINGS_TEMPLATE

    cat >> elgg-config/settings.php <<SETTINGS_VALUES
\$CONFIG->dbuser = '${ELGG_DB_USER:-elgg}';
\$CONFIG->dbpass = '${ELGG_DB_PASS:-elgg}';
\$CONFIG->dbname = '${ELGG_DB_NAME:-elgg}';
\$CONFIG->dbhost = '${ELGG_DB_HOST:-db}';
\$CONFIG->dbport = '3306';
\$CONFIG->dbprefix = 'elgg_';
\$CONFIG->dbencoding = 'utf8mb4';
\$CONFIG->dataroot = '${ELGG_DATA_ROOT:-/var/www/data/}';
\$CONFIG->wwwroot = '${ELGG_SITE_URL:-http://elgg/}';
\$CONFIG->cacheroot = '${ELGG_DATA_ROOT:-/var/www/data/}cache/';
\$CONFIG->assetroot = '${ELGG_DATA_ROOT:-/var/www/data/}assets/';
SETTINGS_VALUES

    php -r "
        require_once 'vendor/autoload.php';

        \$params = [
            'dbuser' => '${ELGG_DB_USER:-elgg}',
            'dbpassword' => '${ELGG_DB_PASS:-elgg}',
            'dbname' => '${ELGG_DB_NAME:-elgg}',
            'dbhost' => '${ELGG_DB_HOST:-db}',
            'dbport' => '3306',
            'dbprefix' => 'elgg_',
            'sitename' => 'Elgg 5.x Plugin Test',
            'siteemail' => '${ELGG_ADMIN_EMAIL:-admin@example.com}',
            'wwwroot' => '${ELGG_SITE_URL:-http://elgg/}',
            'dataroot' => '${ELGG_DATA_ROOT:-/var/www/data/}',
            'displayname' => 'Admin',
            'email' => '${ELGG_ADMIN_EMAIL:-admin@example.com}',
            'username' => 'admin',
            'password' => '${ELGG_ADMIN_PASSWORD:-admin12345}',
        ];

        \$installer = new \ElggInstaller();
        \$installer->batchInstall(\$params);
        echo 'Elgg 5.x installed successfully.' . PHP_EOL;
    " 2>&1 || echo "Install completed (check for errors above)."

    echo "Activating plugins..."
    php -r "
        require_once 'vendor/autoload.php';
        \$app = \Elgg\Application::getInstance();
        \$app->bootCore();
        _elgg_services()->plugins->generateEntities();

        // Resolve dep plugin IDs from elgg-plugin.php plugin.dependencies.
        // IDs are lowercased to match mod/ directory names.
        // Deps not present in mod/ are skipped with a warning.
        \$dep_ids = [];
        \$plugin_file = '/var/www/html/mod/${PLUGIN_ID}/elgg-plugin.php';
        if (file_exists(\$plugin_file)) {
            \$manifest = include \$plugin_file;
            foreach (array_keys(\$manifest['plugin']['dependencies'] ?? []) as \$id) {
                \$dep_ids[] = strtolower(\$id);
            }
        }

        foreach (\$dep_ids as \$dep_id) {
            \$dep = elgg_get_plugin_from_id(\$dep_id);
            if (!\$dep) {
                echo 'WARNING: dep plugin ' . \$dep_id . ' not in mod/ — skipping (not mounted).' . PHP_EOL;
                continue;
            }
            if (\$dep->isActive()) {
                echo 'Dep plugin ' . \$dep_id . ' already active.' . PHP_EOL;
                continue;
            }
            try {
                \$dep->activate();
                echo 'Dep plugin ' . \$dep_id . ' activated.' . PHP_EOL;
            } catch (\Throwable \$e) {
                echo 'FAILED to activate dep ' . \$dep_id . ': ' . \$e->getMessage() . PHP_EOL;
                exit(1);
            }
        }

        // Activate the main plugin.
        \$plugin = elgg_get_plugin_from_id('${PLUGIN_ID}');
        if (!\$plugin) {
            echo 'ERROR: plugin ${PLUGIN_ID} not found at /var/www/html/mod/${PLUGIN_ID}' . PHP_EOL;
            exit(1);
        }
        if (\$plugin->isActive()) {
            echo 'Plugin ${PLUGIN_ID} already active.' . PHP_EOL;
        } else {
            try {
                \$plugin->activate();
                echo 'Plugin ${PLUGIN_ID} activated.' . PHP_EOL;
            } catch (\Throwable \$e) {
                echo 'FAILED to activate ${PLUGIN_ID}: ' . \$e->getMessage() . PHP_EOL;
                exit(1);
            }
        }

        // elgg_clear_caches() resets view_locations so the plugin's views are found.
        // elgg_invalidate_caches() is a no-op for localFileCache — must use elgg_clear_caches().
        elgg_clear_caches();
        echo 'Caches cleared.' . PHP_EOL;

        // Enable registration for Playwright tests.
        elgg_save_config('allow_registration', true);
    " 2>&1 || echo "Plugin activation completed (check for errors above)."

    touch /var/www/html/.elgg-installed
    echo "Elgg 5.x setup complete."
fi

chown -R www-data:www-data /var/www/data

echo "Starting Apache..."
exec apache2-foreground
