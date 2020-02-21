<?php
/**
 * Test_Main class file
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat;

use Cyr_To_Lat\Symfony\Polyfill\Mbstring\Mbstring;
use Mockery;
use ReflectionClass;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
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
	public function tearDown() {
		unset( $GLOBALS['wp_version'] );
		unset( $GLOBALS['wpdb'] );
		unset( $GLOBALS['current_screen'] );
	}

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_constructor() {
		$classname = __NAMESPACE__ . '\Main';

		Mockery::mock( 'overload:' . Settings::class );
		Mockery::mock( 'overload:' . Converter::class );
		Mockery::mock( 'overload:' . WP_CLI::class );
		Mockery::mock( 'overload:' . ACF::class );

		if ( ! defined( 'WP_CLI' ) ) {
			define( 'WP_CLI', true );
		}

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Set expectations for constructor calls.
		$mock->expects( $this->once() )->method( 'init' );

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
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_init_with_cli_error() {
		$subject = Mockery::mock( Main::class )->makePartial();
		$subject->shouldReceive( 'init_hooks' )->never();

		if ( ! defined( 'WP_CLI' ) ) {
			define( 'WP_CLI', true );
		}

		$wp_cli = \Mockery::mock( 'alias:WP_CLI' );

		$subject->init();
	}

	/**
	 * Test init() with CLI
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_init_with_cli() {
		$subject = Mockery::mock( Main::class )->makePartial();
		$subject->shouldReceive( 'init_hooks' )->once();

		if ( ! defined( 'WP_CLI' ) ) {
			define( 'WP_CLI', true );
		}

		$wp_cli = \Mockery::mock( 'alias:WP_CLI' );
		$wp_cli->shouldReceive( 'add_command' )->andReturn( null );

		$subject->init();
	}

	/**
	 * Test init_hooks()
	 */
	public function test_init_hooks() {
		\WP_Mock::userFunction( 'did_action' )->with( 'wpml_after_startup' )->andReturn( false );

		$subject = $this->get_subject();

		\WP_Mock::expectFilterAdded( 'wp_unique_post_slug', [ $subject, 'wp_unique_post_slug_filter' ], 10, 6 );
		\WP_Mock::expectFilterAdded( 'wp_unique_term_slug', [ $subject, 'wp_unique_term_slug_filter' ], 10, 3 );
		\WP_Mock::expectFilterAdded( 'pre_term_slug', [ $subject, 'pre_term_slug_filter' ], 10, 2 );

		\WP_Mock::expectFilterAdded( 'sanitize_file_name', [ $subject, 'ctl_sanitize_filename' ], 10, 2 );
		\WP_Mock::expectFilterAdded( 'wp_insert_post_data', [ $subject, 'ctl_sanitize_post_name' ], 10, 2 );

		$subject->init_hooks();
	}

	/**
	 * Test init_hooks with WPML()
	 */
	public function test_init_hooks_with_wpml() {
		\WP_Mock::userFunction( 'did_action' )->with( 'wpml_after_startup' )->andReturn( true );

		$subject = $this->get_subject();

		\WP_Mock::expectFilterAdded( 'sanitize_title', [ $subject, 'ctl_sanitize_title' ], 9, 3 );

		\WP_Mock::expectFilterAdded( 'wp_unique_post_slug', [ $subject, 'wp_unique_post_slug_filter' ], 10, 6 );
		\WP_Mock::expectFilterAdded( 'wp_unique_term_slug', [ $subject, 'wp_unique_term_slug_filter' ], 10, 3 );
		\WP_Mock::expectFilterAdded( 'pre_term_slug', [ $subject, 'pre_term_slug_filter' ], 10, 2 );

		\WP_Mock::expectFilterAdded( 'sanitize_file_name', [ $subject, 'ctl_sanitize_filename' ], 10, 2 );
		\WP_Mock::expectFilterAdded( 'wp_insert_post_data', [ $subject, 'ctl_sanitize_post_name' ], 10, 2 );

		$subject->init_hooks();
	}

	/**
	 * Test that ctl_sanitize_title() does nothing when context is 'query'
	 */
	public function test_ctl_sanitize_title_query_context() {
		$subject = $this->get_subject();

		$title     = 'some title';
		$raw_title = '';
		$context   = 'query';

		$this->assertSame( $title, $subject->ctl_sanitize_title( $title, $raw_title, $context ) );
	}

	/**
	 * Test that ctl_sanitize_title() returns ctl_pre_sanitize_title filter value if set
	 */
	public function test_ctl_sanitize_title_filter_set() {
		$subject = $this->get_subject();

		$title     = 'some title';

		$filtered_title = 'filtered title';

		\WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, urldecode( $title ) )->reply( $filtered_title );

		$this->assertSame( $filtered_title, $subject->ctl_sanitize_title( $title ) );
	}

	/**
	 * Test ctl_sanitize_title()
	 *
	 * @param string $title    Title to sanitize.
	 * @param string $expected Expected result.
	 *
	 * @dataProvider dp_test_ctl_sanitize_title
	 */
	public function test_ctl_sanitize_title( $title, $expected ) {
		$locale     = 'ru_RU';
		$iso9_table = $this->get_conversion_table( $locale );

		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'get_table' )->andReturn( $iso9_table );
		$settings->shouldReceive( 'is_chinese_locale' )->andReturn( false );

		$converter = $this->getMockBuilder( 'Converter' )->disableOriginalConstructor()->getMock();
		$cli       = $this->getMockBuilder( 'WP_CLI' )->disableOriginalConstructor()->getMock();
		$acf       = $this->getMockBuilder( 'ACF' )->disableOriginalConstructor()->getMock();

		$subject = new Main( $settings, $converter, $cli, $acf );

		\WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, urldecode( $title ) )->reply( false );
		$this->assertSame( $expected, $subject->ctl_sanitize_title( $title ) );
	}

	/**
	 * Data provider for test_ctl_sanitize_title
	 *
	 * @return array
	 */
	public function dp_test_ctl_sanitize_title() {
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
	 * Test ctl_sanitize_title() for term
	 * Name of this function must be wp_insert_term() to use debug_backtrace in the tested method
	 *
	 * @param string $title    Title to sanitize.
	 * @param string $term     Term to sanitize.
	 * @param string $expected Expected result.
	 *
	 * @test
	 * @dataProvider dp_wp_insert_term
	 */
	public function wp_insert_term( $title, $term, $expected ) {
		global $wpdb;

		$locale     = 'ru_RU';
		$iso9_table = $this->get_conversion_table( $locale );

		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'get_table' )->andReturn( $iso9_table );
		$settings->shouldReceive( 'is_chinese_locale' )->andReturn( false );

		$converter = $this->getMockBuilder( 'Converter' )->disableOriginalConstructor()->getMock();
		$cli       = $this->getMockBuilder( 'WP_CLI' )->disableOriginalConstructor()->getMock();
		$acf       = $this->getMockBuilder( 'ACF' )->disableOriginalConstructor()->getMock();

		$subject = new Main( $settings, $converter, $cli, $acf );

		\WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, urldecode( $title ) )->reply( false );

		$wpdb        = Mockery::mock( wpdb::class );
		$wpdb->terms = 'wp_terms';
		$wpdb->shouldReceive( 'prepare' )->once()->andReturn( '' );
		$wpdb->shouldReceive( 'get_var' )->once()->andReturn( $term );

		$this->assertSame( $expected, $subject->ctl_sanitize_title( $title ) );
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
	 * Test wp_unique_post_slug_filter()
	 */
	public function test_wp_unique_post_slug_filter() {
		$slug = 'post_slug';
		$post_ID = 5;
		$post_status = 'some_status';
		$post_type = 'some_type';
		$post_parent = 2;
		$original_slug = 'some_slug';

		$mock = \Mockery::mock( Main::class )->makePartial();
		$mock->shouldReceive( 'transliterate' )->with( $slug )->once();

		$mock->wp_unique_post_slug_filter( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug );
	}

	/**
	 * Test wp_unique_term_slug_filter()
	 */
	public function test_wp_unique_term_slug_filter() {
		$slug = 'term_slug';
		$term = (object) [ 'term_id' => 5 ];
		$original_slug = 'some_slug';

		$mock = \Mockery::mock( Main::class )->makePartial();
		$mock->shouldReceive( 'transliterate' )->with( $slug )->once();

		$mock->wp_unique_term_slug_filter( $slug, $term, $original_slug );
	}

	/**
	 * Test pre_term_slug_filter()
	 */
	public function test_pre_term_slug_filter() {
		$value = 'term_slug';
		$taxonomy = 'tax_slug';

		$mock = \Mockery::mock( Main::class )->makePartial();
		$mock->shouldReceive( 'transliterate' )->with( $value )->once();

		$mock->pre_term_slug_filter( $value, $taxonomy );
	}

	/**
	 * Test transliterate()
	 *
	 * @param string $string   String to transliterate.
	 * @param string $expected Expected result.
	 *
	 * @dataProvider dp_test_transliterate
	 */
	public function test_transliterate( $string, $expected ) {
		$locale     = 'ru_RU';
		$iso9_table = $this->get_conversion_table( $locale );

		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'get_table' )->andReturn( $iso9_table );
		$settings->shouldReceive( 'is_chinese_locale' )->andReturn( false );

		$converter = $this->getMockBuilder( 'Converter' )->disableOriginalConstructor()->getMock();
		$cli       = $this->getMockBuilder( 'WP_CLI' )->disableOriginalConstructor()->getMock();
		$acf       = $this->getMockBuilder( 'ACF' )->disableOriginalConstructor()->getMock();

		$subject = new Main( $settings, $converter, $cli, $acf );

		$this->assertSame( $expected, $subject->transliterate( $string ) );
	}

	/**
	 * Data provider for test_ctl_sanitize_title
	 *
	 * @return array
	 */
	public function dp_test_transliterate() {
		return [
			'empty string'               => [
				'',
				'',
			],
			'default table'              => [
				'АБВГДЕЁЖЗИЙІКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯѢѲѴабвгдеёжзийіклмнопрстуфхцчшщъыьэюяѣѳѵ',
				'ABVGDEYOZHZIJIKLMNOPRSTUFHCZCHSHSHHYEYUYAYEFHYHabvgdeyozhzijiklmnoprstufhczchshshhyeyuyayefhyh',
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

		$this->assertSame( $expected, $subject->split_chinese_string( $string, $table ) );
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
	 * Test that ctl_sanitize_filename() returns ctl_pre_sanitize_filename filter value if set
	 */
	public function test_ctl_pre_sanitize_filename_filter_set() {
		$subject = $this->get_subject();

		$filename     = 'filename.jpg';
		$filename_raw = '';

		$filtered_filename = 'filtered-filename.jpg';

		\WP_Mock::onFilter( 'ctl_pre_sanitize_filename' )->with( false, $filename )->reply( $filtered_filename );

		$this->assertSame( $filtered_filename, $subject->ctl_sanitize_filename( $filename, $filename_raw ) );
	}

	/**
	 * Test ctl_sanitize_filename()
	 *
	 * @param string $filename Filename to sanitize.
	 * @param string $expected Expected result.
	 *
	 * @dataProvider dp_test_ctl_sanitize_filename
	 */
	public function test_ctl_sanitize_filename( $filename, $expected ) {
		$locale     = 'ru_RU';
		$iso9_table = $this->get_conversion_table( $locale );

		\WP_Mock::userFunction(
			'seems_utf8',
			[
				'args'   => [ $filename ],
				'return' => true,
			]
		);

		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'get_table' )->andReturn( $iso9_table );
		$settings->shouldReceive( 'is_chinese_locale' )->andReturn( false );

		$converter = $this->getMockBuilder( 'Converter' )->disableOriginalConstructor()->getMock();
		$cli       = $this->getMockBuilder( 'WP_CLI' )->disableOriginalConstructor()->getMock();
		$acf       = $this->getMockBuilder( 'ACF' )->disableOriginalConstructor()->getMock();

		$subject = new Main( $settings, $converter, $cli, $acf );

		\WP_Mock::onFilter( 'ctl_pre_sanitize_filename' )->with( false, $filename )->reply( false );

		FunctionMocker::replace(
			'function_exists',
			function ( $arg ) {
				return 'mb_strtolower' === $arg;
			}
		);

		$this->assertSame( $expected, $subject->ctl_sanitize_filename( $filename, '' ) );

		FunctionMocker::replace(
			'function_exists',
			function ( $arg ) {
				return 'mb_strtolower' !== $arg;
			}
		);

		$this->assertSame( $expected, $subject->ctl_sanitize_filename( $filename, '' ) );
	}

	/**
	 * Data provider for test_ctl_sanitize_title
	 *
	 * @return array
	 */
	public function dp_test_ctl_sanitize_filename() {
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
	 * Test that ctl_sanitize_post_name() does nothing if no Block/Gutenberg editor is active
	 */
	public function test_ctl_sanitize_post_name_without_gutenberg() {
		$subject = Mockery::mock( Main::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$data = [ 'something' ];

		\WP_Mock::userFunction(
			'has_filter',
			[
				'args'   => [ 'replace_editor', 'gutenberg_init' ],
				'return' => false,
			]
		);

		$GLOBALS['wp_version'] = '4.9';
		$this->assertSame( $data, $subject->ctl_sanitize_post_name( $data ) );

		FunctionMocker::replace( 'function_exists', true );

		\WP_Mock::userFunction(
			'is_plugin_active',
			[
				'times'  => 1,
				'args'   => [ 'classic-editor/classic-editor.php' ],
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'get_option',
			[
				'times'  => 1,
				'args'   => [ 'classic-editor-replace' ],
				'return' => 'replace',
			]
		);

		$GLOBALS['wp_version'] = '5.0';
		$this->assertSame( $data, $subject->ctl_sanitize_post_name( $data ) );
	}

	/**
	 * Test ctl_sanitize_post_name()
	 *
	 * @param array $data     Post data to sanitize.
	 * @param array $expected Post data expected after sanitization.
	 *
	 * @dataProvider dp_test_ctl_sanitize_post_name
	 */
	public function test_ctl_sanitize_post_name( $data, $expected ) {
		$GLOBALS['wp_version'] = '5.0';

		$subject = Mockery::mock( Main::class )->makePartial()->shouldAllowMockingProtectedMethods();
		FunctionMocker::replace( 'function_exists', true );

		\WP_Mock::userFunction(
			'is_plugin_active',
			[
				'args'   => [ 'classic-editor/classic-editor.php' ],
				'return' => false,
			]
		);

		$current_screen            = Mockery::mock( WP_Screen::class );
		$current_screen->base      = 'post';
		$GLOBALS['current_screen'] = $current_screen;

		\WP_Mock::userFunction(
			'sanitize_title',
			[
				'times'  => '0+',
				'args'   => [ $data['post_title'] ],
				'return' => 'sanitized(' . $data['post_title'] . ')',
			]
		);
		$this->assertSame( $expected, $subject->ctl_sanitize_post_name( $data ) );
	}

	/**
	 * Data provider for test_ctl_sanitize_post_name()
	 */
	public function dp_test_ctl_sanitize_post_name() {
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
	 * Test ctl_prepare_in()
	 *
	 * @param mixed  $items    Items to prepare.
	 * @param string $format   Format.
	 * @param string $expected Expected result.
	 *
	 * @dataProvider dp_test_ctl_prepare_in
	 */
	public function test_ctl_prepare_in( $items, $format, $expected ) {
		global $wpdb;

		$items    = (array) $items;
		$how_many = count( $items );
		if ( $how_many > 0 ) {
			$format          = $format ? "'" . $format . "'" : "'%s'";
			$placeholders    = array_fill( 0, $how_many, $format );
			$prepared_format = implode( ',', $placeholders );
			$args            = array_merge( [ $prepared_format ], $items );
			$result          = call_user_func_array( 'sprintf', $args );
			$result          = str_replace( "''", '', $result );

			$wpdb = Mockery::mock( wpdb::class );
			$wpdb->shouldReceive( 'prepare' )->zeroOrMoreTimes()->andReturn( $result );
		}

		$subject = $this->get_subject();
		if ( $format ) {
			$this->assertSame( $expected, $subject->ctl_prepare_in( $items, $format ) );
		} else {
			$this->assertSame( $expected, $subject->ctl_prepare_in( $items ) );
		}
	}

	/**
	 * Data provider for test_ctl_prepare_in()
	 */
	public function dp_test_ctl_prepare_in() {
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
	 * @return Main
	 */
	private function get_subject() {
		$settings  = Mockery::mock( Settings::class );
		$converter = Mockery::mock( Converter::class );
		$cli       = Mockery::mock( WP_CLI::class );
		$acf       = Mockery::mock( ACF::class );

		$subject = new Main( $settings, $converter, $cli, $acf );

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
