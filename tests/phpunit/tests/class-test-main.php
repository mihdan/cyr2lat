<?php
/**
 * Test_Main class file
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound

namespace Cyr_To_Lat;

use Cyr_To_Lat\Settings\Settings;
use Cyr_To_Lat\Symfony\Polyfill\Mbstring\Mbstring;
use Exception;
use Mockery;
use PHPUnit\Runner\Version;
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
		Mockery::mock( 'overload:' . Admin_Notices::class );

		$requirements     = Mockery::mock( 'overload:' . Requirements::class );
		$requirements_met = true;
		$requirements->shouldReceive( 'are_requirements_met' )->with()->andReturnUsing(
			function () use ( &$requirements_met ) {
				return $requirements_met;
			}
		);

		Mockery::mock( 'overload:' . Post_Conversion_Process::class );
		Mockery::mock( 'overload:' . Term_Conversion_Process::class );
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

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock );

		self::assertInstanceOf( Settings::class, $this->get_protected_property( $mock, 'settings' ) );
		self::assertInstanceOf( Admin_Notices::class, $this->get_protected_property( $mock, 'admin_notices' ) );
		self::assertInstanceOf( Post_Conversion_Process::class, $this->get_protected_property( $mock, 'process_all_posts' ) );
		self::assertInstanceOf( Term_Conversion_Process::class, $this->get_protected_property( $mock, 'process_all_terms' ) );
		self::assertInstanceOf( Converter::class, $this->get_protected_property( $mock, 'converter' ) );
		self::assertInstanceOf( WP_CLI::class, $this->get_protected_property( $mock, 'cli' ) );
		self::assertInstanceOf( ACF::class, $this->get_protected_property( $mock, 'acf' ) );

		$requirements_met = false;

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock );

		self::assertInstanceOf( Settings::class, $this->get_protected_property( $mock, 'settings' ) );
		self::assertInstanceOf( Admin_Notices::class, $this->get_protected_property( $mock, 'admin_notices' ) );
		self::assertNull( $this->get_protected_property( $mock, 'process_all_posts' ) );
		self::assertNull( $this->get_protected_property( $mock, 'process_all_terms' ) );
		self::assertNull( $this->get_protected_property( $mock, 'converter' ) );
		self::assertNull( $this->get_protected_property( $mock, 'cli' ) );
		self::assertNull( $this->get_protected_property( $mock, 'acf' ) );
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
	 *
	 * @noinspection PhpRedundantOptionalArgumentInspection
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
	 * @param boolean $polylang  Polylang is active.
	 * @param boolean $sitepress WPML is active.
	 *
	 * @dataProvider dp_test_init_hooks
	 */
	public function test_init_hooks( $polylang, $sitepress ) {
		$subject = Mockery::mock( Main::class )->makePartial();

		WP_Mock::expectFilterAdded( 'sanitize_title', [ $subject, 'sanitize_title' ], 9, 3 );
		WP_Mock::expectFilterAdded( 'sanitize_file_name', [ $subject, 'sanitize_filename' ], 10, 2 );
		WP_Mock::expectFilterAdded( 'wp_insert_post_data', [ $subject, 'sanitize_post_name' ], 10, 2 );
		WP_Mock::expectFilterAdded( 'pre_insert_term', [ $subject, 'pre_insert_term_filter' ], PHP_INT_MAX, 2 );
		WP_Mock::expectFilterAdded( 'get_terms_args', [ $subject, 'get_terms_args_filter' ], PHP_INT_MAX, 2 );

		FunctionMocker::replace(
			'class_exists',
			function ( $class ) use ( $polylang, $sitepress ) {
				if ( 'Polylang' === $class ) {
					return $polylang;
				}

				if ( 'SitePress' === $class ) {
					return $sitepress;
				}

				return null;
			}
		);

		if ( $polylang ) {
			WP_Mock::expectFilterAdded( 'locale', [ $subject, 'pll_locale_filter' ] );
		} else {
			WP_Mock::expectFilterNotAdded( 'locale', [ $subject, 'pll_locale_filter' ] );
		}

		if ( $sitepress ) {
			WP_Mock::expectFilterAdded( 'ctl_locale', [ $subject, 'wpml_locale_filter' ], - PHP_INT_MAX );
		} else {
			WP_Mock::expectFilterNotAdded( 'ctl_locale', [ $subject, 'wpml_locale_filter' ] );
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
			[ false, false ],
			[ true, false ],
			[ false, true ],
			[ true, true ],
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
	 * Test sanitize_title() for insert_term
	 *
	 * @param string            $title    Title to sanitize.
	 * @param string|int|object $term     Term to use.
	 * @param string            $expected Expected result.
	 *
	 * @dataProvider dp_test_sanitize_title_for_insert_term
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_sanitize_title_for_insert_term( $title, $term, $expected ) {
		global $wpdb;

		$taxonomy     = 'taxonomy';
		$prepared_tax = '\'' . $taxonomy . '\'';

		$subject = $this->get_subject();

		$times = $term ? 1 : 0;

		if ( is_object( $term ) ) {
			WP_Mock::userFunction( 'is_wp_error' )->with( $term )->andReturn( true );
			$times = 0;
		} else {
			WP_Mock::userFunction( 'is_wp_error' )->with( $term )->andReturn( false );
		}

		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, urldecode( $title ) )->reply( false );

		$subject->shouldReceive( 'prepare_in' )->times( $times )->with( [ $taxonomy ] )->andReturn( $prepared_tax );
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb                = Mockery::mock( wpdb::class );
		$wpdb->terms         = 'wp_terms';
		$wpdb->term_taxonomy = 'wp_term_taxonomy';

		$request          = "SELECT slug FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt
							ON t.term_id = tt.term_id
							WHERE t.name = %s";
		$prepared_request = 'SELECT slug FROM ' . $wpdb->terms . " t LEFT JOIN {$wpdb->term_taxonomy} tt
							ON t.term_id = tt.term_id
							WHERE t.name = " . $title;
		$sql              = $prepared_request . ' AND tt.taxonomy IN (' . $prepared_tax . ')';

		$wpdb->shouldReceive( 'prepare' )->times( $times )->with(
			$request,
			$title
		)->andReturn( $prepared_request );
		$wpdb->shouldReceive( 'get_var' )->times( $times )->with( $sql )->andReturn( $term );

		$subject->pre_insert_term_filter( $term, $taxonomy );
		self::assertSame( $expected, $subject->sanitize_title( $title ) );
		// Make sure we search in the db only once being called from wp_insert_term().
		self::assertSame( $title, $subject->sanitize_title( $title ) );
	}

	/**
	 * Data provider for test_sanitize_title_for_insert_term()
	 */
	public function dp_test_sanitize_title_for_insert_term() {
		return [
			[ 'title', 'term', 'term' ],
			[ 'title', '', 'title' ],
			[ 'title', 0, 'title' ],
			[ 'title', (object) [], 'title' ],
		];
	}

	/**
	 * Test sanitize_title() for get_terms
	 *
	 * @param string $title               Title to sanitize.
	 * @param string $term                Term to us.
	 * @param array  $taxonomies          Taxonomies to use.
	 * @param string $prepared_taxonomies Prepared taxonomies to use.
	 * @param string $expected            Expected result.
	 *
	 * @dataProvider dp_test_sanitize_title_for_get_terms
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_sanitize_title_for_get_terms( $title, $term, $taxonomies, $prepared_taxonomies, $expected ) {
		global $wpdb;

		$subject = $this->get_subject();

		$times = $taxonomies ? 1 : 0;

		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, urldecode( $title ) )->reply( false );

		$subject->shouldReceive( 'prepare_in' )->times( $times )->with( $taxonomies )
			->andReturn( $prepared_taxonomies );
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb                = Mockery::mock( wpdb::class );
		$wpdb->terms         = 'wp_terms';
		$wpdb->term_taxonomy = 'wp_term_taxonomy';

		$request          = "SELECT slug FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt
							ON t.term_id = tt.term_id
							WHERE t.name = %s";
		$prepared_request = 'SELECT slug FROM ' . $wpdb->terms . " t LEFT JOIN {$wpdb->term_taxonomy} tt
							ON t.term_id = tt.term_id
							WHERE t.name = " . $title;

		$sql = $prepared_request;

		if ( $taxonomies ) {
			$sql .= ' AND tt.taxonomy IN (' . $prepared_taxonomies . ')';
		}

		$wpdb->shouldReceive( 'prepare' )->once()->with(
			$request,
			$title
		)->andReturn( $prepared_request );
		$wpdb->shouldReceive( 'get_var' )->once()->with( $sql )->andReturn( $term );

		$subject->get_terms_args_filter( [ 'some args' ], $taxonomies );
		self::assertSame( $expected, $subject->sanitize_title( $title ) );
		// Make sure we search in the db only once being called from wp_insert_term().
		self::assertSame( $title, $subject->sanitize_title( $title ) );
	}

	/**
	 * Data provider for test_sanitize_title_for_get_terms()
	 */
	public function dp_test_sanitize_title_for_get_terms() {
		return [
			[ 'title', 'term', [ 'taxonomy' ], "'taxonomy'", 'term' ],
			[ 'title', 'term', [ 'taxonomy1', 'taxonomy2' ], "'taxonomy1', 'taxonomy2'", 'term' ],
			[ 'title', 'term', [], '', 'term' ],
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
			'no wc'                  => [ 'color', false, null, 1 ],
			'no attr taxes'          => [ 'color', true, [], 1 ],
			'not in attr taxes'      => [ 'color', true, $attribute_taxonomies, 1 ],
			'in attr taxes'          => [ 'цвет', true, $attribute_taxonomies, 0 ],
			'in attr taxes with pa_' => [ 'pa_цвет', true, $attribute_taxonomies, 0 ],
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

		$settings = $this->get_protected_property( $subject, 'settings' );

		if (
			class_exists( Version::class ) &&
			version_compare( substr( Version::id(), 0, 1 ), '7', '>=' )
		) {
			WP_Mock::expectFilter( 'ctl_table', $settings->get_table() );
		}

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
	 * Data provider for test_sanitize_filename
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

		WP_Mock::userFunction( 'pll_get_post_language' )->with( $post_id, 'locale' )->andReturn( $pll_locale );

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

		WP_Mock::userFunction( 'pll_get_post_language' )->with( $post_id, 'locale' )->andReturn( $pll_locale );

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

		WP_Mock::userFunction( 'pll_get_post_language' )->with( $post_id, 'locale' )->andReturn( $pll_locale );

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
	 *
	 * @noinspection PhpUndefinedFieldInspection
	 */
	public function test_pll_locale_filter_with_term() {
		$locale           = 'en_US';
		$pll_locale       = 'ru';
		$term_lang_choice = 92;

		$subject = Mockery::mock( Main::class )->makePartial();

		FunctionMocker::replace( 'defined' );

		WP_Mock::userFunction( 'is_admin' )->with()->andReturn( true );

		$pll_get_language         = Mockery::mock( PLL_Language::class );
		$pll_get_language->locale = $pll_locale;

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
	 * Test wpml_locale_filter().
	 *
	 * @param string $locale        Current locale.
	 * @param string $language_code Current language code.
	 * @param string $expected      Expected.
	 *
	 * @dataProvider dp_test_wpml_locale_filter
	 */
	public function test_wpml_locale_filter( $locale, $language_code, $expected ) {
		$languages = [
			'be' =>
				[
					'code'           => 'be',
					'id'             => '64',
					'english_name'   => 'Belarusian',
					'native_name'    => 'Belarusian',
					'major'          => '0',
					'active'         => '1',
					'default_locale' => 'be_BY',
					'encode_url'     => '0',
					'tag'            => 'be',
					'display_name'   => 'Belarusian',
				],
			'en' =>
				[
					'code'           => 'en',
					'id'             => '1',
					'english_name'   => 'English',
					'native_name'    => 'English',
					'major'          => '1',
					'active'         => '1',
					'default_locale' => 'en_US',
					'encode_url'     => '0',
					'tag'            => 'en',
					'display_name'   => 'English',
				],
			'ru' =>
				[
					'code'           => 'ru',
					'id'             => '46',
					'english_name'   => 'Russian',
					'native_name'    => 'Русский',
					'major'          => '1',
					'active'         => '1',
					'default_locale' => 'ru_RU',
					'encode_url'     => '0',
					'tag'            => 'ru',
					'display_name'   => 'Russian',
				],
			'uk' =>
				[
					'code'           => 'uk',
					'id'             => '55',
					'english_name'   => 'Ukrainian',
					'native_name'    => 'Ukrainian',
					'major'          => '0',
					'active'         => '1',
					'default_locale' => 'uk',
					'encode_url'     => '0',
					'tag'            => 'uk',
					'display_name'   => 'Ukrainian',
				],
		];

		WP_Mock::userFunction( 'wpml_get_current_language' )->with()->andReturn( $language_code );
		WP_Mock::onFilter( 'wpml_active_languages' )->with( null )->reply( $languages );

		$subject = Mockery::mock( Main::class )->makePartial();

		self::assertSame( $expected, $subject->wpml_locale_filter( $locale ) );
	}

	/**
	 * Data provider for test_wpml_locale_filter().
	 *
	 * @return array
	 */
	public function dp_test_wpml_locale_filter() {
		return [
			'Existing language code, return locale from wpml' => [ 'en_US', 'ru', 'ru_RU' ],
			'Not existing language code, return from current' => [ 'en_US', 'some', 'en_US' ],
		];
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
			$hieroglyphs = Mbstring::mb_str_split( $item );
			foreach ( $hieroglyphs as $hieroglyph ) {
				$transposed_table[ $hieroglyph ] = $key;
			}
		}

		return $transposed_table;
	}
}
