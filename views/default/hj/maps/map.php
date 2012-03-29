<?php

elgg_load_js('hj.framework.ajax');
elgg_load_js('hj.maps.google');
elgg_load_js('hj.maps.base');

$entity = elgg_extract('entity', $vars, false);
$markers = elgg_extract('markers', $vars, false);
$width = elgg_extract('width', $vars, '100%');
$height = elgg_extract('height', $vars, '450px');

$useSessionLocation = get_input('useSessionLocation', null);

$e = array($entity->guid);
if (is_array($markers)) {
    foreach ($markers as $ent) {
        $e[] = $ent->guid;
    }
}
$e = implode(',',$e);
$params_input = elgg_view('input/hidden', array('value' => "action/maps/getter?e=$e&sl=$useSessionLocation", 'name' => 'map_params'));

$html = <<<HTML
<div class="hj-ajaxed-map-static clearfix">
        <div id="hj-entity-map-{$entity->guid}" class="hj-map-full-page left" style="width:{$width};height:{$height};"></div>
    $params_input
</div>

HTML;

echo $html;