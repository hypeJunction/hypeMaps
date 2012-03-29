<?php

require_once (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/engine/start.php');

$guid = (int) get_input('e', 0);
$size = get_input('size', 'tiny');
$entity = get_entity($guid);

if (!$entity) {
    exit;
}
$type = $entity->getType();
$icon_url = $entity->getIconURL($size);
$icon = imagecreatefromjpeg($icon_url);
list($width, $height) = getimagesize($icon_url);
if ($width > 0 && $height > 0) {
    $new_width = 20;
    $new_height = 20;
} else {
    $new_width = 0;
    $new_height = 0;
}

$background = imagecreatefrompng(elgg_get_plugins_path() . "hypeMaps/graphics/icons/default_$type.png");
imagesavealpha($background, true);

$insert = imagecreatetruecolor($new_width, $new_height);
imagecopyresampled($insert, $icon, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

$insert_x = imagesx($insert);
$insert_y = imagesy($insert);
$xOffset = 6;
$yOffset = 6;
imagecopymerge($background, $insert, $xOffset, $yOffset, 0, 0, $insert_x, $insert_y, 100);


header("Content-type: image/png");
header('Expires: ' . date('r', time() + 864000));
header("Pragma: public");
header("Cache-Control: public");
header("Content-Length: " . strlen($contents));

imagepng($background, null, 0);