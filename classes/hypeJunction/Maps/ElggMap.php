<?php

namespace hypeJunction\Maps;

use ElggAnnotation;
use ElggRiverItem;
use hypeJunction\Lists\ElggList;

class ElggMap extends ElggList {

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
	 * Metric system used
	 */
	const METRIC_SYSTEM = HYPEMAPS_METRIC_SYSTEM;
	const KM_TO_MILE = 0.621371;
	const MILE_TO_KM = 1.60934;

	/**
	 * Construct a new list
	 * @param array $options	Options to pass to the getter function
	 */
	function __construct($options = array(), $getter = 'elgg_get_entities') {
		parent::__construct($options, $getter);
		$this->setLocation();
	}

	/**
	 * Prepare and render a map
	 * @param array $params
	 * @return string
	 */
	public static function showMap($params = array()) {

		$location = get_input('location', '');
		if (is_array($location)) {
			$find = elgg_extract('find', $location, false);
			$cached = elgg_extract('cached', $location, '');
			$location = ($find) ? $find : $cached;
		}

		$radius = get_input('radius', HYPEMAPS_SEARCH_RADIUS);
		$query = get_input('query', '');
		$limit = get_input('limit', 20);
		$offset = get_input('offset', 0);

		$getter = elgg_extract('getter', $params, 'elgg_get_entities');

		$defaults = array(
			'full_view' => false,
			'list_type' => 'mapbox',
			'pagination' => true,
			'limit' => $limit,
			'offset' => $offset,
		);
		$options = elgg_extract('options', $params);

		$options = array_merge($defaults, $options);

		$map = new ElggMap($options, $getter);
		$map->setSearchLocation($location, $radius);
		$map->setSearchQuery($query);

		return elgg_view('page/components/mapbox', array(
			'list' => $map
		));
	}

	/**
	 * Set the search location
	 * @param string $location		Location address
	 * @param integer $radius		Radius in the unit of preset metric system (kilometer or mile)
	 */
	public function setSearchLocation($location = '', $radius = 0) {

		$this->setRadius($radius);
		$this->setLocation($location);

		try {
			$query = new ElggMapQuery('proximity', array(
				'location' => $this->getLocation(),
				'latitude' => $this->getLatitude(),
				'longitude' => $this->getLongitude(),
				'radius' => $this->radius
			));

			$this->options = $query->sqlGetOptions($this->options);
		} catch (Exception $e) {
			elgg_log($e->getMessage(), 'ERROR');
		}
		return $this;
	}

	/**
	 * Get an array of attributes to contruct a new mapbox
	 * @param array $vars
	 * @return array
	 */
	public function getMapboxAttributes() {

		$attributes = array(
			'data-mapbox' => true,
			'data-hash' => $this->hash,
			'data-location' => $this->location,
			'data-lat' => $this->latitude,
			'data-long' => $this->longitude,
		);
		return elgg_trigger_plugin_hook('attributes:mapbox', 'maps', array(
			'mapbox' => $this
				), $attributes);
	}

	/**
	 * Get a list of attributes to attach to the list item
	 * @param mixed $item	List item (entity, annotation or river)
	 * @return array
	 */
	public function getItemAttributes($item = null) {

		if (elgg_instanceof($item)) {
			$entity = $item;
		} else if ($item instanceof ElggRiverItem) {

			$entity = $item->getObjectEntity();
			if (!$entity) {
				$item->getSubjectEntity();
			}
		} else if ($item instanceof ElggAnnotation) {
			$entity = $item->getEntity();
		}

		if (!elgg_instanceof($entity)) {
			return array();
		}

		$latitude = $entity->getLatitude();
		$longitude = $entity->getLongitude();

		$mappable = ($latitude && $longitude);
		$attributes = array(
			'data-mappable' => $mappable,
			'data-guid' => $entity->guid,
			'data-url' => $entity->getURL(),
			'data-title' => (elgg_instanceof($entity, 'object')) ? $entity->title : $entity->name,
			'data-location' => $entity->getLocation(),
			'data-lat' => $latitude,
			'data-long' => $longitude,
			'data-pin' => ($mappable) ? $entity->getIconURL('marker') : null,
			'data-proximity' => ($this->location) ? $entity->getVolatileData('select:proximity') : null,
		);
		return elgg_trigger_plugin_hook('attributes:item', 'maps', array(
			'item' => $item
				), $attributes);
	}

	/**
	 * Initialize a map with a location
	 * @return ElggMap
	 */
	private function setLocation($location = '') {

		if (!$location) {
			if (isset($_SESSION['geopositioning'])) {
				$geopositioning = $_SESSION['geopositioning'];
				$location = $geopositioning['location'];
			} else {
				$site = elgg_get_site_entity();
				$user = elgg_get_logged_in_user_entity();

				if (!$user || (!$user->getLatitude() || !$user->getLongitude())) {
					$entity = $site;
				} else {
					$entity = $user;
				}

				$location = $entity->getLocation();
			}
		}

		$latlong = elgg_geocode_location($location);
		if (!$latlong) {
			$latlong = array();
		}
		$this->location = $location;
		$this->latitude = elgg_extract('lat', $latlong, 0);
		$this->longitude = elgg_extract('long', $latlong, 0);

		return $this;
	}

	/**
	 * Get the location of the original map (search) center
	 * @return string
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * Get map center latitude
	 * @return float
	 */
	public function getLatitude() {
		return (float) $this->latitude;
	}

	/**
	 * Get map center longitude
	 * @return float
	 */
	public function getLongitude() {
		return (float) $this->longitude;
	}

	/**
	 * Set search radius
	 * @param integer $radius		Radius in the unit of preset metric system (kilometer or mile)
	 */
	private function setRadius($radius = 0) {
		$this->radius = $this->getKilometers($radius);
	}

	/**
	 * Convert value from the preset metric system to kilometers
	 * @param $value
	 * @return float
	 */
	public static function getKilometers($value) {
		return (self::METRIC_SYSTEM == 'US') ? $value * self::MILE_TO_KM : $value;
	}

	/**
	 * Get human readable proximity value
	 * @param float $value
	 * @return string
	 */
	public static function getProximity($value) {
		if (self::METRIC_SYSTEM == 'US') {
			$miles = number_format(round($value * self::KM_TO_MILE, 2), 2, '.', ' ');
			return elgg_echo('maps:proximity:US', array($miles));
		} else {
			$kilometers = number_format(round($value, 2), 2, '.', ' ');
			return elgg_echo('maps:proximity:SI', array($kilometers));
		}
	}

}
