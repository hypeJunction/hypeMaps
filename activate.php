<?php

$subtypes = array(
	'hjplace' => 'hjPlace'
);

foreach ($subtypes as $subtype => $class) {
	if (get_subtype_id('object', $subtype)) {
		update_subtype('object', $subtype, $class);
	} else {
		add_subtype('object', $subtype, $class);
	}
}

elgg_set_plugin_setting('metric_system', 'SI', 'hypeMaps');