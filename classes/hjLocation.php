<?php

class hjLocation {

    /**
     * Convert an address field array into a string
     *
     * @param array|string $address
     * @return string
     */
    public function getAddressString($address) {
        if (is_array($address)) {
            foreach ($address as $key => $field) {
                if (!empty($field)) {
                    $output[] = $field;
                }
            }
            $address = implode(', ', $output);
        }
        return $address;
    }

    /**
     * Geocodes an address string using google maps service
     *
     * @param string $address
     * @return mixed array(lat,long,radius)
     */
    public function getGeoCodedAddress($address) {
        $address = $this->getAddressString($address);
        $params = array(
            'address' => $address,
            'sensor' => 'false'
        );
        $query = http_build_query($params);

        $url = "http://maps.googleapis.com/maps/api/geocode/json?$query";
//        if (!$data = @file_get_contents($url) && elgg_is_admin_logged_in()) {
//            register_error('There is a problem with reaching Google Maps server. Please make sure that file_get_contents() is not disabled by your host');
//        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        $data = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($data, true);

        if (is_array($data) && $data['status'] == 'OK') {
            $lat = $data['results'][0]['geometry']['location']['lat'];
            $long = $data['results'][0]['geometry']['location']['lng'];
            $latlong = new hjLatLong($lat, $long);
        } else {
            $latlong = new hjLatLong(0, 0);
        }
        return $latlong;
    }

    /**
     * Reverse GeoCoding
     *
     * @param latlong hjLatLong object
     * @return string
     */
    public function getReverseGeoCode($latlong) {
        $latlong_str = "{$latlong->lat()},{$latlong->long()}";
        $params = array(
            'latlng' => $latlong_str,
            'sensor' => 'false'
        );
        $query = http_build_query($params);

        $url = "http://maps.googleapis.com/maps/api/geocode/json?$query";
        $data = @file_get_contents($url);

        $data = json_decode($data, true);

        if (is_array($data) && $data['status'] == 'OK' && $data['results'][0]['geometry']['location_type'] == 'ROOFTOP') {
            $address = $data['results'][0]['formatted_address'];
        } else {
            $address = $latlong_str;
        }
        return $address;
    }

}

?>