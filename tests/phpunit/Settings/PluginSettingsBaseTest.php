<?php
/**
 * PluginSettingsBaseTest class file.
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat\Tests\Settings;

use Cyr_To_Lat\Settings\PluginSettingsBase;
use Cyr_To_Lat\Cyr_To_Lat_TestCase;
use tad\FunctionMocker\FunctionMocker;

/**
 * Class PluginSettingsBaseTest
 *
 * @group settings
 * @group plugin-base
 */
class PluginSettingsBaseTest extends Cyr_To_Lat_TestCase {

	/**
	 * Test plugin_basename().
	 */
	public function test_plugin_basename() {
		$plugin_file      = '/var/www/wp-content/plugins/cyr2lat/cyr-to-lat.php';
		$plugin_base_name = 'cyr2lat/cur-to-lat.php';

		$subject = \Mockery::mock( PluginSettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$constant = FunctionMocker::replace( 'constant', $plugin_file );

		\WP_Mock::userFunction( 'plugin_basename' )->with( $plugin_file )->once()->andReturn( $plugin_base_name );

		self::assertSame( $plugin_base_name, $subject->plugin_basename() );
		$constant->wasCalledWithOnce( [ 'CYR_TO_LAT_FILE' ] );
	}

	/**
	 * Test plugin_url().
	 */
	public function test_plugin_url() {
		$plugin_url = 'http://test.test/wp-content/plugins/cyr2lat';

		$subject = \Mockery::mock( PluginSettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$constant = FunctionMocker::replace( 'constant', $plugin_url );

		self::assertSame( $plugin_url, $subject->plugin_url() );
		$constant->wasCalledWithOnce( [ 'CYR_TO_LAT_URL' ] );
	}

	/**
	 * Test plugin_version().
	 */
	public function test_plugin_version() {
		$plugin_version = '1.0.0';

		$subject = \Mockery::mock( PluginSettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$constant = FunctionMocker::replace( 'constant', $plugin_version );

		self::assertSame( $plugin_version, $subject->plugin_version() );
		$constant->wasCalledWithOnce( [ 'CYR_TO_LAT_VERSION' ] );
	}

	/**
	 * Test settings_link_label().
	 */
	public function test_settings_link_label() {
		$subject = \Mockery::mock( PluginSettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'View Cyr To Lat settings', $subject->settings_link_label() );
	}

	/**
	 * Test settings_link_text().
	 */
	public function test_settings_link_text() {
		$subject = \Mockery::mock( PluginSettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'Settings', $subject->settings_link_text() );
	}

	/**
	 * Test text_domain().
	 */
	public function test_text_domain() {
		$subject = \Mockery::mock( PluginSettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'cyr2lat', $subject->text_domain() );
	}
}
