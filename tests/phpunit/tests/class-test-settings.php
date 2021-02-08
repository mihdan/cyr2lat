<?php
/**
 * Test_Settings class file
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedClassConstantInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Cyr_To_Lat;

use Mockery;
use ReflectionClass;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class Test_Settings
 *
 * @group settings
 */
class Test_Settings extends Cyr_To_Lat_TestCase {

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 * @noinspection NullPointerExceptionInspection
	 */
	public function test_constructor() {
		$classname = Settings::class;

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Set expectations for constructor calls.
		WP_Mock::expectActionAdded( 'plugins_loaded', [ $mock, 'init' ] );

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock );
	}

	/**
	 * Test init()
	 */
	public function test_init() {
		$subject = Mockery::mock( Settings::class )->makePartial();
		$subject->shouldReceive( 'load_plugin_textdomain' )->once();
		$subject->shouldReceive( 'init_form_fields' )->once();
		$subject->shouldReceive( 'init_settings' )->once();
		$subject->shouldReceive( 'init_hooks' )->once();

		$subject->init();
	}

	/**
	 * Test init_hooks()
	 */
	public function test_init_hooks() {
		$subject = new Settings();

		WP_Mock::passthruFunction( 'plugin_basename' );

		WP_Mock::expectFilterAdded(
			'plugin_action_links_' . $this->cyr_to_lat_file,
			[ $subject, 'add_settings_link' ],
			10,
			4
		);

		WP_Mock::expectActionAdded( 'admin_menu', [ $subject, 'add_settings_page' ] );
		WP_Mock::expectActionAdded( 'current_screen', [ $subject, 'setup_sections' ] );
		WP_Mock::expectActionAdded( 'current_screen', [ $subject, 'setup_fields' ] );

		WP_Mock::expectFilterAdded(
			'pre_update_option_' . $subject::OPTION_NAME,
			[ $subject, 'pre_update_option_filter' ],
			10,
			3
		);

		WP_Mock::expectActionAdded( 'admin_enqueue_scripts', [ $subject, 'admin_enqueue_scripts' ] );
		WP_Mock::expectActionAdded( 'in_admin_header', [ $subject, 'in_admin_header' ] );

		$subject->init_hooks();
	}

	/**
	 * Test add_settings_link()
	 */
	public function test_add_settings_link() {
		$subject = new Settings();

		WP_Mock::passthruFunction( 'admin_url' );

		$expected = [
			'settings' =>
				'<a href="options-general.php?page=' . $subject::PAGE .
				'" aria-label="View Cyr To Lat settings">Settings</a>',
		];

		self::assertSame( $expected, $subject->add_settings_link( [], null, null, null ) );
	}

	/**
	 * Test init_locales()
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_init_locales() {
		$subject = new Settings();

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
	 */
	public function test_init_form_fields() {
		$subject = new Settings();

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
		self::assertSame( $expected, $subject->form_fields );
	}

	/**
	 * Test init_settings()
	 *
	 * @param mixed $settings Plugin settings.
	 *
	 * @dataProvider dp_test_init_settings
	 */
	public function test_init_settings( $settings ) {
		$subject = Mockery::mock( Settings::class )->makePartial();

		WP_Mock::userFunction(
			'get_option',
			[
				'args'   => [ $subject::OPTION_NAME, null ],
				'return' => $settings,
				'times'  => 1,
			]
		);

		$form_fields = $this->get_test_form_fields();
		$subject->shouldReceive( 'get_form_fields' )->andReturn( $form_fields );

		$form_fields_pluck = [
			'iso9'  => [ 'iso9' ],
			'bel'   => [ 'bel' ],
			'uk'    => [ 'uk' ],
			'bg_BG' => [ 'bg_BG' ],
			'mk_MK' => [ 'mk_MK' ],
			'ka_GE' => [ 'ka_GE' ],
			'kk'    => [ 'kk' ],
		];

		WP_Mock::userFunction(
			'wp_list_pluck',
			[
				'args'   => [ $form_fields, 'default' ],
				'return' => $form_fields_pluck,
				'times'  => 1,
			]
		);

		// phpcs:ignore Generic.Commenting.DocComment.MissingShort
		/** @noinspection PhpUndefinedFieldInspection */
		$subject->settings = null;
		$subject->init_settings();

		if ( ! is_array( $settings ) ) {
			$expected = array_merge( array_fill_keys( array_keys( $form_fields ), '' ), $form_fields_pluck );
		} else {
			$expected = array_merge( $form_fields_pluck, $settings );
		}

		self::assertSame( $expected, $subject->settings );
	}

	/**
	 * Data provider for test_init_settings()
	 */
	public function dp_test_init_settings() {
		return [
			[ false ],
			[ $this->get_test_form_fields() ],
		];
	}

	/**
	 * Test get_form_fields()
	 *
	 * @param mixed $form_fields Form fields.
	 * @param array $expected    Expected result.
	 *
	 * @dataProvider dp_test_get_form_fields
	 */
	public function test_get_form_fields( $form_fields, $expected ) {
		$subject = Mockery::mock( Settings::class )->makePartial();

		// phpcs:ignore Generic.Commenting.DocComment.MissingShort
		/** @noinspection PhpUndefinedFieldInspection */
		$subject->form_fields = null;

		if ( empty( $form_fields ) ) {
			$subject->shouldReceive( 'init_form_fields' )->andReturnUsing(
				function () use ( $subject ) {
					// phpcs:ignore Generic.Commenting.DocComment.MissingShort
					/** @noinspection PhpUndefinedFieldInspection */
					$subject->form_fields = $this->get_test_form_fields();
				}
			)->once();
		} else {
			$subject->form_fields = $form_fields;
		}

		self::assertSame( $expected, $subject->get_form_fields() );
	}

	/**
	 * Data provider for test_get_form_fields()
	 */
	public function dp_test_get_form_fields() {
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
	 * Test add_settings_page()
	 */
	public function test_add_settings_page() {
		$subject = new Settings();

		$parent_slug = 'options-general.php';
		$page_title  = 'Cyr To Lat';
		$menu_title  = 'Cyr To Lat';
		$capability  = 'manage_options';
		$slug        = $subject::PAGE;
		$callback    = [ $subject, 'settings_page' ];

		WP_Mock::userFunction(
			'add_submenu_page',
			[
				'args' => [ $parent_slug, $page_title, $menu_title, $capability, $slug, $callback ],
			]
		);

		$subject->add_settings_page();
	}

	/**
	 * Test settings_page()
	 *
	 * @param boolean $is_options_screen Is plugin options screen.
	 *
	 * @dataProvider dp_test_settings_page
	 */
	public function test_settings_page( $is_options_screen ) {
		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->andReturn( $is_options_screen );

		if ( $is_options_screen ) {
			WP_Mock::userFunction(
				'do_settings_sections',
				[
					'args'  => [ $subject::PAGE ],
					'times' => 1,
				]
			);
			WP_Mock::userFunction(
				'settings_fields',
				[
					'args'  => [ $subject::OPTION_GROUP ],
					'times' => 1,
				]
			);
			WP_Mock::userFunction(
				'submit_button',
				[
					'args'  => [],
					'times' => 1,
				]
			);
			WP_Mock::userFunction(
				'wp_nonce_field',
				[
					'args'  => [ $subject::OPTION_GROUP . '-options' ],
					'times' => 1,
				]
			);
			WP_Mock::userFunction(
				'submit_button',
				[
					'args'  => [ 'Convert Existing Slugs', 'secondary', 'ctl-convert-button' ],
					'times' => 1,
				]
			);

			$expected = '		<div class="wrap">
			<h2 id="title">
				Cyr To Lat Plugin Options			</h2>

			<form id="ctl-options" action="" method="post">
							</form>

			<form id="ctl-convert-existing-slugs" action="" method="post">
				<input type="hidden" name="ctl-convert" />
							</form>

			<div id="appreciation">
				<h2>
					Your appreciation				</h2>
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
		} else {
			ob_start();
			$subject->settings_page();
			self::assertEmpty( ob_get_clean() );
		}
	}

	/**
	 * Data provider for test_settings_page()
	 *
	 * @return array
	 */
	public function dp_test_settings_page() {
		return [
			[ false ],
			[ true ],
		];
	}

	/**
	 * Test setup_sections()
	 *
	 * @param boolean $is_options_screen Is plugin options screen.
	 * @param boolean $locale                Current locale.
	 *
	 * @dataProvider dp_test_setup_sections
	 */
	public function test_setup_sections( $is_options_screen, $locale ) {
		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->andReturn( $is_options_screen );

		$subject->form_fields = $this->get_test_form_fields( $locale );

		WP_Mock::userFunction( 'get_locale' )->with()->andReturn( $locale );

		if ( $is_options_screen ) {
			$current = ( 'en_US' === $locale || 'ru_RU' === $locale ) ? __( '<br>(current)', 'cyr2lat' ) : '';
			WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [
						'iso9_section',
						'ISO9 Table' . $current,
						[ $subject, 'section_callback' ],
						$subject::PAGE,
					],
					'times' => 1,
				]
			);

			$current = ( 'bel' === $locale ) ? __( '<br>(current)', 'cyr2lat' ) : '';
			WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'bel_section', 'bel Table' . $current, [ $subject, 'section_callback' ], $subject::PAGE ],
					'times' => 1,
				]
			);

			$current = ( 'uk' === $locale ) ? __( '<br>(current)', 'cyr2lat' ) : '';
			WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'uk_section', 'uk Table' . $current, [ $subject, 'section_callback' ], $subject::PAGE ],
					'times' => 1,
				]
			);

			$current = ( 'bg_BG' === $locale ) ? __( '<br>(current)', 'cyr2lat' ) : '';
			WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'bg_BG_section', 'bg_BG Table' . $current, [ $subject, 'section_callback' ], $subject::PAGE ],
					'times' => 1,
				]
			);

			$current = ( 'mk_MK' === $locale ) ? __( '<br>(current)', 'cyr2lat' ) : '';
			WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'mk_MK_section', 'mk_MK Table' . $current, [ $subject, 'section_callback' ], $subject::PAGE ],
					'times' => 1,
				]
			);

			$current = ( 'sr_RS' === $locale ) ? __( '<br>(current)', 'cyr2lat' ) : '';
			WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'sr_RS_section', 'sr_RS Table' . $current, [ $subject, 'section_callback' ], $subject::PAGE ],
					'times' => 1,
				]
			);

			$current = ( 'el' === $locale ) ? __( '<br>(current)', 'cyr2lat' ) : '';
			WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'el_section', 'el Table' . $current, [ $subject, 'section_callback' ], $subject::PAGE ],
					'times' => 1,
				]
			);

			$current = ( 'hy' === $locale ) ? __( '<br>(current)', 'cyr2lat' ) : '';
			WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'hy_section', 'hy Table' . $current, [ $subject, 'section_callback' ], $subject::PAGE ],
					'times' => 1,
				]
			);

			$current = ( 'ka_GE' === $locale ) ? __( '<br>(current)', 'cyr2lat' ) : '';
			WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'ka_GE_section', 'ka_GE Table' . $current, [ $subject, 'section_callback' ], $subject::PAGE ],
					'times' => 1,
				]
			);

			$current = ( 'kk' === $locale ) ? __( '<br>(current)', 'cyr2lat' ) : '';
			WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'kk_section', 'kk Table' . $current, [ $subject, 'section_callback' ], $subject::PAGE ],
					'times' => 1,
				]
			);

			$current = ( 'he_IL' === $locale ) ? __( '<br>(current)', 'cyr2lat' ) : '';
			WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'he_IL_section', 'he_IL Table' . $current, [ $subject, 'section_callback' ], $subject::PAGE ],
					'times' => 1,
				]
			);

			$current = ( 'zh_CN' === $locale ) ? __( '<br>(current)', 'cyr2lat' ) : '';
			WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'zh_CN_section', 'zh_CN Table' . $current, [ $subject, 'section_callback' ], $subject::PAGE ],
					'times' => 1,
				]
			);
		}

		$subject->setup_sections();
	}

	/**
	 * Data provider for test_setup_sections()
	 *
	 * @return array
	 */
	public function dp_test_setup_sections() {
		return [
			[ false, null ],
			[ true, 'en_US' ],
			[ true, 'ru_RU' ],
			[ true, 'bel' ],
			[ true, 'uk' ],
			[ true, 'bg_BG' ],
			[ true, 'mk_MK' ],
			[ true, 'sr_RS' ],
			[ true, 'hy' ],
			[ true, 'ka_GE' ],
			[ true, 'kk' ],
			[ true, 'he_IL' ],
			[ true, 'zh_CN' ],
		];
	}

	/**
	 * Test section_callback()
	 */
	public function test_section_callback() {
		$locale = 'iso9';

		WP_Mock::userFunction( 'get_locale' )->andReturn( $locale );

		$subject = new Settings();

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
	 * Test setup_fields()
	 *
	 * @param boolean $is_options_screen Is plugin options screen.
	 *
	 * @dataProvider dp_test_setup_fields
	 */
	public function test_setup_fields( $is_options_screen ) {
		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->andReturn( $is_options_screen );

		if ( $is_options_screen ) {
			WP_Mock::userFunction(
				'register_setting',
				[
					'args' => [ $subject::OPTION_GROUP, $subject::OPTION_NAME ],
				]
			);
			// phpcs:ignore Generic.Commenting.DocComment.MissingShort
			/** @noinspection PhpUndefinedFieldInspection */
			$subject->form_fields = $this->get_test_form_fields();

			foreach ( $subject->form_fields as $key => $field ) {
				$field['field_id'] = $key;

				WP_Mock::userFunction(
					'add_settings_field',
					[
						'args'  => [
							$key,
							$field['label'],
							[ $subject, 'field_callback' ],
							$subject::PAGE,
							$field['section'],
							$field,
						],
						'times' => 1,
					]
				);
			}
		}

		$subject->setup_fields();
	}

	/**
	 * Data provider for test_setup_fields()
	 *
	 * @return array
	 */
	public function dp_test_setup_fields() {
		return [
			[ false ],
			[ true ],
		];
	}

	/**
	 * Test field_callback()
	 *
	 * @param array  $arguments Arguments.
	 * @param string $expected  Expected result.
	 *
	 * @dataProvider dp_test_field_callback
	 */
	public function test_field_callback( $arguments, $expected ) {
		$subject = Mockery::mock( Settings::class )->makePartial();

		if ( isset( $arguments['field_id'] ) ) {
			$subject->shouldReceive( 'get_option' )->with( $arguments['field_id'] )->andReturn( $arguments['default'] );

			WP_Mock::passthruFunction( 'wp_kses_post' );
			WP_Mock::userFunction(
				'checked',
				[
					'args'   => [ '', 'yes', false ],
					'return' => 'checked="checked"',
				]
			);
			WP_Mock::userFunction(
				'checked',
				[
					'args'   => [ 'no', 'yes', false ],
					'return' => '',
				]
			);
			WP_Mock::userFunction(
				'checked',
				[
					'args'   => [ 'yes', 'yes', false ],
					'return' => 'checked="checked"',
				]
			);
			WP_Mock::userFunction(
				'checked',
				[
					'args'   => [ 1, 0, false ],
					'return' => '',
				]
			);
			WP_Mock::userFunction(
				'checked',
				[
					'args'   => [ 1, 1, false ],
					'return' => 'checked="checked"',
				]
			);
			WP_Mock::userFunction(
				'checked',
				[
					'args'   => [ 1, 2, false ],
					'return' => '',
				]
			);

			WP_Mock::passthruFunction( 'wp_kses' );

			WP_Mock::userFunction(
				'selected',
				[
					'args'   => [ 1, 0, false ],
					'return' => '',
				]
			);
			WP_Mock::userFunction(
				'selected',
				[
					'args'   => [ 1, 1, false ],
					'return' => 'selected="selected"',
				]
			);
			WP_Mock::userFunction(
				'selected',
				[
					'args'   => [ 2, 2, false ],
					'return' => 'selected="selected"',
				]
			);
			WP_Mock::userFunction(
				'selected',
				[
					'args'   => [ 1, 2, false ],
					'return' => '',
				]
			);
		}

		ob_start();
		$subject->field_callback( $arguments );
		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Data provider for dp_test_field_callback()
	 *
	 * @return array
	 */
	public function dp_test_field_callback() {
		return [
			[ [], '' ],
			[
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
			[
				[
					'label'        => 'some label',
					'section'      => 'some_section',
					'type'         => 'unknown',
					'placeholder'  => '',
					'helper'       => 'This is helper',
					'supplemental' => '',
					'default'      => 'some_value',
					'field_id'     => 'some_id',
				],
				'<span class="helper"> This is helper</span>',
			],
			[
				[
					'label'        => 'some label',
					'section'      => 'some_section',
					'type'         => 'unknown',
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => 'This is supplemental',
					'default'      => 'some_value',
					'field_id'     => 'some_id',
				],
				'<p class="description">This is supplemental</p>',
			],
			[
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
				'<input name="cyr_to_lat_settings[some_id]" id="some_id" type="text" placeholder="" value="some text" class="regular-text" />',
			],
			[
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
				'<input name="cyr_to_lat_settings[some_id]" id="some_id" type="password" placeholder="" value="some password" class="regular-text" />',
			],
			[
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
				'<input name="cyr_to_lat_settings[some_id]" id="some_id" type="number" placeholder="" value="15" class="regular-text" />',
			],
			[
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
				'<textarea name="cyr_to_lat_settings[some_id]" id="some_id" placeholder="" rows="5" cols="50"><p>This is some<br>textarea</p></textarea>',
			],
			[
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
				'<fieldset><label for="some_id_1"><input id="some_id_1" name="cyr_to_lat_settings[some_id]" type="checkbox" value="yes" checked="checked" /> </label><br/></fieldset>',
			],
			[
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
				'<fieldset><label for="some_id_1"><input id="some_id_1" name="cyr_to_lat_settings[some_id]" type="checkbox" value="yes"  /> </label><br/></fieldset>',
			],
			[
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
				'<fieldset><label for="some_id_1"><input id="some_id_1" name="cyr_to_lat_settings[some_id]" type="checkbox" value="yes" checked="checked" /> </label><br/></fieldset>',
			],
			[
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
			[
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
			[
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
				'<fieldset><label for="some_id_1"><input id="some_id_1" name="cyr_to_lat_settings[some_id]" type="radio" value="0"  /> green</label><br/><label for="some_id_2"><input id="some_id_2" name="cyr_to_lat_settings[some_id]" type="radio" value="1" checked="checked" /> yellow</label><br/><label for="some_id_3"><input id="some_id_3" name="cyr_to_lat_settings[some_id]" type="radio" value="2"  /> red</label><br/></fieldset>',
			],
			[
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
			[
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
			[
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
				'<select name="cyr_to_lat_settings[some_id]"><option value="0" >green</option><option value="1" selected="selected">yellow</option><option value="2" >red</option></select>',
			],
			[
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
			[
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
			[
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
				'<select multiple="multiple" name="cyr_to_lat_settings[some_id][]"><option value="0" >green</option><option value="1" >yellow</option><option value="2" >red</option></select>',
			],
			[
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
				'<select multiple="multiple" name="cyr_to_lat_settings[some_id][]"><option value="0" >green</option><option value="1" selected="selected">yellow</option><option value="2" selected="selected">red</option></select>',
			],
			[
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
				'<div class="ctl-table-cell"><label for="iso9-0">ю</label><input name="cyr_to_lat_settings[iso9][ю]" id="iso9-0" type="text" placeholder="" value="yu" class="regular-text" /></div><div class="ctl-table-cell"><label for="iso9-1">я</label><input name="cyr_to_lat_settings[iso9][я]" id="iso9-1" type="text" placeholder="" value="ya" class="regular-text" /></div>',
			],
		];
	}

	/**
	 * Test get_option()
	 *
	 * @param array  $settings    Plugin options.
	 * @param string $key         Setting name.
	 * @param mixed  $empty_value Empty value for this setting.
	 * @param mixed  $expected    Expected result.
	 *
	 * @dataProvider dp_test_get_option
	 */
	public function test_get_option( $settings, $key, $empty_value, $expected ) {
		$subject = Mockery::mock( Settings::class )->makePartial();

		// phpcs:ignore Generic.Commenting.DocComment.MissingShort
		/** @noinspection PhpUndefinedFieldInspection */
		$subject->settings = null;
		if ( empty( $settings ) ) {
			$subject->shouldReceive( 'init_settings' )->once()->andReturnUsing(
				function () use ( $subject ) {
					// phpcs:ignore Generic.Commenting.DocComment.MissingShort
					/** @noinspection PhpUndefinedFieldInspection */
					$subject->settings = $this->get_test_settings();
				}
			);
		} else {
			$subject->shouldReceive( 'init_settings' )->never();
			$subject->settings = $settings;

			if ( ! isset( $settings[ $key ] ) ) {
				$form_fields = $this->get_test_settings();
				$subject->shouldReceive( 'get_form_fields' )->andReturn( $form_fields )->once();
			}
		}

		self::assertSame( $expected, $subject->get_option( $key, $empty_value ) );
	}

	/**
	 * Data provider for test_get_option()
	 */
	public function dp_test_get_option() {
		return [
			[ null, null, null, '' ],
			[ $this->get_test_settings(), null, null, '' ],
			[ $this->get_test_settings(), 'iso9', null, [ 'iso9' ] ],
			[ $this->get_test_settings(), 'non-existent-key', [ 'iso-100500' ], [ 'iso-100500' ] ],
		];
	}

	/**
	 * Test get_field_default()
	 *
	 * @param mixed $field    Field.
	 * @param mixed $expected Expected result.
	 *
	 * @dataProvider dp_test_get_field_default
	 */
	public function test_get_field_default( $field, $expected ) {
		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( $expected, $subject->get_field_default( $field ) );
	}

	/**
	 * Data provider for test_get_field_default.
	 *
	 * @return array
	 */
	public function dp_test_get_field_default() {
		return [
			[ null, '' ],
			[ '', '' ],
			[ [], '' ],
			[ [ 'default' => 'default_value' ], 'default_value' ],
		];
	}

	/**
	 * Test set_option()
	 *
	 * @param array  $settings Plugin options.
	 * @param string $key      Setting name.
	 * @param mixed  $value    Value for this setting.
	 * @param mixed  $expected Expected result.
	 *
	 * @dataProvider dp_test_set_option
	 */
	public function test_set_option( $settings, $key, $value, $expected ) {
		$subject = Mockery::mock( Settings::class )->makePartial();

		// phpcs:ignore Generic.Commenting.DocComment.MissingShort
		/** @noinspection PhpUndefinedFieldInspection */
		$subject->settings = null;
		if ( empty( $settings ) ) {
			$subject->shouldReceive( 'init_settings' )->once()->andReturnUsing(
				function () use ( $subject ) {
					// phpcs:ignore Generic.Commenting.DocComment.MissingShort
					/** @noinspection PhpUndefinedFieldInspection */
					$subject->settings = $this->get_test_settings();
				}
			);
		} else {
			$subject->shouldReceive( 'init_settings' )->never();
			$subject->settings = $settings;
		}

		WP_Mock::userFunction(
			'update_option',
			[
				'args'  => [ Settings::OPTION_NAME, $expected ],
				'times' => 1,
			]
		);

		$subject->set_option( $key, $value );

		self::assertSame( $expected, $subject->settings );
	}

	/**
	 * Data provider for test_set_option()
	 */
	public function dp_test_set_option() {
		return [
			[ null, null, null, array_merge( $this->get_test_settings(), [ '' => null ] ) ],
			[ $this->get_test_settings(), null, null, array_merge( $this->get_test_settings(), [ '' => null ] ) ],
			[ $this->get_test_settings(), 'iso9', null, array_merge( $this->get_test_settings(), [ 'iso9' => null ] ) ],
			[
				$this->get_test_settings(),
				'iso9',
				[ 'new-iso9' ],
				array_merge( $this->get_test_settings(), [ 'iso9' => [ 'new-iso9' ] ] ),
			],
			[
				$this->get_test_settings(),
				'non-existent-key',
				[ 'iso-100500' ],
				array_merge( $this->get_test_settings(), [ 'non-existent-key' => [ 'iso-100500' ] ] ),
			],
		];
	}

	/**
	 * Test pre_update_option_filter()
	 *
	 * @param array $form_fields Form fields.
	 * @param mixed $value       New option value.
	 * @param mixed $old_value   Old option value.
	 * @param mixed $expected    Expected result.
	 *
	 * @dataProvider dp_test_pre_update_option_filter
	 */
	public function test_pre_update_option_filter( $form_fields, $value, $old_value, $expected ) {
		$subject = Mockery::mock( Settings::class )->makePartial();
		$subject->shouldReceive( 'get_form_fields' )->andReturn( $form_fields );

		$option = 'option';

		self::assertSame( $expected, $subject->pre_update_option_filter( $value, $old_value, $option ) );
	}

	/**
	 * Data provider for test_pre_update_option_filter()
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
	 * Test admin_enqueue_scripts()
	 *
	 * @param boolean $is_options_screen Is plugin options screen.
	 *
	 * @dataProvider dp_test_admin_enqueue_scripts
	 */
	public function test_admin_enqueue_scripts( $is_options_screen ) {
		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->andReturn( $is_options_screen );

		if ( $is_options_screen ) {
			WP_Mock::userFunction( 'wp_enqueue_script' )->with(
				$subject::HANDLE,
				$this->cyr_to_lat_url . '/dist/js/settings/app.js',
				[],
				$this->cyr_to_lat_version,
				true,
			)->once();

			WP_Mock::userFunction( 'wp_localize_script' )->with(
				$subject::HANDLE,
				$subject::OBJECT,
				[
					'optionsSaveSuccessMessage' => 'Options saved.',
					'optionsSaveErrorMessage'   => 'Error saving options.',
				]
			)->once();

			WP_Mock::userFunction( 'wp_enqueue_style' )->with(
				$subject::HANDLE,
				$this->cyr_to_lat_url . '/css/cyr-to-lat-admin.css',
				[],
				$this->cyr_to_lat_version
			)->once();
		}

		$subject->admin_enqueue_scripts();
	}

	/**
	 * Data provider for test_admin_enqueue_scripts()
	 *
	 * @return array
	 */
	public function dp_test_admin_enqueue_scripts() {
		return [
			[ false ],
			[ true ],
		];
	}

	/**
	 * Test in_admin_header()
	 *
	 * @param boolean $is_options_screen Is plugin options screen.
	 *
	 * @dataProvider dp_test_in_admin_header
	 */
	public function test_in_admin_header( $is_options_screen ) {
		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->andReturn( $is_options_screen );

		$expected = '';

		if ( $is_options_screen ) {
			$expected = '		<div id="ctl-confirm-popup">
			<div id="ctl-confirm-content">
				<p>
					<strong>Important:</strong>
					This operation is irreversible. Please make sure that you have made backup copy of your database.				</p>
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
		}

		ob_start();
		$subject->in_admin_header();
		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Data provider for in_admin_header().
	 *
	 * @return array
	 */
	public function dp_test_in_admin_header() {
		return [
			[ false ],
			[ true ],
		];
	}

	/**
	 * Test load_plugin_textdomain()
	 */
	public function test_load_plugin_textdomain() {
		$subject = new Settings();

		WP_Mock::passthruFunction( 'plugin_basename' );
		WP_Mock::userFunction(
			'load_plugin_textdomain',
			[
				'cyr2lat',
				false,
				dirname( $this->cyr_to_lat_file ) . '/languages/',
			]
		);

		$subject->load_plugin_textdomain();
	}

	/**
	 * Test get_table()
	 */
	public function test_get_table() {
		$subject    = Mockery::mock( Settings::class )->makePartial();
		$locale     = 'not_existing_locale';
		$iso9_table = $this->get_conversion_table( $locale );

		$subject->shouldReceive( 'get_option' )->with( $locale )->andReturn( '' );
		$subject->shouldReceive( 'get_option' )->with( 'iso9' )->andReturn( $iso9_table );

		WP_Mock::userFunction(
			'get_locale',
			[
				'return' => $locale,
			]
		);

		self::assertSame( $iso9_table, $subject->get_table() );
	}

	/**
	 * Test is_chinese_locale()
	 *
	 * @param string  $locale   Current locale.
	 * @param boolean $expected Expected result.
	 *
	 * @dataProvider dp_test_is_chinese_locale
	 */
	public function test_is_chinese_locale( $locale, $expected ) {
		$subject = new Settings();

		WP_Mock::userFunction(
			'get_locale',
			[
				'return' => $locale,
			]
		);

		self::assertSame( $expected, $subject->is_chinese_locale() );
	}

	/**
	 * Data provider for test_is_chinese_locale
	 *
	 * @return array
	 */
	public function dp_test_is_chinese_locale() {
		return [
			[ 'zh_CN', true ],
			[ 'zh_HK', true ],
			[ 'zh_SG', true ],
			[ 'zh_TW', true ],
			[ 'some locale', false ],
		];
	}

	/**
	 * Test transpose_chinese_table()
	 *
	 * @param string $is_chinese_locale Current locale.
	 * @param array  $table             Conversion table.
	 * @param array  $expected          Expected result.
	 *
	 * @dataProvider dp_test_transpose_chinese_table
	 */
	public function test_transpose_chinese_table( $is_chinese_locale, $table, $expected ) {
		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_chinese_locale' )->andReturn( $is_chinese_locale );

		self::assertSame( $expected, $subject->transpose_chinese_table( $table ) );
	}

	/**
	 * Data provider for test_transpose_chinese_table
	 *
	 * @return array
	 */
	public function dp_test_transpose_chinese_table() {
		return [
			[
				false,
				[ 'я' => 'ya' ],
				[ 'я' => 'ya' ],
			],
			[
				true,
				[ 'A' => '啊阿吖嗄锕' ],
				[
					'啊' => 'A',
					'阿' => 'A',
					'吖' => 'A',
					'嗄' => 'A',
					'锕' => 'A',
				],
			],
		];
	}

	/**
	 * Test is_options_screen()
	 *
	 * @param mixed   $current_screen Current admin screen.
	 * @param boolean $expected       Expected result.
	 *
	 * @dataProvider dp_test_is_options_screen
	 */
	public function test_is_options_screen( $current_screen, $expected ) {
		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction(
			'get_current_screen',
			[
				'return' => $current_screen,
			]
		);

		self::assertSame( $expected, $subject->is_options_screen() );
	}

	/**
	 * Data provider for dp_test_is_options_screen()
	 *
	 * @return array
	 */
	public function dp_test_is_options_screen() {
		return [
			[ null, false ],
			[ (object) [ 'id' => 'something' ], false ],
			[ (object) [ 'id' => 'options' ], true ],
			[ (object) [ 'id' => 'settings_page_cyr-to-lat' ], true ],
		];
	}

	/**
	 * Get test form fields.
	 *
	 * @param string $locale Current locale.
	 *
	 * @return array
	 */
	private function get_test_form_fields( $locale = 'iso9' ) {
		$form_fields = [
			'iso9'  => [
				'label'        => 'ISO9 Table',
				'section'      => 'iso9_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'iso9' ],
			],
			'bel'   => [
				'label'        => 'bel Table',
				'section'      => 'bel_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'bel' ],
			],
			'uk'    => [
				'label'        => 'uk Table',
				'section'      => 'uk_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'uk' ],
			],
			'bg_BG' => [
				'label'        => 'bg_BG Table',
				'section'      => 'bg_BG_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'bg_BG' ],
			],
			'mk_MK' => [
				'label'        => 'mk_MK Table',
				'section'      => 'mk_MK_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'mk_MK' ],
			],
			'sr_RS' => [
				'label'        => 'sr_RS Table',
				'section'      => 'sr_RS_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'sr_RS' ],
			],
			'el'    => [
				'label'        => 'el Table',
				'section'      => 'el_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'el' ],
			],
			'hy'    => [
				'label'        => 'hy Table',
				'section'      => 'hy_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'hy' ],
			],
			'ka_GE' => [
				'label'        => 'ka_GE Table',
				'section'      => 'ka_GE_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'ka_GE' ],
			],
			'kk'    => [
				'label'        => 'kk Table',
				'section'      => 'kk_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'kk' ],
			],
			'he_IL' => [
				'label'        => 'he_IL Table',
				'section'      => 'he_IL_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'he_IL' ],
			],
			'zh_CN' => [
				'label'        => 'zh_CN Table',
				'section'      => 'zh_CN_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => [ 'zh_CN' ],
			],
		];

		$locale = isset( $form_fields[ $locale ] ) ? $locale : 'iso9';

		$form_fields[ $locale ]['label'] .= '<br>(current)';

		return $form_fields;
	}

	/**
	 * Get test settings.
	 *
	 * @return array
	 */
	private function get_test_settings() {
		return [
			'iso9'  => [ 'iso9' ],
			'bel'   => [ 'bel' ],
			'uk'    => [ 'uk' ],
			'bg_BG' => [ 'bg_BG' ],
			'mk_MK' => [ 'mk_MK' ],
			'ka_GE' => [ 'ka_GE' ],
			'kk'    => [ 'kk' ],
		];
	}
}
