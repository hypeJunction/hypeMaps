<?php

echo '<div class="hj-framework-list-head-wrapper">';
if (isset($vars['list_options']['filter'])) {
	echo '<div class="hj-framework-list-filter">';
	echo $vars['list_options']['filter'];
	echo '</div>';
}
echo '</div>';