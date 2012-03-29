<?php

function hj_maps_setup() {
    if (elgg_is_logged_in()) {
        elgg_set_plugin_setting('default_location', 'New York, NY, United States', 'hypeMaps');
        elgg_set_plugin_setting('default_zoom', '15', 'hypeMaps');
        
        elgg_set_plugin_setting('hj:maps:setup', true);
        return true;
    }
    return false;
}
