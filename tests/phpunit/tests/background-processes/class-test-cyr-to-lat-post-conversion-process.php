<?php

/**
 * Test_Cyr_To_Lat_Post_Conversion_Process class file
 *
 * @package cyr-to-lat
 */

use PHPUnit\Framework\TestCase;

/**
 * Class Test_Cyr_To_Lat_Post_Conversion_Process
 *
 * @group process
 */
class Test_Cyr_To_Lat_Post_Conversion_Process extends TestCase {

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
	 * Test task()
	 *
	 * @param string $post_name      Post name.
	 * @param string $sanitized_name Sanitized post name.
	 *
	 * @dataProvider dp_test_task
	 */
	public function test_task( $post_name, $sanitized_name ) {
		global $wpdb;

		$post = (object) [
			'ID'        => 5,
			'post_name' => $post_name,
		];

		$main = \Mockery::mock( Cyr_To_Lat_Main::class );
		$main->shouldReceive( 'ctl_sanitize_title' )->andReturn( $sanitized_name );

		if ( $sanitized_name !== $post->post_name ) {
			\WP_Mock::userFunction(
				'add_post_meta',
				[
					'args'  => [ $post->ID, '_wp_old_slug', $post->post_name ],
					'times' => 1,
				]
			);
			$wpdb        = Mockery::mock( '\wpdb' );
			$wpdb->posts = 'wp_posts';
			$wpdb->shouldReceive( 'update' )->once()
			     ->with( $wpdb->posts, [ 'post_name' => $sanitized_name ], [ 'ID' => $post->ID ] );
		}

		$subject = \Mockery::mock( Cyr_To_Lat_Post_Conversion_Process::class, [ $main ] )->makePartial()
		                   ->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'log' )->with( 'Post slug converted: ' . $post->post_name . ' => ' . $sanitized_name )
		        ->once();

		$this->assertFalse( $subject->task( $post ) );
	}

	/**
	 * Data provider for test_task()
	 */
	public function dp_test_task() {
		return [
			[ 'post_name', 'post_name' ],
			[ 'post_name', 'sanitized_name' ],
		];
	}

	/**
	 * Test complete()
	 */
	public function test_complete() {
		$subject = \Mockery::mock( Cyr_To_Lat_Post_Conversion_Process::class )->makePartial()
		                   ->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'log' )->with( 'Post slugs conversion completed.' )->once();

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
