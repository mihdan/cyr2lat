<?php
/**
 * PluginLoadedTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Integration;

use CyrToLat\Main;
use WP_UnitTestCase;

/**
 * Class PluginLoadedTest
 *
 * @group integration
 */
class PluginLoadedTest extends WP_UnitTestCase {

	/**
	 * Test that WordPress integration bootstrap loads the plugin.
	 *
	 * @return void
	 */
	public function test_plugin_is_loaded(): void {
		self::assertTrue( function_exists( 'cyr_to_lat' ) );
		self::assertInstanceOf( Main::class, cyr_to_lat() );
	}
}
