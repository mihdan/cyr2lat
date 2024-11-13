<?php
/**
 * TablesTest class file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedMethodInspection */
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
	 */
	public function tearDown(): void {
		unset( $GLOBALS['cyr_to_lat_plugin'] );
		parent::tearDown();
	}

	/**
	 * Test screen_id().
	 */
	public function test_screen_id(): void {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'settings_page_cyr-to-lat', $subject->screen_id() );
	}

	/**
	 * Test option_group().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_option_group(): void {
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
	public function test_option_page(): void {
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
	public function test_option_name(): void {
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
	public function test_page_title(): void {
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
	public function test_menu_title(): void {
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
	public function test_section_title(): void {
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
	public function test_parent_slug(): void {
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
	public function test_init_locales(): void {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$method = $this->set_method_accessibility( $subject, 'init_locales' );
		$method->invoke( $subject );

		$expected = [
			'iso9'  => 'Default<br>ISO9',
			'bel'   => 'Belarusian<br>bel',
			'uk'    => 'Ukrainian<br>uk',
			'bg_BG' => 'Bulgarian<br>bg_BG',
			'mk_MK' => 'Macedonian<br>mk_MK',
			'sr_RS' => 'Serbian<br>sr_RS',
			'el'    => 'Greek<br>el',
			'hy'    => 'Armenian<br>hy',
			'ka_GE' => 'Georgian<br>ka_GE',
			'kk'    => 'Kazakh<br>kk',
			'he_IL' => 'Hebrew<br>he_IL',
			'zh_CN' => 'Chinese (China)<br>zh_CN',
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
	public function test_init_form_fields(): void {
		$subject = Mockery::mock( Tables::class )->makePartial()->shouldAllowMockingProtectedMethods();

		FunctionMocker::replace(
			'\CyrToLat\ConversionTables::get',
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
					'title'        => 'Default<br>ISO9<br>(current)',
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
					'title'        => 'Belarusian<br>bel',
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
					'title'        => 'Ukrainian<br>uk',
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
					'title'        => 'Bulgarian<br>bg_BG',
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
					'title'        => 'Macedonian<br>mk_MK',
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
					'title'        => 'Serbian<br>sr_RS',
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
					'title'        => 'Greek<br>el',
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
					'title'        => 'Armenian<br>hy',
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
					'title'        => 'Georgian<br>ka_GE',
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
					'title'        => 'Kazakh<br>kk',
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
					'title'        => 'Hebrew<br>he_IL',
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
					'title'        => 'Chinese (China)<br>zh_CN',
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
	public function test_settings_page(): void {
		$admin_url    = 'http://test.test/wp-admin/options.php';
		$option_page  = 'cyr-to-lat';
		$option_group = 'cyr_to_lat_group';

		$subject = Mockery::mock( Tables::class )->makePartial();
		$subject->shouldAllowMockingProtectedMethods();
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
	public function test_section_callback(): void {
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
	public function test_admin_enqueue_scripts(): void {
		$plugin_url     = 'http://test.test/wp-content/plugins/cyr-to-lat';
		$plugin_version = '1.0.0';
		$admin_url      = 'http://test.test/wp-admin/options.php';
		$nonce          = 'some-nonce';

		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'min_suffix' )->andReturn( '' );
		$GLOBALS['cyr_to_lat_plugin'] = $main;

		$subject = Mockery::mock( Tables::class )->makePartial();
		$subject->shouldAllowMockingProtectedMethods();
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
}
