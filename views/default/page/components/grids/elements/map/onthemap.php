<?php

$onthemap = elgg_extract('content', $vars);

$onthemap = '<ul class="elgg-gallery hj-map-onthemap">' . $onthemap . '</ul>';

echo elgg_view_module('onthemap', elgg_echo('hj:maps:onthemap'), $onthemap);