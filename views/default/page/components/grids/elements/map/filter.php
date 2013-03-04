<?php

echo '<div class="hj-framework-list-filter hj-maps-location-filter">';
echo elgg_view_form('maps/filter', array(
	'action' => full_url(),
	'method' => 'GET',
	'disable_security' => true
), $vars);
echo '</div>';