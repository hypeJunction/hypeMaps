<?php

echo elgg_view_form('maps/filter/objects', [
	'action' => elgg_get_current_url(),
	'method' => 'GET',
	'disable_security' => true,
	'class' => 'maps-filter'
], $vars);
