<?php

namespace hypeJunction\Maps;

use Elgg\IntegrationTestCase;
use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Result;
use hypeJunction\Maps\Upgrades\EncodeSettingsAsJson;

/**
 * Covers the EncodeSettingsAsJson upgrade batch and its 6.x/7.x migration
 * fixes:
 *
 *   - 1fcc6eb: Batch became an abstract class in 6.x — the upgrade now
 *     `extends AsynchronousUpgrade` and implements run(Result, $offset): Result.
 *   - 46d2ac6: countItems() references self::UNKNOWN_COUNT (was Batch::UNKNOWN_COUNT).
 *   - 362948f: legacy serialize()'d `mappable_subtypes` / `markertypes` settings
 *     are re-stored as JSON by the batch run.
 */
class UpgradeTest extends IntegrationTestCase {

	/** @var string[] Settings the batch operates on, mirrored from the SUT. */
	private const KEYS = ['mappable_subtypes', 'markertypes'];

	/** @var array<string,mixed> */
	private $saved = [];

	public function up() {
		$plugin = \elgg_get_plugin_from_id('hypemaps');
		if (!$plugin) {
			$this->markTestSkipped('hypemaps plugin not installed');
		}
		foreach (self::KEYS as $k) {
			$this->saved[$k] = $plugin->getSetting($k);
		}
	}

	public function down() {
		$plugin = \elgg_get_plugin_from_id('hypemaps');
		if (!$plugin) {
			return;
		}
		foreach ($this->saved as $k => $v) {
			if ($v === null || $v === false) {
				$plugin->unsetSetting($k);
			} else {
				$plugin->setSetting($k, (string) $v);
			}
		}
	}

	/**
	 * 1fcc6eb: extends the abstract AsynchronousUpgrade (not `implements Batch`).
	 *
	 * @return void
	 */
	public function testUpgradeIsAsynchronous(): void {
		$u = new EncodeSettingsAsJson();
		$this->assertInstanceOf(AsynchronousUpgrade::class, $u);
		$this->assertFalse($u->shouldBeSkipped());
		$this->assertFalse($u->needsIncrementOffset());
	}

	/**
	 * getVersion() is the fixed date-stamp; countItems() returns the inherited
	 * UNKNOWN_COUNT constant (proves the self::UNKNOWN_COUNT reference resolves).
	 *
	 * @return void
	 */
	public function testUpgradeMetadata(): void {
		$u = new EncodeSettingsAsJson();
		$this->assertSame(2026041200, $u->getVersion());
		$this->assertSame(EncodeSettingsAsJson::UNKNOWN_COUNT, $u->countItems());
	}

	/**
	 * 362948f: run() re-encodes both legacy serialize()'d settings as JSON and
	 * returns a Result recording one success per converted setting.
	 *
	 * @return void
	 */
	public function testRunConvertsLegacySerializedToJson(): void {
		$plugin = \elgg_get_plugin_from_id('hypemaps');
		$legacy_subtypes = ['blog', 'file'];
		$legacy_markers = ['user', 'group'];

		$plugin->setSetting('mappable_subtypes', serialize($legacy_subtypes));
		$plugin->setSetting('markertypes', serialize($legacy_markers));

		$u = new EncodeSettingsAsJson();
		$result = $u->run(new Result(), 0);

		$this->assertInstanceOf(Result::class, $result);
		$this->assertSame(2, $result->getSuccessCount());
		$this->assertSame(0, $result->getFailureCount());

		$subtypes_after = $plugin->getSetting('mappable_subtypes');
		$markers_after = $plugin->getSetting('markertypes');

		$this->assertSame($legacy_subtypes, json_decode($subtypes_after, true));
		$this->assertSame($legacy_markers, json_decode($markers_after, true));
	}
}
