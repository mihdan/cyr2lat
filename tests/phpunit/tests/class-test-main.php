<?php
/**
 * Test_Main class file
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedMethodInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Cyr_To_Lat;

use Exception;
use Mockery;
use ReflectionClass;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;
use WP_REST_Server;
use WP_Screen;
use wpdb;

/**
 * Class Test_Main
 *
 * @group main
 */
class Test_Main extends Cyr_To_Lat_TestCase {

	/**
	 * End test
	 */
	public function tearDown(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		unset( $GLOBALS['wp_version'], $GLOBALS['wpdb'], $GLOBALS['current_screen'], $_POST, $_GET );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @noinspection        NullPointerExceptionInspection
	 */
	public function test_constructor() {
		$classname = Main::class;

		Mockery::mock( 'overload:' . Settings::class );
		Mockery::mock( 'overload:' . Post_Conversion_Process::class );
		Mockery::mock( 'overload:' . Term_Conversion_Process::class );
		Mockery::mock( 'overload:' . Admin_Notices::class );
		Mockery::mock( 'overload:' . Converter::class );
		Mockery::mock( 'overload:' . WP_CLI::class );
		Mockery::mock( 'overload:' . ACF::class );

		FunctionMocker::replace(
			'defined',
			function ( $name ) {
				if ( 'WP_CLI' === $name ) {
					return true;
				}

				return null;
			}
		);

		FunctionMocker::replace(
			'constant',
			function ( $name ) {
				if ( 'WP_CLI' === $name ) {
					return true;
				}

				return null;
			}
		);

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Set expectations for constructor calls.
		$mock->expects( self::once() )->method( 'init' );

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock );
	}

	/**
	 * Test init()
	 */
	public function test_init() {
		$subject = Mockery::mock( Main::class )->makePartial();
		$subject->shouldReceive( 'init_hooks' )->once();

		$subject->init();
	}

	/**
	 * Test init() with CLI when CLI throws an Exception
	 */
	public function test_init_with_cli_error() {
		$subject = Mockery::mock( Main::class )->makePartial();
		$subject->shouldReceive( 'init_hooks' )->never();

		FunctionMocker::replace(
			'defined',
			function ( $name ) {
				if ( 'WP_CLI' === $name ) {
					return true;
				}

				return null;
			}
		);

		FunctionMocker::replace(
			'constant',
			function ( $name ) {
				if ( 'WP_CLI' === $name ) {
					return true;
				}

				return null;
			}
		);

		$add_command = FunctionMocker::replace(
			'\WP_CLI::add_command',
			function () {
				throw new Exception();
			}
		);

		$subject->init();

		$add_command->wasCalledWithOnce( [ 'cyr2lat', null ] );
	}

	/**
	 * Test init() with CLI
	 */
	public function test_init_with_cli() {
		$subject = Mockery::mock( Main::class )->makePartial();
		$subject->shouldReceive( 'init_hooks' )->once();

		FunctionMocker::replace(
			'defined',
			function ( $name ) {
				if ( 'WP_CLI' === $name ) {
					return true;
				}

				return null;
			}
		);

		FunctionMocker::replace(
			'constant',
			function ( $name ) {
				if ( 'WP_CLI' === $name ) {
					return true;
				}

				return null;
			}
		);

		$add_command = FunctionMocker::replace(
			'\WP_CLI::add_command',
			null
		);

		$subject->init();

		$add_command->wasCalledWithOnce( [ 'cyr2lat', null ] );
	}

