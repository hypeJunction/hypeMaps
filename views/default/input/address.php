<?php
$value = elgg_extract('value', $vars);
$entity = get_entity($value);

$fieldset .= "<fieldset class=\"hj-maps-fieldset\">";
$fieldset .= "<legend>" . elgg_echo('hj:maps:address:form:legend') . "</legend>";

$fieldset .= '<label>' . elgg_echo('hj:label:maps:street1') . '</label>';
$fieldset .= elgg_view('input/text', array(
    'name' => "address[street1]",
    'value' => ($entity->street1) ? $entity->street1 : $value['street1']
));

$fieldset .= '<label>' . elgg_echo('hj:label:maps:street2') . '</label>';
$fieldset .= elgg_view('input/text', array(
    'name' => "address[street2]",
    'value' => ($entity->street2) ? $entity->street2 : $value['street2']
));

$fieldset .= '<label>' . elgg_echo('hj:label:maps:city') . '</label>';
$fieldset .= elgg_view('input/text', array(
    'name' => "address[city]",
    'value' => ($entity->city) ? $entity->city : $value['city']
));

$fieldset .= '<label>' . elgg_echo('hj:label:maps:province') . '</label>';
$fieldset .= elgg_view('input/text', array(
    'name' => "address[address_province]",
    'value' => ($entity->province) ? $entity->province : $value['address_street1']
));

$fieldset .= '<label>' . elgg_echo('hj:label:maps:postal_code') . '</label>';
$fieldset .= elgg_view('input/text', array(
    'name' => "address[postal_code]",
    'value' => ($entity->postal_code) ? $entity->postal_code : $value['postal_code']
));

$country_options = hj_framework_get_country_list();
asort($country_options);
array_unshift($country_options, elgg_echo('hj:label:maps:country:select'));

$fieldset .= '<label>' . elgg_echo('hj:label:maps:country') . '</label>';
$fieldset .= elgg_view('input/dropdown', array(
    'name' => "address[address_country]",
    'value' => ($entity->country) ? $entity->country : $value['country'],
    'options_values' => $country_options
));
$fieldset .= "</fieldset>";

echo $fieldset;