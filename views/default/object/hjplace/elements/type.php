<?php

$entity = elgg_extract('entity', $vars);
if ($entity->markertype) {
	echo elgg_echo("markertype:value:$entity->markertype");
}