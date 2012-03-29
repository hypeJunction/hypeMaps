<?php
$value = elgg_extract('value', $vars);
$entity = get_entity($value);

if (elgg_instanceof($entity)) {
    $latitude = $entity->getLatitude();
    $longitude = $entity->getLongitude();
}

$fieldset .= "<fieldset class=\"hj-maps-fieldset\">";
$fieldset .= "<legend>" . elgg_echo('hj:maps:coordinates:form:legend') . "</legend>";

$fieldset .= '<label>' . elgg_echo('hj:label:maps:latitude') . '</label>';
$fieldset .= elgg_view('input/text', array(
    'name' => "latitude",
    'value' => $latitude
));

$fieldset .= '<label>' . elgg_echo('hj:label:maps:longitude') . '</label>';
$fieldset .= elgg_view('input/text', array(
    'name' => "longitude",
    'value' => $longitude
));

$fieldset .= "</fieldset>";

echo $fieldset;