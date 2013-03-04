<?php

if (!isset($vars['order_by'])) {
	$vars['order_by'] = 'priority';
}

echo elgg_view_menu('list_filter', $vars);