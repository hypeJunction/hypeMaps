<?php

$user = elgg_extract('entity', $vars, false);

if (!$user) {
    return true;
}

$session_location = $_SESSION['location'];
if ($temp_location = $user->temp_location) {
    $session_location = $temp_location;
}

elgg_register_menu_item('sessionlocation', array(
    'name' => 'change',
    'text' => elgg_echo('hj:maps:changelocation'),
    'href' => "action/maps/changelocation?e=$user->guid&rec=temp",
    'is_action' => true,
    'rel' => 'fancybox',
    'class' => 'hj-ajaxed-edit hj-maps-submenu-link'
));

elgg_register_menu_item('sessionlocation', array(
    'name' => 'detect',
    'text' => elgg_echo('hj:maps:detectlocation'),
    'href' => "action/maps/setlocation?e=$user->guid&rec=reset_temp",
    'is_action' => true,
    'class' => 'hj-maps-submenu-link'
    
));

//$title = elgg_echo('hj:maps:sessionlocation');
$content = $session_location;
$content .= elgg_view_menu('sessionlocation', array(
    'entity' => $user,
    'class' => 'elgg-menu-hz right'
));

echo elgg_view_module('info', $title, $content, $footer);
