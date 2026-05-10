<?php
/**
 * PluginFileTest class file
 *
 * @package cyr-to-lat
 */

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound

namespace CyrToLat\Tests\Unit;

use CyrToLat\Main;
use Mockery;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class PluginFileTest
 *
 * @group plugin-file
 */
class PluginFileTest extends CyrToLatTestCase {

	/**
	 * Tear down.
	 */
	public function tearDown(): void {
		unset( $GLOBALS['cyr_to_lat_plugin'] );
		parent::tearDown();
	}

	/**
	 * Test the main file.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_main_plugin_file(): void {
		$plugin_dir_url         = 'http://test.test/wp-content/plugins/cyr2lat/';
		$plugin_dir_url_unslash = rtrim( $plugin_dir_url, '/' );

		WP_Mock::userFunction( 'plugin_dir_url' )->with( PLUGIN_MAIN_FILE )
			->andReturn( $plugin_dir_url );
		WP_Mock::userFunction( 'untrailingslashit' )->with( $plugin_dir_url )
			->andReturn( $plugin_dir_url_unslash );

		$defined = FunctionMocker::replace(
			'defined',
			static function ( $name ) {
				static $version_defined = false;

				if ( 'ABSPATH' === $name ) {
					return true;
				}

				if ( 'CYR_TO_LAT_VERSION' === $name ) {
					if ( ! $version_defined ) {
						$version_defined = true;

						return false;
					}

					return true;
				}

				return false;
			}
		);

		$define = FunctionMocker::replace( 'define' );

		FunctionMocker::replace(
			'constant',
			static function ( $name ) {
				if ( 'CYR_TO_LAT_FILE' === $name ) {
					return PLUGIN_MAIN_FILE;
				}

				if ( 'CYR_TO_LAT_PATH' === $name ) {
					return dirname( PLUGIN_MAIN_FILE );
				}

				return null;
			}
		);

		$main = Mockery::mock( 'overload:' . Main::class );
		$main->shouldReceive( 'init' )->once();

		require PLUGIN_MAIN_FILE;

		$expected    = [
			'version' => CYR_TO_LAT_TEST_VERSION,
		];
		$plugin_file = PLUGIN_MAIN_FILE;

		$plugin_headers = $this->get_file_data(
			$plugin_file,
			[ 'version' => 'Version' ],
			'plugin'
		);

		self::assertSame( $expected, $plugin_headers );

		self::assertSame( CYR_TO_LAT_TEST_VERSION, constant( 'CYR_TO_LAT_VERSION' ) );
		self::assertSame( PLUGIN_MAIN_FILE, constant( 'CYR_TO_LAT_FILE' ) );
		self::assertSame( dirname( PLUGIN_MAIN_FILE ), constant( 'CYR_TO_LAT_PATH' ) );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_URL', $plugin_dir_url_unslash ] );
	}

	/**
	 * Test that readme.txt contains a proper stable tag.
	 */
	public function test_stable_tag_in_readme_txt(): void {
		if ( preg_match( '/-.+$/', CYR_TO_LAT_TEST_VERSION ) ) {
			$this->markTestSkipped( 'Not a final version, skipping stable tag in readme.txt test.' );
		}

		$expected    = [
			'stable_tag' => CYR_TO_LAT_TEST_VERSION,
		];
		$readme_file = PLUGIN_PATH . '/readme.txt';

		$readme_headers = $this->get_file_data(
			$readme_file,
			[ 'stable_tag' => 'Stable tag' ],
			'plugin'
		);

		self::assertSame( $expected, $readme_headers );
	}

	/**
	 * Test that readme.txt contains changelog records for the current version.
	 */
	public function test_changelog(): void {
		if ( preg_match( '/-.+$/', CYR_TO_LAT_TEST_VERSION ) ) {
			$this->markTestSkipped( 'Not a final version, skipping changelog test.' );
		}

		$readme_file    = CYR_TO_LAT_TEST_PATH . '/readme.txt';
		$changelog_file = CYR_TO_LAT_TEST_PATH . '/changelog.txt';

		// phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$readme    = file_get_contents( $readme_file );
		$changelog = file_get_contents( $changelog_file );
		// phpcs:enable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		$readme_changelog = substr( $readme, strpos( $readme, "\n== Changelog ==" ) );

		self::assertSame(
			$this->get_current_version_records( $readme_changelog ),
			$this->get_current_version_records( $changelog )
		);
	}

	/**
	 * Get current version records from a changelog section.
	 *
	 * @param string $changelog Changelog.
	 *
	 * @return string
	 * @noinspection RegExpSingleCharAlternation
	 */
	private function get_current_version_records( string $changelog ): string {
		$current_version_records = '';

		$pattern = '/= ' . preg_quote( CYR_TO_LAT_TEST_VERSION, '/' ) . ' \((?:\d{2}|XX)\.(?:\d{2}|XX)\.\d{4}\) =\n((?:.|\n)+)\n= /U';

		if ( preg_match( $pattern, $changelog, $m ) ) {
			$current_version_records = $m[1];
		}

		self::assertNotEmpty( trim( $current_version_records ) );

		return $current_version_records;
	}
}
