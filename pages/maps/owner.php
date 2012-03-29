<?php

$user = elgg_get_logged_in_user_entity();

$type = get_input('type', null);
$subtype = get_input('subtype', null);
$owner = get_user_by_username(get_input('username'), $user->username);
$container = get_input('container_guid', null);
$markertype = get_input('markertype', null);

if ($type)
    $type = explode(',', $type);
if ($subtype) {
    $subtype = explode(',', $subtype);
    if (!in_array('object', $type)) {
        $type[] = 'object';
    }
}
if ($container)
    $container = explode(',', $container);
if ($markertype)
    $markertype = explode(',', $markertype);
$limit = get_input('limit', 0);
$offset = get_input('offset', 0);


$title = elgg_echo('hj:maps:places:owner', array($owner->name));
elgg_push_breadcrumb($title);

$entities = elgg_get_entities_from_metadata(array(
    'types' => $type,
    'subtypes' => $subtype,
    'owner_guids' => $owner->guid,
    'container_guids' => $container,
    'limit' => $limit,
    'offset' => $offset,
    'metadata_name_value_pairs' => array(
        array('name' => 'location', 'value' => '', 'operand' => '!='),
        array('name' => 'markertype', 'value' => $markertype)
    )
        ));

$params = array('entity' => $owner, 'markers' => $entities);

$map = elgg_view('hj/maps/map', $params);
$stats = elgg_view('hj/maps/stats', $params);

$content = elgg_view_layout('hj/dynamic', array('content' => array($map, $stats), 'grid' => array('8', '4')));

$sidebar = elgg_view('hj/maps/sidebar', $params);
$module = elgg_view_module('aside', $title, $content);

$content = elgg_view_layout('hj/profile', array(
    'content' => $module,
    'sidebar' => $sidebar,
        ));

$body = elgg_view_layout('one_column', array('content' => $content));

echo elgg_view_page($title, $body);
