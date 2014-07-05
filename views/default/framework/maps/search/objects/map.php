<?php

namespace hypeJunction\Maps;

$subtypes = array_intersect(get_mappable_object_subtypes(), get_input('mappable_subtypes', array()));
if (!count($subtypes)) {
	$subtypes = get_mappable_object_subtypes();
}
$vars['options']['subtypes'] = $subtypes;

echo ElggMap::showMap($vars);