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

		$this->latitude = (float) $this->latitude;
		$this->longitude = (float) $this->longitude;
		$this->radius = (float) $this->radius;

		if ($this->hasSpatial()) {
			$this->sqlJoinSpatial('eg');
			$this->options['selects']['proximity'] = "(GLength(LineStringFromWKB(LineString(eg.geometry,GeomFromText('POINT({$this->latitude} {$this->longitude})')))))*60*1.825 as proximity";
			$this->options['order_by'] = "proximity ASC, e.time_updated DESC";
			$this->options['callback'] = __NAMESPACE__ . '\\mappable_entity_row_to_elggstar';
		} else {
			$this->sqlJoinCoordinates('mdlat', 'mdlong');
			$this->options['selects']['proximity'] = "(((acos(sin(($this->latitude*pi()/180))*sin((mdlat.value*pi()/180))+cos(($this->latitude*pi()/180))*cos((mdlat.value*pi()/180))*cos((($this->longitude-mdlong.value)*pi()/180)))))*180/pi())*60*1.1515*1.60934 AS proximity";
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
			$this->sqlJoinCoordinates('mdlat', 'mdlong');
			$this->options['wheres']['proximity'] = "(((acos(sin(($this->latitude*pi()/180))*sin((mdlat.value*pi()/180))+cos(($this->latitude*pi()/180))*cos((mdlat.value*pi()/180))*cos((($this->longitude-mdlong.value)*pi()/180)))))*180/pi())*60*1.1515*1.60934 <= {$this->radius}";
		}

		return $this;
	}

	/**
	 * Join coordinates metadata
	 *
	 * In Elgg 3.x metadata values are stored directly in the metadata table
	 * (no more metastrings). The metadata table has `name` and `value` columns.
	 *
	 * @param string $mdlat  Join alias for latitude metadata row
	 * @param string $mdlong Join alias for longitude metadata row
	 * @return ElggMap
	 */
	private function sqlJoinCoordinates($mdlat = 'mdlat', $mdlong = 'mdlong') {

		$dbprefix = elgg()->db->getTablePrefix();

		$this->options['joins'][$mdlat] = "JOIN {$dbprefix}metadata $mdlat ON e.guid = $mdlat.entity_guid AND $mdlat.name = 'geo:lat'";
		$this->options['joins'][$mdlong] = "JOIN {$dbprefix}metadata $mdlong ON e.guid = $mdlong.entity_guid AND $mdlong.name = 'geo:long'";

		return $this;
	}

	/**
	 * Join spatial table
	 * @param string $eg	Join name for spatial table
	 * @return ElggMap
	 */
	private function sqlJoinSpatial($eg = 'eg') {

		$dbprefix = elgg()->db->getTablePrefix();
		$this->options['joins'][$eg] = "JOIN {$dbprefix}entity_geometry $eg ON e.guid = $eg.entity_guid";
		return $this;
	}

	/**
	 * Check if hypeGeo entity_geometry table exists
	 * @return boolean
	 */
	private function hasSpatial() {
		if (!isset(self::$spatial)) {
			$prefix = elgg()->db->getTablePrefix();
			$tables = elgg()->db->getConnection('read')->executeQuery("SHOW TABLES LIKE '{$prefix}entity_geometry'")->fetchAllAssociative();
			self::$spatial = count($tables) > 0;
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

	$entity = _elgg_services()->entityTable->rowToElggStar($row);
	if ($entity instanceof \ElggEntity) {
		$entity->setVolatileData('select:proximity', (float) $row->proximity);
	}
	return $entity;
}
