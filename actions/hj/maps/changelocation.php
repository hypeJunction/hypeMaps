<?php

$rec = get_input('rec', 'perm');
$guid = get_input('e');
$entity = get_entity($guid);

if (!$entity) {
    exit;
}

switch ($rec) {
    case 'perm' :
        $form_body .= '<label>' . elgg_echo('hj:maps:permanentlocation') . '</label><br />';
        $form_body .= elgg_view('input/location', array(
            'name' => 'location',
            'value' => $entity->location
                ));
        break;

    case 'temp' :
        if (!$temp_location = $entity->temp_location) {
            $temp_location = $_SESSION['temp_location'];
        }
    $form_body .= '<label>' . elgg_echo('hj:maps:label:sessionlocation') . '</label><br />';
        $form_body .= elgg_view('input/location', array(
            'name' => 'temp_location',
            'value' => $temp_location
                ));
        break;
}
$form_body .= elgg_view('input/hidden', array('value' => $entity->guid, 'name' => 'e'));
$form_body .= elgg_view('input/hidden', array('value' => $rec, 'name' => $rec));
$form_body .= '<div>' . elgg_view('input/submit', array('value' => elgg_echo('submit'))) . '</div>';

$html = elgg_view('input/form', array(
    'body' => $form_body,
    'action' => 'action/maps/setlocation'
        ));

$output['data'] = $html;
print(json_encode($output));
return true;
