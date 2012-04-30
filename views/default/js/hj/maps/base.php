<?php if (FALSE) : ?>
	<script type="text/javascript">
<?php endif; ?>

elgg.provide('hj.maps.base');

hj.maps.base.init = function() {
	if (!window.maps) {
		window.maps = new Object();
	}
	if (!window.markers) {
		window.markers = new Object();
	}
	$('.hj-ajaxed-map-single-popup')
	.unbind('click')
	.bind('click', hj.maps.base.popup);

	$('.hj-ajaxed-map-abstract-popup')
	.unbind('click')
	.bind('click', hj.maps.base.popup);

	$('.hj-ajaxed-map-static').each(function() {
		hj.maps.base.staticmap($(this));
	});

	$('#hj-maps-change-session-location')
	.unbind('submit')
	.bind('submit', function(event) {
		event.preventDefault();
		var address = $('input[name="address"]', $(this)).val();
		var list_id = $(this).attr('rel');

		var params = {
			address : address
		}

		new google.maps.Geocoder().geocode(params, function(result) {
			var coords = result[0]['geometry'].location;
			window.maps[list_id].setCenter(coords);
		});
	})

	$('#hj-maps-change-default-location')
	.unbind('submit')
	.bind('submit', function(event) {
		event.preventDefault();
		var address = $('input[name="temp_location"]', $(this)).val();
		var list_id = $(this).attr('rel');

		var params = {
			address : address
		}

		new google.maps.Geocoder().geocode(params, function(result) {
			var coords = result[0]['geometry'].location;
			window.maps[list_id].setCenter(coords);
		});

		elgg.action('action/maps/setlocation', {
			data : {
				temp_location : address
			}
		})
	})

	$('.hj-location-autocomplete')
	.each(function() {
		hj.maps.base.autocomplete($(this));
		$('.pac-container').css('z-index', 10000);
	})
		
	$('input[name="location"]')
	.each(function() {
		hj.maps.base.autocomplete($(this));
	})

}

hj.maps.base.autocomplete = function ($input) {
	var options = {};
	var input = $input.get(0);
	var autocomplete = new google.maps.places.Autocomplete(input, options);

}
hj.maps.base.popup = function(event) {
	event.preventDefault();

	var action = $(this).attr('href');

	$.fancybox({
		content : window.loader
	});

	elgg.action(action, {
		success : function(output) {
			var container = $("<div>")
			.css({'width' : 900})
			.html(output.output);

			$.fancybox({
				content : container,
				autoDimensions : true,
				onComplete : function() {
					elgg.trigger_hook('success', 'hj:framework:ajax');
				}
			});

			$.fancybox.resize();
		}
	});
}

hj.maps.base.staticmap = function(selector) {
	var container = selector.find('.hj-map-full-page:first').attr('rel');
	if (!window.maps) {
		window.maps = new Object();
	}
	$container_div = $('#map-container-' + container);

	if (!window.maps[container] || $container_div.html().length == 0) {
		// Initialize a new map
		window.markers[container] = new Array();
		var map = hj.maps.base.initMap(window.hjdata.lists[container].geo);
		window.maps[container] = map;
	} else {
		// Check if the container has a map
			
		// Update markers on the map
		hj.maps.base.setMarkers(window.maps[container], window.hjdata.lists[container].geo);
	}
}

hj.maps.base.initMap = function(data, container) {

	// Map has already been initialized
	if (window.maps[container]) {
		return window.maps[container];
	}

	if(data.center) {
		var latlng = new google.maps.LatLng (data.center.latitude, data.center.longitude);
	}

	var params = {
		zoom: <?php echo elgg_get_plugin_setting('default_zoom', 'hypeMaps') ?>,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		overviewMapControl : false,
		panControl : false,
		center : latlng,
		streetViewControl : false,
		zoomControl : true,
		zoomControlOptions : {
			position : google.maps.ControlPosition.RIGHT_TOP,
			style : google.maps.ZoomControlStyle.LARGE
		}
	}

	var map = new google.maps.Map(document.getElementById('map-container-' + data.container), params);

	if (data.markers) {
		hj.maps.base.setMarkers(map, data);
		var user_position;
		if (data.useSessionLocation === true) {
			if (data.user && data.user.temp_location) {
				user_position = new google.maps.LatLng(data.user.temp_location.latitude, data.user.temp_location.longitude);
			} else if (window.sessionLocation) {
				user_position = window.sessionLocation.coords;
			} else if (hj.maps.base.getSessionLocation()) {
				//
			} else if (elgg.is_logged_in()) {
				user_position = new google.maps.LatLng(data.user.location.latitude, data.user.location.longitude);
			} else {
				user_position = latlng;
			}
			map.setCenter(user_position);
		}
		google.maps.event.addListener(map, 'idle', function() {
			hj.maps.base.getMarkers(this, data);
		});
	} else {
		var center_marker = new google.maps.Marker({
			position: latlng,
			map: map
		});
	}

	return map;
}

