<?php

$shortcuts = hj_framework_path_shortcuts('hypeMaps');

elgg_register_action('hypeMaps/settings/save', $shortcuts['actions'] . 'settings/maps.php', 'admin');
elgg_register_action('edit/object/hjplace', $shortcuts['actions'] . 'edit/object/hjplace.php');