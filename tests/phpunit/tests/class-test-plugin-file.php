<?php
/**
 * Test_Cyr_To_Lat_Plugin_File class file
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection ReturnTypeCanBeDeclaredInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Cyr_To_Lat;

use Mockery;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class Test_Cyr_To_Lat_Plugin_File
 *
 * @group plugin-file
 */
class Test_Cyr_To_Lat_Plugin_File  extends Cyr_To_Lat_TestCase {

	/**
	 * Tear down.
	 */
	public function tearDown(): void {
		unset( $GLOBALS['cyr_to_lat_plugin'] );
		parent::tearDown();
	}

	/**
	 * Test main file.
	 *
	 * Does not work with php 5.6 due to the bug in Reflection class prior php 7.0,
	 * and relevant problem in Patchwork.
	 *
	 * @requires            PHP >= 7.0
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @noinspection        PhpIncludeInspection
	 */
	public function test_main_plugin_file() {
		$plugin_dir_url         = 'http://test.test/wp-content/plugins/cyr2lat/';
		$plugin_dir_url_unslash = rtrim( $plugin_dir_url, '/' );

		\WP_Mock::userFunction( 'plugin_dir_url' )->with( PLUGIN_MAIN_FILE )
		        ->andReturn( $plugin_dir_url );
		\WP_Mock::userFunction( 'untrailingslashit' )->with( $plugin_dir_url )
		        ->andReturn( $plugin_dir_url_unslash );

		$defined = FunctionMocker::replace(
			'defined',
			function ( $name ) {
				static $version_defined = false;

				if ( 'ABSPATH' === $name ) {
					return true;
				}

				if ( 'CYR_TO_LAT_VERSION' === $name ) {
					if ( ! $version_defined ) {
						$version_defined = true;

						return false;
					}

					return true;
				}

				return false;
			}
		);

		$define = FunctionMocker::replace( 'define' );

		FunctionMocker::replace(
			'constant',
			function ( $name ) {
				if ( 'CYR_TO_LAT_FILE' === $name ) {
					return PLUGIN_MAIN_FILE;
				}

				if ( 'CYR_TO_LAT_PATH' === $name ) {
					return dirname( PLUGIN_MAIN_FILE );
				}

				return null;
			}
		);

		$main = \Mockery::mock( 'overload:' . Main::class );
		$main->shouldReceive( 'init' )->once();

		require PLUGIN_MAIN_FILE;

		// Include main file the second time to make sure that plugin is not activated again.
		include PLUGIN_MAIN_FILE;

		$defined->wasCalledWithTimes( [ 'ABSPATH' ], 2 );
		$defined->wasCalledWithTimes( [ 'CYR_TO_LAT_VERSION' ], 2 );

		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_VERSION', CYR_TO_LAT_TEST_VERSION ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_FILE', PLUGIN_MAIN_FILE ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_PATH', dirname( PLUGIN_MAIN_FILE ) ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_URL', $plugin_dir_url_unslash ] );
	}
}
