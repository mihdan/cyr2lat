<?php
/**
 * Test_Cyr_To_Lat_Term_Conversion_Process class file
 *
 * @package cyr-to-lat
 */

use PHPUnit\Framework\TestCase;

/**
 * Class Test_Cyr_To_Lat_Term_Conversion_Process
 *
 * @group process
 */
class Test_Cyr_To_Lat_Term_Conversion_Process extends TestCase {

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
		unset( $GLOBALS['wpdb'] );
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test task()
	 *
	 * @param string $term_slug      Term slug.
	 * @param string $sanitized_slug Sanitized term slug.
	 *
	 * @dataProvider dp_test_task
	 */
	public function test_task( $term_slug, $sanitized_slug ) {
		global $wpdb;

		$term = (object) [
			'term_id' => 25,
			'slug'    => $term_slug,
		];

		$main = \Mockery::mock( Cyr_To_Lat_Main::class );
		$main->shouldReceive( 'ctl_sanitize_title' )->andReturn( $sanitized_slug );

		if ( $sanitized_slug !== $term->slug ) {
			$wpdb        = Mockery::mock( '\wpdb' );
			$wpdb->terms = 'wp_terms';
			$wpdb->shouldReceive( 'update' )->once()
			     ->with( $wpdb->terms, [ 'slug' => $sanitized_slug ], [ 'term_id' => $term->term_id ] );
		}

		$subject = \Mockery::mock( Cyr_To_Lat_Term_Conversion_Process::class, [ $main ] )->makePartial()
		                   ->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'log' )->with( 'Term slug converted: ' . $term->slug . ' => ' . $sanitized_slug )
		        ->once();

		$this->assertFalse( $subject->task( $term ) );
	}

	/**
	 * Data provider for test_task()
	 */
	public function dp_test_task() {
		return [
			[ 'slug', 'slug' ],
			[ 'slug', 'sanitized_slug' ],
		];
	}

	/**
	 * Test complete()
	 */
	public function test_complete() {
		$subject = \Mockery::mock( Cyr_To_Lat_Term_Conversion_Process::class )->makePartial()
		                   ->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'log' )->with( 'Term slugs conversion completed.' )->once();

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
}
