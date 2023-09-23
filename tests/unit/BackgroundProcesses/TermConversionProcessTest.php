<?php
/**
 * TermConversionProcessTest class file
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedMethodInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound

namespace CyrToLat\Tests\Unit\BackgroundProcesses;

use CyrToLat\Main;
use CyrToLat\BackgroundProcesses\TermConversionProcess;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use Mockery;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;
use wpdb;

/**
 * Class TermConversionProcessTest
 *
 * @group process
 */
class TermConversionProcessTest extends CyrToLatTestCase {

	/**
	 * End test
	 *
	 * @noinspection PhpLanguageLevelInspection
	 * @noinspection PhpUndefinedClassInspection
	 */
	public function tearDown(): void {
		unset( $GLOBALS['wpdb'] );
	}

	/**
	 * Test task()
	 *
	 * @param string $term_slug           Term slug.
	 * @param string $transliterated_slug Sanitized term slug.
	 *
	 * @dataProvider dp_test_task
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_task( string $term_slug, string $transliterated_slug ) {
		global $wpdb;

		$term = (object) [
			'term_id'          => 25,
			'slug'             => $term_slug,
			'taxonomy'         => 'category',
			'term_taxonomy_id' => 5,
		];

		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'transliterate' )->with( $term_slug )->andReturn( $transliterated_slug );

		if ( $transliterated_slug !== $term->slug ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$wpdb        = Mockery::mock( wpdb::class );
			$wpdb->terms = 'wp_terms';
			$wpdb->shouldReceive( 'update' )->once()
				->with( $wpdb->terms, [ 'slug' => $transliterated_slug ], [ 'term_id' => $term->term_id ] );
		}

		WP_Mock::userFunction(
			'get_locale',
			[ 'return' => 'ru_RU' ]
		);

		$subject = Mockery::mock( TermConversionProcess::class, [ $main ] )->makePartial()
			->shouldAllowMockingProtectedMethods();

		$method = 'task';

		$this->set_method_accessibility( $subject, $method );

		WP_Mock::expectFilterAdded(
			'locale',
			[ $subject, 'filter_term_locale' ]
		);

		WP_Mock::userFunction(
			'remove_filter',
			[
				'args'  => [ 'locale', [ $subject, 'filter_term_locale' ] ],
				'times' => 1,
			]
		);

		if ( $transliterated_slug !== $term->slug ) {
			$subject->shouldReceive( 'log' )
				->with( 'Term slug converted: ' . $term->slug . ' => ' . $transliterated_slug )->once();
		}

		self::assertFalse( $subject->$method( $term ) );
	}

	/**
	 * Data provider for test_task()
	 */
	public static function dp_test_task(): array {
		return [
			[ 'slug', 'slug' ],
			[ 'slug', 'transliterated_slug' ],
		];
	}

	/**
	 * Test complete()
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_complete() {
		$subject = Mockery::mock( TermConversionProcess::class )->makePartial();
		$subject->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'log' )->with( 'Term slugs conversion completed.' )->once();

		$method = 'complete';

		$this->set_method_accessibility( $subject, $method );

		WP_Mock::userFunction(
			'wp_cache_flush',
			[
				'return' => null,
				'times'  => 1,
			]
		);

		WP_Mock::userFunction(
			'wp_next_scheduled',
			[
				'return' => null,
				'times'  => 1,
			]
		);

		WP_Mock::userFunction(
			'set_site_transient',
			[
				'times' => 1,
			]
		);

		$subject->$method();
	}

	/**
	 * Tests filter_term_locale with Polylang
	 *
	 * @param false|string $pll_pll_get_term_language Polylang term language.
	 * @param string       $locale                    Site locale.
	 * @param string       $expected                  Expected results.
	 *
	 * @dataProvider dp_test_filter_term_locale_with_polylang
	 * @throws ReflectionException Reflection exception.
	 */
	public function test_filter_term_locale_with_polylang( $pll_pll_get_term_language, string $locale, string $expected ) {
		$term = (object) [
			'taxonomy'         => 'category',
			'term_taxonomy_id' => 5,
		];

		WP_Mock::userFunction(
			'get_locale',
			[
				'return' => $locale,
			]
		);

		FunctionMocker::replace(
			'class_exists',
			static function ( $class_name ) {
				return 'Polylang' === $class_name;
			}
		);

		WP_Mock::userFunction( 'pll_get_term_language' )->with( $term->term_taxonomy_id )
			->andReturn( $pll_pll_get_term_language );

		$main    = Mockery::mock( Main::class );
		$subject = new TermConversionProcess( $main );
		$this->set_protected_property( $subject, 'term', $term );
		self::assertSame( $expected, $subject->filter_term_locale() );
	}

	/**
	 * Data provider for test_filter_term_locale_with_polylang()
	 *
	 * @return array
	 */
	public static function dp_test_filter_term_locale_with_polylang(): array {
		return [
			[ false, 'en_US', 'en_US' ],
			[ 'ru', 'en_US', 'ru' ],
		];
	}

	/**
	 * Tests filter_term_locale() with WPML
	 *
	 * @param array|null $wpml_element_language_details Element language details.
	 * @param array|null $wpml_active_languages         Active languages.
	 * @param string     $locale                        Site locale.
	 * @param string     $expected                      Expected result.
	 *
	 * @dataProvider dp_test_filter_term_locale_with_wpml
	 * @throws ReflectionException Reflection exception.
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function test_filter_term_locale_with_wpml( $wpml_element_language_details, $wpml_active_languages, string $locale, string $expected ) {
		$term = (object) [
			'taxonomy'         => 'category',
			'term_taxonomy_id' => 5,
		];

		$args = [
			'element_type' => $term->taxonomy,
			'element_id'   => $term->term_taxonomy_id,
		];

		WP_Mock::onFilter( 'wpml_element_language_details' )->with( false, $args )
			->reply( $wpml_element_language_details );

		WP_Mock::onFilter( 'wpml_active_languages' )->with( false, [] )->reply( $wpml_active_languages );

		WP_Mock::userFunction(
			'get_locale',
			[
				'return' => $locale,
			]
		);

		$main    = Mockery::mock( Main::class );
		$subject = new TermConversionProcess( $main );
		$this->set_protected_property( $subject, 'term', $term );
		self::assertSame( $expected, $subject->filter_term_locale() );
	}

	/**
	 * Data provider for test_filter_term_locale_with_wpml()
	 *
	 * @return array
	 */
	public static function dp_test_filter_term_locale_with_wpml(): array {
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
}
