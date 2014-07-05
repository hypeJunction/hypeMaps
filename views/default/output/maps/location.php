<?php

namespace hypeJunction\Maps;

$value = elgg_extract('value', $vars, '');

if (!$value) {
	return true;
}

echo elgg_view('output/url', array(
	'text' => $value,
	'href' => "//maps.google.com/maps?q=$value",
	'target' => '_blank'
));
