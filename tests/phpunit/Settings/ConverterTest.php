<?php
/**
 * ConverterTest class file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Cyr_To_Lat\Tests\Settings;

use Cyr_To_Lat\Settings\Converter;
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
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_init_form_fields() {
		$post_types = [ 'post', 'page', 'attachment' ];
		$expected   = [
			'background_post_types'    =>
				[
					'label'        => 'Post Types',
					'section'      => 'background_section',
					'type'         => 'checkbox',
					'placeholder'  => '',
					'helper'       => 'Post types included in the conversion.',
					'supplemental' => '',
					'options'      =>
						[
							'post'          => 'post',
							'page'          => 'page',
							'attachment'    => 'attachment',
							'nav_menu_item' => 'nav_menu_item',
						],
					'default'      =>
						[ 'post', 'page', 'nav_menu_item' ],
					'disabled'     => [],
				],
			'background_post_statuses' =>
				[
					'label'        => 'Post Statuses',
					'section'      => 'background_section',
					'type'         => 'checkbox',
					'placeholder'  => '',
					'helper'       => 'Post statuses included in the conversion.',
					'supplemental' => '',
					'options'      =>
						[
							'publish' => 'publish',
							'future'  => 'future',
							'private' => 'private',
							'draft'   => 'draft',
							'pending' => 'pending',
						],
					'default'      =>
						[ 'publish', 'future', 'private' ],
				],
		];

		WP_Mock::userFunction( 'get_post_types' )->with( [ 'public' => true ] )->andReturn( $post_types );

		$subject = Mockery::mock( Converter::class )->makePartial();

		$subject->init_form_fields();
		self::assertSame( $expected, $this->get_protected_property( $subject, 'form_fields' ) );
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
	 *
	 * @noinspection PhpUndefinedClassConstantInspection
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
		WP_Mock::userFunction( 'submit_button' )->with()->once();
		WP_Mock::userFunction( 'submit_button' )
			->with( 'Convert Existing Slugs', 'secondary', 'ctl-convert-button' )->once();

		$expected = '		<div class="wrap">
			<h1>
				Cyr To Lat Plugin Options			</h1>

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
	 *
	 * @param string $id       Section id.
	 * @param string $expected Expected value.
	 *
	 * @dataProvider dp_test_section_callback
	 */
	public function test_section_callback( $id, $expected ) {
		WP_Mock::passthruFunction( 'wp_kses_post' );

		$converter = Mockery::mock( Converter::class )->makePartial();

		ob_start();
		$converter->section_callback( [ 'id' => $id ] );
		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Data provider for test_section_callback().
	 *
	 * @return array
	 */
	public function dp_test_section_callback() {
		return [
			'Non-existing id'    => [ '', '' ],
			'Background section' => [
				'background_section',
				'			<h2 class="title">
				Existing Slugs Conversion Settings			</h2>
			<p>
				Existing <strong>product attribute</strong> slugs will <strong>NOT</strong> be converted.			</p>
			',
			],
		];
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
				<p>
					Also, you have to make a copy of your media files if the attachment post type is selected for
				conversion.				</p>
				<p>
					Upon conversion of attachments, please regenerate thumbnails.				</p>
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
			function ( $name ) use ( $plugin_url, $plugin_version ) {
				if ( 'CYR_TO_LAT_URL' === $name ) {
					return $plugin_url;
				}
				if ( 'CYR_TO_LAT_VERSION' === $name ) {
					return $plugin_version;
				}

				return '';
			}
		);

		WP_Mock::userFunction( 'wp_enqueue_script' )
			->with(
				Converter::HANDLE,
				$plugin_url . '/assets/js/converter/app.js',
				[],
				$plugin_version,
				true
			)
			->once();

		WP_Mock::userFunction( 'wp_enqueue_style' )
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

		WP_Mock::userFunction( 'wp_enqueue_script' )->never();
		WP_Mock::userFunction( 'wp_enqueue_style' )->never();

		$subject->admin_enqueue_scripts();
	}
}