	/**
	 * Test init_hooks()
	 *
	 * @param string $polylang Polylang is active.
	 *
	 * @dataProvider dp_test_init_hooks
	 */
	public function test_init_hooks( $polylang ) {
		$subject = Mockery::mock( Main::class )->makePartial();

		WP_Mock::expectFilterAdded( 'sanitize_title', [ $subject, 'sanitize_title' ], 9, 3 );
		WP_Mock::expectFilterAdded( 'sanitize_file_name', [ $subject, 'sanitize_filename' ], 10, 2 );
		WP_Mock::expectFilterAdded( 'wp_insert_post_data', [ $subject, 'sanitize_post_name' ], 10, 2 );

		FunctionMocker::replace(
			'class_exists',
			function ( $class ) use ( $polylang ) {
				if ( 'Polylang' === $class ) {
					return $polylang;
				}

				return null;
			}
		);
		if ( $polylang ) {
			WP_Mock::expectFilterAdded( 'locale', [ $subject, 'pll_locale_filter' ] );
		} else {
			WP_Mock::expectFilterNotAdded( 'locale', [ $subject, 'pll_locale_filter' ] );
		}

		$subject->init_hooks();
	}

	/**
	 * Data provider for dp_test_init_hooks().
	 *
	 * @return array
	 */
	public function dp_test_init_hooks() {
		return [
			[ true ],
			[ false ],
		];
	}

	/**
	 * Test that sanitize_title() does nothing when context is 'query'
	 */
	public function test_sanitize_title_query_context() {
		$subject = Mockery::mock( Main::class )->makePartial();

		$title     = 'some title';
		$raw_title = '';
		$context   = 'query';

		self::assertSame( $title, $subject->sanitize_title( $title, $raw_title, $context ) );
	}

	/**
	 * Test that sanitize_title() returns ctl_pre_sanitize_title filter value if set
	 */
	public function test_sanitize_title_filter_set() {
		$subject = Mockery::mock( Main::class )->makePartial();

		$title = 'some title';

		$filtered_title = 'filtered title';

		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, urldecode( $title ) )->reply( $filtered_title );