hj.maps.base.setMarkers = function(map, data) {
	var markers = data.markers;
	var stats = '#map-stats-' + data.container;
	var user_position;

	if (data.user && data.user.temp_location) {
		user_position = new google.maps.LatLng(data.user.temp_location.latitude, data.user.temp_location.longitude);
	} else if (window.sessionLocation || hj.maps.base.getSessionLocation()) {
		user_position = window.sessionLocation.coords;
	} else if (elgg.is_logged_in()) {
		user_position = new google.maps.LatLng(data.user.location.latitude, data.user.location.longitude);
	}

	if (user_position) {
		if (!window.markers[data.container][data.user.entity.guid]) {
			var user_marker = new google.maps.Marker({
				position: user_position,
				map: map,
				icon: elgg.get_site_url() + 'mod/hypeMaps/graphics/icons/youarehere.png',
				title: data.user.entity.title,
				zIndex: 0
			});
			window.markers[data.container][data.user.entity.guid] = user_marker;
		}
	}

	if (markers) {
		for (var i = 0; i < markers.length; i++) {
			var params = markers[i];

			$container_div = $('#map-container-' + data.container);

			if (window.markers[data.container][params.entity.guid]) {
				continue;
			}

			var paramsLatLng = new google.maps.LatLng(params.location.latitude, params.location.longitude);

			if (user_position) {
				var distance = google.maps.geometry.spherical.computeDistanceBetween(paramsLatLng, user_position);
			}

			if (!isNaN(distance)) {
				distance = Math.round(distance);
				if (distance > 1000) {
					distance = Math.round(distance/100)/10 + elgg.echo('hj:measurements:km');
				} else {
					distance = distance + elgg.echo('hj:measurements:m');
				}
			} else {
				distance = '';
			}
			var paramsInfo = params.entity.tooltip;

			var marker = new google.maps.Marker({
				position: paramsLatLng,
				icon: params.entity.icon,
				title: params.entity.title,
				zIndex: i,
				entityInfo: paramsInfo,
				entityURL: params.entity.url,
				distance: distance
			});

			marker.setMap(map);
			window.markers[data.container][params.entity.guid] = marker;

			var entityInfoWindow = new google.maps.InfoWindow({
				content: 'holding...',
				maxWidth : 600,
				disableAutoPan : false
			});

			google.maps.event.addListener(marker, 'click', function() {
				entityInfoWindow.setContent(this.entityInfo);
				entityInfoWindow.open(map, this);
				window.infoWindow = entityInfoWindow;
				map.setCenter(this.position);
				$('.hj-map-selected', $(stats)).html(this.entityInfo);
				hj.framework.ajax.base.init();
			});
		}
	}
	$.each(window.markers[data.container], function(key, val) {
		var remove_marker = true;
		$.each(window.hjdata.lists[data.container].items, function(key1, val1) {
			if (key == val1) {
				remove_marker = false;
			}
		});
		if (remove_marker) {
			if (window.markers[data.container][key]) {
				window.markers[data.container][key].setMap(null);
				//window.markers[data.container][key] = null;
				//delete window.markers[data.container][key];
			}
			//				google.maps.event.addListener(map, 'idle', function() {
			//					hj.maps.base.getMarkers(this, data);
			//				});
		}
	});
	return true;
}

hj.maps.base.getMarkers = function(map, data) {
	var stats = '#map-stats-' + data.container;
	var onthemap = '.hj-map-onthemap';

	$(onthemap, $(stats)).html('');

	for (var i = 0; i < data.markers.length; i++) {
		var entity = data.markers[i];
		var entityLatLng = new google.maps.LatLng(entity.location.latitude, entity.location.longitude);
		var mapBounds = new google.maps.LatLngBounds(entityLatLng, entityLatLng);
		if (map.getBounds()) {
			mapBounds = map.getBounds();
		}
		if (mapBounds.contains(entityLatLng)) {
			$('.hj-map-onthemap', $(stats))
			.append(entity.entity.summary);
			$('.hj-ajaxed-mapobject-preview')
			.die()
			.live('click', hj.framework.ajax.base.view);
		}
	}

	$('.hj-map-entity')
	.each(function() {
		var id = $(this).attr('id').replace('hj-map-entity-', '');
		var marker = window.markers[data.container][id];
		var map = window.maps[data.container];

		$(this)
		.unbind('click')
		.bind('click', function() {
			if (window.infoWindow) {
				window.infoWindow.close();
			}
			google.maps.event.trigger(marker, 'click');
		})

		$(this)
		.unbind('mouseenter')
		.bind('mouseenter', function() {
			//map.setCenter(marker.getPosition());
			if (!window.mzindex) {
				window.mzindex = data.markers.length;
			}
			window.mzindex++;
			marker.setZIndex(window.mzindex);
		});

		$(this)
		.find('.hj-distance')
		.html(marker.distance);
	})
}

hj.maps.base.setStats = function(data) {
	var stats = $("<div>");
	var stats_onthemap = $("<div>");
	var stats_selected = $("<div>");

	stats
	.attr('id', 'map-stats-' + data.container)
	.addClass('elgg-module elgg-module-info hj-padding-ten')
	.append(stats_selected)
	.append(stats_onthemap);

	stats_onthemap
	.addClass('hj-map-onthemap')
	.html(elgg.echo('hj:maps:noneonthemap'))
	.before('<div class="elgg-head">' + elgg.echo('hj:maps:onthemap') + '</div>');

	stats_selected
	.addClass('hj-map-selected')
	.html(elgg.echo('hj:maps:noneselected'))
	.before('<div class="elgg-head">' + elgg.echo('hj:maps:selected') + '</div>');

	return stats;
}

hj.maps.base.updateLists = function(hook, type, params, value) {
	var list_id = params.list_id,
	data = params.data;
	if (data.geo) {
		var n_data = new Array();
		n_data.push(data);
		data = n_data;
		
		$.each(data, function(key, val) {
			window.hjdata.lists[list_id].geo.markers.push(val.geo);
			hj.maps.base.setMarkers(window.maps[list_id], window.hjdata.lists[list_id].geo, list_id);
		});
		google.maps.event.trigger(window.maps[list_id], 'idle');
	}
}

elgg.register_hook_handler('init', 'system', hj.maps.base.init);
elgg.register_hook_handler('success', 'hj:framework:ajax', hj.maps.base.init);
elgg.register_hook_handler('new_lists', 'hj:framework:ajax', hj.maps.base.updateLists);
<?php if (FALSE) : ?></script><?php endif; ?>
