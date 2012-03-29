<?php
$value = elgg_extract('value', $vars);
$entity = get_entity($value);

$fieldset .= "<fieldset class=\"hj-maps-fieldset\">";
$fieldset .= "<legend>" . elgg_echo('hj:maps:address:form:legend') . "</legend>";

$fieldset .= '<label>' . elgg_echo('hj:label:maps:street1') . '</label>';
$fieldset .= elgg_view('input/text', array(
    'name' => "address_street1",
    'value' => $entity->street1
));

$fieldset .= '<label>' . elgg_echo('hj:label:maps:street2') . '</label>';
$fieldset .= elgg_view('input/text', array(
    'name' => "address_street2",
    'value' => $entity->street2
));

$fieldset .= '<label>' . elgg_echo('hj:label:maps:city') . '</label>';
$fieldset .= elgg_view('input/text', array(
    'name' => "address_city",
    'value' => $entity->city
));

$fieldset .= '<label>' . elgg_echo('hj:label:maps:province') . '</label>';
$fieldset .= elgg_view('input/text', array(
    'name' => "address_province",
    'value' => $entity->province
));

$fieldset .= '<label>' . elgg_echo('hj:label:maps:postal_code') . '</label>';
$fieldset .= elgg_view('input/text', array(
    'name' => "address_postal_code",
    'value' => $entity->postal_code
));

$fieldset .= '<label>' . elgg_echo('hj:label:maps:country') . '</label>';
$fieldset .= elgg_view('input/dropdown', array(
    'name' => "address_country",
    'value' => $entity->country,
    'options_values' => hj_framework_get_country_list()
));
$fieldset .= "</fieldset>";

echo $fieldset;