<?php if (FALSE) : ?>
    <script type="text/javascript">
<?php endif; ?>
    elgg.provide('hj.maps.base');

    hj.maps.base.init = function() {
        window.loader = '<div class="hj-ajax-loader hj-loader-circle"></div>';
        hj.maps.base.getSessionLocation();

        $('.hj-ajaxed-map-single-popup')
        .unbind('click')
        .bind('click', hj.maps.base.popup);

        $('.hj-ajaxed-map-abstract-popup')
        .unbind('click')
        .bind('click', hj.maps.base.popup);

        if (!window.mapInit) {
            $('.hj-ajaxed-map-static').each(function() {
                hj.maps.base.staticmap($(this));
            });
        }

    }

    hj.maps.base.getSessionLocation = function() {
        if(navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                window.sessionLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            });
        } else if (google.gears) {
            var geo = google.gears.factory.create('beta.geolocation');
            geo.getCurrentPosition(function(position) {
                window.sessionLocation = new google.maps.LatLng(position.latitude, position.longitude);
            });
        }
        return true;
    }

    hj.maps.base.popup = function(event) {
        event.preventDefault();

        var action = $(this).attr('href');

        $.fancybox({
            content : window.loader
        });

        elgg.action(action, {
            success : function(output) {

                var container = $("<div>");
                output = output.output;
                container
                .attr('id', 'hj-entity-map-' + output.center.id)
                .css({'width':'500px', 'height':'500px', 'float':'left'});

                var popout_width = '500';

                if (output.markers) {
                    var stats = hj.maps.base.setStats(output);
                    popout_width = '800';
                }

                $.fancybox({
                    content : container,
                    autoDimensions : false,
                    width : popout_width,
                    height : '500',
                    onComplete : function() {
                        var map = hj.maps.base.initMap(output);
                        elgg.trigger_hook('success', 'hj:framework:ajax');
                        if (output.markers) {
                            container.after(stats);
                        }
                    }
                });

                $.fancybox.resize();
            }
        });
    }

    hj.maps.base.staticmap = function(selector) {
        var action = selector.find('input[name="map_params"]').val();
        var container = $("div[id^=hj-entity-map-]", selector);

        container.html(window.loader);

        elgg.action(action, {
            success : function(output) {
                container.html('');

                var map = hj.maps.base.initMap(output.output);
                //elgg.trigger_hook('success', 'hj:framework:ajax');

            }
        });
    }

    hj.maps.base.initMap = function(data) {
        if(data.center) {
            var latlng = new google.maps.LatLng (data.center.latitude, data.center.longitude);
        }
        var params = {
            zoom: <?php echo elgg_get_plugin_setting('default_zoom', 'hypeMaps') ?>,
            center: latlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }

        var map = new google.maps.Map(document.getElementById('hj-entity-map-' + data.center.id), params);
        window.mapInit = true;

        if (data.markers) {
            hj.maps.base.setMarkers(map, data);
            google.maps.event.addListener(map, 'idle', function() {
                hj.maps.base.getMarkers(this, data);
            });
            if (window.sessionLocation && data.center.useSessionLocation) {
                map.setCenter(window.sessionLocation);
            }
        } else {
            var center_marker = new google.maps.Marker({
                position: latlng,
                map: map
            });
        }
        if (window.sessionLocation) {
            elgg.action('action/maps/setlocation', {
                data: {
                    e:elgg.get_logged_in_user_guid,
                    session_latitude:window.sessionLocation.lat(),
                    session_longitude:window.sessionLocation.lng()
                }
            });
        }
    }

    hj.maps.base.setMarkers = function(map, data) {

        var markers = data.markers;
        var stats = '#hj-entity-map-stats-' + data.center.id;
        var user_position;

        if (data.user.temp_location) {
            user_position = new google.maps.LatLng(data.user.temp_location.latitude, data.user.temp_location.longitude);
        } else if (window.sessionLocation) {
            user_position = window.sessionLocation;
        } else {
            user_position = new google.maps.LatLng(data.user.location.latitude, data.user.location.longitude);
        }

        if (user_position) {
            var user_marker = new google.maps.Marker({
                position: user_position,
                map: map,
                icon: elgg.get_site_url() + 'mod/hypeMaps/graphics/icons/youarehere.png',
                title: data.user.entity.title,
                zIndex: 0
            });
        }
        for (var i = 0; i < markers.length; i++) {
            var params = markers[i];

            var paramsLatLng = new google.maps.LatLng(params.location.latitude, params.location.longitude);

            var distance = google.maps.geometry.spherical.computeDistanceBetween(paramsLatLng, user_position);

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
                map: map,
                icon: params.entity.icon,
                title: params.entity.title,
                zIndex: i,
                entityInfo: paramsInfo,
                entityURL: params.entity.url,
                distance: distance
            });

            var entityInfoWindow = new google.maps.InfoWindow({
                content: 'holding...'
            });

            google.maps.event.addListener(marker, 'click', function() {
                //                entityInfoWindow.setContent(this.entityInfo);
                //                entityInfoWindow.open(map, this);
                map.setCenter(this.position);
                $('.hj-map-selected', $(stats)).html(this.entityInfo);
                $('.hj-map-selected .hj-distance', $(stats)).html(this.distance);
            });

            //            google.maps.event.addListener(marker, 'click', function() {
            //                entityInfoWindow.setContent(this.entityInfo);
            //                entityInfoWindow.open(map, this);
            //            });
        }
        return true;
    }

    hj.maps.base.getMarkers = function(map, data) {
        var stats = '#hj-entity-map-stats-' + data.center.id;
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
                $('.hj-map-onthemap', $(stats)).append(entity.entity.tooltip);
                $('.hj-ajaxed-mapobject-preview')
                .die()
                .live('click', hj.framework.ajax.base.view);
            }
        }
    }

    hj.maps.base.setStats = function(data) {
        var stats = $("<div>");
        var stats_onthemap = $("<div>");
        var stats_selected = $("<div>");

        stats
        .attr('id', 'hj-entity-map-stats-' + data.center.id)
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

    elgg.register_hook_handler('init', 'system', hj.maps.base.init);
    elgg.register_hook_handler('success', 'hj:framework:ajax', hj.maps.base.init, 500);

<?php if (FALSE) : ?></script><?php endif; ?>