		self::assertSame( $filtered_title, $subject->sanitize_title( $title ) );
	}

	/**
	 * Test sanitize_title()
	 *
	 * @param string $title    Title to sanitize.
	 * @param string $expected Expected result.
	 *
	 * @dataProvider dp_test_sanitize_title
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_sanitize_title( $title, $expected ) {
		$subject = $this->get_subject();

		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, urldecode( $title ) )->reply( false );
		self::assertSame( $expected, $subject->sanitize_title( $title ) );
	}

	/**
	 * Data provider for test_sanitize_title
	 *
	 * @return array
	 */
	public function dp_test_sanitize_title() {
		return [
			'empty string'               => [
				'',
				'',
			],
			'default table'              => [
				'АБВГДЕЁЖЗИЙІКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯѢѲѴабвгдеёжзийіклмнопрстуфхцчшщъыьэюяѣѳѵ',
				'ABVGDEYOZHZIJIKLMNOPRSTUFHCZCHSHSHHYEYUYAYEFHYHabvgdeyozhzijiklmnoprstufhczchshshhyeyuyayefhyh',
			],
			'iconv'                      => [
				'Символ евро - €.',
				'Simvol evro - €.',
			],
			'most used prohibited chars' => [
				'z!"#$%&()*+,/:;<=>?@[\]^`{|}`Åz',
				'z!"#$%&()* ,/:;<=>?@[\]^`{|}`Åz',
			],
			'allowed chars'              => [
				"ABC-XYZ-abc-xyz-0123456789'_.",
				"ABC-XYZ-abc-xyz-0123456789'_.",
			],
			'plus minus'                 => [
				'ABC-XYZ-+abc-xyz',
				'ABC-XYZ- abc-xyz',
			],
			'series of minus signs'      => [
				'-ABC---XYZ-',
				'-ABC---XYZ-',
			],
			'urldecode'                  => [
				'%D0%81',
				'YO',
			],
		];
	}

	/**
	 * Test sanitize_title() for term
	 * Name of this function must be wp_insert_term() to use debug_backtrace in the tested method
	 *
	 * @param string $title    Title to sanitize.
	 * @param string $term     Term to sanitize.
	 * @param string $expected Expected result.
	 *
	 * @test
	 * @dataProvider dp_wp_insert_term
	 * @throws ReflectionException ReflectionException.
	 */
	public function wp_insert_term( $title, $term, $expected ) {
		global $wpdb;

		$subject = $this->get_subject();

		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, urldecode( $title ) )->reply( false );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb        = Mockery::mock( wpdb::class );
		$wpdb->terms = 'wp_terms';
		$wpdb->shouldReceive( 'prepare' )->once()->andReturn( '' );
		$wpdb->shouldReceive( 'get_var' )->once()->andReturn( $term );

		self::assertSame( $expected, $subject->sanitize_title( $title ) );
	}

	/**
	 * Data provider for wp_insert_term()
	 */
	public function dp_wp_insert_term() {
		return [
			[ 'title', 'term', 'term' ],
			[ 'title', '', 'title' ],
		];
	}

	/**
	 * Test sanitize_title() for term WC attribute taxonomy
	 *
	 * @param string $title                Title.
	 * @param bool   $is_wc                Is WooCommerce active.
	 * @param array  $attribute_taxonomies Attribute Taxonomies.
	 * @param bool   $expected             Expected result.
	 *
	 * @dataProvider dp_test_sanitize_title_for_wc_attribute_taxonomy
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_sanitize_title_for_wc_attribute_taxonomy(
		$title, $is_wc, $attribute_taxonomies, $expected
	) {
		FunctionMocker::replace(
			'function_exists',
			function ( $function_name ) use ( $is_wc ) {
				if ( 'wc_get_attribute_taxonomies' === $function_name ) {
					return $is_wc;
				}

				return null;

			}
		);

		WP_Mock::userFunction( 'wc_get_attribute_taxonomies' )->with()->andReturn( $attribute_taxonomies );

		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, urldecode( $title ) )->reply( false );

		$subject = $this->get_subject();
		$subject->shouldReceive( 'transliterate' )->times( $expected );

		$subject->sanitize_title( $title );
	}

	/**
	 * Data provider for test_sanitize_title_for_wc_attribute_taxonomy
	 *
	 * @return array
	 */
	public function dp_test_sanitize_title_for_wc_attribute_taxonomy() {
		$attribute_taxonomies = [
			'id:3' => (object) [
				'attribute_id'      => '3',
				'attribute_name'    => 'weight',
				'attribute_label'   => 'Weight',
				'attribute_type'    => 'select',
				'attribute_orderby' => 'menu_order',
				'attribute_public'  => '1',
			],
			'id:9' => (object) [
				'attribute_id'      => '9',
				'attribute_name'    => 'цвет',
				'attribute_label'   => 'Цвет',
				'attribute_type'    => 'select',
				'attribute_orderby' => 'menu_order',
				'attribute_public'  => '1',
			],
		];

		return [
			'no wc'             => [ 'color', false, null, 1 ],
			'no attr taxes'     => [ 'color', true, [], 1 ],
			'not in attr taxes' => [ 'color', true, $attribute_taxonomies, 1 ],
			'in attr taxes'     => [ 'цвет', true, $attribute_taxonomies, 0 ],
		];
	}

	/**
	 * Test transliterate()
	 *
	 * @param string $string   String to transliterate.
	 * @param string $expected Expected result.
	 *
	 * @dataProvider dp_test_transliterate
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_transliterate( $string, $expected ) {
		$subject = $this->get_subject();

		self::assertSame( $expected, $subject->transliterate( $string ) );
	}

	/**
	 * Data provider for test_transliterate
	 *
	 * @return array
	 */
	public function dp_test_transliterate() {
		$bad_multibyte_content = pack( 'C*', ...array_slice( unpack( 'C*', 'я' ), 1 ) );

		return [
			'empty string'          => [
				'',
				'',
			],
			'default table'         => [
				'АБВГДЕЁЖЗИЙІКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯѢѲѴабвгдеёжзийіклмнопрстуфхцчшщъыьэюяѣѳѵ',
				'ABVGDEYOZHZIJIKLMNOPRSTUFHCZCHSHSHHYEYUYAYEFHYHabvgdeyozhzijiklmnoprstufhczchshshhyeyuyayefhyh',
			],
			'bad multibyte content' => [
				$bad_multibyte_content,
				$bad_multibyte_content,
			],
		];
	}


	/**
	 * Test split_chinese_string().
	 *
	 * @param string $string   String.
	 * @param string $expected Expected result.
	 *
	 * @throws ReflectionException ReflectionException.
	 *
	 * @dataProvider dp_test_split_chinese_string
	 */
	public function test_split_chinese_string( $string, $expected ) {
		$locale = 'zh_CN';
		$table  = $this->get_conversion_table( $locale );
		$table  = $this->transpose_chinese_table( $table );

		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'is_chinese_locale' )->andReturn( true );

		$subject = Mockery::mock( Main::class )->makePartial();
		$this->set_protected_property( $subject, 'settings', $settings );

		self::assertSame( $expected, $subject->split_chinese_string( $string, $table ) );
	}

	/**
	 * Data provider for test_split_chinese_string
	 *
	 * @return array
	 */
	public function dp_test_split_chinese_string() {
		return [
			'general'     => [
				'我是俄罗斯人',
				'-我--是--俄--罗--斯--人-',
			],
			'less than 4' => [
				'俄罗斯',
				'俄罗斯',
			],
			'with Latin'  => [
				'我是 cool 俄罗斯 bool 人',
				'-我--是- cool -俄--罗--斯- bool -人-',
			],
		];
	}

	/**
	 * Test that sanitize_filename() returns ctl_pre_sanitize_filename filter value if set
	 */
	public function test_pre_sanitize_filename_filter_set() {
		$subject = Mockery::mock( Main::class )->makePartial();

		$filename     = 'filename.jpg';
		$filename_raw = '';

		$filtered_filename = 'filtered-filename.jpg';

		WP_Mock::onFilter( 'ctl_pre_sanitize_filename' )->with( false, $filename )->reply( $filtered_filename );

		self::assertSame( $filtered_filename, $subject->sanitize_filename( $filename, $filename_raw ) );
	}

	/**
	 * Test sanitize_filename()
	 *
	 * @param string $filename Filename to sanitize.
	 * @param string $expected Expected result.
	 *
	 * @dataProvider dp_test_sanitize_filename
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_sanitize_filename( $filename, $expected ) {
		WP_Mock::userFunction(
			'seems_utf8',
			[
				'args'   => [ $filename ],
				'return' => true,
			]
		);

		$subject = $this->get_subject();

		WP_Mock::onFilter( 'ctl_pre_sanitize_filename' )->with( false, $filename )->reply( false );

		FunctionMocker::replace(
			'function_exists',
			function ( $arg ) {
				return 'mb_strtolower' === $arg;
			}
		);

		self::assertSame( $expected, $subject->sanitize_filename( $filename, '' ) );

		FunctionMocker::replace(
			'function_exists',
			function ( $arg ) {
				return 'mb_strtolower' !== $arg;
			}
		);

		self::assertSame( $expected, $subject->sanitize_filename( $filename, '' ) );
	}

	/**
	 * Data provider for test_sanitize_title
	 *
	 * @return array
	 */
	public function dp_test_sanitize_filename() {
		return [
			'empty string'               => [
				'',
				'',
			],
			'default table'              => [
				'АБВГДЕЁЖЗИЙІКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯѢѲѴабвгдеёжзийіклмнопрстуфхцчшщъыьэюяѣѳѵ',
				'abvgdeyozhzijiklmnoprstufhczchshshhyeyuyayefhyhabvgdeyozhzijiklmnoprstufhczchshshhyeyuyayefhyh',
			],
			'iconv'                      => [
				'Символ евро - €.',
				'simvol evro - €.',
			],
			'most used prohibited chars' => [
				'z!"#$%&()*+,/:;<=>?@[\]^`{|}`Åz',
				'z!"#$%&()*+,/:;<=>?@[\]^`{|}`åz',
			],
			'allowed chars'              => [
				"ABC-XYZ-abc-xyz-0123456789'_.",
				"abc-xyz-abc-xyz-0123456789'_.",
			],
			'plus minus'                 => [
				'ABC-XYZ-+abc-xyz',
				'abc-xyz-+abc-xyz',
			],
			'series of minus signs'      => [
				'-ABC---XYZ-',
				'-abc---xyz-',
			],
		];
	}

	/**
	 * Test that sanitize_post_name() does nothing if no Block/Gutenberg editor is active
	 */
	public function test_sanitize_post_name_without_gutenberg() {
		$subject = Mockery::mock( Main::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$data = [ 'something' ];

		WP_Mock::userFunction(
			'has_filter',
			[
				'args'   => [ 'replace_editor', 'gutenberg_init' ],
				'return' => false,
			]
		);

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_version'] = '4.9';
		self::assertSame( $data, $subject->sanitize_post_name( $data ) );

		FunctionMocker::replace( 'function_exists', true );

		WP_Mock::userFunction(
			'is_plugin_active',
			[
				'times'  => 1,
				'args'   => [ 'classic-editor/classic-editor.php' ],
				'return' => true,
			]
		);

		WP_Mock::userFunction(
			'get_option',
			[
				'times'  => 1,
				'args'   => [ 'classic-editor-replace' ],
				'return' => 'replace',
			]
		);

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_version'] = '5.0';
		self::assertSame( $data, $subject->sanitize_post_name( $data ) );
	}

	/**
	 * Test that sanitize_post_name() does nothing if current screen is not post edit screen
	 */
	public function test_sanitize_post_name_not_post_edit_screen() {
		$data = [ 'something' ];

		WP_Mock::userFunction(
			'has_filter',
			[
				'args'   => [ 'replace_editor', 'gutenberg_init' ],
				'return' => false,
			]
		);

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_version'] = '5.0';

		$subject = Mockery::mock( Main::class )->makePartial()->shouldAllowMockingProtectedMethods();
		FunctionMocker::replace( 'function_exists', true );

		WP_Mock::userFunction(
			'is_plugin_active',
			[
				'args'   => [ 'classic-editor/classic-editor.php' ],
				'return' => false,
			]
		);

		$current_screen       = Mockery::mock( WP_Screen::class );
		$current_screen->base = 'not post';

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['current_screen'] = null;
		self::assertSame( $data, $subject->sanitize_post_name( $data ) );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['current_screen'] = $current_screen;
		self::assertSame( $data, $subject->sanitize_post_name( $data ) );
	}

	/**
	 * Test sanitize_post_name()
	 *
	 * @param array $data     Post data to sanitize.
	 * @param array $expected Post data expected after sanitization.
	 *
	 * @dataProvider dp_test_sanitize_post_name
	 */
	public function test_sanitize_post_name( $data, $expected ) {

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_version'] = '5.0';

		$subject = Mockery::mock( Main::class )->makePartial()->shouldAllowMockingProtectedMethods();
		FunctionMocker::replace( 'function_exists', true );

		WP_Mock::userFunction(
			'is_plugin_active',
			[
				'args'   => [ 'classic-editor/classic-editor.php' ],
				'return' => false,
			]
		);

		$current_screen       = Mockery::mock( WP_Screen::class );
		$current_screen->base = 'post';

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['current_screen'] = $current_screen;

		WP_Mock::userFunction(
			'sanitize_title',
			[
				'times'  => '0+',
				'args'   => [ $data['post_title'] ],
				'return' => 'sanitized(' . $data['post_title'] . ')',
			]
		);
		self::assertSame( $expected, $subject->sanitize_post_name( $data ) );
	}

	/**
	 * Data provider for test_sanitize_post_name()
	 */
	public function dp_test_sanitize_post_name() {
		return [
			[
				'post name set' => [
					'post_name'   => 'some post name',
					'post_title'  => 'some title',
					'post_status' => 'publish',
				],
				[
					'post_name'   => 'some post name',
					'post_title'  => 'some title',
					'post_status' => 'publish',
				],
			],
			[
				'no post name' => [
					'post_name'   => '',
					'post_title'  => 'title',
					'post_status' => 'publish',
				],
				[
					'post_name'   => 'sanitized(title)',
					'post_title'  => 'title',
					'post_status' => 'publish',
				],
			],
		];
	}

	/**
	 * Test pll_locale_filter() with REST.
	 */
	public function test_pll_locale_filter_with_rest() {
		$locale     = 'en_US';
		$pll_locale = 'ru';
		$data       = '';

		$subject = Mockery::mock( Main::class )->makePartial();

		FunctionMocker::replace(
			'defined',
			function ( $constant_name ) {
				return 'REST_REQUEST' === $constant_name;
			}
		);

		FunctionMocker::replace(
			'constant',
			function ( $name ) {
				return 'REST_REQUEST' === $name;
			}
		);

		$rest_server = new WP_REST_Server();
		WP_Mock::userFunction( 'rest_get_server' )->andReturn( $rest_server );

		FunctionMocker::replace(
			'WP_REST_Server::get_raw_data',
			function () use ( &$data ) {
				return $data;
			}
		);

		self::assertSame( $locale, $subject->pll_locale_filter( $locale ) );

		$data = '{"lang":"' . $pll_locale . '"}';
		self::assertSame( $pll_locale, $subject->pll_locale_filter( $locale ) );

		// Test that result is cached.
		FunctionMocker::replace( 'defined' );
		self::assertSame( $pll_locale, $subject->pll_locale_filter( $locale ) );
	}

	/**
	 * Test pll_locale_filter() on frontend.
	 */
	public function test_pll_locale_filter_on_frontend() {
		$locale = 'en_US';

		$subject = Mockery::mock( Main::class )->makePartial();

		FunctionMocker::replace( 'defined' );

		WP_Mock::userFunction( 'is_admin' )->with()->andReturn( false );

		self::assertSame( $locale, $subject->pll_locale_filter( $locale ) );
	}

	/**
	 * Test pll_locale_filter() with classic editor and post_id.
	 */
	public function test_pll_locale_filter_with_classic_editor_and_post_id() {
		$locale     = 'en_US';
		$pll_locale = 'ru';
		$post_id    = 23;

		$subject = Mockery::mock( Main::class )->makePartial();

		FunctionMocker::replace( 'defined' );

		WP_Mock::userFunction( 'is_admin' )->with()->andReturn( true );

		self::assertSame( $locale, $subject->pll_locale_filter( $locale ) );

		WP_Mock::userFunction( 'pll_get_post_language' )->with( $post_id )->andReturn( $pll_locale );

		FunctionMocker::replace(
			'filter_input',
			function ( $type, $var_name, $filter ) use ( $post_id ) {
				if ( INPUT_POST === $type && 'post_ID' === $var_name && FILTER_SANITIZE_STRING === $filter ) {
					return $post_id;
				}

				return null;
			}
		);

		self::assertSame( $locale, $subject->pll_locale_filter( $locale ) );

		$_POST['post_ID'] = $post_id;

		self::assertSame( $pll_locale, $subject->pll_locale_filter( $locale ) );

		// Test that result is cached.
		FunctionMocker::replace( 'filter_input' );
		self::assertSame( $pll_locale, $subject->pll_locale_filter( $locale ) );
	}

	/**
	 * Test pll_locale_filter() with classic editor and pll_post_id.
	 */
	public function test_pll_locale_filter_with_classic_editor_and_pll_post_id() {
		$locale     = 'en_US';
		$pll_locale = 'ru';
		$post_id    = 23;

		$subject = Mockery::mock( Main::class )->makePartial();

		FunctionMocker::replace( 'defined' );

		WP_Mock::userFunction( 'is_admin' )->with()->andReturn( true );

		self::assertSame( $locale, $subject->pll_locale_filter( $locale ) );

		WP_Mock::userFunction( 'pll_get_post_language' )->with( $post_id )->andReturn( $pll_locale );

		FunctionMocker::replace(
			'filter_input',
			function ( $type, $var_name, $filter ) use ( $post_id ) {
				if ( INPUT_POST === $type && 'pll_post_id' === $var_name && FILTER_SANITIZE_STRING === $filter ) {
					return $post_id;
				}

				return null;
			}
		);

		self::assertSame( $locale, $subject->pll_locale_filter( $locale ) );

		$_POST['pll_post_id'] = $post_id;

		self::assertSame( $pll_locale, $subject->pll_locale_filter( $locale ) );

		// Test that result is cached.
		FunctionMocker::replace( 'filter_input' );
		self::assertSame( $pll_locale, $subject->pll_locale_filter( $locale ) );
	}

	/**
	 * Test pll_locale_filter() with classic editor and post.
	 */
	public function test_pll_locale_filter_with_classic_editor_and_post() {
		$locale     = 'en_US';
		$pll_locale = 'ru';
		$post_id    = 23;

		$subject = Mockery::mock( Main::class )->makePartial();

		FunctionMocker::replace( 'defined' );

		WP_Mock::userFunction( 'is_admin' )->with()->andReturn( true );

		self::assertSame( $locale, $subject->pll_locale_filter( $locale ) );

		WP_Mock::userFunction( 'pll_get_post_language' )->with( $post_id )->andReturn( $pll_locale );

		FunctionMocker::replace(
			'filter_input',
			function ( $type, $var_name, $filter ) use ( $post_id ) {
				if ( INPUT_GET === $type && 'post' === $var_name && FILTER_SANITIZE_STRING === $filter ) {
					return $post_id;
				}

				return null;
			}
		);

		self::assertSame( $locale, $subject->pll_locale_filter( $locale ) );

		$_GET['post'] = $post_id;

		self::assertSame( $pll_locale, $subject->pll_locale_filter( $locale ) );

		// Test that result is cached.
		FunctionMocker::replace( 'filter_input' );
		self::assertSame( $pll_locale, $subject->pll_locale_filter( $locale ) );
	}

	/**
	 * Test pll_locale_filter() with term.
	 */
	public function test_pll_locale_filter_with_term() {
		$locale           = 'en_US';
		$pll_locale       = 'ru';
		$term_lang_choice = 92;

		$subject = Mockery::mock( Main::class )->makePartial();

		FunctionMocker::replace( 'defined' );

		WP_Mock::userFunction( 'is_admin' )->with()->andReturn( true );

		$pll_get_language       = Mockery::mock( PLL_Language::class );
		$pll_get_language->slug = $pll_locale;

		$model = Mockery::mock( PLL_Model::class );
		$model->shouldReceive( 'get_language' )->with( $term_lang_choice )->andReturn( $pll_get_language );

		$polylang        = Mockery::mock( Polylang::class );
		$polylang->model = $model;

		self::assertSame( $locale, $subject->pll_locale_filter( $locale ) );

		WP_Mock::userFunction( 'PLL' )->with()->andReturn( $polylang );

		FunctionMocker::replace(
			'filter_input',
			function ( $type, $var_name, $filter ) use ( $term_lang_choice ) {
				if ( INPUT_POST === $type && 'term_lang_choice' === $var_name && FILTER_SANITIZE_STRING === $filter ) {
					return $term_lang_choice;
				}

				return null;
			}
		);

		self::assertSame( $locale, $subject->pll_locale_filter( $locale ) );

		$_POST['term_lang_choice'] = $term_lang_choice;

		self::assertSame( $pll_locale, $subject->pll_locale_filter( $locale ) );

		// Test that result is cached.
		FunctionMocker::replace( 'filter_input' );
		self::assertSame( $pll_locale, $subject->pll_locale_filter( $locale ) );
	}

	/**
	 * Test prepare_in()
	 *
	 * @param mixed  $items    Items to prepare.
	 * @param string $format   Format.
	 * @param string $expected Expected result.
	 *
	 * @dataProvider dp_test_prepare_in
	 */
	public function test_prepare_in( $items, $format, $expected ) {
		global $wpdb;

		$items    = (array) $items;
		$how_many = count( $items );
		if ( $how_many > 0 ) {
			$format          = $format ? "'" . $format . "'" : "'%s'";
			$placeholders    = array_fill( 0, $how_many, $format );
			$prepared_format = implode( ',', $placeholders );
			$args            = array_merge( [ $prepared_format ], $items );
			$result          = sprintf( ...$args );
			$result          = str_replace( "''", '', $result );

			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$wpdb = Mockery::mock( wpdb::class );
			$wpdb->shouldReceive( 'prepare' )->zeroOrMoreTimes()->andReturn( $result );
		}

		$subject = Mockery::mock( Main::class )->makePartial();
		if ( $format ) {
			self::assertSame( $expected, $subject->prepare_in( $items, $format ) );
		} else {
			self::assertSame( $expected, $subject->prepare_in( $items ) );
		}
	}

	/**
	 * Data provider for test_prepare_in()
	 */
	public function dp_test_prepare_in() {
		return [
			[ null, null, '' ],
			[ '', null, '' ],
			[ [], null, '' ],
			[ [ '' ], null, '' ],
			[ [ 'post', 'page' ], null, "'post','page'" ],
			[ [ 'post', 'page' ], '%s', "'post','page'" ],
			[ [ '1', '2' ], '%d', "'1','2'" ],
			[ [ '13.5', '2' ], '%f', "'13.500000','2.000000'" ],
		];
	}

	/**
	 * Get test subject
	 *
	 * @return Mockery\Mock
	 * @throws ReflectionException ReflectionException.
	 */
	private function get_subject() {
		$locale     = 'ru_RU';
		$iso9_table = $this->get_conversion_table( $locale );

		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'get_table' )->andReturn( $iso9_table );
		$settings->shouldReceive( 'is_chinese_locale' )->andReturn( false );

		$process_all_posts = Mockery::mock( Post_Conversion_Process::class );
		$process_all_terms = Mockery::mock( Term_Conversion_Process::class );
		$admin_notices     = Mockery::mock( Admin_notices::class );

		$converter = Mockery::mock( Converter::class );
		$cli       = Mockery::mock( WP_CLI::class );
		$acf       = Mockery::mock( ACF::class );

		$subject = Mockery::mock( Main::class )->makePartial();

		$this->set_protected_property( $subject, 'settings', $settings );
		$this->set_protected_property( $subject, 'process_all_posts', $process_all_posts );
		$this->set_protected_property( $subject, 'process_all_terms', $process_all_terms );
		$this->set_protected_property( $subject, 'admin_notices', $admin_notices );
		$this->set_protected_property( $subject, 'converter', $converter );
		$this->set_protected_property( $subject, 'cli', $cli );
		$this->set_protected_property( $subject, 'acf', $acf );

		return $subject;
	}

	/**
	 * Transpose Chinese table.
	 *
	 * Chinese tables are stored in different way, to show them compact.
	 *
	 * @param array $table Table.
	 *
	 * @return array
	 */
	protected function transpose_chinese_table( $table ) {
		$transposed_table = [];
		foreach ( $table as $key => $item ) {
			$hieroglyphs = mb_str_split( $item );
			foreach ( $hieroglyphs as $hieroglyph ) {
				$transposed_table[ $hieroglyph ] = $key;
			}
		}

		return $transposed_table;
	}
}
