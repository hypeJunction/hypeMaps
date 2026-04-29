<?php

namespace hypeJunction\Maps;

use Elgg\IntegrationTestCase;

/**
 * Tests for lib/hooks.php hook handlers.
 *
 * Pre-migration behavioral lock-in for Elgg 3.x.
 */
class HooksTest extends IntegrationTestCase {

    public function up() {
        $pluginRoot = dirname(__DIR__, 5);
        if (!function_exists('hypeJunction\\Maps\\get_marker_url')) {
            require_once $pluginRoot . '/autoloader.php';
            require_once $pluginRoot . '/lib/functions.php';
            require_once $pluginRoot . '/lib/hooks.php';
        }
    }

    public function down() {}

    /**
     * @return void
     */
    public function testGetMarkerUrlReturnsOriginalWhenNotMarkerSize(): void {
        $user = $this->createUser();
        $originalUrl = 'http://example.com/original.png';
        $result = get_marker_url('entity:icon:url', 'user', $originalUrl, [
            'entity' => $user,
            'size' => 'small',
        ]);
        $this->assertSame($originalUrl, $result);
    }

    /**
     * @return void
     */
    public function testGetMarkerUrlReturnsIconUrlForMarkerSize(): void {
        $user = $this->createUser();
        $result = get_marker_url('entity:icon:url', 'user', 'original', [
            'entity' => $user,
            'size' => 'marker',
        ]);
        $this->assertIsString($result);
        $this->assertStringContainsString('.png', $result);
    }

    /**
     * @return void
     */
    public function testGetMarkerUrlRespectsMapiconOverride(): void {
        $user = $this->createUser();
        $user->mapicon = 'http://example.com/custom-icon.png';
        $result = get_marker_url('entity:icon:url', 'user', 'original', [
            'entity' => $user,
            'size' => 'marker',
        ]);
        $this->assertSame('http://example.com/custom-icon.png', $result);
    }

    /**
     * @return void
     */
    public function testSetupSiteSearchMapsRespectsSettings(): void {
        $plugin = \elgg_get_plugin_from_id('hypeMaps');
        if (!$plugin) {
            $this->markTestSkipped('hypeMaps plugin not installed');
        }
        $originals = [
            'search_all' => $plugin->getSetting('search_all'),
            'search_users' => $plugin->getSetting('search_users'),
            'search_groups' => $plugin->getSetting('search_groups'),
        ];
        try {
            $plugin->setSetting('search_all', '1');
            $plugin->setSetting('search_users', '1');
            $plugin->setSetting('search_groups', '');

            $result = setup_site_search_maps('search:site', 'maps', [], []);
            $this->assertIsArray($result);
            $this->assertArrayHasKey('all', $result);
            $this->assertArrayHasKey('users', $result);
            $this->assertArrayNotHasKey('groups', $result);
            $this->assertSame('user', $result['users']['options']['types']);
        } finally {
            foreach ($originals as $k => $v) {
                $plugin->setSetting($k, (string) $v);
            }
        }
    }

    /**
     * @return void
     */
    public function testSetupGroupSearchMapsReturnsUnchangedForNonGroup(): void {
        $initial = ['existing' => 'value'];
        $result = setup_group_search_maps('search:group', 'maps', $initial, ['entity' => null]);
        $this->assertSame($initial, $result);
    }

    /**
     * @return void
     */
    public function testSetupGroupSearchMapsWithGroup(): void {
        $plugin = \elgg_get_plugin_from_id('hypeMaps');
        if (!$plugin) {
            $this->markTestSkipped('hypeMaps plugin not installed');
        }
        $original = $plugin->getSetting('search_group_members');
        try {
            $plugin->setSetting('search_group_members', '1');
            $group = $this->createGroup();
            $result = setup_group_search_maps('search:group', 'maps', [], ['entity' => $group]);
            $this->assertIsArray($result);
            $this->assertArrayHasKey('group_members', $result);
            $this->assertSame('user', $result['group_members']['options']['types']);
            $this->assertSame((int) $group->guid, (int) $result['group_members']['options']['relationship_guid']);
        } finally {
            $plugin->setSetting('search_group_members', (string) $original);
        }
    }

    /**
     * @return void
     */
    public function testAjaxListViewReturnsOriginalWhenNotXhr(): void {
        // Not in xhr context — should return original
        $result = ajax_list_view('view', 'page/components/list', 'original', ['vars' => []]);
        $this->assertSame('original', $result);
    }
}
