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

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound

namespace CyrToLat\Tests\Unit\Settings;

use Cyr_To_Lat\Settings\Abstracts\SettingsBase;
use Cyr_To_Lat\Settings\Converter;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
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
class ConverterTest extends CyrToLatTestCase {

	/**
	 * Test screen_id().
	 */
	public function test_screen_id() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'settings_page_cyr-to-lat', $subject->screen_id() );
	}

	/**
	 * Test option_group().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_option_group() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'option_group';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'cyr_to_lat_group', $subject->$method() );
	}

	/**
	 * Test option_page().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_option_page() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'option_page';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'cyr-to-lat', $subject->$method() );
	}

	/**
	 * Test option_name().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_option_name() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'option_name';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'cyr_to_lat_settings', $subject->$method() );
	}

	/**
	 * Test page_title().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_page_title() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'page_title';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'Converter', $subject->$method() );
	}

	/**
	 * Test menu_title().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_menu_title() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'menu_title';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'Cyr To Lat', $subject->$method() );
	}

	/**
	 * Test section_title().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_section_title() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'section_title';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'converter', $subject->$method() );
	}

	/**
	 * Test parent_slug().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_parent_slug() {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'parent_slug';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'options-general.php', $subject->$method() );
	}

	/**
	 * Test init_hooks()
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_init_hooks() {
		$mock   = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method = 'init_hooks';

		$this->set_method_accessibility( $mock, $method );
		$mock->shouldReceive( 'plugin_basename' )->with()->andReturn( 'cyr2lat/cyr-to-lat.php' );
		$mock->shouldReceive( 'option_name' )->with()->andReturn( 'cyr_to_lat_settings' );

		WP_Mock::expectActionAdded( 'in_admin_header', [ $mock, 'in_admin_header' ] );
		WP_Mock::expectActionAdded( 'init', [ $mock, 'delayed_init_settings' ], PHP_INT_MAX );

		$mock->$method();
	}

	/**
	 * Test init_form_fields().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_init_form_fields() {
		$expected = [
			'background_post_types'    =>
				[
					'label'        => 'Post Types',
					'section'      => 'types-statuses',
					'type'         => 'checkbox',
					'placeholder'  => '',
					'helper'       => 'Post types included in the conversion.',
					'supplemental' => '',
					'options'      =>
						[
							'post'          => 'post',
							'page'          => 'page',
							'nav_menu_item' => 'nav_menu_item',
						],
					'default'      =>
						[ 'post', 'page', 'nav_menu_item' ],
					'disabled'     => [],
				],
			'background_post_statuses' =>
				[
					'label'        => 'Post Statuses',
					'section'      => 'types-statuses',
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

		$mock = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$mock->init_form_fields();

		self::assertSame( $expected, $this->get_protected_property( $mock, 'form_fields' ) );
	}

	/**
	 * Test get_convertible_post_types().
	 */
	public function test_get_convertible_post_types() {
		$post_types = [
			'post'       => 'post',
			'page'       => 'page',
			'attachment' => 'attachment',
		];
		$expected   = [
			'post'          => 'post',
			'page'          => 'page',
			'attachment'    => 'attachment',
			'nav_menu_item' => 'nav_menu_item',
		];

		WP_Mock::userFunction( 'get_post_types' )->with( [ 'public' => true ] )->andReturn( $post_types );

		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( $expected, $subject::get_convertible_post_types() );
	}

	/**
	 * Test delayed_init_form_fields()
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_delayed_init_form_fields() {
		$post_types = [
			'post'       => 'post',
			'page'       => 'page',
			'attachment' => 'attachment',
		];
		$expected   = [
			'background_post_types'    =>
				[
					'label'        => 'Post Types',
					'section'      => 'types-statuses',
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
					'section'      => 'types-statuses',
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

		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$subject->init_form_fields();
		$subject->delayed_init_form_fields();
		self::assertSame( $expected, $this->get_protected_property( $subject, 'form_fields' ) );
	}

	/**
	 * Test delayed_init_settings().
	 */
	public function test_delayed_init_settings() {
		$option_name   = 'cyr_to_lat_settings';
		$form_fields   = $this->get_test_form_fields();
		$test_settings = $this->get_test_settings();

		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'delayed_init_form_fields' )->with()->once();
		$subject->shouldReceive( 'option_name' )->with()->twice()->andReturn( $option_name );
		$subject->shouldReceive( 'form_fields' )->with()->once()->andReturn( $form_fields );

		WP_Mock::userFunction( 'get_site_option' )->with( $option_name . '_network_wide', [] )->once()
			->andReturn( false );

		WP_Mock::userFunction( 'get_option' )->with( $option_name, null )->once()->andReturn( $test_settings );
		WP_Mock::userFunction( 'wp_list_pluck' )->with( $form_fields, 'default' )->once()
			->andReturn( $form_fields );

		$subject->delayed_init_settings();
	}

	/**
	 * Test settings_page()
	 */
	public function test_settings_page() {
		$admin_url    = 'http://test.test/wp-admin/options.php';
		$option_page  = 'cyr-to-lat';
		$option_group = 'cyr_to_lat_group';

		$subject = Mockery::mock( Converter::class )->makePartial();
		$subject->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'option_page' )->with()->andReturn( $option_page );
		$subject->shouldReceive( 'option_group' )->with()->andReturn( $option_group );

		WP_Mock::userFunction( 'admin_url' )->with( 'options.php' )->once()->andReturn( $admin_url );
		WP_Mock::userFunction( 'do_settings_sections' )->with( $option_page )->once();
		WP_Mock::userFunction( 'settings_fields' )->with( $option_group )->once();
		WP_Mock::userFunction( 'wp_nonce_field' )->with( $subject::NONCE )->once();
		WP_Mock::userFunction( 'submit_button' )->with()->never();
		WP_Mock::userFunction( 'submit_button' )
			->with( 'Convert Existing Slugs', 'secondary', 'ctl-convert-button' )->once();

		$expected = '		<h1 class="ctl-settings-header">
			<img
					src="https://site.org/wp-content/plugins/cyr2lat/assets/images/logo.svg"
					alt="Cyr To Lat Logo"
					class="ctl-logo"
			/>
			Cyr To Lat		</h1>

		<form
				id="ctl-options"
				class="ctl-converter"
				action="http://test.test/wp-admin/options.php"
				method="post">
					</form>
				<form id="ctl-convert-existing-slugs" action="" method="post">
			<input type="hidden" name="ctl-convert"/>
					</form>
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

		$converter = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

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
				'types-statuses',
				'			<h2 class="title">
				Existing Slugs Conversion Settings			</h2>
			<p>
				Existing <strong>product attribute</strong> slugs will <strong>NOT</strong> be converted.			</p>
					<h3 class="ctl-section-types-statuses">Post Types and Statuses</h3>
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
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_admin_enqueue_scripts() {
		$plugin_url     = 'http://test.test/wp-content/plugins/cyr-to-lat';
		$plugin_version = '1.0.0';
		$min            = '.min';

		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->with()->andReturn( true );
		$this->set_protected_property( $subject, 'min_prefix', $min );

		FunctionMocker::replace(
			'constant',
			static function ( $name ) use ( $plugin_url, $plugin_version ) {
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
				$plugin_url . '/assets/js/apps/converter.js',
				[],
				$plugin_version,
				true
			)
			->once();

		WP_Mock::userFunction( 'wp_enqueue_style' )
			->with(
				Converter::HANDLE,
				$plugin_url . "/assets/css/converter$min.css",
				[ SettingsBase::HANDLE ],
				$plugin_version
			)
			->once();

		$subject->admin_enqueue_scripts();
	}
}
