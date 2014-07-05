<?php
/**
 * Add session geopositioning data to the config
 */

namespace hypeJunction\Maps;

$geopositioning = get_geopositioning();
?>

if (typeof elgg.session.geopositioning === 'undefined') {
	elgg.session.geopositioning = <?php echo json_encode($geopositioning) ?>;
}



