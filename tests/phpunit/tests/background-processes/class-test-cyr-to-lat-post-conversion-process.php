<?php
/**
 * Test_Cyr_To_Lat_Post_Conversion_Process class file
 *
 * @package cyr-to-lat
 * @group   process
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

		\WP_Mock::userFunction(
			'sanitize_title',
			[
				'args'   => [ $post_name ],
				'return' => $sanitized_name,
			]
		);

		if ( $sanitized_name !== $post->post_name ) {
			\WP_Mock::userFunction(
				'update_post_meta',
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

		\WP_Mock::userFunction(
			'get_locale',
			[ 'return' => 'ru_RU' ]
		);

		$subject = \Mockery::mock( Cyr_To_Lat_Post_Conversion_Process::class, [ $main ] )->makePartial()
		                   ->shouldAllowMockingProtectedMethods();

		\WP_Mock::expectFilterAdded(
			'locale',
			[ $subject, 'filter_post_locale' ]
		);

		\WP_Mock::userFunction(
			'remove_filter',
			[
				'args'  => [ 'locale', [ $subject, 'filter_post_locale' ] ],
				'times' => 1,
			]
		);

		if ( $sanitized_name !== $post->post_name ) {
			$subject->shouldReceive( 'log' )
			        ->with( 'Post slug converted: ' . $post->post_name . ' => ' . $sanitized_name )
			        ->once();
		}

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

	/**
	 * Tests filter_post_locale()
	 *
	 * @param array  $wpml_post_language_details Post language details.
	 * @param string $locale                     Site locale.
	 * @param string $expected                   Expected result.
	 *
	 * @dataProvider dp_test_filter_post_locale
	 * @throws ReflectionException Reflection exception.
	 */
	public function test_filter_post_locale( $wpml_post_language_details, $locale, $expected ) {
		$post = (object) [
			'ID' => 5,
		];

		\WP_Mock::onFilter( 'wpml_post_language_details' )
		        ->with( false, $post->ID )
		        ->reply( $wpml_post_language_details );

		\WP_Mock::userFunction(
			'get_locale',
			[
				'return' => $locale,
			]
		);

		$main    = \Mockery::mock( Cyr_To_Lat_Main::class );
		$subject = new Cyr_To_Lat_Post_Conversion_Process( $main );
		$this->mock_property( $subject, 'post', $post );
		$this->assertSame( $expected, $subject->filter_post_locale() );
	}

	/**
	 * Data provider for test_filter_post_locale()
	 *
	 * @return array
	 */
	public function dp_test_filter_post_locale() {
		return [
			[ null, 'ru_RU', 'ru_RU' ],
			[ [], 'ru_RU', 'ru_RU' ],
			[ [ 'some' => 'uk' ], 'ru_RU', 'ru_RU' ],
			[ [ 'locale' => 'uk' ], 'ru_RU', 'uk' ],
		];
	}

	/**
	 * Mock an object property.
	 *
	 * @param object $object        Object.
	 * @param string $property_name Property name.
	 * @param mixed  $value         Property vale.
	 *
	 * @throws ReflectionException Reflection exception.
	 */
	private function mock_property( $object, $property_name, $value ) {
		$reflection_class = new \ReflectionClass( $object );

		$property = $reflection_class->getProperty( $property_name );
		$property->setAccessible( true );
		$property->setValue( $object, $value );
		$property->setAccessible( false );
	}
}
