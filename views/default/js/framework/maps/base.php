<?php if (FALSE) : ?>
	<script type="text/javascript">
<?php endif; ?>

	elgg.provide('framework.maps');
	elgg.provide('framework.maps.map');
	elgg.provide('framework.maps.marker');
	elgg.provide('framework.maps.center');

	framework.maps.init = function() {

		if (!framework.maps.map) {
			framework.maps.map = new Array();
		}

		framework.maps.initListMaps();
		framework.maps.initLocationMaps();

		$('.hj-maps-popup').live('click', framework.maps.popupTrigger);

	}

	framework.maps.initListMaps = function() {

		$('.hj-framework-map-view')
		.each(function() {

			var $container = $(this);
			var $map = $(this).find('.hj-map-wrapper').first();

			var list_id = $map.attr('id');

			if(framework.maps.map[list_id]) return;


			var markers = $map.children('li.elgg-item');
			markers.hide();

			var params = {
				zoom: 1,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				overviewMapControl : false,
				panControl : false,
				center : new google.maps.LatLng ($map.data('lat'), $map.data('long')),
				streetViewControl : false,
				zoomControl : true,
				zoomControlOptions : {
					position : google.maps.ControlPosition.RIGHT_TOP,
					style : google.maps.ZoomControlStyle.LARGE
				}
			}

			var map = new google.maps.Map(document.getElementById(list_id), params);
			framework.maps.map[list_id] = map;

			if (markers && markers.length > 0) {

				var data = {
					map : map,
					list_id : list_id,
					markers : markers
				}

				framework.maps.setMarkers(data);

				google.maps.event.addListener(map, 'idle', function() {
					framework.maps.getMarkers(data);
				});

			} else if (markers) {

				var item = markers.eq(0);

				var paramsLatLng = new google.maps.LatLng(item.data('lat'), item.data('long'));

				var marker = new google.maps.Marker({
					position: paramsLatLng,
					icon: item.data('marker'),
					title: item.data('title'),
					zIndex: 1,
					map: map
				});

				map.setCenter(paramsLatLng);
				map.setZoom(15);

			}
		});
	}

	framework.maps.initLocationMaps = function() {

		$('.hj-maps-location-map')
		.each(function() {

			var $map = $(this);
			var center = new google.maps.LatLng ($map.data('lat'), $map.data('long'));

			var params = {
				zoom: 15,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				overviewMapControl : false,
				panControl : false,
				center : center,
				streetViewControl : false,
				zoomControl : true,
				zoomControlOptions : {
					position : google.maps.ControlPosition.RIGHT_TOP,
					style : google.maps.ZoomControlStyle.LARGE
				}
			}

			var map = new google.maps.Map($map[0], params);

			var marker = new google.maps.Marker({
				position: center,
				icon: $map.data('icon'),
				title: $map.data('title'),
				zIndex: 1,
				map: map
			});

		})

	}

	framework.maps.setMarkers = function(data) {

		var markers = data.markers, map = data.map, list_id = data.list_id, $onthemap = $('.hj-map-onthemap', $('#' + list_id).closest('.hj-framework-map-view'));
		var updatedListItemUids = new Array();

		if (!framework.maps.marker[list_id]) {
			framework.maps.marker[list_id] = new Object();
		}

		if (!markers || markers.length <= 0) {
			$.each(framework.maps.marker[list_id], function(itemUid, marker) {
				marker.setMap(null);
			});
		} else {
			var latLngBounds = new google.maps.LatLngBounds();
			for (var i = 0; i < markers.length; i++) {

				var item = markers.eq(i);

				var uid = item.data('uid');
				var ts = item.data('ts');
				var paramsLatLng = new google.maps.LatLng(item.data('lat'), item.data('long'));
				var paramsInfo = item.html();

				updatedListItemUids.push(uid);
				latLngBounds.extend(paramsLatLng);

				if (!framework.maps.marker[list_id][uid]) {

					var marker = new google.maps.Marker({
						position: paramsLatLng,
						icon: item.data('marker'),
						title: item.data('title'),
						zIndex: i,
						entityInfo: paramsInfo,
						entityURL: item.data('url'),
						map: map,
						entityUid: uid,
						entityTs: ts
					});

					var entityInfoWindow = new google.maps.InfoWindow({
						content: 'holding...',
						maxWidth : 600,
						disableAutoPan : false
					});

					framework.maps.marker[list_id][uid] = marker;

					google.maps.event.addListener(marker, 'click', function() {
						entityInfoWindow.setContent(this.entityInfo);
						entityInfoWindow.open(map, this);
						window.infoWindow = entityInfoWindow;
						map.setCenter(this.position);
						$('li', $onthemap).removeClass('elgg-state-highlighted');
						$('li[data-uid=' + this.entityUid + ']', $onthemap).addClass('elgg-state-highlighted');
					});
					google.maps.event.addListener(marker, 'mouseover', function() {
						$('li', $onthemap).removeClass('hover');
						$('li[data-uid=' + this.entityUid + ']', $onthemap).addClass('hover');
					});
					google.maps.event.addListener(marker, 'mouseout', function() {
						$('li', $onthemap).removeClass('hover');
					});

				} else {
					marker = framework.maps.marker[list_id][uid];
					if (marker) {
						marker.setMap(map);
					}
				}
				map.fitBounds(latLngBounds);
			}
		}

		$.each(framework.maps.marker[list_id], function(itemUid, marker) {
			
			if ($.inArray(parseInt(itemUid), updatedListItemUids) < 0) {
				marker.setMap(null);
			}
			
		});
	}

	framework.maps.getMarkers = function(data) {

		var $onthemap = $('.hj-map-onthemap', data.container).eq(0);

		for (var i = 0; i < data.markers.length; i++) {
			var marker = data.markers.eq(i);
			var uid = marker.data('uid');
			var entityLatLng = new google.maps.LatLng(marker.data('lat'), marker.data('long'));
			var mapBounds = new google.maps.LatLngBounds(entityLatLng, entityLatLng);
			if (data.map.getBounds()) {
				mapBounds = data.map.getBounds();
			}
			//		if (mapBounds.contains(entityLatLng)) {
			//			$('[data-uid=' + uid + ']', $onthemap).show();
			//		} else {
			//			$('li[data-uid="' + uid + '"]', $onthemap).hide();
			//		}
		}

		$($onthemap)
		.children('li')
		.each(function() {
			var uid = $(this).data('uid');
			var marker = framework.maps.marker[data.list_id][uid];

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
		})
	}

	framework.maps.processUpdatedList = function(hook, type, updatedList) {
				
		var $markers = '';
		if (updatedList.items) {
			$markers = updatedList.items.join('');
		}
		var	list_id = updatedList.list_id,
		markers = $('<div>').html($markers),
		map = framework.maps.map[list_id];

		var data = {
			map : map,
			list_id : list_id,
			markers : markers.children('li')
		}

		$('.hj-framework-list-head-wrapper', $('#' + list_id).closest('.hj-framework-list-wrapper')).replaceWith(updatedList.head);
		$('.hj-framework-list-pagination-wrapper', $('#' + list_id).closest('.hj-framework-list-wrapper')).replaceWith(updatedList.pagination);
		$('.elgg-module-onthemap', $('#' + list_id).closest('.hj-framework-list-wrapper')).replaceWith(updatedList.onthemap);

		if (updatedList.center) {
			var latlng = new google.maps.LatLng(updatedList.center.latitude, updatedList.center.longitude);
			map.setCenter(latlng);
		}

		framework.maps.setMarkers(data);

		google.maps.event.addListener(map, 'idle', function() {
			framework.maps.getMarkers(data);
		});
	}

	framework.maps.loader = $('<div>').addClass('hj-ajax-loader').addClass('hj-loader-circle').hide();
	framework.maps.dialog = $('<div id="maps-dialog">');

	framework.maps.popupTrigger = function(e) {

		$element = $(this);
		$dialog = framework.maps.dialog;

		e.preventDefault;

		elgg.post($element.attr('href'), {
			data : {
				guid : $element.data('uid'),
				view : 'xhr',
				endpoint : 'content'
			},

			beforeSend : function() {
				$dialog
				.html(framework.maps.loader.show())
				.dialog({
					modal : true,
					dialogClass: 'hj-framework-dialog',
					title : elgg.echo('hj:framework:ajax:loading'),
					minWidth : 500,
					minHeight : 500,
					autoResize : true
				})
			},
			complete : function() {

			},
			success : function(response) {
				$dialog.dialog({ title : response.output.title });
				$dialog.html(response.output.body.content);

				elgg.trigger_hook('ajax:success', 'framework', { response : response });
				elgg.trigger_hook('maps:init', 'framework:maps');
			}
		
		})

		return false;
	}

	elgg.register_hook_handler('init', 'system', framework.maps.init);
	elgg.register_hook_handler('refresh:lists:map', 'framework', framework.maps.processUpdatedList);
	elgg.register_hook_handler('maps:init', 'framework:maps', framework.maps.initListMaps);
	elgg.register_hook_handler('maps:init', 'framework:maps', framework.maps.initLocationMaps);

<?php if (FALSE) : ?></script><?php endif; ?>
