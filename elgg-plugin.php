<?php

return [
	'plugin' => [
		'name' => 'hypeMaps',
		'version' => '3.0.0',
	],
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
];
