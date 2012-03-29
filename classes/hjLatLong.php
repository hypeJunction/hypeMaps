<?php

class hjLatLong {

    public function __construct($lat, $long, $wrap = false) {
        if ($lat && $long) {
            $this->lat = $lat;
            $this->long = $long;
            $this->wrap = $wrap;
            return true;
        }
        return false;
    }

    public function lat($precision = 6) {
        return round($this->lat, $precision);
    }

    public function long($precision = 6) {
        return round($this->long, $precision);
    }
    
    public function output() {
        return array($this->lat(), $this->long());
    }
    
}