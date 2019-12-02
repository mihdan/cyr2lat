<?php
/**
 * Test_Conversion_Process class file
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat;

use Mockery;
use stdClass;

/**
 * Class Test_Conversion_Process
 *
 * @group process
 */
class Test_Conversion_Process extends Cyr_To_Lat_TestCase {

	/**
	 * Test task()
	 */
	public function test_task() {
		$subject = Mockery::mock( Conversion_Process::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$this->assertFalse( $subject->task( new stdClass() ) );
	}

	/**
	 * Test complete()
	 */
	public function test_complete() {
		$subject = Mockery::mock( Conversion_Process::class )->makePartial()->shouldAllowMockingProtectedMethods();

		\WP_Mock::userFunction(
			'wp_next_scheduled',
			[
				'return' => null,
				'times'  => 1,
			]
		);

		\WP_Mock::userFunction(
			'set_site_transient',
			[
				'times' => 1,
			]
		);

		$subject->complete();
		$this->assertTrue( true );
	}

	/**
	 * Test is_process_completed()
	 *
	 * @param mixed   $transient Transient.
	 * @param boolean $expected  Expected result.
	 *
	 * @dataProvider dp_test_is_process_completed
	 */
	public function test_is_process_completed( $transient, $expected ) {
		$main    = Mockery::mock( Main::class );
		$subject = new Conversion_Process( $main );

		\WP_Mock::userFunction(
			'get_site_transient',
			[
				'args'   => [ CYR_TO_LAT_PREFIX . '_background_process_process_completed' ],
				'return' => $transient,
				'times'  => 1,
			]
		);

		if ( $transient ) {
			\WP_Mock::userFunction(
				'delete_site_transient',
				[
					'args'  => [ CYR_TO_LAT_PREFIX . '_background_process_process_completed' ],
					'times' => 1,
				]
			);
		}

		$this->assertSame( $expected, $subject->is_process_completed() );
	}

	/**
	 * Data provider for test_is_process_completed()
	 */
	public function dp_test_is_process_completed() {
		return [
			[ true, true ],
			[ false, false ],
		];
	}

	/**
	 * Test is_process_running()
	 *
	 * @param mixed   $transient Transient.
	 * @param boolean $expected  Expected result.
	 *
	 * @dataProvider dp_test_is_process_running
	 */
	public function test_is_process_running( $transient, $expected ) {
		$main    = Mockery::mock( Main::class );
		$subject = new Conversion_Process( $main );

		\WP_Mock::userFunction(
			'get_site_transient',
			[
				'args'   => [ CYR_TO_LAT_PREFIX . '_background_process_process_lock' ],
				'return' => $transient,
				'times'  => 1,
			]
		);

		$this->assertSame( $expected, $subject->is_process_running() );
	}

	/**
	 * Data provider for test_is_process_running()
	 */
	public function dp_test_is_process_running() {
		return [
			[ true, true ],
			[ false, false ],
		];
	}

	/**
	 * Test log()
	 *
	 * @param boolean $debug Is WP_DEBUG_LOG on.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider        dp_test_log
	 */
	public function test_log( $debug ) {
		$subject = Mockery::mock( Conversion_Process::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$test_log = 'test.log';
		$message  = 'Test message';
		if ( $debug ) {
			define( 'WP_DEBUG_LOG', true );
		}

		@unlink( $test_log );
		$error_log = ini_get( 'error_log' );
		ini_set( 'error_log', $test_log );

		$subject->log( $message );
		if ( $debug ) {
			$this->assertNotFalse( strpos( $this->get_log( $test_log ), 'Cyr To Lat: ' . $message ) );
		} else {
			$this->assertFalse( $this->get_log( $test_log ) );
		}

		ini_set( 'error_log', $error_log );
		@unlink( $test_log );
	}

	/**
	 * Data provider for test_log()
	 *
	 * @return array
	 */
	public function dp_test_log() {
		return [
			[ false ],
			[ true ],
		];
	}

	/**
	 * Get test log content
	 *
	 * @param string $test_log Test log filename.
	 *
	 * @return false|string
	 */
	private function get_log( $test_log ) {
		return @file_get_contents( $test_log );
	}
}
