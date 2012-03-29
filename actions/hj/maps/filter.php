<?php

$query['type'] = implode(',', get_input('type'));
$query['subtype'] = implode(',', get_input('subtype'));
$query['owner_guid'] = implode(',', get_input('owner_guid'));
$query['container_guid'] = implode(',', get_input('container_guid'));
$query['markertype'] = implode(',', get_input('markertype'));

$query = http_build_query($query);
forward("places/all?$query");