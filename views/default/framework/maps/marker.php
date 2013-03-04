<?php

$entity = elgg_extract('entity', $vars, false);

if (!elgg_instanceof($entity)) return true;

$type = $entity->getType();

echo elgg_view("framework/bootstrap/$type/elements/marker", $vars);