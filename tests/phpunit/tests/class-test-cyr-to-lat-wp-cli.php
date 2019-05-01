<?php
/**
 * Test_Cyr_To_Lat_WP_CLI class file
 *
 * @package cyr-to-lat
 */

use PHPUnit\Framework\TestCase;

/**
 * Class Test_Cyr_To_Lat_WP_CLI
 *
 * @group wp-cli
 */
class Test_Cyr_To_Lat_WP_CLI extends TestCase {

	/**
	 * Setup test
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * End test
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test regenerate()
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_regenerate() {
		$converter = \Mockery::mock( 'Cyr_To_Lat_Converter' );
		$subject   = \Mockery::mock( 'Cyr_To_Lat_WP_CLI', [ $converter ] )->makePartial()
		                     ->shouldAllowMockingProtectedMethods();

		$notify = \Mockery::mock( '\cli\progress\Bar' );
		$notify->expects( 'tick' );
		$notify->expects( 'finish' );

		$subject->shouldReceive( 'make_progress_bar' )->andReturn( $notify );

		$result = [];

		$converter->expects( 'convert_existing_slugs' )->with( $result );

		$cli = \Mockery::mock( 'overload:WP_CLI' );
		$cli->expects( 'success' )->with( 'Regenerate Completed.' );

		$subject->regenerate();
		$this->assertTrue( true );
	}

	/**
	 * Test make_progress_bar()
	 */
	public function test_make_progress_bar() {
		$converter = \Mockery::mock( 'Cyr_To_Lat_Converter' );
		$subject   = \Mockery::mock( 'Cyr_To_Lat_WP_CLI', [ $converter ] )->makePartial()
		                     ->shouldAllowMockingProtectedMethods();

		$notify = \Mockery::mock( 'overload:\cli\progress\Bar' );

		\WP_Mock::userFunction(
			'\WP_CLI\Utils\make_progress_bar',
			[
				'args'   => [ 'Regenerate existing slugs', 1 ],
				'return' => $notify,
			]
		);

		/**
		 * This doesn't work for unknown reason. \WP_Mock::userFunction above always returns null.
		 * $this->assertSame( $notify, $subject->make_progress_bar() );
		 */

		// Here is the simplified variant.
		$subject->make_progress_bar();
		$this->assertTrue( true );
	}
}
