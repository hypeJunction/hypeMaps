<?php

$entity = elgg_extract('entity', $vars);
$owner = $entity->getOwnerEntity();

if (!isset($entity->location)) {
	$entity->location = $owner->location;
}

if (!isset($entity->zoom)) {
	$entity->zoom = 13;
}

if (!isset($entity->pin_color)) {
	$entity->pin_color = 'red';
}

echo '<div>';
echo '<label>' . elgg_echo('maps:widget:staticmap:title') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'params[title]',
	'value' => $entity->title,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('maps:widget:staticmap:description') . '</label>';
echo elgg_view('input/plaintext', array(
	'name' => 'params[description]',
	'value' => $entity->description,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('maps:widget:staticmap:location') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'params[location]',
	'value' => $entity->location,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('maps:widget:staticmap:zoom') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[zoom]',
	'value' => $entity->zoom,
	'options' => range(0, 21),
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('maps:widget:staticmap:pin_color') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[pin_color]',
	'value' => $entity->pin_color,
	'options' => array('black', 'brown', 'green', 'purple', 'yellow', 'blue', 'gray', 'orange', 'red', 'white'),
));
echo '</div>';