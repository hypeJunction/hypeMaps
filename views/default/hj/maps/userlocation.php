<?php

$user = elgg_extract('entity', $vars, false);

if (!$user) {
    return true;
}

$userlocation = $user->location;

elgg_register_menu_item('userlocation', array(
    'name' => 'change',
    'text' => elgg_echo('hj:maps:changelocation'),
    'href' => "action/maps/changelocation?e=$user->guid&rec=perm",
    'is_action' => true,
    'rel' => 'fancybox',
    'class' => 'hj-ajaxed-edit hj-maps-submenu-link'
));

//$title = elgg_echo('hj:maps:userlocation');
$content = $userlocation;
$content .= elgg_view_menu('userlocation', array(
    'entity' => $user,
    'class' => 'elgg-menu-hz right'
));

echo elgg_view_module('info', $title, $content, $footer);