<?php
/**
 * TablesTest class file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Cyr_To_Lat\Tests\Settings;

use Cyr_To_Lat\Settings\Tables;
use Cyr_To_Lat\Cyr_To_Lat_TestCase;
use Mockery;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class TablesTest
 *
 * @group settings
 * @group settings-tables
 */
class TablesTest extends Cyr_To_Lat_TestCase {

	/**
	 * Test screen_id().
	 */
	public function test_screen_id() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'settings_page_cyr-to-lat', $subject->screen_id() );
	}

	/**
	 * Test option_group().
	 */
	public function test_option_group() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'cyr_to_lat_group', $subject->option_group() );
	}

	/**
	 * Test option_page().
	 */
	public function test_option_page() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'cyr-to-lat', $subject->option_page() );
	}

	/**
	 * Test option_name().
	 */
	public function test_option_name() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'cyr_to_lat_settings', $subject->option_name() );
	}

	/**
	 * Test page_title().
	 */
	public function test_page_title() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'Tables', $subject->page_title() );
	}

	/**
	 * Test menu_title().
	 */
	public function test_menu_title() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'Cyr To Lat', $subject->menu_title() );
	}

	/**
	 * Test section_title().
	 */
	public function test_section_title() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( '', $subject->section_title() );
	}

	/**
	 * Test parent_slug().
	 */
	public function test_parent_slug() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'options-general.php', $subject->parent_slug() );
	}

	/**
	 * Test init_locales()
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_init_locales() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$method = $this->set_method_accessibility( $subject, 'init_locales' );
		$method->invoke( $subject );

		$expected = [
			'iso9'  => [
				'label' => __( 'ISO9 Table', 'cyr2lat' ),
			],
			'bel'   => [
				'label' => __( 'bel Table', 'cyr2lat' ),
			],
			'uk'    => [
				'label' => __( 'uk Table', 'cyr2lat' ),
			],
			'bg_BG' => [
				'label' => __( 'bg_BG Table', 'cyr2lat' ),
			],
			'mk_MK' => [
				'label' => __( 'mk_MK Table', 'cyr2lat' ),
			],
			'sr_RS' => [
				'label' => __( 'sr_RS Table', 'cyr2lat' ),
			],
			'el'    => [
				'label' => __( 'el Table', 'cyr2lat' ),
			],
			'hy'    => [
				'label' => __( 'hy Table', 'cyr2lat' ),
			],
			'ka_GE' => [
				'label' => __( 'ka_GE Table', 'cyr2lat' ),
			],
			'kk'    => [
				'label' => __( 'kk Table', 'cyr2lat' ),
			],
			'he_IL' => [
				'label' => __( 'he_IL Table', 'cyr2lat' ),
			],
			'zh_CN' => [
				'label' => __( 'zh_CN Table', 'cyr2lat' ),
			],
		];

		self::assertSame( $expected, $this->get_protected_property( $subject, 'locales' ) );

		$expected = [ 'something' ];
		$this->set_protected_property( $subject, 'locales', $expected );
		$method->invoke( $subject );
		self::assertSame( $expected, $this->get_protected_property( $subject, 'locales' ) );
	}

	/**
	 * Test init_form_fields()
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_init_form_fields() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		FunctionMocker::replace(
			'\Cyr_To_Lat\Conversion_Tables::get',
			function ( $locale = '' ) {
				switch ( $locale ) {
					case 'bel':
						return [ 'bel' ];
					case 'uk':
						return [ 'uk' ];
					case 'bg_BG':
						return [ 'bg_BG' ];
					case 'mk_MK':
						return [ 'mk_MK' ];
					case 'sr_RS':
						return [ 'sr_RS' ];
					case 'el':
						return [ 'el' ];
					case 'hy':
						return [ 'hy' ];
					case 'ka_GE':
						return [ 'ka_GE' ];
					case 'kk':
						return [ 'kk' ];
					case 'he_IL':
						return [ 'he_IL' ];
					case 'zh_CN':
						return [ 'zh_CN' ];
					default:
						return [ 'iso9' ];
				}
			}
		);

		WP_Mock::userFunction( 'get_locale' )->with()->andReturn( 'iso9' );

		$expected = $this->get_test_form_fields();

		$subject->init_form_fields();
		self::assertSame( $expected, $this->get_protected_property( $subject, 'form_fields' ) );
	}

	/**
	 * Test settings_page()
	 */
	public function test_settings_page() {
		$admin_url    = 'http://test.test/wp-admin/options.php';
		$option_page  = 'cyr-to-lat';
		$option_group = 'cyr_to_lat_group';

		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'option_page' )->with()->andReturn( $option_page );
		$subject->shouldReceive( 'option_group' )->with()->andReturn( $option_group );

		WP_Mock::userFunction( 'admin_url' )->with( 'options.php' )->once()->andReturn( $admin_url );
		WP_Mock::userFunction( 'do_settings_sections' )->with( $option_page )->once();
		WP_Mock::userFunction( 'settings_fields' )->with( $option_group )->once();
		WP_Mock::userFunction( 'submit_button' )->with()->once();

		$expected = '		<div class="wrap">
			<h1>
				Cyr To Lat Plugin Options			</h1>

			<form id="ctl-options" action="' . $admin_url . '" method="post">
							</form>

			<div id="appreciation">
				<h2>
					Your Appreciation				</h2>
				<a
					target="_blank"
					href="https://wordpress.org/support/view/plugin-reviews/cyr2lat?rate=5#postform">
					Leave a ★★★★★ plugin review on WordPress.org				</a>
			</div>
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
		$locale = 'iso9';

		WP_Mock::userFunction( 'get_locale' )->andReturn( $locale );

		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		ob_start();
		$subject->section_callback(
			[ 'id' => $locale . '_section' ]
		);
		self::assertSame( '<div id="ctl-current"></div>', ob_get_clean() );

		ob_start();
		$subject->section_callback(
			[ 'id' => 'other_section' ]
		);
		self::assertSame( '', ob_get_clean() );
	}

	/**
	 * Test admin_enqueue_scripts().
	 */
	public function test_admin_enqueue_scripts() {
		$plugin_url     = 'http://test.test/wp-content/plugins/cyr-to-lat';
		$plugin_version = '1.0.0';

		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
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
				Tables::HANDLE,
				$plugin_url . '/assets/js/tables/app.js',
				[],
				$plugin_version,
				true
			)
			->once();

		WP_Mock::userFunction( 'wp_localize_script' )
			->with(
				Tables::HANDLE,
				Tables::OBJECT,
				[
					'optionsSaveSuccessMessage' => 'Options saved.',
					'optionsSaveErrorMessage'   => 'Error saving options.',
				]
			)
			->once();

		WP_Mock::userFunction( 'wp_enqueue_style' )
			->with(
				Tables::HANDLE,
				$plugin_url . '/assets/css/tables.css',
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
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->with()->andReturn( false );

		WP_Mock::userFunction( 'wp_enqueue_script' )->never();
		WP_Mock::userFunction( 'wp_localize_script' )->never();
		WP_Mock::userFunction( 'wp_enqueue_style' )->never();

		$subject->admin_enqueue_scripts();
	}

	/**
	 * Test setup_sections().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_setup_sections() {
		$tab_option_page = 'cyr-to-lat';
		$current_screen  = (object) [ 'id' => 'settings_page_cyr-to-lat' ];

		WP_Mock::userFunction( 'get_current_screen' )->with()->once()->andReturn( $current_screen );

		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'option_page' )->andReturn( $tab_option_page );

		$form_fields = $this->get_test_form_fields();

		$this->set_protected_property( $subject, 'form_fields', $form_fields );

		foreach ( $form_fields as $form_field ) {
			WP_Mock::userFunction( 'add_settings_section' )
				->with(
					$form_field['section'],
					$form_field['label'],
					[ $subject, 'section_callback' ],
					$tab_option_page
				)
				->once();
		}

		$subject->setup_sections();
	}

	/**
	 * Test setup_sections() not on own screen.
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_setup_sections_not_on_own_screen() {
		$subject = Mockery::mock( Tables::class )->makePartial();

		$subject->setup_sections();
	}
}
