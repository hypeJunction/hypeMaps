//<script>

	elgg.provide('elgg.maps');

	elgg.maps.markers = [];
	elgg.maps.infowindows = [];

	elgg.maps.init = function() {

		$(window).resize(function(e) {
			if ($('#maps-modal').length) {
				$('#maps-modal').dialog({
					position: {my: "center", at: "center", of: window}
				});
			}
		});

		if (typeof navigator === 'undefined') {
			$('.maps-find-me').hide();
		}

		$('.maps-find-me').live('click', elgg.maps.findMe);

		$('[data-mapbox]').live('initialize', elgg.maps.initMapbox);
		$('[data-mapbox]').each(function() {
			$(this).trigger('initialize');
		});

		$('[data-mapbox]').stick_in_parent({
			sticky_class: 'maps-stuck'
		});

		$('.maps-container .elgg-pagination li a').live('click', function(e) {
			e.preventDefault();
			var $elem = $(this);
			var $container = $elem.closest('.maps-container');

			elgg.post($elem.attr('href'), {
				dataType: 'html',
				data: {
					mapbox: true,
					hash: $container.find('[data-mapbox]').data('hash')
				},
				beforeSend: function() {
					$('body').addClass('maps-state-loading');
				},
				success: function(data) {
					var $output = $(data);

					$container.find('.maps-items').replaceWith($output.find('.maps-items'));
					$container.find('.elgg-pagination').replaceWith($output.find('.elgg-pagination').eq(0));
					$container.find('[data-mapbox]').trigger('initialize');
				},
				complete: function() {
					$('body').removeClass('maps-state-loading');
				}
			});
		});

		$('.maps-filter').live('submit', function(e) {
			e.preventDefault();

			var $elem = $(this);
			var $container = $('.maps-container');

			$elem.ajaxSubmit({
				dataType: 'html',
				data: {
					mapbox: true,
					//hash: $container.find('[data-mapbox]').data('hash')
				},
				beforeSend: function() {
					$('body').addClass('maps-state-loading');
				},
				success: function(data) {
					var $output = $(data);
					var data = $output.find('[data-mapbox]').data();
					$container.find('.maps-items').replaceWith($output.find('.maps-items'));
					$container.find('.elgg-pagination').replaceWith($output.find('.elgg-pagination').eq(0));
					$container.find('[data-mapbox]')
							.data('location', data.location)
							.data('lat', data.lat)
							.data('long', data.long)
							.trigger('initialize');
				},
				complete: function() {
					$('body').removeClass('maps-state-loading');
				}
			});
		});
	};

	elgg.maps.initMapbox = function(e) {

		var $map = $(this);
		if ($map.closest('.maps-sticky-container').length) {
			elgg.maps.scrollOrigOffsetY = $map.offset().top;
			document.onscroll = elgg.maps.scroll;
			$map.width($(document).width()).addClass('ready');
		}

		if (!$map.data('gmap')) {
			var gmap = new google.maps.Map($map[0], {
				center: new google.maps.LatLng($map.data('lat'), $map.data('long')),
				zoom: 13,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				overviewMapControl: false,
				panControl: false,
				streetViewControl: false,
				zoomControl: true,
				zoomControlOptions: {
					position: google.maps.ControlPosition.LEFT_TOP,
					style: google.maps.ZoomControlStyle.LARGE
				}
			});
			var markers = [];
			var infowindows = [];

			if (typeof elgg.maps.adsense_publisher_id === 'string') {
				var adUnitDiv = document.createElement('div');
				var adUnitOptions = {
					format: google.maps.adsense.AdFormat.HALF_BANNER,
					position: google.maps.ControlPosition.RIGHT_BOTTOM,
					map: gmap,
					visible: true,
					publisherId: elgg.maps.adsense_publisher_id,
					backgroundColor: '#F4F4F4',
					borderColor: '#E8E8E8',
					titleColor: '#000000',
					textColor: '#666666',
					urlColor: '#4690d6'
				};
				new google.maps.adsense.AdUnit(adUnitDiv, adUnitOptions);
			}

		} else {
			var gmap = $map.data('gmap');
			var markers = $map.data('markers');
			var infowindows = $map.data('infowindows');
			gmap.setCenter(new google.maps.LatLng($map.data('lat'), $map.data('long')));
		}

		// clear markers
		for (var i = 0; i < markers.length; i++) {
			markers[i].setMap(null);
		}

		// get new mappable objects
		var $mappable = $('[data-mappable]', $map.closest('.maps-container'));
		var zIndex = $mappable.length + 1;
		$map.data('zIndex', zIndex);

		$mappable.each(function() {

			var $elem = $(this);
			var infowindow = new google.maps.InfoWindow({
				content: $(this).clone(true, true).html(),
				maxWidth: 600
			});
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng($elem.data('lat'), $elem.data('long')),
				map: gmap,
				animation: google.maps.Animation.DROP,
				flat: true,
				icon: {
					url: $elem.data('pin'),
					size: google.maps.Size(20, 20)
				},
				title: $elem.data('title'),
				zIndex: 1
			});
			markers.push(marker);
			infowindows.push(infowindow);
			google.maps.event.addListener(marker, 'mouseover', function() {
				$elem.siblings().removeClass('elgg-state-highlighted');
				$elem.addClass('elgg-state-highlighted');
			});
			google.maps.event.addListener(infowindow, 'domready', function() {
				$('.elgg-lightbox').fancybox();
			});
			google.maps.event.addListener(marker, 'click', function() {
				for (var i = 0; i < infowindows.length; i++) {
					infowindows[i].close();
				}
				infowindow.open(gmap, marker);
			});

			$('.maps-item-pin', $elem).live('mouseover click', function(e) {
				zIndex++;
				marker.setZIndex(zIndex);
				gmap.setZoom(13);
				gmap.setCenter(marker.getPosition());
				$elem.siblings().removeClass('elgg-state-highlighted');
				$elem.addClass('elgg-state-highlighted');
			});
		});

		$map.data('gmap', gmap);
		$map.data('markers', markers);
		$map.data('infowindows', infowindows);
	};

	elgg.maps.scroll = function() {

		if ($(window).scrollTop() >= elgg.maps.scrollOrigOffsetY - 5) {
			$('.maps-sticky-container [data-mapbox]').addClass('sticky-map');
			$('.maps-sticky-container .maps-items').css('padding-top', 450);
		} else {
			$('.maps-sticky-container [data-mapbox]').removeClass('sticky-map');
			$('.maps-sticky-container .maps-items').css('padding-top', 0);
		}

	};

	elgg.maps.distanceIncrement = 0; // 500 m

	/**
	 * If the geopositioning of the session is not set, try to obtain it
	 * using the browser geolocation service
	 *
	 * @link http://nominatim.openstreetmap.org/reverse Uses nominatim reverse geocoding service
	 * @see elgg.maps.setGeopositioning for caching logic
	 * @param object position
	 * @returns void
	 */
	elgg.maps.findMe = function(e) {
		navigator.geolocation.getCurrentPosition(function(position) {
			if (typeof elgg.session.geopositioning === 'undefined') {
				elgg.session.geopositioning = {};
			}

			// Do not refresh position if distance is less than the increment constant
			if (elgg.maps.distance(position.coords.latitude, position.coords.longitude, elgg.session.geopositioning.latitude, elgg.session.geopositioning.longitude) > elgg.maps.distanceIncrement) {

				elgg.session.geopositioning.latitude = position.coords.latitude;
				elgg.session.geopositioning.longitude = position.coords.longitude;

				elgg.maps.setGeopositioning();
//				if ($('.maps-filter').length) {
//					if (typeof elgg.maps.geocoder === 'undefined') {
//						elgg.maps.geocoder = new google.maps.Geocoder();
//					}
//					var latlng = new google.maps.LatLng(elgg.session.geopositioning.latitude, elgg.session.geopositioning.longitude);
//					elgg.maps.geocoder.geocode({'latLng': latlng}, function(results, status) {
//						if (status === google.maps.GeocoderStatus.OK) {
//							if (results[1]) {
//								elgg.maps.setGeopositioning(results);
//							}
//						}
//					});
//				}
			} else {
				elgg.maps.setGeopositioning();
			}
		});
	};

	/**
	 * Calculate distance in metres between two geographicsl points
	 */
	elgg.maps.distance = function(lat1, lon1, lat2, lon2) {
		var radlat1 = Math.PI * lat1 / 180;
		var radlat2 = Math.PI * lat2 / 180;
		var radlon1 = Math.PI * lon1 / 180;
		var radlon2 = Math.PI * lon2 / 180;
		var theta = lon1 - lon2;
		var radtheta = Math.PI * theta / 180;
		var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
		dist = Math.acos(dist);
		dist = dist * 180 / Math.PI;
		dist = dist * 60 * 1.1515 * 1.609344 * 1000;
		return dist;
	};

	/**
	 * Update session geopositioning and set wall location input values
	 * This is used as a jsonp callbcack for nominatim lookup
	 * @param object data
	 * @returns void
	 */
	elgg.maps.setGeopositioning = function(data) {
		if (typeof elgg.session.geopositioning === 'undefined') {
			elgg.session.geopositioning = {};
		}

		if (data && data[0]) {
			elgg.session.geopositioning.location = data[0].formatted_address;
		}

		$('[data-mapbox]')
				.data('location', elgg.session.geopositioning.location)
				.data('lat', elgg.session.geopositioning.latitude)
				.data('long', elgg.session.geopositioning.longitude)
				.trigger('initialize');

//		elgg.action('maps/geopositioning/update', {
//			data: elgg.session.geopositioning,
//			success: function() {
//				$('.maps-filter')
//						.find('input[name="location[find]"]')
//						.val(elgg.session.geopositioning.location);
//
//				$('.maps-filter').trigger('submit');
//			}
//		});
	};

	elgg.register_hook_handler('init', 'system', elgg.maps.init);