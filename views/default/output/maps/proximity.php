<?php

/**
 * Output the proximity/distance
 * @uses $vars['value']  Value in kilometers
 */

namespace hypeJunction\Maps;

$value = elgg_extract('value', $vars);
if (!$value) {
	return true;
}
unset($vars['value']);

$proximity_str = ElggMap::getProximity($value);
$attrs = elgg_format_attributes($vars);
echo "<div $attrs>$proximity_str</div>";
