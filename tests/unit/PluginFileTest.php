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
	 *
	 * @noinspection PhpLanguageLevelInspection
	 * @noinspection PhpUndefinedClassInspection
	 */
	public function tearDown(): void {
		unset( $GLOBALS['cyr_to_lat_plugin'] );
		parent::tearDown();
	}

	/**
	 * Test main file.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_main_plugin_file() {
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
		$main->shouldReceive( 'instance' )->once()->andReturn( $main );
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

		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_VERSION', CYR_TO_LAT_TEST_VERSION ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_FILE', PLUGIN_MAIN_FILE ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_PATH', dirname( PLUGIN_MAIN_FILE ) ] );
		$define->wasCalledWithOnce( [ 'CYR_TO_LAT_URL', $plugin_dir_url_unslash ] );
	}

	/**
	 * Test that readme.txt contains proper stable tag.
	 */
	public function test_readme_txt() {
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
}
