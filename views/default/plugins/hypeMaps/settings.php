<?php

namespace hypeJunction\Maps;

$entity = elgg_extract('entity', $vars);

echo '<div>';
echo '<label>' . elgg_echo('maps:settings:params[google_api_key]') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('maps:settings:hint:google_api_key') . '</div>';
echo elgg_view('input/text', array(
	'name' => 'params[google_api_key]',
	'value' => $entity->google_api_key
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo("maps:settings:params[adsense_units]") . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('maps:settings:hint:adsense_units') . '</div>';
echo elgg_view('input/dropdown', array(
	'name' => "params[adsense_units]",
	'value' => $entity->adsense_units,
	'options_values' => array(
		0 => elgg_echo('disable'),
		1 => elgg_echo('enable')
	),
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('maps:settings:params[adsense_publisher_id]') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('maps:settings:hint:adsense_publisher_id') . '</div>';
echo elgg_view('input/text', array(
	'name' => 'params[adsense_publisher_id]',
	'value' => $entity->adsense_publisher_id
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('maps:settings:params[adsense_plugin_author_share]') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('maps:settings:hint:adsense_plugin_author_share') . '</div>';
echo elgg_view('input/text', array(
	'name' => 'params[adsense_plugin_author_share]',
	'value' => round((int)$entity->adsense_plugin_author_share, 0)
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('maps:settings:params[default_location]') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('maps:settings:hint:default_location') . '</div>';
echo elgg_view('input/text', array(
	'name' => 'params[default_location]',
	'value' => $entity->default_location
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('maps:settings:params[metric_system]') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('maps:settings:hint:metric_system') . '</div>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[metric_system]',
	'value' => $entity->metric_system,
	'options_values' => array(
		'SI' => elgg_echo('maps:settings:SI'),
		'US' => elgg_echo('maps:settings:US')
	),
));
echo '</div>';

$settings = array(
	'search_all',
	'search_users',
	'search_friends',
	'search_objects',
	'search_groups',
	'search_group_members',
	'search_group_content',
);

foreach ($settings as $s) {
	echo '<div>';
	echo '<label>' . elgg_echo("maps:settings:params[{$s}]") . '</label>';
	echo '<div class="elgg-text-help">' . elgg_echo("maps:settings:hint:$s") . '</div>';
	echo elgg_view('input/dropdown', array(
		'name' => "params[{$s}]",
		'value' => $entity->$s,
		'options_values' => array(
			0 => elgg_echo('disable'),
			1 => elgg_echo('enable')
		),
	));
	echo '</div>';
}

$registered_entities = elgg_get_config('registered_entities');
foreach ($registered_entities['object'] as $subtype) {
	$subtype_options[elgg_echo("item:object:$subtype")] = $subtype;
}

echo '<div>';
echo '<label>' . elgg_echo('maps:settings:params[mapptable_subtypes]') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('maps:settings:hint:mappable_subtypes') . '</div>';
echo elgg_view('input/checkboxes', array(
	'name' => 'params[mappable_subtypes]',
	'value' => ($entity->mappable_subtypes) ? unserialize($entity->mappable_subtypes) : array(),
	'options' => $subtype_options,
	'multiple' => true,
));
echo '</div>';


echo '<div>';
echo '<label>' . elgg_echo('maps:settings:params[icons_path]') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('maps:settings:hint:icons_path') . '</div>';
echo elgg_view('input/text', array(
	'name' => 'params[icons_path]',
	'value' => $entity->icons_path
));
echo '</div>';

$markertypes = get_marker_types_options();

echo '<div>';
echo '<label>' . elgg_echo('maps:settings:params[markertypes]') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('maps:settings:hint:markertypes') . '</div>';
$defaults = get_marker_types_defaults();
echo '<ul class="elgg-checkboxes elgg-horizontal">';
foreach ($defaults as $mt) {
	$icon = elgg_view('output/img', array(
		'src' => get_marker_icons_path(true) . "{$mt}.png",
		'width' => 25
	));
	echo '<li><label class="mam">' . elgg_view('input/checkbox', array(
		'name' => 'params[markertypes][]',
		'value' => $mt,
		'checked' => (array_key_exists($mt, $markertypes)),
	)) . $icon . elgg_echo("maps:marker:type:$mt") . '</label></li>';
}
echo '</ul>';
echo '</div>';
