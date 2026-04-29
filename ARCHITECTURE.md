# hypeMaps — Architecture (Elgg 4.x)

## Overview

Elgg plugin that renders interactive Google Maps and proximity-search for entities. Provides a `mapbox` list type, marker icons per entity type, group map tools, and a geocoding cache.

## Entry Points

| File | Purpose |
|------|---------|
| `elgg-plugin.php` | Declarative config: bootstrap, routes, actions, hooks, widgets, upgrades |
| `classes/hypeJunction/Maps/Bootstrap.php` | `load()` defines constants + requires lib files; `init()` registers JS/CSS/group tools; `activate()` creates DB table |

## Key Classes

| Class | Purpose |
|-------|---------|
| `ElggMap` | Extends `ElggList`; holds map center, radius, and proximity options; renders via `page/components/mapbox` view |
| `ElggMapQuery` | Extends `ElggListQuery`; adds SQL ORDER BY and WHERE proximity clauses using either spatial index or metadata lat/long joins |

## Constants (defined in Bootstrap::load())

| Constant | Value |
|----------|-------|
| `hypeJunction\Maps\PLUGIN_ID` | `'hypemaps'` |
| `hypeJunction\Maps\PAGEHANDLER` | `'maps'` |
| `HYPEMAPS_METRIC_SYSTEM` | Plugin setting `metric_system` (default: `'SI'`) |
| `HYPEMAPS_SEARCH_RADIUS` | `0` |
| `HYPEMAPS_RELEASE` | Unix timestamp of 2.x release |

## Registered Hooks (elgg-plugin.php)

| Hook | Type | Handler |
|------|------|---------|
| `search:site` | `maps` | `setup_site_search_maps` — builds per-type map search config |
| `search:group` | `maps` | `setup_group_search_maps` — builds group-scoped search maps |
| `view` | `page/components/list`, `page/components/gallery` | `list_type_map_view` — swaps list for mapbox view when `list_type=mapbox` |
| `view` | `all` | `ajax_list_view` — intercepts mapbox AJAX partial renders |
| `entity:icon:url` | `user`, `object`, `group` | `get_marker_url` (priority 600) — returns PNG marker URL for `size=marker` |
| `register:menu:site` | `default` | `register_site_menu` — adds Maps link to site menu |
| `register:menu:owner_block` | `default` | `register_owner_block_menu` — adds map link to owner block |

## Routes

| Route | Path | Resource view |
|-------|------|---------------|
| `default:maps:search` | `/maps/search/{id?}` | `maps/search` |
| `default:maps:group` | `/maps/group/{group_guid}/{id?}` | `maps/group` |

## Actions

| Action | Access |
|--------|--------|
| `hypemaps/settings/save` | admin |
| `maps/geopositioning/update` | public |

## Widget

`staticmap` — registered for contexts `['profile', 'dashboard', 'groups']`, multiple allowed.

## Database

`{prefix}geocode_cache` — created in `Bootstrap::activate()`. Caches geocoded location strings to lat/long.

| Column | Type |
|--------|------|
| `id` | INT AUTO_INCREMENT PK |
| `location` | VARCHAR(255) UNIQUE |
| `lat` | DECIMAL(10,7) |
| `long` | DECIMAL(10,7) |

## Proximity Search

Two strategies depending on available tables:

1. **Spatial** (if `{prefix}entity_geometry` exists): Uses MySQL `GLength(LineStringFromWKB(...))` formula
2. **Metadata** (fallback): Joins `{prefix}metadata` twice on `geo:lat` and `geo:long`, uses Haversine formula

## JS/CSS Assets

| Handle | Type | Source |
|--------|------|--------|
| `google.maps` | JS | Google Maps API (key from plugin setting) |
| `jquery.sticky-kit` | JS | Bower vendor |
| `jquery.form` | JS | External |
| `maps.mapbox` | JS | Simple cache view `js/framework/maps/mapbox` |
| `maps` | CSS | Simple cache view `css/framework/maps/stylesheet` |

## Upgrade Scripts

| Class | Purpose |
|-------|---------|
| `Upgrades\EncodeSettingsAsJson` | Migrates `mappable_subtypes` and `markertypes` settings from `serialize()` format to JSON |

## Migration Notes (3.x → 4.x)

- `start.php` → `Bootstrap::load()` / `init()`
- `manifest.xml` → `elgg-plugin.php` `plugin` key
- All hook handlers converted to `\Elgg\Hook $hook` single-arg signature
- `elgg_load_css/js()` → `elgg_load_external_file()`
- `elgg_format_attributes()` → `elgg_format_element()`
- `get_current_language()` → `elgg_get_current_language()`
- `insert_data()` / `get_data()` → Doctrine DBAL `executeStatement()` / `executeQuery()`
- `ElggEntity::getLocation()/setLocation()` → `$entity->location` property
- `forward(REFERER)` → `elgg_ok_response()` / `elgg_error_response()`
- Plugin ID lowercased: `hypeMaps` → `hypemaps`
