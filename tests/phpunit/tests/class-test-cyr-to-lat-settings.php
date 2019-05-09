<?php
/**
 * Test_Cyr_To_Lat_Settings class file
 *
 * @package cyr-to-lat
 */

use PHPUnit\Framework\TestCase;

/**
 * Class Test_Cyr_To_Lat_Settings
 *
 * @group settings
 */
class Test_Cyr_To_Lat_Settings extends TestCase {

	/**
	 * Test subject.
	 *
	 * @var object
	 */
	private $subject;

	/**
	 * Setup test
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * End test
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 */
	public function test_constructor() {
		$classname = 'Cyr_To_Lat_Settings';

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Set expectations for constructor calls.
		\WP_Mock::expectActionAdded( 'plugins_loaded', [ $mock, 'init' ] );

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock );

		$this->assertTrue( true );
	}

	/**
	 * Test init()
	 */
	public function test_init() {
		$subject = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial();
		$subject->shouldReceive( 'load_plugin_textdomain' )->once();
		$subject->shouldReceive( 'init_form_fields' )->once();
		$subject->shouldReceive( 'init_settings' )->once();
		$subject->shouldReceive( 'init_hooks' )->once();

		$subject->init();
		$this->assertTrue( true );
	}

	/**
	 * Test init_hooks()
	 */
	public function test_init_hooks() {
		$subject = new Cyr_To_Lat_Settings();

		\WP_Mock::passthruFunction( 'plugin_basename' );

		\WP_Mock::expectFilterAdded(
			'plugin_action_links_' . CYR_TO_LAT_FILE,
			[
				$subject,
				'add_settings_link',
			],
			10,
			4
		);

		\WP_Mock::expectActionAdded( 'admin_menu', [ $subject, 'add_settings_page' ] );
		\WP_Mock::expectActionAdded( 'current_screen', [ $subject, 'setup_sections' ] );
		\WP_Mock::expectActionAdded( 'current_screen', [ $subject, 'setup_fields' ] );

		\WP_Mock::expectFilterAdded(
			'pre_update_option_' . $subject::OPTION_NAME,
			[
				$subject,
				'pre_update_option_filter',
			],
			10,
			3
		);

		\WP_Mock::expectActionAdded( 'admin_enqueue_scripts', [ $subject, 'admin_enqueue_scripts' ] );

		$subject->init_hooks();
		$this->assertTrue( true );
	}

	/**
	 * Test add_settings_link()
	 */
	public function test_add_settings_link() {
		$subject = new Cyr_To_Lat_Settings();

		\WP_Mock::passthruFunction( 'admin_url' );

		$expected = [
			'settings' =>
				'<a href="options-general.php?page=' . $subject::PAGE .
				'" aria-label="View Cyr To Lat settings">Settings</a>',
		];

		$this->assertSame( $expected, $subject->add_settings_link( [], null, null, null ) );
	}

	/**
	 * Test init_form_fields()
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_init_form_fields() {
		$subject = new Cyr_To_Lat_Settings();

		$tables = Mockery::mock( 'overload:Cyr_To_Lat_Conversion_Tables' );
		$tables->shouldReceive( 'get' )->with()->andReturn( [ 'iso9' ] );
		$tables->shouldReceive( 'get' )->with( 'bel' )->andReturn( [ 'bel' ] );
		$tables->shouldReceive( 'get' )->with( 'uk' )->andReturn( [ 'uk' ] );
		$tables->shouldReceive( 'get' )->with( 'bg_BG' )->andReturn( [ 'bg_BG' ] );
		$tables->shouldReceive( 'get' )->with( 'mk_MK' )->andReturn( [ 'mk_MK' ] );
		$tables->shouldReceive( 'get' )->with( 'ka_GE' )->andReturn( [ 'ka_GE' ] );
		$tables->shouldReceive( 'get' )->with( 'kk' )->andReturn( [ 'kk' ] );

		$expected = $this->get_test_form_fields();

		$subject->init_form_fields();
		$this->assertSame( $expected, $subject->form_fields );
	}

	/**
	 * Test init_settings()
	 *
	 * @param mixed $settings Plugin settings.
	 *
	 * @dataProvider dp_test_init_settings
	 */
	public function test_init_settings( $settings ) {
		$subject = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial();

		\WP_Mock::userFunction(
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

		\WP_Mock::userFunction(
			'wp_list_pluck',
			[
				'args'   => [ $form_fields, 'default' ],
				'return' => $form_fields_pluck,
				'times'  => 1,
			]
		);

		$subject->settings = null;
		$subject->init_settings();

		if ( ! is_array( $settings ) ) {
			$expected = array_merge( array_fill_keys( array_keys( $form_fields ), '' ), $form_fields_pluck );
		} else {
			$expected = array_merge( $form_fields_pluck, $settings );
		}

		$this->assertSame( $expected, $subject->settings );
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
		$this->subject = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial();

		$this->subject->form_fields = null;

		if ( empty( $form_fields ) ) {
			$this->subject->shouldReceive( 'init_form_fields' )->andReturnUsing(
				function () {
					$this->subject->form_fields = $this->get_test_form_fields();
				}
			)->once();
		} else {
			$this->subject->form_fields = $form_fields;
		}

		$this->assertSame( $expected, $this->subject->get_form_fields() );
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
		$subject = new Cyr_To_Lat_Settings();

		$parent_slug = 'options-general.php';
		$page_title  = 'Cyr To Lat';
		$menu_title  = 'Cyr To Lat';
		$capability  = 'manage_options';
		$slug        = $subject::PAGE;
		$callback    = [ $subject, 'ctl_settings_page' ];

		\WP_Mock::userFunction(
			'add_submenu_page',
			[
				'args' => [ $parent_slug, $page_title, $menu_title, $capability, $slug, $callback ],
			]
		);

		$subject->add_settings_page();
		$this->assertTrue( true );
	}

	/**
	 * Test ctl_settings_page()
	 *
	 * @param boolean $is_ctl_options_screen Is plugin options screen.
	 *
	 * @dataProvider dp_test_ctl_settings_page
	 */
	public function test_ctl_settings_page( $is_ctl_options_screen ) {
		$subject = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_ctl_options_screen' )->andReturn( $is_ctl_options_screen );

		if ( $is_ctl_options_screen ) {
			\WP_Mock::userFunction(
				'do_settings_sections',
				[
					'args'  => [ $subject::PAGE ],
					'times' => 1,
				]
			);
			\WP_Mock::userFunction(
				'settings_fields',
				[
					'args'  => [ $subject::OPTION_GROUP ],
					'times' => 1,
				]
			);
			\WP_Mock::userFunction(
				'submit_button',
				[
					'args'  => [],
					'times' => 1,
				]
			);
			\WP_Mock::userFunction(
				'wp_nonce_field',
				[
					'args'  => [ $subject::OPTION_GROUP . '-options' ],
					'times' => 1,
				]
			);
			\WP_Mock::userFunction(
				'submit_button',
				[
					'args'  => [ 'Convert Existing Slugs', 'secondary', 'cyr2lat-convert' ],
					'times' => 1,
				]
			);

			$expected = '		<div class="wrap">
			<h2 id="title">
				Cyr To Lat Plugin Options			</h2>

			<form id="ctl-options" action="" method="post">
							</form>

			<form id="ctl-convert-existing-slugs" action="" method="post">
							</form>

			<div>
				<h2 id="donate">
					Donate				</h2>
				<p>
					Would you like to support the advancement of this plugin?				</p>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="BENCPARA8S224">
					<input
							type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif"
							name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img
							alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1"
							height="1">
				</form>

				<h2 id="appreciation">
					Your appreciation				</h2>
				<a
						target="_blank"
						href="https://wordpress.org/support/view/plugin-reviews/cyr2lat?rate=5#postform">
					Leave a ★★★★★ plugin review on WordPress.org				</a>
			</div>
		</div>
		';
			ob_start();
			$subject->ctl_settings_page();
			$this->assertSame( $expected, ob_get_clean() );
		} else {
			ob_start();
			$subject->ctl_settings_page();
			$this->assertEmpty( ob_get_clean() );
		}
	}

	/**
	 * Data provider for test_ctl_settings_page()
	 *
	 * @return array
	 */
	public function dp_test_ctl_settings_page() {
		return [
			[ false ],
			[ true ],
		];
	}

	/**
	 * Test setup_sections()
	 *
	 * @param boolean $is_ctl_options_screen Is plugin options screen.
	 *
	 * @dataProvider dp_test_setup_sections
	 */
	public function test_setup_sections( $is_ctl_options_screen ) {
		$subject = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_ctl_options_screen' )->andReturn( $is_ctl_options_screen );

		if ( $is_ctl_options_screen ) {
			\WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'iso9_section', 'ISO9 Table', [ $subject, 'cyr_to_lat_section' ], $subject::PAGE ],
					'times' => 1,
				]
			);
			\WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'bel_section', 'bel Table', [ $subject, 'cyr_to_lat_section' ], $subject::PAGE ],
					'times' => 1,
				]
			);
			\WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'uk_section', 'uk Table', [ $subject, 'cyr_to_lat_section' ], $subject::PAGE ],
					'times' => 1,
				]
			);
			\WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'bg_BG_section', 'bg_BG Table', [ $subject, 'cyr_to_lat_section' ], $subject::PAGE ],
					'times' => 1,
				]
			);
			\WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'mk_MK_section', 'mk_MK Table', [ $subject, 'cyr_to_lat_section' ], $subject::PAGE ],
					'times' => 1,
				]
			);
			\WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'ka_GE_section', 'ka_GE Table', [ $subject, 'cyr_to_lat_section' ], $subject::PAGE ],
					'times' => 1,
				]
			);
			\WP_Mock::userFunction(
				'add_settings_section',
				[
					'args'  => [ 'kk_section', 'kk Table', [ $subject, 'cyr_to_lat_section' ], $subject::PAGE ],
					'times' => 1,
				]
			);
		}

		$subject->setup_sections();

		$this->assertTrue( true );
	}

	/**
	 * Data provider for test_setup_sections()
	 *
	 * @return array
	 */
	public function dp_test_setup_sections() {
		return [
			[ false ],
			[ true ],
		];
	}

	/**
	 * Test cyr_to_lat_section()
	 */
	public function test_cyr_to_lat_section() {
		$subject = new Cyr_To_Lat_Settings();
		$subject->cyr_to_lat_section( [] );
		$this->assertTrue( true );
	}

	/**
	 * Test setup_fields()
	 *
	 * @param boolean $is_ctl_options_screen Is plugin options screen.
	 *
	 * @dataProvider dp_test_setup_fields
	 */
	public function test_setup_fields( $is_ctl_options_screen ) {
		$subject = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_ctl_options_screen' )->andReturn( $is_ctl_options_screen );

		if ( $is_ctl_options_screen ) {
			\WP_Mock::userFunction(
				'register_setting',
				[
					'args' => [ $subject::OPTION_GROUP, $subject::OPTION_NAME ],
				]
			);
			\WP_Mock::userFunction(
				'get_option',
				[
					'args'  => [ $subject::OPTION_NAME ],
					'times' => 1,
				]
			);
			$subject->form_fields = $this->get_test_form_fields();

			foreach ( $subject->form_fields as $key => $field ) {
				$field['field_id'] = $key;

				\WP_Mock::userFunction(
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

		$this->assertTrue( true );
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
		$subject = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial();

		if ( isset( $arguments['field_id'] ) ) {
			$subject->shouldReceive( 'get_option' )->with( $arguments['field_id'] )->andReturn( $arguments['default'] );

			\WP_Mock::passthruFunction( 'wp_kses_post' );
			\WP_Mock::userFunction(
				'checked',
				[
					'args'   => [ '', 'yes', false ],
					'return' => 'checked="checked"',
				]
			);
			\WP_Mock::userFunction(
				'checked',
				[
					'args'   => [ 'no', 'yes', false ],
					'return' => '',
				]
			);
			\WP_Mock::userFunction(
				'checked',
				[
					'args'   => [ 'yes', 'yes', false ],
					'return' => 'checked="checked"',
				]
			);
			\WP_Mock::userFunction(
				'checked',
				[
					'args'   => [ 1, 0, false ],
					'return' => '',
				]
			);
			\WP_Mock::userFunction(
				'checked',
				[
					'args'   => [ 1, 1, false ],
					'return' => 'checked="checked"',
				]
			);
			\WP_Mock::userFunction(
				'checked',
				[
					'args'   => [ 1, 2, false ],
					'return' => '',
				]
			);

			\WP_Mock::passthruFunction( 'wp_kses' );

			\WP_Mock::userFunction(
				'selected',
				[
					'args'   => [ 1, 0, false ],
					'return' => '',
				]
			);
			\WP_Mock::userFunction(
				'selected',
				[
					'args'   => [ 1, 1, false ],
					'return' => 'selected="selected"',
				]
			);
			\WP_Mock::userFunction(
				'selected',
				[
					'args'   => [ 2, 2, false ],
					'return' => 'selected="selected"',
				]
			);
			\WP_Mock::userFunction(
				'selected',
				[
					'args'   => [ 1, 2, false ],
					'return' => '',
				]
			);
		}

		ob_start();
		$subject->field_callback( $arguments );
		$this->assertSame( $expected, ob_get_clean() );
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
		$this->subject           = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial();
		$this->subject->settings = null;
		if ( empty( $settings ) ) {
			$this->subject->shouldReceive( 'init_settings' )->once()->andReturnUsing(
				function () {
					$this->subject->settings = $this->get_test_settings();
				}
			);
		} else {
			$this->subject->shouldReceive( 'init_settings' )->never();
			$this->subject->settings = $settings;

			if ( ! isset( $settings[ $key ] ) ) {
				$form_fields = $this->get_test_settings();
				$this->subject->shouldReceive( 'get_form_fields' )->andReturn( $form_fields )->once();
			}
		}

		$this->assertSame( $expected, $this->subject->get_option( $key, $empty_value ) );
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
		$subject = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial()
		                   ->shouldAllowMockingProtectedMethods();

		$this->assertSame( $expected, $subject->get_field_default( $field ) );
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
		$this->subject           = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial();
		$this->subject->settings = null;
		if ( empty( $settings ) ) {
			$this->subject->shouldReceive( 'init_settings' )->once()->andReturnUsing(
				function () {
					$this->subject->settings = $this->get_test_settings();
				}
			);
		} else {
			$this->subject->shouldReceive( 'init_settings' )->never();
			$this->subject->settings = $settings;
		}

		\WP_Mock::userFunction(
			'update_option',
			[
				'args'  => [ $this->subject::OPTION_NAME, $expected ],
				'times' => 1,
			]
		);

		$this->subject->set_option( $key, $value );

		$this->assertSame( $expected, $this->subject->settings );
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
		$subject = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial();
		$subject->shouldReceive( 'get_form_fields' )->andReturn( $form_fields );

		$option = 'option';

		$this->assertSame( $expected, $subject->pre_update_option_filter( $value, $old_value, $option ) );
	}

	/**
	 * Data provider for test_pre_update_option_filter()
	 *
	 * @return array
	 */
	public function dp_test_pre_update_option_filter() {
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
		];
	}

	/**
	 * Test admin_enqueue_scripts()
	 *
	 * @param boolean $is_ctl_options_screen Is plugin options screen.
	 *
	 * @dataProvider dp_test_admin_enqueue_scripts
	 */
	public function test_admin_enqueue_scripts( $is_ctl_options_screen ) {
		$subject = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_ctl_options_screen' )->andReturn( $is_ctl_options_screen );

		if ( $is_ctl_options_screen ) {
			\WP_Mock::userFunction(
				'wp_enqueue_script',
				[
					'args'  => [
						'cyr-to-lat-settings',
						CYR_TO_LAT_URL . '/dist/js/settings/app.js',
						[],
						CYR_TO_LAT_VERSION,
						true,
					],
					'times' => 1,
				]
			);
			\WP_Mock::userFunction(
				'wp_enqueue_style',
				[
					'args'  => [
						'cyr-to-lat-admin',
						CYR_TO_LAT_URL . '/css/cyr-to-lat-admin.css',
						[],
						CYR_TO_LAT_VERSION,
					],
					'times' => 1,
				]
			);
		}

		$subject->admin_enqueue_scripts();

		$this->assertTrue( true );
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
	 * Test load_plugin_textdomain()
	 */
	public function test_load_plugin_textdomain() {
		$subject = new Cyr_To_Lat_Settings();

		\WP_Mock::passthruFunction( 'plugin_basename' );
		\WP_Mock::userFunction(
			'load_plugin_textdomain',
			[
				'cyr2lat',
				false,
				dirname( CYR_TO_LAT_FILE ) . '/languages/',
			]
		);

		$subject->load_plugin_textdomain();

		$this->assertTrue( true );
	}

	/**
	 * Test is_ctl_options_screen()
	 *
	 * @param mixed   $current_screen Current admin screen.
	 * @param boolean $expected       Expected result.
	 *
	 * @dataProvider dp_test_is_ctl_options_screen
	 */
	public function test_is_ctl_options_screen( $current_screen, $expected ) {
		$subject = \Mockery::mock( Cyr_To_Lat_Settings::class )->makePartial()
		                   ->shouldAllowMockingProtectedMethods();

		\WP_Mock::userFunction(
			'get_current_screen',
			[
				'return' => $current_screen,
			]
		);

		$this->assertSame( $expected, $subject->is_ctl_options_screen() );
	}

	/**
	 * Data provider for dp_test_is_ctl_options_screen()
	 *
	 * @return array
	 */
	public function dp_test_is_ctl_options_screen() {
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
	 * @return array
	 */
	private function get_test_form_fields() {
		return [
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
		];
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
