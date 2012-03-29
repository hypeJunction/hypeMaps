<?php

/**
 * Maps owner block
 */
$user = elgg_get_logged_in_user_entity();

$session_location = $_SESSION['location'];
if ($temp_location = $user->temp_location) {
    $session_location = $temp_location;
}
$userlocation = $user->location;

if ($session_location) {
    elgg_register_menu_item('page', array(
        'name' => 'sessionlocation',
        'title' => elgg_echo('hj:maps:sessionlocation'),
        'text' => elgg_echo('hj:maps:sessionlocation'),
        'priority' => 100
    ));
    elgg_register_menu_item('page', array(
        'name' => 'sessionlocation:details',
        'parent_name' => 'sessionlocation',
        'text' => elgg_view('hj/maps/sessionlocation', array('entity' => $user)),
        'class' => 'hj-maps-menu-child',
        'href' => false
    ));
}


elgg_register_menu_item('page', array(
    'name' => 'userlocation',
    'title' => elgg_echo('hj:maps:userlocation'),
    'text' => elgg_echo('hj:maps:userlocation'),
    'priority' => 200
));
elgg_register_menu_item('page', array(
    'name' => 'userlocation:details',
    'parent_name' => 'userlocation',
    'text' => elgg_view('hj/maps/userlocation', array('entity' => $user)),
    'class' => 'hj-maps-menu-child',
    'href' => false
));



$markers = elgg_extract('markers', $vars, false);

if ($markers) {
    elgg_register_menu_item('page', array(
        'name' => 'markerfilter',
        'title' => elgg_echo('hj:maps:markerfilter'),
        'text' => elgg_echo('hj:maps:markerfilter'),
        'priority' => 300
    ));
    elgg_register_menu_item('page', array(
        'name' => 'markerfilter:details',
        'parent_name' => 'markerfilter',
        'text' => elgg_view('hj/maps/filter', array('entities' => $markers)),
        'class' => 'hj-maps-menu-child',
        'href' => false
    ));
}

elgg_load_js('hj.framework.fieldcheck');
$form = hj_framework_get_data_pattern('object', 'hjplace');
elgg_register_menu_item('page', array(
    'name' => 'addnewplace',
    'title' => elgg_echo('hj:maps:addnew'),
    'text' => elgg_echo('hj:maps:addnew'),
    'href' => "action/framework/entities/edit?f=$form->guid&ajaxify=0",
    'is_action' => true,
    'rel' => 'fancybox',
    'id' => "hj-ajaxed-add-hjplace",
    'class' => "hj-ajaxed-add",
    'target' => "",
    'priority' => 400
));

//elgg_register_menu_item('page', array(
//    'name' => 'addnewplace:details',
//    'parent_name' => 'addnewplace',
//    'text' => elgg_view_entity($form, array('params' => $params)),
//    'class' => 'hj-maps-menu-child',
//    'href' => false
//));


$content_menu = elgg_view_menu('page', array(
    'entity' => $user,
    'class' => 'profile-content-menu',
    'context' => elgg_get_context(),
    'sort_by' => 'priority'
        ));

echo <<<HTML
        <div id="hj-maps-owner-block-$user->guid" class="hj-maps-owner-block">
                $content_menu
</div>

HTML;
