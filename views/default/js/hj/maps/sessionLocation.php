<?php if (FALSE) : ?>
	<script type="text/javascript">
<?php endif; ?>

	elgg.provide('hj.maps.base');
	
	hj.maps.base.getSessionLocation = function() {
		if(navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
				window.sessionLocation = new Object();
				if (typeof google !== "undefined") {
					window.sessionLocation.coords = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
				}
				window.sessionLocation.lat = position.coords.latitude;
				window.sessionLocation.lng = position.coords.longitude;
				hj.maps.base.saveSessionLocation();
			});
			return true;
		} else if (typeof google !== "undefined" && google.gears) {
			var geo = google.gears.factory.create('beta.geolocation');
			geo.getCurrentPosition(function(position) {
				window.sessionLocation = new Object();
				window.sessionLocation.coords = new google.maps.LatLng(position.latitude, position.longitude);
				window.sessionLocation.lat = position.latitude;
				window.sessionLocation.lng = position.longitude;
				hj.maps.base.saveSessionLocation();
			});
			return true;
		}
		return false;
	}

	hj.maps.base.saveSessionLocation = function() {
		if (window.sessionLocation) {
			elgg.action('action/maps/setlocation', {
				data: {
					e:elgg.get_logged_in_user_guid,
					session_latitude:window.sessionLocation.lat,
					session_longitude:window.sessionLocation.lng
				},
				success : function() {
					elgg.trigger_hook('success', 'hj:framework:ajax');
				}
			});
		}
	}


	elgg.register_hook_handler('init', 'system', hj.maps.base.getSessionLocation, 200);

<?php if (FALSE) : ?></script><?php endif; ?>