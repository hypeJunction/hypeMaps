<?php

echo elgg_view_form('maps/filter/_default', array(
	'action' => current_page_url(),
	'method' => 'GET',
	'disable_security' => true,
	'class' => 'maps-filter'
		), $vars);