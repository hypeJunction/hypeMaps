<?php

$result = hj_framework_edit_object_action();

if ($result) {
	$entity = elgg_extract('entity', $result);
	
	print json_encode(array('guid' => $entity->guid));

	forward($result['forward']);

} else {
	forward(REFERER);
}
