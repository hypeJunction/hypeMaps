<?php

namespace hypeJunction\Maps;

use Elgg\IntegrationTestCase;

/**
 * Smoke-test view rendering for key plugin views.
 *
 * Pre-migration behavioral lock-in for Elgg 3.x.
 */
class ViewsTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    /**
     * @return void
     */
    public function testInputMarkertypeViewRenders(): void {
        $out = \elgg_view('input/markertype', ['name' => 'markertype', 'value' => '']);
        $this->assertIsString($out);
    }

    /**
     * @return void
     */
    public function testOutputMapsLocationViewRenders(): void {
        if (!\elgg_view_exists('output/maps/location')) {
            $this->markTestSkipped('Plugin view not registered (plugin not active)');
        }
        $out = \elgg_view('output/maps/location', ['value' => 'Berlin']);
        $this->assertIsString($out);
    }

    /**
     * @return void
     */
    public function testOutputMapsPinViewRenders(): void {
        if (!\elgg_view_exists('output/maps/pin')) {
            $this->markTestSkipped('Plugin view not registered (plugin not active)');
        }
        $out = \elgg_view('output/maps/pin', []);
        $this->assertIsString($out);
    }

    /**
     * @return void
     */
    public function testStaticmapWidgetEditViewRenders(): void {
        if (!\elgg_view_exists('widgets/staticmap/edit')) {
            $this->markTestSkipped('Plugin view not registered (plugin not active)');
        }
        $widget = new \ElggWidget();
        $out = \elgg_view('widgets/staticmap/edit', ['entity' => $widget]);
        $this->assertIsString($out);
    }
}
