<?php
/**
 * Add session geopositioning data to the config
 */

namespace hypeJunction\Maps;

$geopositioning = get_geopositioning();
?>
//<script>
	if (typeof elgg.session.geopositioning === 'undefined') {
		elgg.session.geopositioning = <?php echo json_encode($geopositioning) ?>;
	}

	<?php
	if (!elgg_get_plugin_setting('adsense_units', PLUGIN_ID)) {
		return true;
	}
	?>

	elgg.provide('elgg.maps');
	elgg.maps.adsense_publisher_id = '<?php echo get_adsense_publisher_id() ?>';


