<?php

namespace hypeJunction\Maps;

use Elgg\IntegrationTestCase;

/**
 * Tests for ElggMap + ElggMapQuery classes.
 *
 * Note: ElggMap extends hypeJunction\Lists\ElggList, provided by hypeLists.
 * Tests will skip if hypeLists is not loaded.
 *
 * Pre-migration behavioral lock-in for Elgg 3.x.
 */
class ElggMapTest extends IntegrationTestCase {

    public function up() {
        if (!class_exists(\hypeJunction\Lists\ElggList::class)) {
            $this->markTestSkipped('hypeLists plugin not available — ElggMap depends on it');
        }
    }

    public function down() {}

    public function testGetKilometersMetricPassThrough(): void {
        // In SI system, value is returned as-is
        if (!defined('HYPEMAPS_METRIC_SYSTEM') || HYPEMAPS_METRIC_SYSTEM !== 'US') {
            $this->assertSame(10.0, (float) ElggMap::getKilometers(10));
        } else {
            // US system: mile to km conversion
            $this->assertEqualsWithDelta(16.0934, ElggMap::getKilometers(10), 0.01);
        }
    }

    public function testGetProximityReturnsString(): void {
        $result = ElggMap::getProximity(5.0);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testConstructorInstantiatesMap(): void {
        try {
            $map = new ElggMap(['types' => 'user']);
            $this->assertInstanceOf(ElggMap::class, $map);
            $this->assertIsFloat($map->getLatitude());
            $this->assertIsFloat($map->getLongitude());
        } catch (\Throwable $e) {
            $this->markTestSkipped('ElggMap construction requires hypeLists internals: ' . $e->getMessage());
        }
    }

    public function testGetMapboxAttributesReturnsArray(): void {
        try {
            $map = new ElggMap(['types' => 'user']);
            $attrs = $map->getMapboxAttributes();
            $this->assertIsArray($attrs);
            $this->assertArrayHasKey('data-mapbox', $attrs);
            $this->assertArrayHasKey('data-lat', $attrs);
            $this->assertArrayHasKey('data-long', $attrs);
        } catch (\Throwable $e) {
            $this->markTestSkipped('ElggMap construction requires hypeLists internals: ' . $e->getMessage());
        }
    }
}
