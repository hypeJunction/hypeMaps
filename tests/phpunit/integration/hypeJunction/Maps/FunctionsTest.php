<?php

namespace hypeJunction\Maps;

use Elgg\IntegrationTestCase;

/**
 * Tests for lib/functions.php library functions.
 *
 * Pre-migration behavioral lock-in for Elgg 3.x.
 */
class FunctionsTest extends IntegrationTestCase {

    public function up() {
        $pluginRoot = dirname(__DIR__, 5);
        if (!function_exists('hypeJunction\\Maps\\get_site_search_maps')) {
            require_once $pluginRoot . '/autoloader.php';
            require_once $pluginRoot . '/lib/functions.php';
            require_once $pluginRoot . '/lib/hooks.php';
        }
    }

    public function down() {}

    public function testGetSiteSearchMapsReturnsArray(): void {
        $maps = get_site_search_maps();
        $this->assertIsArray($maps);
    }

    public function testGetSiteSearchMapsSortedByPriority(): void {
        // Register a hook that returns two maps with distinct priorities
        $handler = function (\Elgg\Hook $hook) {
            $return = $hook->getValue();
            $return['high'] = ['title' => 'High', 'priority' => 100];
            $return['low'] = ['title' => 'Low', 'priority' => 999];
            return $return;
        };
        \elgg_register_plugin_hook_handler('search:site', 'maps', $handler);

        $maps = get_site_search_maps();

        \elgg_unregister_plugin_hook_handler('search:site', 'maps', $handler);

        $keys = array_keys($maps);
        $highIdx = array_search('high', $keys, true);
        $lowIdx = array_search('low', $keys, true);
        $this->assertNotFalse($highIdx);
        $this->assertNotFalse($lowIdx);
        $this->assertLessThan($lowIdx, $highIdx, 'Lower priority value sorted first');
    }

    public function testGetGroupSearchMapsReturnsArrayForNonGroup(): void {
        $maps = get_group_search_maps(null);
        $this->assertIsArray($maps);
    }

    public function testGetGroupSearchMapsWithGroup(): void {
        $group = $this->createGroup();
        $maps = get_group_search_maps($group);
        $this->assertIsArray($maps);
    }

    public function testGetMappableTypeSubtypePairsReturnsArray(): void {
        $pairs = get_mappable_type_subtype_pairs();
        $this->assertIsArray($pairs);
    }

    public function testGetMappableObjectSubtypesReturnsArray(): void {
        $subtypes = get_mappable_object_subtypes();
        $this->assertIsArray($subtypes);
    }

    /**
     * SECURITY REGRESSION: unserialize() on plugin settings.
     *
     * get_mappable_object_subtypes() calls unserialize() on the 'mappable_subtypes'
     * plugin setting. If a (admin) writer can place a serialized object with a
     * gadget chain (e.g. __destruct / __wakeup), this would be an RCE sink.
     *
     * This test locks in the CURRENT behavior (unserialize is called) so that
     * migration MUST replace it with json_decode or a safe-list of classes.
     * Post-migration the test should be updated to assert no unserialize().
     */
    public function testUnserializeOnMappableSubtypesIsInvokedLegacy(): void {
        $plugin = \elgg_get_plugin_from_id('hypeMaps');
        if (!$plugin) {
            $this->markTestSkipped('hypeMaps plugin not installed');
        }
        $original = $plugin->getSetting('mappable_subtypes');
        try {
            // Write a serialized ARRAY (safe payload — no objects)
            $plugin->setSetting('mappable_subtypes', serialize(['blog', 'file']));
            $result = get_mappable_object_subtypes();
            $this->assertIsArray($result);
            $this->assertSame(['blog', 'file'], $result);
        } finally {
            $plugin->setSetting('mappable_subtypes', (string) $original);
        }
    }

    /**
     * Same pattern for markertypes setting — also passes through unserialize().
     */
    public function testUnserializeOnMarkertypesIsInvokedLegacy(): void {
        $plugin = \elgg_get_plugin_from_id('hypeMaps');
        if (!$plugin) {
            $this->markTestSkipped('hypeMaps plugin not installed');
        }
        $original = $plugin->getSetting('markertypes');
        try {
            $plugin->setSetting('markertypes', serialize(['user', 'group']));
            $options = get_marker_types_options();
            $this->assertIsArray($options);
        } finally {
            $plugin->setSetting('markertypes', (string) $original);
        }
    }

    public function testGetMarkerIconsPathReturnsFilesystemPath(): void {
        $path = get_marker_icons_path(false);
        $this->assertIsString($path);
        $this->assertNotEmpty($path);
    }

    public function testGetMarkerIconsPathReturnsUrl(): void {
        $url = get_marker_icons_path(true);
        $this->assertIsString($url);
        $this->assertNotEmpty($url);
    }

    public function testGetMarkerTypesDefaultsReturnsArray(): void {
        $defaults = get_marker_types_defaults();
        $this->assertIsArray($defaults);
    }

    public function testGeopositioningDefaultsWhenUnset(): void {
        unset($_SESSION['geopositioning']);
        $pos = get_geopositioning();
        $this->assertIsArray($pos);
        $this->assertArrayHasKey('location', $pos);
        $this->assertArrayHasKey('latitude', $pos);
        $this->assertArrayHasKey('longitude', $pos);
        $this->assertSame('', $pos['location']);
        $this->assertSame(0, $pos['latitude']);
        $this->assertSame(0, $pos['longitude']);
    }

    public function testSetGeopositioningStoresInSession(): void {
        unset($_SESSION['geopositioning']);
        set_geopositioning('Berlin', 52.52, 13.405);
        $this->assertArrayHasKey('geopositioning', $_SESSION);
        $this->assertSame('Berlin', $_SESSION['geopositioning']['location']);
        $this->assertSame(52.52, $_SESSION['geopositioning']['latitude']);
        $this->assertSame(13.405, $_SESSION['geopositioning']['longitude']);
    }

    public function testGetGeopositioningReturnsStoredValue(): void {
        $_SESSION['geopositioning'] = ['location' => 'Paris', 'latitude' => 48.85, 'longitude' => 2.35];
        $pos = get_geopositioning();
        $this->assertSame('Paris', $pos['location']);
        $this->assertSame(48.85, $pos['latitude']);
        $this->assertSame(2.35, $pos['longitude']);
    }
}
