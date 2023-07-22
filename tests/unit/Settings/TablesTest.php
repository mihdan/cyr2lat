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

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound

namespace CyrToLat\Tests\Unit\Settings;

use CyrToLat\Main;
use CyrToLat\Settings\Abstracts\SettingsBase;
use CyrToLat\Settings\Tables;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
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
class TablesTest extends CyrToLatTestCase {

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
	 * Test screen_id().
	 */
	public function test_screen_id() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'settings_page_cyr-to-lat', $subject->screen_id() );
	}

	/**
	 * Test option_group().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_option_group() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
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
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
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
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
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
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'page_title';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'Tables', $subject->$method() );
	}

	/**
	 * Test menu_title().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_menu_title() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
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
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'section_title';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'tables', $subject->$method() );
	}

	/**
	 * Test parent_slug().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_parent_slug() {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$method  = 'parent_slug';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( 'options-general.php', $subject->$method() );
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
			'\CyrToLat\Conversion_Tables::get',
			static function ( $locale = '' ) {
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

		$expected = [
			'iso9'  =>
				[
					'label'        => 'ISO9 Table<br>(current)',
					'section'      => 'iso9_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      =>
						[
							0 => 'iso9',
						],
				],
			'bel'   =>
				[
					'label'        => 'bel Table',
					'section'      => 'bel_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      =>
						[
							0 => 'bel',
						],
				],
			'uk'    =>
				[
					'label'        => 'uk Table',
					'section'      => 'uk_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      =>
						[
							0 => 'uk',
						],
				],
			'bg_BG' =>
				[
					'label'        => 'bg_BG Table',
					'section'      => 'bg_BG_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      =>
						[
							0 => 'bg_BG',
						],
				],
			'mk_MK' =>
				[
					'label'        => 'mk_MK Table',
					'section'      => 'mk_MK_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      =>
						[
							0 => 'mk_MK',
						],
				],
			'sr_RS' =>
				[
					'label'        => 'sr_RS Table',
					'section'      => 'sr_RS_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      =>
						[
							0 => 'sr_RS',
						],
				],
			'el'    =>
				[
					'label'        => 'el Table',
					'section'      => 'el_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      =>
						[
							0 => 'el',
						],
				],
			'hy'    =>
				[
					'label'        => 'hy Table',
					'section'      => 'hy_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      =>
						[
							0 => 'hy',
						],
				],
			'ka_GE' =>
				[
					'label'        => 'ka_GE Table',
					'section'      => 'ka_GE_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      =>
						[
							0 => 'ka_GE',
						],
				],
			'kk'    =>
				[
					'label'        => 'kk Table',
					'section'      => 'kk_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      =>
						[
							0 => 'kk',
						],
				],
			'he_IL' =>
				[
					'label'        => 'he_IL Table',
					'section'      => 'he_IL_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      =>
						[
							0 => 'he_IL',
						],
				],
			'zh_CN' =>
				[
					'label'        => 'zh_CN Table',
					'section'      => 'zh_CN_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      =>
						[
							0 => 'zh_CN',
						],
				],
		];

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
		WP_Mock::userFunction( 'submit_button' )->with()->never();

		$expected = '		<h1 class="ctl-settings-header">
			<img
					src="https://site.org/wp-content/plugins/cyr2lat/assets/images/logo.svg"
					alt="Cyr To Lat Logo"
					class="ctl-logo"
			/>
			Cyr To Lat		</h1>

		<form
				id="ctl-options"
				class="ctl-tables"
				action="http://test.test/wp-admin/options.php"
				method="post">
					</form>
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
		$admin_url      = 'http://test.test/wp-admin/options.php';
		$nonce          = 'some-nonce';

		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'min_suffix' )->andReturn( '' );
		$GLOBALS['cyr_to_lat_plugin'] = $main;

		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->with()->andReturn( true );

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
				Tables::HANDLE,
				$plugin_url . '/assets/js/apps/tables.js',
				[],
				$plugin_version,
				true
			)
			->once();

		WP_Mock::userFunction( 'admin_url' )->with( 'admin-ajax.php' )->once()->andReturn( $admin_url );

		WP_Mock::userFunction( 'wp_create_nonce' )
			->with( Tables::SAVE_TABLE_ACTION )
			->once()
			->andReturn( $nonce );

		WP_Mock::userFunction( 'wp_localize_script' )
			->with(
				Tables::HANDLE,
				Tables::OBJECT,
				[
					'ajaxUrl' => $admin_url,
					'action'  => Tables::SAVE_TABLE_ACTION,
					'nonce'   => $nonce,
				]
			)
			->once();

		WP_Mock::userFunction( 'wp_enqueue_style' )
			->with(
				Tables::HANDLE,
				$plugin_url . '/assets/css/tables.css',
				[ SettingsBase::HANDLE ],
				$plugin_version
			)
			->once();

		$subject->admin_enqueue_scripts();
	}

	/**
	 * Test setup_sections().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_setup_sections() {
		$tab_option_page = 'cyr-to-lat';

		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'option_page' )->andReturn( $tab_option_page );

		$form_fields = self::get_test_form_fields();

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
	 */
	public function test_setup_sections_not_on_own_screen() {
		$subject = Mockery::mock( Tables::class )->makePartial();

		$subject->setup_sections();
	}
}
