<?php

namespace hypeJunction\Maps;

use ElggAnnotation;
use ElggRiverItem;
use hypeJunction\Lists\ElggList;

/**
 * ElggMap class.
 */
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
	 *
	 * @param array  $options Options to pass to the getter function
	 * @param string $getter  Getter function name
	 */
	public function __construct($options = [], $getter = 'elgg_get_entities') {
		parent::__construct($options, $getter);
		$this->setLocation();
	}

	/**
	 * Prepare and render a map
	 *
	 * @param array $params Render parameters
	 * @return string
	 */
	public static function showMap($params = []) {

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

		$defaults = [
			'full_view' => false,
			'list_type' => 'mapbox',
			'pagination' => true,
			'limit' => $limit,
			'offset' => $offset,
		];
		$options = elgg_extract('options', $params);

		$options = array_merge($defaults, $options);

		$map = new ElggMap($options, $getter);
		$map->setSearchLocation($location, $radius);
		$map->setSearchQuery($query);

		return elgg_view('page/components/mapbox', [
			'list' => $map
		]);
	}

	/**
	 * Set the search location
	 *
	 * @param string  $location Location address
	 * @param integer $radius   Radius in the unit of preset metric system (kilometer or mile)
	 * @return self
	 */
	public function setSearchLocation($location = '', $radius = 0) {

		$this->setRadius($radius);
		$this->setLocation($location);

		try {
			$query = new ElggMapQuery('proximity', [
				'location' => $this->getLocation(),
				'latitude' => $this->getLatitude(),
				'longitude' => $this->getLongitude(),
				'radius' => $this->radius
			]);

			$this->options = $query->sqlGetOptions($this->options);
		} catch (Exception $e) {
			elgg_log($e->getMessage(), 'ERROR');
		}

		return $this;
	}

	/**
	 * Get an array of attributes to construct a new mapbox
	 *
	 * @return array
	 */
	public function getMapboxAttributes() {

		$attributes = [
			'data-mapbox' => true,
			'data-hash' => $this->hash,
			'data-location' => $this->location,
			'data-lat' => $this->latitude,
			'data-long' => $this->longitude,
		];
		return elgg_trigger_event_results('attributes:mapbox', 'maps', [
			'mapbox' => $this
		], $attributes);
	}

	/**
	 * Get a list of attributes to attach to the list item
	 * @param mixed $item List item (entity, annotation or river)
	 * @return array
	 */
	public function getItemAttributes($item = null) {

		if ($item instanceof \ElggEntity) {
			$entity = $item;
		} elseif ($item instanceof ElggRiverItem) {
			$entity = $item->getObjectEntity();
			if (!$entity) {
				$item->getSubjectEntity();
			}
		} else if ($item instanceof ElggAnnotation) {
			$entity = $item->getEntity();
		}

		if (!$entity instanceof \ElggEntity) {
			return [];
		}

		$lat_key = 'geo:lat';
		$long_key = 'geo:long';

		$latitude = $entity->$lat_key;
		$longitude = $entity->$long_key;

		$mappable = ($latitude && $longitude);
		$attributes = [
			'data-mappable' => $mappable,
			'data-guid' => $entity->guid,
			'data-url' => $entity->getURL(),
			'data-title' => ($entity instanceof \ElggObject) ? $entity->title : $entity->name,
			'data-location' => $entity->location,
			'data-lat' => $latitude,
			'data-long' => $longitude,
			'data-pin' => ($mappable) ? $entity->getIconURL('marker') : null,
			'data-proximity' => ($this->location) ? $entity->getVolatileData('select:proximity') : null,
		];
		return elgg_trigger_event_results('attributes:item', 'maps', [
			'item' => $item
		], $attributes);
	}

	/**
	 * Initialize a map with a location
	 *
	 * @param string $location Location address
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

				$lat_key = 'geo:lat';
				$long_key = 'geo:long';

				if (!$user || (!$user->$lat_key || !$user->$long_key)) {
					$entity = $site;
				} else {
					$entity = $user;
				}

				$location = $entity->location;
			}
		}

		$latlong = elgg_trigger_event_results('geocode', 'location', ['location' => $location], false);
		if (!$latlong) {
			$latlong = [];
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
	 *
	 * @param integer $radius Radius in the unit of preset metric system (kilometer or mile)
	 * @return void
	 */
	private function setRadius($radius = 0) {
		$this->radius = $this->getKilometers($radius);
	}

	/**
	 * Convert value from the preset metric system to kilometers
	 *
	 * @param float $value Value in the preset metric system
	 * @return float
	 */
	public static function getKilometers($value) {
		return (self::METRIC_SYSTEM == 'US') ? $value * self::MILE_TO_KM : $value;
	}

	/**
	 * Get human readable proximity value
	 *
	 * @param float $value Distance value in kilometers
	 * @return string
	 */
	public static function getProximity($value) {
		if (self::METRIC_SYSTEM == 'US') {
			$miles = number_format(round($value * self::KM_TO_MILE, 2), 2, '.', ' ');
			return elgg_echo('maps:proximity:US', [$miles]);
		} else {
			$kilometers = number_format(round($value, 2), 2, '.', ' ');
			return elgg_echo('maps:proximity:SI', [$kilometers]);
		}
	}
}
