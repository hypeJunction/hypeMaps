<?php

class hjEntityLocation extends ElggEntity {

    public function __construct($guid = null) {
        parent::initializeAttributes();
        parent::load($guid);
    }

    /**
     * HELPER METHODS
     */

    /**
     * Sets address field metadata
     *
     * @param string|array $address
     * @return bool
     */
    public function setAddressMetadata($address) {
        if (is_array($address)) {
            foreach ($address as $key => $field) {
                $this->$key = $field;
            }
        }
        return true;
    }

    /**
     * Convert an address field array into a string
     *
     * @param array|string $address
     * @return string
     */
    public function getAddressString($address) {
        $location = new hjLocation();
        return $location->getAddressString($address);
    }

    /**
     * Geocodes an address string using google maps service
     *
     * @param string $address
     * @return mixed array(lat,long,radius)
     */
    public function getGeoCodedAddress($address) {
        $location = new hjLocation();
        return $location->getGeoCodedAddress($address);
    }

    public function getMapParams() {
        $entity = get_entity($this->guid);
        $params = array(
            'entity' => array(
                'guid' => $this->guid,
                'title' => $entity->title,
                'icon' => $this->getMapIcon(),
                //'icon' => $this->getIconURL('tiny'),
                //'icon' => elgg_get_config('url') . 'mod/hypeMaps/graphics/icons/' . $this->getSubtype() . '.png',
                //'url' => $this->getURL(),
                //'description' => elgg_get_excerpt($this->description),
                'tooltip' => $this->getMapTooltip()
            ),
            'location' => array(
                'latitude' => $this->getLatitude(),
                'longitude' => $this->getLongitude(),
                'address' => $this->getLocation()
                ));

        if ($this->getTempLocation()) {
            $params['temp_location'] = array(
                'latitude' => $this->getTempLatitude(),
                'longitude' => $this->getTempLongitude(),
                'address' => $this->getTempLocation()
            );
        }
        if (!$entity->title) {
            if ($entity->name) {
                $params['entity']['title'] = $entity->name;
            } else {
                $params['entity']['title'] = elgg_echo('item:object:' . $entity->getSubtype());
            }
        }
        if ($this->guid == elgg_get_logged_in_user_guid()) {
            $params['entity']['title'] = elgg_echo('hj:maps:youarehere');
            //$params['entity']['icon'] = elgg_get_config('url') . 'mod/hypeMaps/graphics/icons/person.png';
        }

        return $params;
    }

    public function getJsonParams() {
        $json = $this->getMapParams();
        return json_encode($json);
    }

//    public function getMarker() {
//        return '';
//    }


    /**
     *  PERMANENT LOCATION OF THE ENTITY
     */

    /**
     * Set permanent entity location
     *
     * @param string|array $address
     * @return type
     */
    public function setEntityLocation($address, $latlong = null) {
        $address = $this->getAddressString($address);
        //$prev_address = $this->getLocation();
        //if ($address != $prev_address) {
        $this->setLocation($address);
        $this->setEntityCoords($address, $latlong);
        //}
        return true;
    }

    /**
     * Sets entity coordinates
     *
     * @param string $address
     * @param object $latlong hjLatLong
     * @return bool
     */
    public function setEntityCoords($address = null, $latlong = null) {
        if (!$latlong) {
            $address = $this->getAddressString($address);
            $latlong = $this->getGeoCodedAddress($address);
        }
        $this->setLatLong($latlong->lat(), $latlong->long());

        if (!$address) {
            $geocode = new hjLocation;
            $address = $geocode->getReverseGeoCode($latlong);
            $this->setEntityLocation($address, $latlong);
        }

        return true;
    }

    /**
     *  TEMPORARY ENTITY LOCATION
     */

    /**
     * Set temporary entity location
     *
     * @param array|string $address
     * @return bool
     */
    public function setTempLocation($address) {
        $address = $this->getAddressString($address);
        $this->set('temp_location', $address);
        return true;
    }

    /**
     * Get temporary entity location
     *
     * @return string
     */
    public function getTempLocation() {
        if (!$location = $this->get('temp_location')) {
            $location = false;
            //$location = $this->getLocation();
        }
        return $location;
    }

    /**
     * Set temporary entity location and coordinates
     *
     * @param string|array $address
     * @return type
     */
    public function setEntityTempLocation($address, $latlong = null) {
        $address = $this->getAddressString($address);
        //$prev_address = $this->temp_location;

        //if ($address != $prev_address) {
            $this->setTempLocation($address);
            $this->setEntityTempCoords($address, $latlong);
        //}
        return true;
    }

    /**
     * Sets temporary entity coordinates
     *
     * @param string $address
     * @param object $latlong hjLatLong
     * @return bool
     */
    public function setEntityTempCoords($address = null, $latlong = null) {
        if (!$latlong) {
            $address = $this->getAddressString($address);
            $latlong = $this->getGeoCodedAddress($address);
        }
        $this->setTempLatLong($latlong);

        if (!$address) {
            $geocode = new hjLocation;
            $address = $geocode->getReverseGeoCode($latlong);
            $this->setEntityTempLocation($address);
        }

        return true;
    }

    /**
     * Sets temporary entity coordinates
     *
     * @param object $latlong hjLatLong
     * @return type
     */
    public function setTempLatLong($latlong) {
        $this->set('temp_latitude', $latlong->lat());
        $this->set('temp_longitude', $latlong->long());
        return true;
    }

    public function getTempLatitude() {
        if (!$lat = $this->get('temp_latitude')) {
            $lat = false;
            //$lat = $this->getLatitude();
        }
        return $lat;
    }

    public function getTempLongitude() {
        if (!$long = $this->get('temp_longitude')) {
            $long = false;
            //$long = $this->getLongitude();
        }
        return $long;
    }

    public function getMapIcon() {
        $entity = get_entity($this->guid);

        if ($this->mapicon) {
            $icon = $this->mapicon;
        } else if ($this->markertype) {
            $icon = elgg_get_config('url') . 'mod/hypeMaps/graphics/icons/' . $this->markertype . '.png';
        } else {
            if (elgg_instanceof($entity, 'object') && $entity->getIconURL('tiny') !== elgg_normalize_url('_graphics/icons/default/tiny.png')) {
                $icon = elgg_get_config('url') . 'places/marker/' . $this->guid;
            } elseif (elgg_instanceof($entity, 'user') && $entity->getIconURL('tiny') !== elgg_normalize_url('_graphics/icons/user/defaulttiny.gif')) {
				$icon = elgg_get_config('url') . 'places/marker/' . $this->guid;
			} else {
                $icon = elgg_get_config('url') . 'mod/hypeMaps/graphics/icons/default.png';
            }
        }

        $icon = elgg_trigger_plugin_hook('hj:maps:mapicon', 'all', array('entity' => $this), $icon);

        return $icon;
    }

    public function getMapTooltip() {
        $type = $this->getType();
        if (!$subtype = $this->getSubtype()) {
            $subtype = 'default';
        }

        $view = "mapobject/$type/$subtype";
        $icon = $this->getMapIcon();
        if (elgg_view_exists($view)) {
            return elgg_view($view, array('entity' => $this, 'icon' => $icon));
        } else {
            return elgg_view('mapobject/default', array('entity' => $this, 'icon' => $icon));
        }
    }

}

?>