<?php

$default_location_label = 'To avoid errors and inconsistencies, please indicate a location that will be used by default for entities that do not have a location or can not be geolocated:';
$default_location = elgg_view('input/location', array(
    'name' => 'params[default_location]',
    'value' => $vars['entity']->default_location,
        ));

$default_zoom_label = 'Default map zoom level';
$default_zoom = elgg_view('input/text', array(
    'name' => 'params[default_zoom]',
    'value' => $vars['entity']->default_zoom,
        ));

$markertypes_label = 'Comma-separated list of marker types (if added, overwrites default values). Icons corresponding to each type should be added to hypeMaps/graphics/icons';
$markertypes = elgg_view('input/text', array(
    'name' => 'params[markertypes]',
    'value' => $vars['entity']->markertypes
));

$settings = <<<__HTML

    <h3>Defaults</h3>
    <div>
        <p><i>$default_location_label</i><br>$default_location</p>
        <p><i>$default_zoom_label</i><br>$default_zoom</p>
    </div>
    
    <hr>
        <h3>Marker Types</h3>
    <div>
        <p><i>$markertypes_label</i><br>$markertypes</p>
    </div>
    <hr>
</div>
__HTML;

echo $settings;