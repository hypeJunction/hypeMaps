<?php
$map_selected = elgg_echo('hj:maps:selected');
$map_noneselected = elgg_echo('hj:maps:noneselected');
$map_onthemap = elgg_echo('hj:maps:onthemap');
$nav = elgg_extract('nav', $vars);
$list_id = elgg_extract('list_id', $vars);

$html = <<<HTML
<div id="map-stats-{$list_id}" class="elgg-module elgg-module-info hj-padding-ten">
    <div class="elgg-head">$map_onthemap</div>    
    <div class="hj-map-onthemap"></div>
	$nav
</div>

HTML;

echo $html;