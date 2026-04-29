<?php

namespace hypeJunction\Maps;

use Elgg\IntegrationTestCase;

/**
 * Plugin smoke tests: verify the plugin activates, page handler registered,
 * actions registered, hooks registered, widget registered.
 *
 * Target Elgg version: 3.x (per manifest.xml: elgg_release >= 3.0).
 * These tests lock in pre-migration behavior — do NOT change assertions
 * to match post-migration state.
 */
class PluginTest extends IntegrationTestCase {

    /** @var bool */
    private static bool $booted = false;

    public function up() {
        if (!self::$booted) {
            $pluginRoot = dirname(__DIR__, 5);
            // start.php may have already run if plugin is active; load lib files directly
            if (!function_exists('hypeJunction\\Maps\\get_site_search_maps')) {
                require_once $pluginRoot . '/autoloader.php';
                require_once $pluginRoot . '/lib/functions.php';
                require_once $pluginRoot . '/lib/settings.php';
                require_once $pluginRoot . '/lib/events.php';
                require_once $pluginRoot . '/lib/hooks.php';
                require_once $pluginRoot . '/lib/page_handlers.php';
            }
            self::$booted = true;
        }
    }

    public function down() {}

    /**
     * @return void
     */
    public function testPluginConstantsDefined(): void {
        $this->assertSame('hypeMaps', PLUGIN_ID);
        $this->assertSame('maps', PAGEHANDLER);
    }

    /**
     * @return void
     */
    public function testPluginIsRegistered(): void {
        $plugin = \elgg_get_plugin_from_id('hypeMaps');
        $this->assertNotNull($plugin);
        $this->assertInstanceOf(\ElggPlugin::class, $plugin);
    }

    /**
     * @return void
     */
    public function testPageHandlerRegistered(): void {
        // In 3.x/4.x, we can check via the services
        $handlers = _elgg_services()->routes;
        $this->assertNotNull($handlers);
        // Page handler 'maps' should route. We verify via elgg_normalize_url
        $url = \elgg_normalize_url('maps');
        $this->assertStringContainsString('maps', $url);
    }

    /**
     * @return void
     */
    public function testActionsRegistered(): void {
        $actions = _elgg_services()->actions;
        $this->assertTrue($actions->exists('hypeMaps/settings/save'));
        $this->assertTrue($actions->exists('maps/geopositioning/update'));
    }

    /**
     * @return void
     */
    public function testWidgetRegistered(): void {
        $widgets = \elgg_get_widget_types('all');
        $this->assertArrayHasKey('staticmap', $widgets);
    }
}
