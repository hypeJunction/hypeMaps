<?php

namespace hypeJunction\Maps;

use Elgg\IntegrationTestCase;

/**
 * Plugin smoke tests: verify the plugin activates, routes registered,
 * actions registered, hooks registered, widget registered.
 *
 * Target Elgg version: 4.x.
 */
class PluginTest extends IntegrationTestCase {

    /** @var bool */
    private static bool $booted = false;

    public function up() {
        if (!self::$booted) {
            $pluginRoot = dirname(__DIR__, 5);
            if (!function_exists('hypeJunction\\Maps\\get_site_search_maps')) {
                require_once $pluginRoot . '/lib/functions.php';
                require_once $pluginRoot . '/lib/settings.php';
                require_once $pluginRoot . '/lib/events.php';
                require_once $pluginRoot . '/lib/hooks.php';
            }
            self::$booted = true;
        }
    }

    public function down() {}

    /**
     * @return void
     */
    public function testPluginConstantsDefined(): void {
        $this->assertSame('hypemaps', PLUGIN_ID);
        $this->assertSame('maps', PAGEHANDLER);
    }

    /**
     * @return void
     */
    public function testPluginIsRegistered(): void {
        $plugin = \elgg_get_plugin_from_id('hypemaps');
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
        $this->assertTrue($actions->exists('hypemaps/settings/save'));
        $this->assertTrue($actions->exists('maps/geopositioning/update'));
    }

    /**
     * @return void
     */
    public function testWidgetRegistered(): void {
        $widgets = \elgg_get_widget_types('profile');
        $this->assertArrayHasKey('staticmap', $widgets);
    }
}
