<?php
/**
 * Test_Cyr_To_Lat_Plugin_File class file
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat;

use Mockery;
use tad\FunctionMocker\FunctionMocker;

/**
 * Class Test_Cyr_To_Lat_Plugin_File
 *
 * @group plugin-file
 */
class Test_Cyr_To_Lat_Plugin_File  extends Cyr_To_Lat_TestCase {

	/**
	 * Tear down.
	 */
	public function tearDown() {
		unset( $GLOBALS['cyr_to_lat_requirements'], $GLOBALS['cyr_to_lat_plugin'] );
		parent::tearDown();
	}

	/**
	 * Test main file when Cyr-To-Lat version defined.
	 */
	public function test_when_cyr_to_lat_version_defined() {
		FunctionMocker::replace(
			'defined',
			function ( $name ) {
				if ( 'ABSPATH' === $name ) {
					return true;
				}

				if ( 'CYR_TO_LAT_VERSION' === $name ) {
					return true;
				}

				return null;
			}
		);

		$define = FunctionMocker::replace( 'define', null );

		require PLUGIN_MAIN_FILE;

		$define->wasNotCalled();
	}

	/**
	 * Test loading of main plugin file.
	 *
	 * Does not work with php 5.6 due to the bug in Reflection class prior php 7.0,
	 * and relevant problem in Patchwork.
	 *
	 * @requires            PHP >= 7.0
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_plugin_file_when_requirements_met() {
		global $cyr_to_lat_requirements, $cyr_to_lat_plugin;

		$requirements = Mockery::mock( 'overload:' . Requirements::class );
		$requirements->shouldReceive( 'are_requirements_met' )->with()->once()->andReturn( true );

		Mockery::mock( 'overload:' . Main::class );

		$define                   = FunctionMocker::replace( 'define', null );
		$this->cyr_to_lat_version = CYR_TO_LAT_TEST_VERSION;
		$this->cyr_to_lat_url     = PLUGIN_MAIN_FILE;

		\WP_Mock::passthruFunction( 'plugin_dir_url' );
		\WP_Mock::passthruFunction( 'untrailingslashit' );

		require_once PLUGIN_MAIN_FILE;

		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_VERSION', $this->cyr_to_lat_version ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_PATH', $this->cyr_to_lat_path ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_URL', $this->cyr_to_lat_url ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_FILE', $this->cyr_to_lat_file ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_PREFIX', $this->cyr_to_lat_prefix ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_POST_CONVERSION_ACTION', $this->cyr_to_lat_post_conversion_action ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_TERM_CONVERSION_ACTION', $this->cyr_to_lat_term_conversion_action ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION', $this->cyr_to_lat_minimum_php_required_version ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS', $this->cyr_to_lat_required_max_input_vars ] );

		$this->assertInstanceOf( Requirements::class, $cyr_to_lat_requirements );
		$this->assertInstanceOf( Main::class, $cyr_to_lat_plugin );
	}

	/**
	 * Test loading of main plugin file.
	 *
	 * Does not work with php 5.6 due to the bug in Reflection class prior php 7.0,
	 * and relevant problem in Patchwork.
	 *
	 * @requires            PHP >= 7.0
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_plugin_file_when_requirements_NOT_met() {
		global $cyr_to_lat_requirements, $cyr_to_lat_plugin;

		$requirements = Mockery::mock( 'overload:' . Requirements::class );
		$requirements->shouldReceive( 'are_requirements_met' )->with()->once()->andReturn( false );

		$define                   = FunctionMocker::replace( 'define', null );
		$this->cyr_to_lat_version = CYR_TO_LAT_TEST_VERSION;
		$this->cyr_to_lat_url     = PLUGIN_MAIN_FILE;

		\WP_Mock::passthruFunction( 'plugin_dir_url' );
		\WP_Mock::passthruFunction( 'untrailingslashit' );

		require_once PLUGIN_MAIN_FILE;

		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_VERSION', $this->cyr_to_lat_version ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_PATH', $this->cyr_to_lat_path ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_URL', $this->cyr_to_lat_url ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_FILE', $this->cyr_to_lat_file ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_PREFIX', $this->cyr_to_lat_prefix ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_POST_CONVERSION_ACTION', $this->cyr_to_lat_post_conversion_action ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_TERM_CONVERSION_ACTION', $this->cyr_to_lat_term_conversion_action ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION', $this->cyr_to_lat_minimum_php_required_version ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS', $this->cyr_to_lat_required_max_input_vars ] );

		$this->assertInstanceOf( Requirements::class, $cyr_to_lat_requirements );
		$this->assertNull( $cyr_to_lat_plugin );
	}
}
