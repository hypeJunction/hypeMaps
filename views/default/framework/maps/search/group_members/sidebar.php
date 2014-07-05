<?php

echo elgg_view_form('maps/filter/users', array(
	'action' => current_page_url(),
	'method' => 'GET',
	'disable_security' => true,
	'class' => 'maps-filter'
		), $vars);