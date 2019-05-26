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
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * End test
	 */
	public function tearDown(): void {
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
			'term_id'          => 25,
			'slug'             => $term_slug,
			'taxonomy'         => 'category',
			'term_taxonomy_id' => 5,
		];

		$main = \Mockery::mock( Cyr_To_Lat_Main::class );
		$main->shouldReceive( 'ctl_sanitize_title' )->andReturn( $sanitized_slug );

		if ( $sanitized_slug !== $term->slug ) {
			$wpdb        = Mockery::mock( '\wpdb' );
			$wpdb->terms = 'wp_terms';
			$wpdb->shouldReceive( 'update' )->once()
			     ->with( $wpdb->terms, [ 'slug' => $sanitized_slug ], [ 'term_id' => $term->term_id ] );
		}

		\WP_Mock::userFunction(
			'get_locale',
			[ 'return' => 'ru_RU' ]
		);

		$subject = \Mockery::mock( Cyr_To_Lat_Term_Conversion_Process::class, [ $main ] )->makePartial()
		                   ->shouldAllowMockingProtectedMethods();

		\WP_Mock::expectFilterAdded(
			'locale',
			[ $subject, 'filter_term_locale' ],
			);

		\WP_Mock::userFunction(
			'remove_filter',
			[
				'args'  => [ 'locale', [ $subject, 'filter_term_locale' ] ],
				'times' => 1,
			]
		);

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

	/**
	 * Tests filter_term_locale()
	 *
	 * @param array  $wpml_element_language_details Element language details.
	 * @param array  $wpml_active_languages         Activa languages.
	 * @param string $locale                        Site locale.
	 * @param string $expected                      Expected result.
	 *
	 * @dataProvider dp_test_filter_term_locale
	 * @throws ReflectionException Reflection exception.
	 */
	public function test_filter_term_locale( $wpml_element_language_details, $wpml_active_languages, $locale, $expected ) {
		$term = (object) [
			'taxonomy'         => 'category',
			'term_taxonomy_id' => 5,
		];

		$args = [
			'element_type' => $term->taxonomy,
			'element_id'   => $term->term_taxonomy_id,
		];

		\WP_Mock::onFilter( 'wpml_element_language_details' )
		        ->with( false, $args )
		        ->reply( $wpml_element_language_details );

		\WP_Mock::onFilter( 'wpml_active_languages' )
		        ->with( false, [] )
		        ->reply( $wpml_active_languages );

		\WP_Mock::userFunction(
			'get_locale',
			[
				'return' => $locale,
			]
		);

		$main    = \Mockery::mock( Cyr_To_Lat_Main::class );
		$subject = new Cyr_To_Lat_Term_Conversion_Process( $main );
		$this->mock_property( $subject, 'term', $term );
		$this->assertSame( $expected, $subject->filter_term_locale() );
	}

	/**
	 * Data provider for test_filter_term_locale()
	 *
	 * @return array
	 */
	public function dp_test_filter_term_locale() {
		return [
			[ null, null, 'ru_RU', 'ru_RU' ],
			[ (object) [], null, 'ru_RU', 'ru_RU' ],
			[ (object) [ 'some' => 'ua' ], null, 'ru_RU', 'ru_RU' ],
			[ (object) [ 'language_code' => 'ua' ], null, 'ru_RU', 'ru_RU' ],
			[ (object) [ 'language_code' => 'ua' ], [], 'ru_RU', 'ru_RU' ],
			[ (object) [ 'language_code' => 'ua' ], [ 'ua' => [ 'some' => 'uk_UA' ] ], 'ru_RU', 'ru_RU' ],
			[ (object) [ 'language_code' => 'ua' ], [ 'ua' => [ 'default_locale' => 'uk_UA' ] ], 'ru_RU', 'uk_UA' ],
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
	private function mock_property( $object, string $property_name, $value ) {
		$reflection_class = new \ReflectionClass( $object );

		$property = $reflection_class->getProperty( $property_name );
		$property->setAccessible( true );
		$property->setValue( $object, $value );
		$property->setAccessible( false );
	}
}
