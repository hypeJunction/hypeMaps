<?php

elgg_load_js('hj.framework.ajax');
elgg_load_js('hj.maps.google');
elgg_load_js('hj.maps.base');

$markers = elgg_extract('markers', $vars, array());
$entity = elgg_extract('entity', $vars, rand(100,9999));
$width = elgg_extract('width', $vars, '100%');
$height = elgg_extract('height', $vars, '450px');

$useSessionLocation = get_input('useSessionLocation', null);

$params = hj_maps_process_markers($markers, array('useSessionLocation' => $useSessionLocation));

$html = <<<HTML
<div class="hj-ajaxed-map-static clearfix">
        <div id="hj-entity-map-{$entity->guid}" class="hj-map-full-page left" style="width:{$width};height:{$height};"></div>
</div>

HTML;

echo $html;
?>

<script type="text/javascript">

	var params = <?php echo json_encode($params) ?>;
	var id = "hj-entity-map-<?php echo $entity->guid ?>";

	window.hjdata.maps[id] = params;

</script>