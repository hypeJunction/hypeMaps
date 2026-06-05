<?php

require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/lib/hooks.php';
require_once __DIR__ . '/lib/events.php';

return [
	'plugin' => [
		'name' => 'hypeMaps',
		'version' => '7.0.0',
	],
	'bootstrap' => \hypeJunction\Maps\Bootstrap::class,
	'routes' => [
		'default:maps:search' => [
			'path' => '/maps/search/{id?}',
			'resource' => 'maps/search',
			'defaults' => [
				'id' => null,
			],
		],
		'default:maps:group' => [
			'path' => '/maps/group/{group_guid}/{id?}',
			'resource' => 'maps/group',
			'requirements' => [
				'group_guid' => '\d+',
			],
			'defaults' => [
				'id' => null,
			],
		],
	],
	'actions' => [
		'hypemaps/settings/save' => [
			'access' => 'admin',
		],
		'maps/geopositioning/update' => [
			'access' => 'public',
		],
	],
	'events' => [
		'search:site' => [
			'maps' => [
				'hypeJunction\Maps\setup_site_search_maps' => [],
			],
		],
		'search:group' => [
			'maps' => [
				'hypeJunction\Maps\setup_group_search_maps' => [],
			],
		],
		'view' => [
			'page/components/list' => [
				'hypeJunction\Maps\list_type_map_view' => [],
			],
			'page/components/gallery' => [
				'hypeJunction\Maps\list_type_map_view' => [],
			],
			'all' => [
				'hypeJunction\Maps\ajax_list_view' => [],
			],
		],
		'entity:icon:url' => [
			'user' => [
				'hypeJunction\Maps\get_marker_url' => ['priority' => 600],
			],
			'object' => [
				'hypeJunction\Maps\get_marker_url' => ['priority' => 600],
			],
			'group' => [
				'hypeJunction\Maps\get_marker_url' => ['priority' => 600],
			],
		],
		'register:menu:site' => [
			'default' => [
				'hypeJunction\Maps\register_site_menu' => [],
			],
		],
		'register:menu:owner_block' => [
			'default' => [
				'hypeJunction\Maps\register_owner_block_menu' => [],
			],
		],
	],
	'widgets' => [
		'staticmap' => [
			'context' => ['profile', 'dashboard', 'groups'],
			'multiple' => true,
		],
	],
	'upgrades' => [
		\hypeJunction\Maps\Upgrades\EncodeSettingsAsJson::class,
	],
];
