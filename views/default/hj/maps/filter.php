<?php

$entities = elgg_extract('entities', $vars, false);

if (!$entities) {
    return true;
}

$filter_fields = array('type', 'subtype', 'owner_guid', 'container_guid', 'markertype');

foreach ($filter_fields as $filter) {
    foreach ($entities as $entity) {
        if (!in_array($entity->$filter, $filter_values[$filter])) {
            $filter_values[$filter][] = $entity->$filter;
        }
    }
    asort($filter_values[$filter]);
}

foreach ($filter_values['type'] as $value) {
    if ($value && $value != 'site') {
        $filter_output['type'][elgg_echo("hj:maps:filter:$value")] = $value;
    }
}
foreach ($filter_values['subtype'] as $value) {
    $subtype = get_subtype_from_id($value);
    if ($value && (int) $value != 0) {
        $filter_output['subtype'][elgg_echo("item:object:$subtype")] = $subtype;
    }
}
foreach ($filter_values['owner_guid'] as $value) {
    $owner = get_entity($value);
    if (!$title = $owner->title) {
        $title = $owner->name;
    }
    if ($title && $value && (int) $value != 0 && !elgg_instanceof($owner, 'site')) {
        $filter_output['owner_guid'][$title] = $value;
    }
}
foreach ($filter_values['container_guid'] as $value) {
    $container = get_entity($value);
    if (!$title = $container->title) {
        $title = $container->name;
    }
    if ($title && $value && (int) $value != 0 && !elgg_instanceof($container, 'site') && !elgg_instanceof($owner, 'user')) {
        $filter_output['container_guid'][$title] = $value;
    }
}
foreach ($filter_values['markertype'] as $value) {
    if (!empty($value)) {
        $filter_output['markertype'][elgg_echo("markertype:value:$value")] = $value;
    }
}

foreach ($filter_output as $key => $value) {
    if (sizeof($value > 0)) {
        $form_body .= '<div>';
        $form_body .= '<label>' . elgg_echo("hj:maps:filter:$key") . '</label><br />';
        $form_body .= elgg_view('input/checkboxes', array(
            'name' => $key,
            'options' => $value,
            'value' => explode(',', get_input($key))
                ));
        $form_body .= '</div>';
    }
}
$form_body .= elgg_view('input/submit', array(
    'value' => elgg_echo('filter'),
        ));
$form_body .= elgg_view('output/url', array(
    'href' => 'places/all',
    'text' => elgg_echo('Clear'),
    'class' => 'hj-maps-submenu-link'
        ));



$module = elgg_view('input/form', array(
    'action' => 'action/maps/filter',
    'body' => $form_body
        ));

//$title = elgg_echo('hj:maps:filter');

echo elgg_view_module('info', '', $module);

