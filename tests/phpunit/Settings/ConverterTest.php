<?php
/**
 * ConverterTest class file.
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat\Tests\Settings;

use Cyr_To_Lat\Settings\Converter;
use Cyr_To_Lat\Settings\Tables;
use Cyr_To_Lat\Cyr_To_Lat_TestCase;
use Mockery;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class ConverterTest
 *
 * @group settings
 * @group settings-converter
 */
class ConverterTest extends Cyr_To_Lat_TestCase {

	/**
	 * Test screen_id().
	 */
	public function test_screen_id() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'settings_page_cyr-to-lat', $subject->screen_id() );
	}

	/**
	 * Test option_group().
	 */
	public function test_option_group() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'cyr_to_lat_group', $subject->option_group() );
	}

	/**
	 * Test option_page().
	 */
	public function test_option_page() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'cyr-to-lat', $subject->option_page() );
	}

	/**
	 * Test option_name().
	 */
	public function test_option_name() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'cyr_to_lat_settings', $subject->option_name() );
	}

	/**
	 * Test page_title().
	 */
	public function test_page_title() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'Converter', $subject->page_title() );
	}

	/**
	 * Test menu_title().
	 */
	public function test_menu_title() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'Cyr To Lat', $subject->menu_title() );
	}

	/**
	 * Test section_title().
	 */
	public function test_section_title() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( '', $subject->section_title() );
	}

	/**
	 * Test parent_slug().
	 */
	public function test_parent_slug() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'options-general.php', $subject->parent_slug() );
	}

	/**
	 * Test init_form_fields()
	 */
	public function test_init_form_fields() {
		$subject = new Converter();

		$subject->init_form_fields();
		self::assertSame( [], $this->get_protected_property( $subject, 'form_fields' ) );
	}

	/**
	 * Test init_hooks()
	 */
	public function test_init_hooks() {
		$mock = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'plugin_basename' )->with()->andReturn( 'cyr2lat/cyr-to-lat.php' );
		$mock->shouldReceive( 'option_name' )->with()->andReturn( 'cyr_to_lat_settings' );

		WP_Mock::expectActionAdded( 'in_admin_header', [ $mock, 'in_admin_header' ] );

		$mock->init_hooks();
	}

	/**
	 * Test settings_page()
	 */
	public function test_settings_page() {
		$admin_url    = 'http://test.test/wp-admin/options.php';
		$option_page  = 'cyr-to-lat';
		$option_group = 'cyr_to_lat_group';

		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'option_page' )->with()->andReturn( $option_page );
		$subject->shouldReceive( 'option_group' )->with()->andReturn( $option_group );

		WP_Mock::userFunction( 'admin_url' )->with( 'options.php' )->once()->andReturn( $admin_url );
		WP_Mock::userFunction( 'do_settings_sections' )->with( $option_page )->once();
		WP_Mock::userFunction( 'settings_fields' )->with( $option_group )->once();
		WP_Mock::userFunction( 'wp_nonce_field' )->with( $subject::NONCE )->once();
		WP_Mock::userFunction( 'submit_button' )
		       ->with( 'Convert Existing Slugs', 'secondary', 'ctl-convert-button' )->once();

		$expected = '		<div class="wrap">
			<h2 id="title">
				Cyr To Lat Plugin Options			</h2>

			<form id="ctl-options" action="' . $admin_url . '" method="post">
							</form>

			<form id="ctl-convert-existing-slugs" action="" method="post">
				<input type="hidden" name="ctl-convert" />
							</form>
		</div>
		';
		ob_start();
		$subject->settings_page();
		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test section_callback()
	 */
	public function test_section_callback() {
		$converter = new Converter();
		$converter->section_callback( [] );
	}

	/**
	 * Test in_admin_header().
	 */
	public function test_in_admin_header() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->with()->andReturn( true );

		$expected = '		<div id="ctl-confirm-popup">
			<div id="ctl-confirm-content">
				<p>
					<strong>Important:</strong>
					This operation is irreversible. Please make sure that you have made a backup copy of your database.				</p>
				<p>Are you sure to continue?</p>
				<div id="ctl-confirm-buttons">
					<input
						type="button" id="ctl-confirm-ok" class="button button-primary"
						value="OK">
					<button
						type="button" id="ctl-confirm-cancel" class="button button-secondary">
						Cancel					</button>
				</div>
			</div>
		</div>
		';

		ob_start();
		$subject->in_admin_header();
		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test in_admin_header() not on own screen.
	 */
	public function test_in_admin_header_not_on_own_screen() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->with()->andReturn( false );

		$expected = '';

		ob_start();
		$subject->in_admin_header();
		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test admin_enqueue_scripts().
	 */
	public function test_admin_enqueue_scripts() {
		$plugin_url     = 'http://test.test/wp-content/plugins/cyr-to-lat';
		$plugin_version = '1.0.0';

		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->with()->andReturn( true );

		FunctionMocker::replace(
			'constant',
			function ( string $name ) use ( $plugin_url, $plugin_version ): string {
				if ( 'CYR_TO_LAT_URL' === $name ) {
					return $plugin_url;
				}
				if ( 'CYR_TO_LAT_VERSION' === $name ) {
					return $plugin_version;
				}

				return '';
			}
		);

		\WP_Mock::userFunction( 'wp_enqueue_script' )
		        ->with(
			        Converter::HANDLE,
			        $plugin_url . '/assets/js/converter/app.js',
			        [],
			        $plugin_version,
			        true
		        )
		        ->once();

		\WP_Mock::userFunction( 'wp_enqueue_style' )
		        ->with(
			        Converter::HANDLE,
			        $plugin_url . '/assets/css/converter.css',
			        [],
			        $plugin_version
		        )
		        ->once();

		$subject->admin_enqueue_scripts();
	}

	/**
	 * Test admin_enqueue_scripts() not on own screen.
	 */
	public function test_admin_enqueue_scripts_not_on_own_screen() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->with()->andReturn( false );

		\WP_Mock::userFunction( 'wp_enqueue_script' )->never();
		\WP_Mock::userFunction( 'wp_enqueue_style' )->never();

		$subject->admin_enqueue_scripts();
	}
}
