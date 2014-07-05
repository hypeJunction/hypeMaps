<?php

/**
 * Output the pin image
 * @uses $vars['value']  URL of the pin
 */

namespace hypeJunction\Maps;

$value = elgg_extract('value', $vars);
if (!$value) {
	return true;
}

unset($vars['value']);

if (isset($vars['img_class'])) {
	$img_class = $vars['img_class'];
	unset($vars['img_class']);
}
if (isset($vars['width'])) {
	$width = $vars['width'];
	unset($vars['width']);
}
if (isset($vars['height'])) {
	$height = $vars['height'];
	unset($vars['height']);
}
if (isset($vars['alt'])) {
	$alt = $vars['alt'];
	unset($vars['alt']);
}

$img_vars = array(
	'src' => $value,
	'class' => $img_class,
	'width' => $width,
	'height' => $height,
	'alt' => $alt,
);

$attrs = elgg_format_attributes($vars);
echo "<div $attrs>";
echo elgg_view('output/img', $img_vars);
echo '</div>';
