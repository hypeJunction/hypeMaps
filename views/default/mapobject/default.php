<?php

$entity = elgg_extract('entity', $vars, false);
if (!$entity || !elgg_instanceof($entity)) {
    return true;
}

$entity = get_entity($entity->guid);
$icon = elgg_extract('icon', $vars, $entity->getIconURL());

if (!$title = $entity->title) {
    $title = $entity->name;
    if (!$title) {
        $title = elgg_echo('item:object:' . $entity->getSubtype());
    }
}

$data = elgg_clean_vars($vars);
$data = hj_framework_extract_params_from_params($data);
$data['full_view'] = true;
$data['fbox_x'] = '900';

$data = hj_framework_json_query($data);

elgg_register_menu_item('mapobject', array(
    'name' => 'distance',
    'text' => '<span class="hj-distance"></span>',
    'href' => false,
    'priority' => 100
));

elgg_register_menu_item('mapobject', array(
    'name' => 'details',
    'text' => elgg_echo('hj:maps:seedetails'),
    'rel' => 'fancybox',
    'data-options' => $data,
    'class' => 'hj-ajaxed-mapobject-preview',
    'href' => "action/framework/entities/view?e=$entity->guid",
    'is_action' => true,
    'priority' => 200
));

$menu = elgg_view_menu('mapobject', array(
    'entity' => $entity,
    'class' => 'elgg-menu-hz',
    'sort_by' => 'priority'
));

$html = <<<HTML
<div class="hj-map-entity clearfix">
    <div class="hj-map">
        <img src="$icon" />
    </div>
    <div class="hj-stats">
        <span class="hj-title">$title</span><br />
        <span class="hj-address">{$entity->getLocation()}</span>
        <span class="hj-extras">
            $menu
        </span>
    </div>
</div>
HTML;

echo $html;