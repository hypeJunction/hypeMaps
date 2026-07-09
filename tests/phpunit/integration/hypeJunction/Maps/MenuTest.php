<?php

namespace hypeJunction\Maps;

use Elgg\Event;
use Elgg\IntegrationTestCase;

/**
 * Tests for lib/events.php menu handlers.
 *
 * On Elgg 7.x a register:menu handler's value is a plain array — handlers must
 * append with `$return[] = ElggMenuItem::factory(...)` and return the array,
 * NOT call `$return->add()` (which fatals). These tests lock in that contract.
 */
class MenuTest extends IntegrationTestCase {

	public function up() {
		$pluginRoot = dirname(__DIR__, 5);
		if (!function_exists('hypeJunction\\Maps\\register_site_menu')) {
			require_once $pluginRoot . '/lib/functions.php';
			require_once $pluginRoot . '/lib/hooks.php';
			require_once $pluginRoot . '/lib/events.php';
		}
	}

	public function down() {}

	private function makeHook(string $name, string $type, $value = null, array $params = []): Event {
		return new Event(elgg(), $name, $type, $value, $params);
	}

	/**
	 * register_site_menu appends an ElggMenuItem (name=maps, href=maps) to the
	 * array value and returns the array — the 7.x array-return contract.
	 *
	 * @return void
	 */
	public function testRegisterSiteMenuAppendsMenuItemToArray(): void {
		$existing = \ElggMenuItem::factory(['name' => 'existing', 'text' => 'Existing', 'href' => '#']);
		$hook = $this->makeHook('register:menu:site', 'default', [$existing]);

		$result = register_site_menu($hook);

		$this->assertIsArray($result);
		$this->assertCount(2, $result, 'handler must append to (not replace) the incoming menu array');

		$appended = end($result);
		$this->assertInstanceOf(\ElggMenuItem::class, $appended);
		$this->assertSame('maps', $appended->getName());
		$this->assertStringContainsString('maps', $appended->getHref());
	}

	/**
	 * register_owner_block_menu is a no-op (returns null) unless the page owner
	 * is a group.
	 *
	 * @return void
	 */
	public function testRegisterOwnerBlockMenuReturnsNullForNonGroup(): void {
		$user = $this->createUser();
		$hook = $this->makeHook('register:menu:owner_block', 'default', [], ['entity' => $user]);

		$this->assertNull(register_owner_block_menu($hook));
	}

	/**
	 * For a group page owner with an enabled group map, the handler appends a
	 * menu item whose href targets maps/group/{guid}/{id}.
	 *
	 * @return void
	 */
	public function testRegisterOwnerBlockMenuLinksToGroupMaps(): void {
		$plugin = \elgg_get_plugin_from_id('hypemaps');
		if (!$plugin) {
			$this->markTestSkipped('hypemaps plugin not installed');
		}
		$original = $plugin->getSetting('search_group_members');
		try {
			$plugin->setSetting('search_group_members', '1');
			$group = $this->createGroup();

			$hook = $this->makeHook('register:menu:owner_block', 'default', [], ['entity' => $group]);
			$result = register_owner_block_menu($hook);

			$this->assertIsArray($result);
			$hrefs = array_map(static fn (\ElggMenuItem $i): string => $i->getHref(), $result);
			$matching = array_filter($hrefs, static fn (string $h): bool => str_contains($h, "maps/group/{$group->guid}/group_members"));
			$this->assertNotEmpty($matching, 'group map menu item must link to maps/group/{guid}/group_members');
		} finally {
			$plugin->setSetting('search_group_members', (string) $original);
		}
	}
}
