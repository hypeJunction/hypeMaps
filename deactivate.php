<?php

$subtypes = array(
	'hjplace' => 'hjPlace'
);

foreach ($subtypes as $subtype => $class) {
	update_subtype('object', $subtype);
}
