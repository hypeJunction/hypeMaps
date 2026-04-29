<?php

namespace hypeJunction\Maps;

use Elgg\Event;
use Elgg\IntegrationTestCase;

/**
 * Tests for lib/hooks.php event handlers.
 */
class HooksTest extends IntegrationTestCase {

    public function up() {
        $pluginRoot = dirname(__DIR__, 5);
        if (!function_exists('hypeJunction\\Maps\\get_marker_url')) {
            require_once $pluginRoot . '/lib/functions.php';
            require_once $pluginRoot . '/lib/hooks.php';
        }
    }

    public function down() {}

    private function makeHook(string $name, string $type, $value = null, array $params = []): Event {
        return new Event(elgg(), $name, $type, $value, $params);
    }

    /**
     * @return void
     */
    public function testGetMarkerUrlReturnsNullWhenNotMarkerSize(): void {
        $user = $this->createUser();
        $hook = $this->makeHook('entity:icon:url', 'user', 'http://example.com/original.png', [
            'entity' => $user,
            'size' => 'small',
        ]);
        $result = get_marker_url($hook);
        $this->assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetMarkerUrlReturnsIconUrlForMarkerSize(): void {
        $user = $this->createUser();
        $hook = $this->makeHook('entity:icon:url', 'user', 'original', [
            'entity' => $user,
            'size' => 'marker',
        ]);
        $result = get_marker_url($hook);
        $this->assertIsString($result);
        $this->assertStringContainsString('.png', $result);
    }

    /**
     * @return void
     */
    public function testGetMarkerUrlRespectsMapiconOverride(): void {
        $user = $this->createUser();
        $user->mapicon = 'http://example.com/custom-icon.png';
        $hook = $this->makeHook('entity:icon:url', 'user', 'original', [
            'entity' => $user,
            'size' => 'marker',
        ]);
        $result = get_marker_url($hook);
        $this->assertSame('http://example.com/custom-icon.png', $result);
    }

    /**
     * @return void
     */
    public function testSetupSiteSearchMapsRespectsSettings(): void {
        $plugin = \elgg_get_plugin_from_id('hypemaps');
        if (!$plugin) {
            $this->markTestSkipped('hypemaps plugin not installed');
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

            $hook = $this->makeHook('search:site', 'maps', [], []);
            $result = setup_site_search_maps($hook);
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
    public function testSetupGroupSearchMapsReturnsNullForNonGroup(): void {
        $hook = $this->makeHook('search:group', 'maps', ['existing' => 'value'], ['entity' => null]);
        $result = setup_group_search_maps($hook);
        $this->assertNull($result);
    }

    /**
     * @return void
     */
    public function testSetupGroupSearchMapsWithGroup(): void {
        $plugin = \elgg_get_plugin_from_id('hypemaps');
        if (!$plugin) {
            $this->markTestSkipped('hypemaps plugin not installed');
        }
        $original = $plugin->getSetting('search_group_members');
        try {
            $plugin->setSetting('search_group_members', '1');
            $group = $this->createGroup();
            $hook = $this->makeHook('search:group', 'maps', [], ['entity' => $group]);
            $result = setup_group_search_maps($hook);
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
    public function testAjaxListViewReturnsNullWhenNotXhr(): void {
        $hook = $this->makeHook('view', 'page/components/list', 'original', ['vars' => []]);
        $result = ajax_list_view($hook);
        $this->assertNull($result);
    }
}
