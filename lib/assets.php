<?php

// CSS and JS
$current_language = get_current_language();
$google_url = "http://maps.googleapis.com/maps/api/js?libraries=geometry,adsense,places&sensor=true&language={$current_language}&output=svembed";
elgg_register_js('maps.google.js', $google_url);

elgg_register_css('maps.base.css', elgg_get_simplecache_url('css', 'framework/maps/base'));
elgg_register_simplecache_view('css/framework/maps/base');

elgg_register_js('maps.base.js', elgg_get_simplecache_url('js', 'framework/maps/base'));
elgg_register_simplecache_view('js/framework/maps/base');