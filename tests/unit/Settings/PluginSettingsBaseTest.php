<?php
/**
 * PluginSettingsBaseTest class file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace CyrToLat\Tests\Unit\Settings;

use CyrToLat\Settings\PluginSettingsBase;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use Mockery;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class PluginSettingsBaseTest
 *
 * @group settings
 * @group plugin-base
 */
class PluginSettingsBaseTest extends CyrToLatTestCase {

	/**
	 * Test plugin_basename().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_plugin_basename() {
		$plugin_file      = '/var/www/wp-content/plugins/cyr2lat/cyr-to-lat.php';
		$plugin_base_name = 'cyr2lat/cur-to-lat.php';

		$subject = Mockery::mock( PluginSettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'plugin_basename';

		$this->set_method_accessibility( $subject, $method );

		$constant = FunctionMocker::replace( 'constant', $plugin_file );

		WP_Mock::userFunction( 'plugin_basename' )->with( $plugin_file )->once()->andReturn( $plugin_base_name );

		self::assertSame( $plugin_base_name, $subject->$method() );
		$constant->wasCalledWithOnce( [ 'CYR_TO_LAT_FILE' ] );
	}

	/**
	 * Test plugin_url().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_plugin_url() {
		$plugin_url = 'http://test.test/wp-content/plugins/cyr2lat';

		$subject = Mockery::mock( PluginSettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'plugin_url';

		$this->set_method_accessibility( $subject, $method );

		$constant = FunctionMocker::replace( 'constant', $plugin_url );

		self::assertSame( $plugin_url, $subject->$method() );
		$constant->wasCalledWithOnce( [ 'CYR_TO_LAT_URL' ] );
	}

	/**
	 * Test plugin_version().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_plugin_version() {
		$plugin_version = '1.0.0';

		$subject = Mockery::mock( PluginSettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'plugin_version';

		$this->set_method_accessibility( $subject, $method );

		$constant = FunctionMocker::replace( 'constant', $plugin_version );

		self::assertSame( $plugin_version, $subject->$method() );
		$constant->wasCalledWithOnce( [ 'CYR_TO_LAT_VERSION' ] );
	}

	/**
	 * Test settings_link_label().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_settings_link_label() {
		$subject = Mockery::mock( PluginSettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'settings_link_label';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'View Cyr To Lat settings', $subject->$method() );
	}

	/**
	 * Test settings_link_text().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_settings_link_text() {
		$subject = Mockery::mock( PluginSettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'settings_link_text';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'Settings', $subject->$method() );
	}

	/**
	 * Test text_domain().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_text_domain() {
		$subject = Mockery::mock( PluginSettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'text_domain';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'cyr2lat', $subject->$method() );
	}
}
