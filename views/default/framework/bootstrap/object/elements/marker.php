<?php

echo elgg_view('output/img', array(
	'src' => hj_maps_get_entity_marker($vars['entity'])
));