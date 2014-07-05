<?php

namespace hypeJunction\Maps;

$filter_context = elgg_extract('filter_context', $vars, 'search');

elgg_register_menu_item('filter:maps', array(
	'name' => "maps:search",
	'text' => elgg_echo('maps:search'),
	'href' => "maps/search",
	'selected' => ($filter_context == 'search'),
	'priority' => 100
));

echo elgg_view_menu('filter:maps', array(
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz elgg-menu-filter'
));
