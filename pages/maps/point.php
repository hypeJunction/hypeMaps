<?php
gatekeeper();

$guid = get_input('e');
$entity = get_entity($guid);

if (elgg_instanceof($entity)) {
    if(!$title = $entity->name) {
        $title = $entity->title;
    }
}

elgg_push_breadcrumb($title);

$map = elgg_view('hj/maps/map', array('entity' => $entity));
$stats = elgg_view('hj/maps/stats', array('entity' => $entity));

$content = elgg_view_layout('hj/dynamic', array('content' => array($map, $stats), 'grid' => array('8', '4')));

if ($entity->title) {
    $title = elgg_echo('hj:maps:owner', array($entity->title));
} else {
    $title = elgg_echo('hj:maps:owner', array($entity->name));
}

//$sidebar = elgg_view('hj/maps/sidebar', array('entity' => $entity));
$sidebar = elgg_view_entity($entity, array('full_view' => true));
$module = elgg_view_module('aside', $title, $content);
$module .= elgg_view_comments($entity);

$content = elgg_view_layout('hj/profile', array(
    'content' => $module,
    'sidebar' => $sidebar,
        ));

$body = elgg_view_layout('one_column', array('content' => $content));

echo elgg_view_page($title, $body);
