<?php
/**
 * SettingsBaseTest class file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

// phpcs:disable WordPress.WP.AlternativeFunctions.json_encode_json_encode

namespace Cyr_To_Lat\Tests\Settings\Abstracts;

use Cyr_To_Lat\Cyr_To_Lat_TestCase;
use Cyr_To_Lat\Settings\Abstracts\SettingsBase;
use Mockery;
use PHPUnit\Runner\Version;
use ReflectionClass;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class SettingsBaseTest
 *
 * @group settings
 * @group settings-base
 */
class SettingsBaseTest extends Cyr_To_Lat_TestCase {

	/**
	 * Test constructor.
	 *
	 * @param bool $is_tab Is this a tab.
	 *
	 * @dataProvider dp_test_constructor
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_constructor( $is_tab ) {
		$classname = SettingsBase::class;

		$subject = Mockery::mock( $classname )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_tab' )->once()->andReturn( $is_tab );

		if ( $is_tab ) {
			WP_Mock::expectActionNotAdded( 'current_screen', [ $subject, 'setup_tabs_section' ] );
		} else {
			WP_Mock::expectActionAdded( 'current_screen', [ $subject, 'setup_tabs_section' ], 9 );
		}

		$subject->shouldReceive( 'init' )->once()->with();

		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $subject );
	}

	/**
	 * Data provider for test_constructor().
	 *
	 * @return array
	 */
	public function dp_test_constructor() {
		return [
			'Tab'       => [ true ],
			'Not a tab' => [ false ],
		];
	}

	/**
	 * Test init().
	 *
	 * @param bool $is_active Is this an active tab.
	 *
	 * @dataProvider dp_test_init
	 */
	public function test_init( $is_active ) {
		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'init_form_fields' )->once();
		$subject->shouldReceive( 'init_settings' )->once();
		$subject->shouldReceive( 'is_tab_active' )->once()->with( $subject )->andReturn( $is_active );

		if ( $is_active ) {
			$subject->shouldReceive( 'init_hooks' )->once();
		} else {
			$subject->shouldReceive( 'init_hooks' )->never();
		}

