<?php

namespace hypeJunction\Maps;

$vars['options_values'] = get_marker_types_options();

echo elgg_view('input/dropdown', $vars);