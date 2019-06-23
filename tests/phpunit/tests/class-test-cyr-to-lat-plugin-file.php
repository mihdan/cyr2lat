<?php
/**
 * Test_Cyr_To_Lat_Plugin_File class file
 *
 * @package cyr-to-lat
 */

use PHPUnit\Framework\TestCase;

/**
 * Class Test_Cyr_To_Lat_Plugin_File
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @group plugin-file
 */
class Test_Cyr_To_Lat_Plugin_File  extends TestCase {

	/**
	 * Setup test
	 */
	public function setUp() {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * End test
	 */
	public function tearDown() {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test loading of main plugin file.
	 */
	public function test_plugin_file_when_requirements_met() {
		$requirements = \Mockery::mock( 'overload:Cyr_To_Lat_Requirements' );
		$requirements->shouldReceive( 'are_requirements_met' )->with()->once()->andReturn( true );
		\Mockery::mock( 'overload:Cyr_To_Lat_Main' );

		require PLUGIN_MAIN_FILE;

		$this->assertInstanceOf( 'Cyr_To_Lat_Main', $plugin );
	}

	/**
	 * Test loading of main plugin file.
	 */
	public function test_plugin_file_when_requirements_not_met() {
		$requirements = \Mockery::mock( 'overload:Cyr_To_Lat_Requirements' );
		$requirements->shouldReceive( 'are_requirements_met' )->with()->once()->andReturn( false );

		require_once PLUGIN_MAIN_FILE;

		$this->assertFalse( $plugin );
	}
}
