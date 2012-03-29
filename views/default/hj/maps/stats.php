<?php
$entity = elgg_extract('entity', $vars);
$map_selected = elgg_echo('hj:maps:selected');
$map_noneselected = elgg_echo('hj:maps:noneselected');
$map_onthemap = elgg_echo('hj:maps:onthemap');

$html = <<<HTML
<div id="hj-entity-map-stats-{$entity->guid}" class="elgg-module elgg-module-info hj-padding-ten">
    <div class="elgg-head">$map_selected</div>
    <div class="hj-map-selected">$map_noneselected</div>
    <div class="elgg-head">$map_onthemap</div>    
    <div class="hj-map-onthemap"></div>

</div>

HTML;

echo $html;