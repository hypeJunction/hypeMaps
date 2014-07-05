<?php

namespace hypeJunction\Maps;

use hypeJunction\Lists\ElggListQuery;

class ElggMapQuery extends ElggListQuery {

	/**
	 * Flag if spatical sql table exists
	 * @var boolean
	 */
	static $spatial;

	/**
	 * Search location
	 * @var string
	 */
	protected $location;

	/**
	 * Search location latitude
	 * @var float
	 */
	protected $latitude;

	/**
	 * Search location longitude
	 * @var float
	 */
	protected $longitude;

	/**
	 * Search radius
	 * @var float
	 */
	protected $radius = false;

	/**
	 * Construct a new query
	 * @param string $search_type
	 * @param mixed $query
	 * @param array $table_map
	 */
	function __construct($search_type, $query = '', $table_map = null) {
		parent::__construct($search_type, $query, $table_map);

		if ($search_type == 'proximity') {
			$this->location = elgg_extract('location', $query);
			$this->latitude = elgg_extract('latitude', $query);
			$this->longitude = elgg_extract('longitude', $query);
			$this->radius = elgg_extract('radius', $query);
		}
	}

	/**
	 * Filter ege* options
	 * @param array $options
	 * @return array
	 */
	public function sqlGetOptions($options = array()) {
		parent::sqlGetOptions($options);

		if ($this->search_type == 'proximity') {
			$this->sqlOrderByProximity();
			$this->sqlConstrainByProximity();
		}

		return $this->options;
	}

	/**
	 * Add queries to the options array to order items by their proximity to search location
	 * @return ElggMap
	 */
	private function sqlOrderByProximity() {

		$this->latitude = sanitize_string((float) $this->latitude);
		$this->longitude = sanitize_string((float) $this->longitude);
		$this->radius = sanitize_string((float) $this->radius);

		if ($this->hasSpatial()) {
			$this->sqlJoinSpatial('eg');
			$this->options['selects']['proximity'] = "(GLength(LineStringFromWKB(LineString(eg.geometry,GeomFromText('POINT({$this->latitude} {$this->longitude})')))))*60*1.825 as proximity";
			$this->options['order_by'] = "proximity ASC, e.time_updated DESC";
			$this->options['callback'] = __NAMESPACE__ . '\\mappable_entity_row_to_elggstar';
		} else {
			$this->sqlJoinCoordinates('msvlat', 'msvlong', 'mdlat', 'mdlong');
			$this->options['selects']['proximity'] = "(((acos(sin(($this->latitude*pi()/180))*sin((msvlat.string*pi()/180))+cos(($this->latitude*pi()/180))*cos((msvlat.string*pi()/180))*cos((($this->longitude-msvlong.string)*pi()/180)))))*180/pi())*60*1.1515*1.60934 AS proximity";
			$this->options['order_by'] = "proximity ASC, e.time_updated DESC";
			$this->options['callback'] = __NAMESPACE__ . '\\mappable_entity_row_to_elggstar';
		}

		return $this;
	}

	/**
	 * Add queries to constrain the items by distance to search location
	 * @return type
	 */
	private function sqlConstrainByProximity() {

		if (!$this->location || $this->radius <= 0) {
			return $this;
		}

		if (isset($this->options['selects']['proximity']) && $this->options['count'] === false) {
			$this->options['wheres']['proximity'] = "proximity <= {$this->radius}";
		} else if ($this->hasSpatial()) {
			$this->sqlJoinSpatial('eg');
			$this->options['wheres']['proximity'] = "(GLength(LineStringFromWKB(LineString(eg.geometry,GeomFromText('POINT({$this->latitude} {$this->longitude})')))))*60*1.825 <= {$this->radius}";
		} else {
			$this->sqlJoinCoordinates('msvlat', 'msvlong', 'mdlat', 'mdlong');
			$this->options['wheres']['proximity'] = "(((acos(sin(($this->latitude*pi()/180))*sin((msvlat.string*pi()/180))+cos(($this->latitude*pi()/180))*cos((msvlat.string*pi()/180))*cos((($this->longitude-msvlong.string)*pi()/180)))))*180/pi())*60*1.1515*1.60934 <= {$this->radius}";
		}

		return $this;
	}

	/**
	 * Join coordinates metadata
	 * @param string $msvlat	Join name for latitude metadata value
	 * @param string $msvlong	Join name for longitude metadata value
	 * @param string $mdlat		Join name for latitude metadata row
	 * @param string $mdlong	Join name for longitude metadata row
	 * @return ElggMap
	 */
	private function sqlJoinCoordinates($msvlat = 'msvlat', $msvlong = 'msvlong', $mdlat = 'mdlat', $mdlong = 'mdlong') {

		$dbprefix = elgg_get_config('dbprefix');
		$map = ElggListQuery::getMetaMap(array('geo:lat', 'geo:long'));

		$msvlat = sanitize_string($msvlat);
		$msvlong = sanitize_string($msvlong);
		$mdlat = sanitize_string($mdlat);
		$mdlong = sanitize_string($mdlong);

		$this->options['joins'][$mdlat] = "JOIN {$dbprefix}metadata $mdlat on e.guid = $mdlat.entity_guid AND $mdlat.name_id = {$map['geo:lat']}";
		$this->options['joins'][$msvlat] = "JOIN {$dbprefix}metastrings $msvlat on $mdlat.value_id = $msvlat.id";

		$this->options['joins'][$mdlong] = "JOIN {$dbprefix}metadata $mdlong on e.guid = $mdlong.entity_guid AND $mdlong.name_id = {$map['geo:long']}";
		$this->options['joins'][$msvlong] = "JOIN {$dbprefix}metastrings $msvlong ON $mdlong.value_id = $msvlong.id";

		return $this;
	}

	/**
	 * Join spatial table
	 * @param string $eg	Join name for spatial table
	 * @return ElggMap
	 */
	private function sqlJoinSpatial($eg = 'eg') {

		$dbprefix = elgg_get_config('dbprefix');
		$eg = sanitize_string($eg);
		$this->options['joins'][$eg] = "JOIN {$dbprefix}entity_geometry $eg ON e.guid = $eg.entity_guid";
		return $this;
	}

	/**
	 * Check if hypeGeo entity_geometry table exists
	 * @return boolean
	 */
	private function hasSpatial() {
		if (!isset(self::$spatial)) {
			$prefix = elgg_get_config('dbprefix');
			$tables = get_db_tables();
			self::$spatial = (in_array("{$prefix}entity_geometry", $tables));
		}
		return self::$spatial;
	}

}

/**
 * Set proximity select as volatile data on a constructed entity
 * @todo Make this a class method in 1.9 (1.8 doesn't use call_user_func())
 * @param stdClass $row
 * @return ElggEntity
 */
function mappable_entity_row_to_elggstar($row) {

	$entity = entity_row_to_elggstar($row);
	if (elgg_instanceof($entity)) {
		$entity->setVolatileData('select:proximity', (float) $row->proximity);
	}
	return $entity;
}
