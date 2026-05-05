<?php
/**
 * FilenameIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Integration;

use WP_UnitTestCase;

/**
 * Class FilenameIntegrationTest
 *
 * @group integration
 */
class FilenameIntegrationTest extends WP_UnitTestCase {

	/**
	 * Set up an allowed admin upload request context.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		set_current_screen( 'upload' );
		cyr_to_lat()->init_all();
	}

	/**
	 * Tear down test globals.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		unset( $GLOBALS['current_screen'] );

		parent::tearDown();
	}

	/**
	 * Test that the plugin registers the filename sanitization filter.
	 *
	 * @return void
	 */
	public function test_sanitize_file_name_filter_is_registered(): void {
		self::assertSame( 10, has_filter( 'sanitize_file_name', [ cyr_to_lat(), 'sanitize_filename' ] ) );
	}

	/**
	 * Test that sanitize_file_name transliterates a Cyrillic filename.
	 *
	 * @return void
	 */
	public function test_sanitize_file_name_transliterates_cyrillic_filename(): void {
		self::assertSame( 'skamejka.jpg', sanitize_file_name( 'Скамейка.jpg' ) );
	}

	/**
	 * Test that sanitize_file_name lowercases UTF-8 filenames before transliteration.
	 *
	 * @return void
	 */
	public function test_sanitize_file_name_lowercases_utf8_filename_before_transliteration(): void {
		self::assertSame( 'j.jpg', sanitize_file_name( 'Й.JPG' ) );
	}

	/**
	 * Test that WordPress whitespace normalization is preserved before transliteration.
	 *
	 * @return void
	 */
	public function test_sanitize_file_name_preserves_wordpress_spacing_normalization(): void {
		self::assertSame( 'privet-mir.txt', sanitize_file_name( 'Привет мир.txt' ) );
	}

	/**
	 * Test that multi-extension filenames keep their current shape.
	 *
	 * @return void
	 */
	public function test_sanitize_file_name_preserves_multi_extension_shape(): void {
		self::assertSame( 'test.tar.gz', sanitize_file_name( 'тест.tar.gz' ) );
	}

	/**
	 * Test that macOS decomposed Cyrillic filenames are normalized before transliteration.
	 *
	 * @return void
	 */
	public function test_sanitize_file_name_transliterates_macos_decomposed_filename(): void {
		self::assertSame( 'yo.jpg', sanitize_file_name( urldecode( '%d0%95%cc%88' ) . '.jpg' ) );
	}

	/**
	 * Test that ctl_pre_sanitize_filename can short-circuit the plugin result.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function test_pre_sanitize_filename_filter_short_circuits_result(): void {
		$filter = static function ( $pre, $filename ): string {
			return 'filtered-name.txt';
		};

		add_filter( 'ctl_pre_sanitize_filename', $filter, 10, 2 );

		try {
			self::assertSame( 'filtered-name.txt', sanitize_file_name( 'Скамейка.txt' ) );
		} finally {
			remove_filter( 'ctl_pre_sanitize_filename', $filter );
		}
	}
}
