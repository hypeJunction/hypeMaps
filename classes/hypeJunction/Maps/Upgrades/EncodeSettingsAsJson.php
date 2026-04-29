<?php

namespace hypeJunction\Maps\Upgrades;

use Elgg\Upgrade\Batch;
use Elgg\Upgrade\Result;

/**
 * Re-encode hypeMaps plugin settings previously written by serialize()
 * as JSON. Affects the `mappable_subtypes` and `markertypes` settings.
 *
 * The runtime readers in lib/functions.php still accept legacy
 * serialize() payloads as a backward-compat fallback. Once this upgrade
 * has run on every site, the fallback can be removed in a future release.
 */
class EncodeSettingsAsJson implements Batch {

	private const SETTINGS = ['mappable_subtypes', 'markertypes'];

	/**
     * @return int
     */
    public function getVersion(): int {
		return 2026041200;
	}

	/**
     * @return bool
     */
    public function shouldBeSkipped(): bool {
		return false;
	}

	/**
     * @return bool
     */
    public function needsIncrementOffset(): bool {
		return false;
	}

	/**
     * @return int
     */
    public function countItems(): int {
		return Batch::UNKNOWN_COUNT;
	}

	/**
     * @param Result $result
     * @param mixed $offset
     * @return Result
     */
    public function run(Result $result, $offset): Result {
		$plugin = elgg_get_plugin_from_id('hypemaps');
		if (!$plugin) {
			$result->markComplete();
			return $result;
		}

		foreach (self::SETTINGS as $name) {
			$raw = $plugin->getSetting($name);
			if (!$raw) {
				continue;
			}

			$decoded = json_decode($raw, true);
			if (is_array($decoded)) {
				continue;
			}

			$decoded = @unserialize($raw, ['allowed_classes' => false]);
			if (!is_array($decoded)) {
				$result->addFailures();
				$result->addError("hypeMaps: setting '{$name}' is neither valid JSON nor a serialized array");
				continue;
			}

			$plugin->setSetting($name, json_encode($decoded));
			$result->addSuccesses();
		}

		$result->markComplete();
		return $result;
	}
}