		$subject->init();
	}

	/**
	 * Data provider for test_init().
	 *
	 * @return array
	 */
	public function dp_test_init() {
		return [
			'Active tab'     => [ true ],
			'Not active tab' => [ false ],
		];
	}

	/**
	 * Test init_hooks().
	 */
	public function test_init_hooks() {
		$plugin_base_name = 'cyr2lat/cyr-to-lat.php';
		$option_name      = 'cyr_to_lat_settings';

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'plugin_basename' )->andReturn( $plugin_base_name );
		$subject->shouldReceive( 'option_name' )->andReturn( $option_name );

		WP_Mock::expectActionAdded( 'plugins_loaded', [ $subject, 'load_plugin_textdomain' ] );

		WP_Mock::expectFilterAdded(
			'plugin_action_links_' . $plugin_base_name,
			[ $subject, 'add_settings_link' ]
		);

		WP_Mock::expectActionAdded( 'admin_menu', [ $subject, 'add_settings_page' ] );
		WP_Mock::expectActionAdded( 'current_screen', [ $subject, 'setup_sections' ] );
		WP_Mock::expectActionAdded( 'current_screen', [ $subject, 'setup_fields' ] );

		WP_Mock::expectFilterAdded(
			'pre_update_option_' . $option_name,
			[ $subject, 'pre_update_option_filter' ],
			10,
			2
		);

		WP_Mock::expectActionAdded( 'admin_enqueue_scripts', [ $subject, 'base_admin_enqueue_scripts' ] );

		$subject->init_hooks();
	}

	/**
	 * Test parent_slug().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_parent_slug() {
		$subject = Mockery::mock( SettingsBase::class )->makePartial();

		$this->set_method_accessibility( $subject, 'parent_slug' );
		self::assertSame( 'options-general.php', $subject->parent_slug() );
	}

	/**
	 * Test is_main_menu_page().
	 *
	 * @param string $parent_slug Parent slug.
	 * @param bool   $expected    Expected.
	 *
	 * @dataProvider dp_test_is_main_menu_page
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_is_main_menu_page( $parent_slug, $expected ) {
		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'parent_slug' )->once()->andReturn( $parent_slug );

		$this->set_method_accessibility( $subject, 'is_main_menu_page' );
		self::assertSame( $expected, $subject->is_main_menu_page() );
	}

	/**
	 * Data provider for test_is_main_menu_page().
	 *
	 * @return array
	 */
	public function dp_test_is_main_menu_page() {
		return [
			'Empty slug' => [ '', true ],
			'Some slug'  => [ 'options-general.php', false ],
		];
	}

	/**
	 * Test get_class_name().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_get_class_name() {
		$subject = Mockery::mock( SettingsBase::class )->makePartial();

		$this->set_method_accessibility( $subject, 'get_class_name' );

		if (
			class_exists( Version::class ) &&
			version_compare( substr( Version::id(), 0, 1 ), '7', '>=' )
		) {
			self::assertStringContainsString(
				'Cyr_To_Lat_Settings_Abstracts_SettingsBase',
				$subject->get_class_name()
			);
		} else {
			self::assertContains(
				'Cyr_To_Lat_Settings_Abstracts_SettingsBase',
				$subject->get_class_name()
			);
		}
	}

	/**
	 * Test is_tab().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_is_tab() {
		$subject = Mockery::mock( SettingsBase::class )->makePartial();

		$this->set_method_accessibility( $subject, 'is_tab' );
		self::assertTrue( $subject->is_tab() );

		$this->set_protected_property( $subject, 'tabs', [ 'some_array' ] );
		self::assertFalse( $subject->is_tab() );
	}

	/**
	 * Test add_settings_link().
	 */
	public function test_add_settings_link() {
		$option_page         = 'cyr-to-lat';
		$settings_link_label = 'Cyr To Lat Settings';
		$settings_link_text  = 'Settings';

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'option_page' )->andReturn( $option_page );
		$subject->shouldReceive( 'settings_link_label' )->andReturn( $settings_link_label );
		$subject->shouldReceive( 'settings_link_text' )->andReturn( $settings_link_text );

		WP_Mock::passthruFunction( 'admin_url' );

		$expected = [
			'settings' =>
				'<a href="options-general.php?page=' . $option_page .
				'" aria-label="' . $settings_link_label . '">' . $settings_link_text . '</a>',
		];

		self::assertSame( $expected, $subject->add_settings_link( [] ) );
	}

	/**
	 * Test init_settings().
	 *
	 * @param mixed $settings Settings.
	 *
	 * @dataProvider dp_test_init_settings
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_init_settings( $settings ) {
		$option_name = 'cyr_to_lat_settings';

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'option_name' )->andReturn( $option_name );

		WP_Mock::userFunction( 'get_option' )->with( $option_name, null )->once()->andReturn( $settings );

		$form_fields = $this->get_test_settings();
		$subject->shouldReceive( 'form_fields' )->andReturn( $form_fields );

		$form_fields_pluck = [
			'pageTitle' => 'Table Viewer',
			'pageSlug'  => 'am-table-viewer',
			'cacheTime' => 3600,
		];

		WP_Mock::userFunction( 'wp_list_pluck' )->with( $form_fields, 'default' )->once()
			->andReturn( $form_fields_pluck );

		$this->set_protected_property( $subject, 'settings', null );
		$subject->init_settings();

		$expected = array_merge( array_fill_keys( array_keys( $form_fields ), '' ), $form_fields_pluck );
		if ( is_array( $settings ) ) {
			$expected = array_merge( $form_fields_pluck, $settings );
		}

		self::assertSame( $expected, $this->get_protected_property( $subject, 'settings' ) );
	}

	/**
	 * Data provider for test_init_settings().
	 *
	 * @return array
	 */
	public function dp_test_init_settings() {
		return [
			'No settings in option'   => [ false ],
			'Some settings in option' => [ $this->get_test_settings() ],
		];
	}

	/**
	 * Test form_fields().
	 *
	 * @param mixed $form_fields Form fields.
	 * @param array $expected    Expected result.
	 *
	 * @dataProvider dp_test_form_fields
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_form_fields( $form_fields, $expected ) {
		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$this->set_protected_property( $subject, 'form_fields', $form_fields );

		if ( empty( $form_fields ) ) {
			$subject->shouldReceive( 'init_form_fields' )->andReturnUsing(
				function () use ( $subject ) {
					$this->set_protected_property( $subject, 'form_fields', $this->get_test_form_fields() );
				}
			)->once();
		}

		self::assertSame( $expected, $subject->form_fields() );
	}

	/**
	 * Data provider for test_form_fields().
	 *
	 * @return array
	 */
	public function dp_test_form_fields() {
		return [
			[ null, $this->get_test_form_fields() ],
			[ [], $this->get_test_form_fields() ],
			[ $this->get_test_form_fields(), $this->get_test_form_fields() ],
			[
				[
					'iso9' => [
						'label'        => 'ISO9 Table',
						'section'      => 'iso9_section',
						'type'         => 'table',
						'placeholder'  => '',
						'helper'       => '',
						'supplemental' => '',
					],
				],
				[
					'iso9' => [
						'label'        => 'ISO9 Table',
						'section'      => 'iso9_section',
						'type'         => 'table',
						'placeholder'  => '',
						'helper'       => '',
						'supplemental' => '',
						'default'      => '',
					],
				],
			],
		];
	}

	/**
	 * Test add_settings_page().
	 *
	 * @param bool $is_main_menu_page Is this the main menu page.
	 *
	 * @dataProvider dp_test_add_settings_page
	 */
	public function test_add_settings_page( $is_main_menu_page ) {
		$parent_slug = 'options-general.php';
		$page_title  = 'Cyr To Lat';
		$menu_title  = 'Cyr To Lat';
		$capability  = 'manage_options';
		$slug        = 'cyr-to-lat';

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_main_menu_page' )->andReturn( $is_main_menu_page );
		$subject->shouldReceive( 'page_title' )->andReturn( $page_title );
		$subject->shouldReceive( 'menu_title' )->andReturn( $menu_title );
		$subject->shouldReceive( 'option_page' )->andReturn( $slug );

		$callback = [ $subject, 'settings_base_page' ];

		if ( $is_main_menu_page ) {
			WP_Mock::userFunction( 'add_menu_page' )
				->with( $page_title, $menu_title, $capability, $slug, $callback );
		} else {
			WP_Mock::userFunction( 'add_submenu_page' )
				->with( $parent_slug, $page_title, $menu_title, $capability, $slug, $callback );
		}

		$subject->add_settings_page();
	}

	/**
	 * Data provider for test_add_settings_page().
	 *
	 * @return array
	 */
	public function dp_test_add_settings_page() {
		return [
			'Main menu page' => [ true ],
			'Submenu page'   => [ false ],
		];
	}

	/**
	 * Test settings_base_page().
	 */
	public function test_settings_base_page() {
		$page = Mockery::mock( SettingsBase::class );
		$page->shouldReceive( 'settings_page' )->with()->once();

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'get_active_tab' )->once()->andReturn( $page );

		$subject->settings_base_page();
	}

	/**
	 * Test admin_enqueue_base_scripts().
	 */
	public function test_base_admin_enqueue_scripts() {
		$plugin_url     = 'http://test.test/wp-content/plugins/cyr2lat';
		$plugin_version = '1.0.0';

		$page = Mockery::mock( SettingsBase::class );
		$page->shouldReceive( 'admin_enqueue_scripts' )->with()->once();

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'get_active_tab' )->once()->andReturn( $page );
		$subject->shouldReceive( 'plugin_url' )->once()->andReturn( $plugin_url );
		$subject->shouldReceive( 'plugin_version' )->once()->andReturn( $plugin_version );

		WP_Mock::userFunction( 'wp_enqueue_style' )
			->with(
				SettingsBase::HANDLE,
				$plugin_url . '/assets/css/settings-base.css',
				[],
				$plugin_version
			)
			->once();

		$subject->base_admin_enqueue_scripts();
	}

	/**
	 * Test setup_sections().
	 *
	 * @param array $tabs Tabs.
	 *
	 * @dataProvider dp_test_setup_sections
	 * @throws ReflectionException ReflectionException.
	 * @noinspection NullCoalescingOperatorCanBeUsedInspection
	 */
	public function test_setup_sections( $tabs ) {
		$tab_option_page = 'cyr-to-lat';

		$tab = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$tab->shouldReceive( 'option_page' )->andReturn( $tab_option_page );

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'get_active_tab' )->once()->andReturn( $tab );

		$form_fields = $this->get_test_form_fields();

		$form_fields['iso9']['title'] = 'Some Section Title';

		$this->set_protected_property( $subject, 'form_fields', $form_fields );
		$this->set_protected_property( $subject, 'tabs', $tabs );

		foreach ( $form_fields as $form_field ) {
			$title = isset( $form_field['title'] ) ? $form_field['title'] : '';
			WP_Mock::userFunction( 'add_settings_section' )
				->with(
					$form_field['section'],
					$title,
					[ $tab, 'section_callback' ],
					$tab_option_page
				)
				->once();
		}

		$subject->setup_sections();
	}

	/**
	 * Data provider for test_setup_sections().
	 *
	 * @return array
	 */
	public function dp_test_setup_sections() {
		return [
			'No tabs'   => [ [] ],
			'Some tabs' => [ [ 'some tab' ] ],
		];
	}

	/**
	 * Test setup_tabs_section().
	 */
	public function test_setup_tabs_section() {
		$tab_option_page = 'cyr-to-lat';

		$tab = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$tab->shouldReceive( 'option_page' )->andReturn( $tab_option_page );

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'get_active_tab' )->once()->andReturn( $tab );

		WP_Mock::userFunction( 'add_settings_section' )
			->with(
				'tabs_section',
				'',
				[ $subject, 'tabs_callback' ],
				$tab_option_page
			)
			->once();

		$subject->setup_tabs_section();
	}

	/**
	 * Test tabs_callback().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_tabs_callback() {
		$option_page        = 'cyr-to-lat';
		$subject_class_name = 'Tables';
		$subject_page_title = 'Tables';
		$tab_class_name     = 'Converter';
		$tab_page_title     = 'Converter';
		$subject_url        = 'http://test.test/wp-admin/admin.php?page=cyr-to-lat';
		$subject_url_arg    = 'http://test.test/wp-admin/admin.php?page=cyr-to-lat';
		$tab_url_arg        = 'http://test.test/wp-admin/admin.php?page=cyr-to-lat&tab=converter';

		$tab = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$tab->shouldReceive( 'get_class_name' )->with()->andReturn( $tab_class_name );
		$tab->shouldReceive( 'page_title' )->with()->andReturn( $tab_page_title );

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'option_page' )->with()->twice()->andReturn( $option_page );
		$subject->shouldReceive( 'get_class_name' )->with()->andReturn( $subject_class_name );
		$subject->shouldReceive( 'page_title' )->with()->andReturn( $subject_page_title );
		$subject->shouldReceive( 'is_tab_active' )->with( $subject )->once()->andReturn( true );
		$subject->shouldReceive( 'is_tab_active' )->with( $tab )->once()->andReturn( false );

		$this->set_protected_property( $subject, 'tabs', [ $tab ] );

		WP_Mock::userFunction( 'menu_page_url' )
			->with( $option_page, false )->twice()->andReturn( $subject_url );
		WP_Mock::userFunction( 'add_query_arg' )
			->with( 'tab', strtolower( $subject_class_name ), $subject_url )->andReturn( $subject_url_arg );
		WP_Mock::userFunction( 'add_query_arg' )
			->with( 'tab', strtolower( $tab_class_name ), $subject_url )->andReturn( $tab_url_arg );

		$expected = '		<div class="ctl-settings-tabs">
					<a class="ctl-settings-tab active" href="http://test.test/wp-admin/admin.php?page=cyr-to-lat">
			' . $subject_page_title . '		</a>
				<a class="ctl-settings-tab" href="http://test.test/wp-admin/admin.php?page=cyr-to-lat&tab=converter">
			' . $tab_page_title . '		</a>
				</div>
		';

		ob_start();
		$subject->tabs_callback();
		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test is_tab_active().
	 *
	 * @param string|null $input      $_GET['tab'].
	 * @param bool        $is_tab     Is tab.
	 * @param string      $class_name Class name.
	 * @param bool        $expected   Expected.
	 *
	 * @dataProvider dp_test_is_tab_active
	 */
	public function test_is_tab_active( $input, $is_tab, $class_name, $expected ) {
		$tab = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$tab->shouldReceive( 'is_tab' )->with()->andReturn( $is_tab );
		$tab->shouldReceive( 'get_class_name' )->with()->andReturn( $class_name );

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();

		FunctionMocker::replace(
			'filter_input',
			function ( $type, $name, $filter ) use ( $input ) {
				if (
					INPUT_GET === $type &&
					'tab' === $name &&
					FILTER_SANITIZE_STRING === $filter
				) {
					return $input;
				}

				return null;
			}
		);

		self::assertSame( $expected, $subject->is_tab_active( $tab ) );
	}

	/**
	 * Data provider for test_is_tab_active().
	 *
	 * @return array
	 */
	public function dp_test_is_tab_active() {
		return [
			'No input, not a tab'     => [ null, false, 'any_class_name', true ],
			'No input, tab'           => [ null, true, 'any_class_name', false ],
			'Wrong input, not a tab'  => [ 'wrong', false, 'General', false ],
			'Wrong input, tab'        => [ 'wrong', true, 'General', false ],
			'Proper input, not a tab' => [ 'general', false, 'General', true ],
			'Proper input, tab'       => [ 'general', true, 'General', true ],
		];
	}

	/**
	 * Test get_tabs().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_get_tabs() {
		$tab     = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$tabs    = [ $tab ];
		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$this->set_protected_property( $subject, 'tabs', $tabs );
		self::assertSame( $tabs, $subject->get_tabs() );
	}

	/**
	 * Test get_active_tab().
	 *
	 * @throws ReflectionException ReflectionException.
	 * @noinspection JsonEncodingApiUsageInspection
	 */
	public function test_get_active_tab() {
		$tab = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_tab_active' )->with( $tab )->andReturn( true );

		$this->set_protected_property( $subject, 'tabs', [] );
		self::assertSame( $subject, $subject->get_active_tab() );

		$this->set_protected_property( $subject, 'tabs', [ $tab ] );
		self::assertSame(
			json_encode( $tab ),
			json_encode( $subject->get_active_tab() )
		);
	}

	/**
	 * Test setup_fields().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_setup_fields() {
		$option_group = 'cyr_to_lat_group';
		$option_name  = 'cyr_to_lat_settings';
		$option_page  = 'cyr-to-lat';

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->andReturn( true );
		$subject->shouldReceive( 'option_group' )->andReturn( $option_group );
		$subject->shouldReceive( 'option_name' )->andReturn( $option_name );
		$subject->shouldReceive( 'option_page' )->andReturn( $option_page );

		$form_fields_test_data = $this->get_test_form_fields();
		$this->set_protected_property( $subject, 'form_fields', $form_fields_test_data );

		WP_Mock::userFunction( 'register_setting' )
			->with( $option_group, $option_name )
			->once();

		foreach ( $form_fields_test_data as $key => $field ) {
			$field['field_id'] = $key;

			WP_Mock::userFunction( 'add_settings_field' )
				->with(
					$key,
					$field['label'],
					[ $subject, 'field_callback' ],
					$option_page,
					$field['section'],
					$field
				)
				->once();
		}

		$subject->setup_fields();
	}

	/**
	 * Test setup_fields() with empty form_fields.
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_setup_fields_with_empty_form_fields() {
		$option_group = 'cyr_to_lat_group';
		$option_name  = 'cyr_to_lat_settings';

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->andReturn( true );
		$subject->shouldReceive( 'option_group' )->andReturn( $option_group );
		$subject->shouldReceive( 'option_name' )->andReturn( $option_name );

		$this->set_protected_property( $subject, 'form_fields', [] );

		WP_Mock::userFunction( 'register_setting' )
			->with( $option_group, $option_name )
			->once();

		WP_Mock::userFunction( 'add_settings_field' )->never();

		$subject->setup_fields();
	}

	/**
	 * Test setup_fields() not on options screen.
	 */
	public function test_setup_fields_not_on_options_screen() {
		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->andReturn( false );

		WP_Mock::userFunction( 'register_setting' )->never();

		WP_Mock::userFunction( 'add_settings_field' )->never();

		$subject->setup_fields();
	}

	/**
	 * Test field_callback().
	 *
	 * @param array  $arguments Arguments.
	 * @param string $expected  Expected result.
	 *
	 * @dataProvider dp_test_field_callback
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function test_field_callback( $arguments, $expected ) {
		$option_name = 'cyr_to_lat_settings';

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'option_name' )->andReturn( $option_name );
		$subject->shouldReceive( 'get' )->with( $arguments['field_id'] )->andReturn( $arguments['default'] );

		WP_Mock::passthruFunction( 'wp_kses_post' );
		WP_Mock::passthruFunction( 'wp_kses' );

		WP_Mock::userFunction( 'checked' )->andReturnUsing(
			function ( $checked, $current, $echo ) {
				$result = '';
				if ( (string) $checked === (string) $current ) {
					$result = 'checked="checked"';
				}

				return $result;
			}
		);

		WP_Mock::userFunction( 'selected' )->andReturnUsing(
			function ( $checked, $current, $echo ) {
				$result = '';
				if ( (string) $checked === (string) $current ) {
					$result = 'selected="selected"';
				}

				return $result;
			}
		);

		ob_start();
		$subject->field_callback( $arguments );
		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Data provider for test_field_callback().
	 *
	 * @return array
	 */
	public function dp_test_field_callback() {
		return array_merge(
			$this->dp_wrong_field_callback(),
			$this->dp_text_field_callback(),
			$this->dp_password_field_callback(),
			$this->dp_number_field_callback(),
			$this->dp_text_area_field_callback(),
			$this->dp_check_box_field_callback(),
			$this->dp_radio_field_callback(),
			$this->dp_select_field_callback(),
			$this->dp_multiple_field_callback(),
			$this->dp_table_field_callback()
		);
	}

	/**
	 * Data provider for wrong field.
	 *
	 * @return array
	 */
	private function dp_wrong_field_callback() {
		return [
			'Wrong type' => [
				[
					'label'        => 'some label',
					'section'      => 'some_section',
					'type'         => 'unknown',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 'some_value',
					'field_id'     => 'some_id',
				],
				'',
			],
		];
	}

	/**
	 * Data provider for text field.
	 *
	 * @return array
	 */
	private function dp_text_field_callback() {
		return [
			'Text'                   => [
				[
					'label'        => 'some label',
					'section'      => 'some_section',
					'type'         => 'text',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 'some text',
					'field_id'     => 'some_id',
				],
				'<input name="cyr_to_lat_settings[some_id]"' .
				' id="some_id" type="text" placeholder="" value="some text" class="regular-text" />',
			],
			'Text with helper'       => [
				[
					'label'        => 'some label',
					'section'      => 'some_section',
					'type'         => 'text',
					'placeholder'  => '',
					'helper'       => 'This is helper',
					'supplemental' => '',
					'default'      => 'some text',
					'field_id'     => 'some_id',
				],
				'<span class="helper"><span class="helper-content">This is helper</span></span>' .
				'<input name="cyr_to_lat_settings[some_id]"' .
				' id="some_id" type="text" placeholder="" value="some text" class="regular-text" />',
			],
			'Text with supplemental' => [
				[
					'label'        => 'some label',
					'section'      => 'some_section',
					'type'         => 'text',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => 'This is supplemental',
					'default'      => 'some text',
					'field_id'     => 'some_id',
				],
				'<input name="cyr_to_lat_settings[some_id]"' .
				' id="some_id" type="text" placeholder="" value="some text" class="regular-text" />' .
				'<p class="description">This is supplemental</p>',
			],
		];
	}

	/**
	 * Data provider for password field.
	 *
	 * @return array
	 */
	private function dp_password_field_callback() {
		return [
			'Password' => [
				[
					'label'        => 'some label',
					'section'      => 'some_section',
					'type'         => 'password',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 'some password',
					'field_id'     => 'some_id',
				],
				'<input name="cyr_to_lat_settings[some_id]"' .
				' id="some_id" type="password" placeholder="" value="some password" class="regular-text" />',
			],
		];
	}

	/**
	 * Data provider for number field.
	 *
	 * @return array
	 */
	private function dp_number_field_callback() {
		return [
			'Number' => [
				[
					'label'        => 'some label',
					'section'      => 'some_section',
					'type'         => 'number',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 15,
					'field_id'     => 'some_id',
				],
				'<input name="cyr_to_lat_settings[some_id]"' .
				' id="some_id" type="number" placeholder="" value="15" class="regular-text" min="" max="" />',
			],
		];
	}

	/**
	 * Data provider for area field.
	 *
	 * @return array
	 */
	private function dp_text_area_field_callback() {
		return [
			'Textarea' => [
				[
					'label'        => 'some label',
					'section'      => 'some_section',
					'type'         => 'textarea',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => '<p>This is some<br>textarea</p>',
					'field_id'     => 'some_id',
				],
				'<textarea name="cyr_to_lat_settings[some_id]"' .
				' id="some_id" placeholder="" rows="5" cols="50"><p>This is some<br>textarea</p></textarea>',
			],
		];
	}

	/**
	 * Data provider for checkbox field.
	 *
	 * @return array
	 */
	private function dp_check_box_field_callback() {
		return [
			'Checkbox with empty value' => [
				[
					'label'        => 'checkbox with empty value',
					'section'      => 'some_section',
					'type'         => 'checkbox',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => '',
					'field_id'     => 'some_id',
				],
				'<fieldset><label for="some_id_1"><input id="some_id_1"' .
				' name="cyr_to_lat_settings[some_id][]" type="checkbox" value="yes"  />' .
				' </label><br/></fieldset>',
			],
			'Checkbox not checked'      => [
				[
					'label'        => 'checkbox not checked',
					'section'      => 'some_section',
					'type'         => 'checkbox',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 'no',
					'field_id'     => 'some_id',
				],
				'<fieldset><label for="some_id_1"><input id="some_id_1"' .
				' name="cyr_to_lat_settings[some_id][]" type="checkbox" value="yes"  />' .
				' </label><br/></fieldset>',
			],
			'Checkbox checked'          => [
				[
					'label'        => 'checkbox checked',
					'section'      => 'some_section',
					'type'         => 'checkbox',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 'yes',
					'field_id'     => 'some_id',
				],
				'<fieldset><label for="some_id_1"><input id="some_id_1"' .
				' name="cyr_to_lat_settings[some_id][]" type="checkbox" value="yes" checked="checked" />' .
				' </label><br/></fieldset>',
			],
		];
	}

	/**
	 * Data provider for radio field.
	 *
	 * @return array
	 */
	private function dp_radio_field_callback() {
		return array_merge(
			$this->dp_empty_radio_field_callback(),
			$this->dp_not_empty_radio_field_callback()
		);
	}

	/**
	 * Data provider for empty radio field.
	 *
	 * @return array
	 */
	private function dp_empty_radio_field_callback() {
		return [
			'Radio buttons empty options' => [
				[
					'label'        => 'radio buttons empty options',
					'section'      => 'some_section',
					'type'         => 'radio',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 1,
					'field_id'     => 'some_id',
				],
				'',
			],
			'Radio buttons not an array'  => [
				[
					'label'        => 'radio buttons not an array',
					'section'      => 'some_section',
					'type'         => 'radio',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 1,
					'options'      => 'green, yellow, red',
					'field_id'     => 'some_id',
				],
				'',
			],
		];
	}

	/**
	 * Data provider for not empty radio field.
	 *
	 * @return array
	 */
	private function dp_not_empty_radio_field_callback() {
		return [
			'Radio buttons' => [
				[
					'label'        => 'radio buttons',
					'section'      => 'some_section',
					'type'         => 'radio',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 1,
					'options'      => [ 'green', 'yellow', 'red' ],
					'field_id'     => 'some_id',
				],
				'<fieldset><label for="some_id_1"><input id="some_id_1"' .
				' name="cyr_to_lat_settings[some_id]" type="radio" value="0"  />' .
				' green</label><br/>' .
				'<label for="some_id_2"><input id="some_id_2"' .
				' name="cyr_to_lat_settings[some_id]" type="radio" value="1" checked="checked" />' .
				' yellow</label><br/>' .
				'<label for="some_id_3"><input id="some_id_3"' .
				' name="cyr_to_lat_settings[some_id]" type="radio" value="2"  />' .
				' red</label><br/></fieldset>',
			],
		];
	}

	/**
	 * Data provider for select field.
	 *
	 * @return array
	 */
	private function dp_select_field_callback() {
		return [
			'Select with empty options'        => [
				[
					'label'        => 'select with empty options',
					'section'      => 'some_section',
					'type'         => 'select',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 1,
					'field_id'     => 'some_id',
				],
				'',
			],
			'Select with options not an array' => [
				[
					'label'        => 'select with options not an array',
					'section'      => 'some_section',
					'type'         => 'select',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 1,
					'options'      => 'green, yellow, red',
					'field_id'     => 'some_id',
				],
				'',
			],
			'Select'                           => [
				[
					'label'        => 'select',
					'section'      => 'some_section',
					'type'         => 'select',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 1,
					'options'      => [ 'green', 'yellow', 'red' ],
					'field_id'     => 'some_id',
				],
				'<select name="cyr_to_lat_settings[some_id]">' .
				'<option value="0" >green</option>' .
				'<option value="1" selected="selected">yellow</option>' .
				'<option value="2" >red</option>' .
				'</select>',
			],
		];
	}

	/**
	 * Data provider for multiple field.
	 *
	 * @return array
	 */
	private function dp_multiple_field_callback() {
		return array_merge(
			$this->dp_empty_multiple_field_callback(),
			$this->dp_not_empty_multiple_field_callback()
		);
	}

	/**
	 * Data provider for empty multiple field.
	 *
	 * @return array
	 */
	private function dp_empty_multiple_field_callback() {
		return [
			'Multiple with empty options'        => [
				[
					'label'        => 'multiple with empty options',
					'section'      => 'some_section',
					'type'         => 'multiple',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 1,
					'field_id'     => 'some_id',
				],
				'',
			],
			'Multiple with options not an array' => [
				[
					'label'        => 'multiple with options not an array',
					'section'      => 'some_section',
					'type'         => 'multiple',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 1,
					'options'      => 'green, yellow, red',
					'field_id'     => 'some_id',
				],
				'',
			],
		];
	}

	/**
	 * Data provider for not empty multiple field.
	 *
	 * @return array
	 */
	private function dp_not_empty_multiple_field_callback() {
		return [
			'Multiple'                         => [
				[
					'label'        => 'multiple',
					'section'      => 'some_section',
					'type'         => 'multiple',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 1,
					'options'      => [ 'green', 'yellow', 'red' ],
					'field_id'     => 'some_id',
				],
				'<select multiple="multiple" name="cyr_to_lat_settings[some_id][]">' .
				'<option value="0" >green</option>' .
				'<option value="1" >yellow</option>' .
				'<option value="2" >red</option>' .
				'</select>',
			],
			'Multiple with multiple selection' => [
				[
					'label'        => 'multiple with multiple selection',
					'section'      => 'some_section',
					'type'         => 'multiple',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => [ 1, 2 ],
					'options'      => [ 'green', 'yellow', 'red' ],
					'field_id'     => 'some_id',
				],
				'<select multiple="multiple" name="cyr_to_lat_settings[some_id][]">' .
				'<option value="0" >green</option>' .
				'<option value="1" selected="selected">yellow</option>' .
				'<option value="2" selected="selected">red</option>' .
				'</select>',
			],
		];
	}

	/**
	 * Data provider for table field.
	 *
	 * @return array
	 */
	private function dp_table_field_callback() {
		return [
			'Table with non-array value' => [
				[
					'label'        => 'ISO9 Table',
					'section'      => 'iso9_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => 'some string',
					'field_id'     => 'iso9',
				],
				'',
			],
			'Table'                      => [
				[
					'label'        => 'ISO9 Table',
					'section'      => 'iso9_section',
					'type'         => 'table',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => '',
					'default'      => [
						'ю' => 'yu',
						'я' => 'ya',
					],
					'field_id'     => 'iso9',
				],
				'<div class="ctl-table-cell">' .
				'<label for="iso9-0">ю</label>' .
				'<input name="cyr_to_lat_settings[iso9][ю]"' .
				' id="iso9-0" type="text" placeholder="" value="yu" class="regular-text" />' .
				'</div>' .
				'<div class="ctl-table-cell">' .
				'<label for="iso9-1">я</label>' .
				'<input name="cyr_to_lat_settings[iso9][я]"' .
				' id="iso9-1" type="text" placeholder="" value="ya" class="regular-text" />' .
				'</div>',
			],
		];
	}

	/**
	 * Test field_callback() without field id.
	 */
	public function test_field_callback_without_field_id() {
		$subject = Mockery::mock( SettingsBase::class )->makePartial();

		$arguments = [];

		ob_start();
		$subject->field_callback( $arguments );
		self::assertSame( '', ob_get_clean() );
	}

	/**
	 * Test get().
	 *
	 * @param array  $settings    Plugin options.
	 * @param string $key         Setting name.
	 * @param mixed  $empty_value Empty value for this setting.
	 * @param mixed  $expected    Expected result.
	 *
	 * @dataProvider dp_test_get
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_get( array $settings, $key, $empty_value, $expected ) {
		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$subject->shouldReceive( 'init_settings' )->never();
		$this->set_protected_property( $subject, 'settings', $settings );

		if ( ! isset( $settings[ $key ] ) ) {
			$subject->shouldReceive( 'form_fields' )->andReturn( $this->get_test_settings() )->once();
		}

		self::assertSame( $expected, $subject->get( $key, $empty_value ) );
	}

	/**
	 * Data provider for test_get().
	 *
	 * @return array
	 */
	public function dp_test_get() {
		$test_data = $this->get_test_settings();

		return [
			'Empty key'        => [ $this->get_test_settings(), '', null, '' ],
			'Some key'         => [ $this->get_test_settings(), 'iso9', null, $test_data['iso9'] ],
			'Non-existent key' => [
				$this->get_test_settings(),
				'non-existent-key',
				[ 'default-value' ],
				[ 'default-value' ],
			],
		];
	}

	/**
	 * Test get() with no settings.
	 */
	public function test_get_with_no_settings() {
		$settings = $this->get_test_settings();
		$key      = 'iso9';
		$expected = $settings[ $key ];

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'init_settings' )->once()->andReturnUsing(
			function () use ( $subject, $settings ) {
				$this->set_protected_property( $subject, 'settings', $settings );
			}
		);

		self::assertSame( $expected, $subject->get( $key ) );
	}

	/**
	 * Test field_default().
	 *
	 * @param array  $field    Field.
	 * @param string $expected Expected result.
	 *
	 * @dataProvider dp_test_field_default
	 */
	public function test_field_default( array $field, $expected ) {
		$subject = Mockery::mock( SettingsBase::class )->makePartial();

		self::assertSame( $expected, $subject->field_default( $field ) );
	}

	/**
	 * Data provider for test_field_default().
	 *
	 * @return array
	 */
	public function dp_test_field_default() {
		return [
			'Empty field'        => [ [], '' ],
			'With default value' => [ [ 'default' => 'default_value' ], 'default_value' ],
		];
	}

	/**
	 * Test update_option().
	 *
	 * @param array  $settings Plugin options.
	 * @param string $key      Setting name.
	 * @param mixed  $value    Value for this setting.
	 * @param mixed  $expected Expected result.
	 *
	 * @dataProvider dp_test_update_option
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_update_option( $settings, $key, $value, $expected ) {
		$option_name = 'cyr_to_lat_settings';

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'init_settings' )->never();
		$subject->shouldReceive( 'option_name' )->once()->andReturn( $option_name );
		$this->set_protected_property( $subject, 'settings', $settings );

		WP_Mock::userFunction( 'update_option' )->with( $option_name, $expected )->once();

		$subject->update_option( $key, $value );

		self::assertSame( $expected, $this->get_protected_property( $subject, 'settings' ) );
	}

	/**
	 * Data provider for test_update_option().
	 *
	 * @return array
	 */
	public function dp_test_update_option() {
		return [
			'Empty key'         => [
				$this->get_test_settings(),
				'',
				null,
				array_merge( $this->get_test_settings(), [ '' => null ] ),
			],
			'Key without value' => [
				$this->get_test_settings(),
				'pageTitle',
				null,
				array_merge( $this->get_test_settings(), [ 'pageTitle' => null ] ),
			],
			'Key with value'    => [
				$this->get_test_settings(),
				'pageTitle',
				[ 'New Page Title' ],
				array_merge( $this->get_test_settings(), [ 'pageTitle' => [ 'New Page Title' ] ] ),
			],
			'Non-existent key'  => [
				$this->get_test_settings(),
				'non-existent-key',
				[ 'some value' ],
				array_merge( $this->get_test_settings(), [ 'non-existent-key' => [ 'some value' ] ] ),
			],
		];
	}

	/**
	 * Test update_option with no settings.
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_update_option_with_no_settings() {
		$option_name = 'cyr_to_lat_settings';
		$settings    = $this->get_test_settings();
		$key         = 'page_title';
		$value       = [
			'label'        => 'New page title',
			'section'      => 'first_section',
			'type'         => 'text',
			'placeholder'  => '',
			'helper'       => '',
			'supplemental' => '',
			'default'      => 'Table Viewer',
		];

		$expected         = $settings;
		$expected[ $key ] = $value;

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'init_settings' )->once()->andReturnUsing(
			function () use ( $subject, $settings ) {
				$this->set_protected_property( $subject, 'settings', $settings );
			}
		);
		$subject->shouldReceive( 'option_name' )->andReturn( $option_name );

		WP_Mock::userFunction( 'update_option' )->with( $option_name, $expected )->once();

		$subject->update_option( $key, $value );

		self::assertSame( $expected, $this->get_protected_property( $subject, 'settings' ) );
	}

	/**
	 * Test pre_update_option_filter().
	 *
	 * @param array $form_fields Form fields.
	 * @param mixed $value       New option value.
	 * @param mixed $old_value   Old option value.
	 * @param mixed $expected    Expected result.
	 *
	 * @dataProvider dp_test_pre_update_option_filter
	 */
	public function test_pre_update_option_filter( $form_fields, $value, $old_value, $expected ) {
		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'form_fields' )->andReturn( $form_fields );

		self::assertSame( $expected, $subject->pre_update_option_filter( $value, $old_value ) );
	}

	/**
	 * Data provider for test_pre_update_option_filter().
	 *
	 * @return array
	 */
	public function dp_test_pre_update_option_filter() {
		$old_value = [
			'iso9' => [ 'Б' => 'B' ],
			'bel'  => [ 'Б' => 'B' ],
		];

		$value = [
			'bel' => [
				'Б' => 'B1',
			],
		];

		$expected = [
			'iso9' => [ 'Б' => 'B' ],
			'bel'  => [ 'Б' => 'B1' ],
		];

		return [
			[ [], 'value', 'value', 'value' ],
			[ [], 'value', 'old_value', 'value' ],
			[
				[
					'no_checkbox' => [
						'label'        => 'some field',
						'section'      => 'some_section',
						'type'         => 'text',
						'placeholder'  => '',
						'helper'       => '',
						'supplemental' => '',
						'default'      => [ '' ],
					],
				],
				[ 'no_checkbox' => '0' ],
				[ 'no_checkbox' => '1' ],
				[ 'no_checkbox' => '0' ],
			],
			[
				[
					'some_checkbox' => [
						'label'        => 'some field',
						'section'      => 'some_section',
						'type'         => 'checkbox',
						'placeholder'  => '',
						'helper'       => '',
						'supplemental' => '',
						'default'      => [ '' ],
					],
				],
				[ 'some_checkbox' => '0' ],
				[ 'some_checkbox' => '1' ],
				[ 'some_checkbox' => 'no' ],
			],
			[
				[
					'some_checkbox' => [
						'label'        => 'some field',
						'section'      => 'some_section',
						'type'         => 'checkbox',
						'placeholder'  => '',
						'helper'       => '',
						'supplemental' => '',
						'default'      => [ '' ],
					],
				],
				[ 'some_checkbox' => '1' ],
				[ 'some_checkbox' => '0' ],
				[ 'some_checkbox' => 'yes' ],
			],
			[ [], $value, $old_value, $expected ],
		];
	}

	/**
	 * Test load_plugin_textdomain().
	 */
	public function test_load_plugin_text_domain() {
		$text_domain      = 'cyr2lat';
		$plugin_base_name = 'cyr2lat/cyr-to-lat.php';

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'text_domain' )->andReturn( $text_domain );
		$subject->shouldReceive( 'plugin_basename' )->andReturn( $plugin_base_name );

		WP_Mock::userFunction( 'load_plugin_textdomain' )
			->with( $text_domain, false, dirname( $plugin_base_name ) . '/languages/' )->once();

		$subject->load_plugin_textdomain();
	}

	/**
	 * Test is_options_screen().
	 *
	 * @param mixed   $current_screen    Current admin screen.
	 * @param boolean $is_main_menu_page It it the main menu page.
	 * @param boolean $expected          Expected result.
	 *
	 * @dataProvider dp_test_is_options_screen
	 */
	public function test_is_options_screen( $current_screen, $is_main_menu_page, $expected ) {
		$screen_id      = 'settings_page_cyr-to-lat';
		$main_screen_id = 'toplevel_page_cyr-to-lat';

		$subject = Mockery::mock( SettingsBase::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_main_menu_page' )->once()->andReturn( $is_main_menu_page );

		if ( $is_main_menu_page ) {
			$subject->shouldReceive( 'screen_id' )->andReturn( $main_screen_id );
		} else {
			$subject->shouldReceive( 'screen_id' )->andReturn( $screen_id );
		}

		WP_Mock::userFunction( 'get_current_screen' )->with()->once()->andReturn( $current_screen );

		self::assertSame( $expected, $subject->is_options_screen() );
	}

	/**
	 * Data provider for test_is_options_screen(0.
	 *
	 * @return array
	 */
	public function dp_test_is_options_screen() {
		return [
			'Current screen not set'        => [ null, false, false ],
			'Wrong screen'                  => [ (object) [ 'id' => 'something' ], false, false ],
			'Options screen'                => [ (object) [ 'id' => 'options' ], false, true ],
			'Plugin screen'                 => [ (object) [ 'id' => 'settings_page_cyr-to-lat' ], false, true ],
			'Plugin screen, main menu page' => [ (object) [ 'id' => 'toplevel_page_cyr-to-lat' ], true, true ],
		];
	}
}
