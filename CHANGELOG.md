<a name="5.0.0"></a>
# [5.0.0] (2026-04-29) — Elgg 5.x Migration

### Breaking Changes

* Requires Elgg ^5.0 and PHP >=8.2 (dropped support for Elgg 4.x)
* Hook event handlers now use `\Elgg\Event` (was `\Elgg\Hook`)
* `elgg-plugin.php` uses `events` key (was `hooks`)
* Language files return array directly (dropped `add_translation()`)
* Docker stack: PHP 8.2, MySQL 8.0

### Internal

* `elgg_trigger_plugin_hook()` → `elgg_trigger_event_results()` in functions.php
* `geocode_cache` table: backtick `\`long\`` column for MySQL 8.0 compatibility

---

<a name="4.0.0"></a>
# [4.0.0] (2026-04-29) — Elgg 4.x Migration

### Breaking Changes

* Requires Elgg ^4.0 (dropped support for Elgg 3.x)
* Removed `start.php` and `manifest.xml` — plugin config now in `elgg-plugin.php`
* Removed `autoloader.php` — replaced with PSR-4 autoload via composer
* Hook handlers now use `\Elgg\Hook` single-argument signature
* Plugin ID lowercased to `hypemaps` (was `hypeMaps`) per Elgg 4.x requirement
* Widget context `'all'` replaced with explicit `['profile', 'dashboard', 'groups']`

### Features

* Added `Bootstrap` class (`DefaultPluginBootstrap`) for all init logic
* `Bootstrap::activate()` creates `geocode_cache` table on plugin activation
* All hooks declared declaratively in `elgg-plugin.php`

### Bug Fixes

* Replaced removed `elgg_load_css/js()` with `elgg_load_external_file()`
* Replaced removed `elgg_format_attributes()` with `elgg_format_element()`
* Replaced removed `get_current_language()` with `elgg_get_current_language()`
* Replaced removed `forward(REFERER)` in actions with `elgg_ok_response()`
* Replaced removed `insert_data()`/`get_data()` with Doctrine DBAL calls
* Fixed Doctrine DBAL named parameter keys (no colon prefix)
* Fixed `ElggEntity::getLocation()`/`setLocation()` → property access

<a name="2.2.2"></a>
## [2.2.2](https://github.com/hypeJunction/hypeMaps/compare/2.2.1...v2.2.2) (2016-02-24)


### Bug Fixes

* **js:** use relative path from site url ([75604f7](https://github.com/hypeJunction/hypeMaps/commit/75604f7))



<a name="2.2.1"></a>
## [2.2.1](https://github.com/hypeJunction/hypeMaps/compare/2.2.0...v2.2.1) (2016-02-23)


### Bug Fixes

* **deps:** change constraint for api-lists ([6d0bfc1](https://github.com/hypeJunction/hypeMaps/commit/6d0bfc1))



<a name="2.2.0"></a>
# [2.2.0](https://github.com/hypeJunction/hypeMaps/compare/2.1.0...v2.2.0) (2016-02-23)


### Features

* **deps:** rework dependency management and autoloading ([ab1939f](https://github.com/hypeJunction/hypeMaps/commit/ab1939f))
* **grunt:** setup release automation ([2a8263c](https://github.com/hypeJunction/hypeMaps/commit/2a8263c))
* **lists:** no longer requires hypeLists and defines new dependency on api-lists ([9fc6f57](https://github.com/hypeJunction/hypeMaps/commit/9fc6f57))



