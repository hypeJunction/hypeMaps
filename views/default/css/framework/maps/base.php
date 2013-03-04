<?php if (FALSE) : ?>
	<style type="text/css">
	<?php endif; ?>

	.hj-framework-map-view {
		background:transparent url(<?php echo elgg_get_site_url() ?>mod/hypeFramework/graphics/loader/circle.gif) no-repeat center center;
	}

	.hj-framework-map-view ul.hj-map-wrapper > li {
		display:none;
	}

	.elgg-module.elgg-module-onthemap {
		margin: 10px 0;
	}
	.elgg-module-onthemap > .elgg-body {
		padding: 10px;
	}
	.elgg-module-onthemap > .elgg-head {
		background: #f4f4f4;
		padding: 9px;
	}
	.hj-map-onthemap > li {
		margin: 3px;
		padding: 1px;
		border: 2px solid transparent;
	}

	.hj-map-onthemap > li:hover,
	.hj-map-onthemap > li.hover
	{
		cursor:pointer;
		margin: 3px;
		padding: 1px;
		border: 2px solid #666;
		-moz-box-shadow:2px 2px 5px #666;
		-webkit-box-shadow:2px 2px 5px #666;
		box-shadow:2px 2px 5px #666;
	}

	.hj-map-onthemap > li.elgg-state-highlighted,
	.hj-map-onthemap > li:hover.elgg-state-highlighted
	{
		margin: 3px;
		padding: 1px;
		border: 2px solid #0054A7;
		-moz-box-shadow:2px 2px 5px #0054A7;
		-webkit-box-shadow:2px 2px 5px #0054A7;
		box-shadow:2px 2px 5px #0054A7;
	}

	.hj-framework-list-wrapper .elgg-form-maps-filter {
		margin:10px 0;
		padding:10px;
		border:1px solid #e8e8e8;
	}

	.elgg-form-maps-filter select {
		padding:4px;
		font-size:12px;
	}

	.hj-map-anchor-distance {
		font-size: 0.7em;
		text-align: center;
		font-weight: bold;
	}

	.hj-maps-filter-location-cache, .hj-maps-filter-location, .hj-maps-filter-radius {
		display: inline-block;
	}

	.elgg-form-maps-filter select, .elgg-form-maps-filter input {
	   max-width: 200px;
	}
	