<?php

$entity = elgg_extract('entity', $vars);

if ($entity->description) {
	echo '<div>';
	echo elgg_view('output/longtext', array(
		'value' => $entity->description,
	));
	echo '</div>';
}

$src = "//maps.googleapis.com/maps/api/staticmap?center={$entity->location}&zoom={$entity->zoom}&size=300x300&markers=color:{$entity->pin_color}%7C{$entity->location}";

echo '<div>';
echo elgg_view('output/url', array(
	'text' => elgg_view('output/img', array(
		'src' => $src,
		'width' => 300,
		'height' => 300,
	)),
	'href' => "//maps.google.com/maps?q=$entity->location",
	'target' => '_blank'
));
echo '</div>';

echo '<div class="pam">';
echo elgg_view('output/maps/location', array(
	'value' => $entity->location,
	'class' => 'elgg-text-help',
));
echo '</div>';