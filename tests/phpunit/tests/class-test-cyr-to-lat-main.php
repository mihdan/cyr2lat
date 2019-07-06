<?php
/**
 * Test_Cyr_To_Lat_Main class file
 *
 * @package cyr-to-lat
 */

use PHPUnit\Framework\TestCase;

/**
 * Class Test_Cyr_To_Lat_Main
 *
 * @group main
 */
class Test_Cyr_To_Lat_Main extends TestCase {

	/**
	 * Setup test
	 */
	public function setUp() {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * End test
	 */
	public function tearDown() {
		unset( $GLOBALS['wp_version'] );
		unset( $GLOBALS['wpdb'] );
		unset( $GLOBALS['current_screen'] );
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 * @test
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_constructor() {
		$classname = 'Cyr_To_Lat_Main';

		$settings  = \Mockery::mock( 'overload:Cyr_To_Lat_Settings' );
		$converter = \Mockery::mock( 'overload:Cyr_To_Lat_Converter' );
		$cli       = \Mockery::mock( 'overload:Cyr_To_Lat_WP_CLI' );
		$acf       = \Mockery::mock( 'overload:Cyr_To_Lat_ACF' );

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
		$subject = \Mockery::mock( Cyr_To_Lat_Main::class )->makePartial();
		$subject->shouldReceive( 'init_hooks' )->once();

		$subject->init();
		$this->assertTrue( true );
	}

	/**
	 * Test init() with CLI when CLI throws an Exception
	 *
	 * @test
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_init_with_cli_error() {
		$subject = \Mockery::mock( Cyr_To_Lat_Main::class )->makePartial();
		$subject->shouldReceive( 'init_hooks' )->never();

		if ( ! defined( 'WP_CLI' ) ) {
			define( 'WP_CLI', true );
		}

		$wp_cli = \Mockery::mock( 'alias:WP_CLI' );

		$subject->init();
		$this->assertTrue( true );
	}

	/**
	 * Test init() with CLI
	 *
	 * @test
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_init_with_cli() {
		$subject = \Mockery::mock( Cyr_To_Lat_Main::class )->makePartial();
		$subject->shouldReceive( 'init_hooks' )->once();

		if ( ! defined( 'WP_CLI' ) ) {
			define( 'WP_CLI', true );
		}

		$wp_cli = \Mockery::mock( 'alias:WP_CLI' );
		$wp_cli->shouldReceive( 'add_command' )->andReturn( null );

		$subject->init();
		$this->assertTrue( true );
	}

	/**
	 * Test init_hooks()
	 */
	public function test_init_hooks() {
		$subject = $this->get_subject();

		\WP_Mock::expectFilterAdded( 'sanitize_title', [ $subject, 'ctl_sanitize_title' ], 9, 3 );
		\WP_Mock::expectFilterAdded( 'sanitize_file_name', [ $subject, 'ctl_sanitize_title' ], 10, 2 );
		\WP_Mock::expectFilterAdded( 'wp_insert_post_data', [ $subject, 'ctl_sanitize_post_name' ], 10, 2 );

		$subject->init_hooks();
		$this->assertTrue( true );
	}

	/**
	 * Test that ctl_sanitize_title does nothing when context is 'query'
	 */
	public function test_ctl_sanitize_title_query_context() {
		$subject = $this->get_subject();

		$title     = 'some title';
		$raw_title = '';
		$context   = 'query';

		$this->assertSame( $title, $subject->ctl_sanitize_title( $title, $raw_title, $context ) );
	}

	/**
	 * Test that ctl_sanitize_title returns ctl_pre_sanitize_title filter value if set
	 */
	public function test_ctl_sanitize_title_filter_set() {
		$subject = $this->get_subject();

		$title     = 'some title';
		$raw_title = '';
		$context   = '';

		$filtered_title = 'filtered title';

		\WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, urldecode( $title ) )->reply( $filtered_title );

		$subject->ctl_sanitize_title( $title, $raw_title, $context );
		$this->assertSame( $filtered_title, $subject->ctl_sanitize_title( $title ) );
	}

	/**
	 * Test ctl_sanitize_title()
	 *
	 * @param string $title    Title to sanitize.
	 * @param string $expected Expected result.
	 *
	 * @test
	 * @dataProvider dp_test_ctl_sanitize_title
	 * @throws ReflectionException Reflection Exception.
	 */
	public function test_ctl_sanitize_title( $title, $expected ) {
		$locale     = 'ru_RU';
		$iso9_table = $this->get_conversion_table( $locale );

		$settings = \Mockery::mock( 'Cyr_To_Lat_Settings' );
		$settings->shouldReceive( 'get_table' )->andReturn( $iso9_table );

		$converter = $this->getMockBuilder( 'Cyr_To_Lat_Converter' )->disableOriginalConstructor()->getMock();

		$cli = $this->getMockBuilder( 'Cyr_To_Lat_WP_CLI' )->disableOriginalConstructor()->getMock();
		$acf = $this->getMockBuilder( 'Cyr_To_Lat_ACF' )->disableOriginalConstructor()->getMock();

		$subject = new Cyr_To_Lat_Main( $settings, $converter, $cli, $acf );

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
		];
	}

	/**
	 * Test that ctl_sanitize_post_name() does nothing if no Block/Gutenberg editor is active
	 */
	public function test_ctl_sanitize_post_name_without_gutenberg() {
		$subject = \Mockery::mock( Cyr_To_Lat_Main::class )->makePartial()->shouldAllowMockingProtectedMethods();

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

		$GLOBALS['wp_version'] = '5.0';
		try {
			$this->assertSame( $data, $subject->ctl_sanitize_post_name( $data ) );
		} catch ( Exception $e ) {
		}

		$subject->shouldReceive( 'ctl_function_exists' )->andReturn( true );
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
		$this->assertSame( $data, $subject->ctl_sanitize_post_name( $data ) );
	}

	/**
	 * Test that ctl_sanitize_post_name() does nothing if current screen is not post edit screen
	 */
	public function test_ctl_sanitize_post_name_not_post_edit_screen() {
		$data = [ 'something' ];

		$GLOBALS['wp_version'] = '5.0';

		$subject = \Mockery::mock( Cyr_To_Lat_Main::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'ctl_function_exists' )->andReturn( true );

		\WP_Mock::userFunction(
			'is_plugin_active',
			[
				'args'   => [ 'classic-editor/classic-editor.php' ],
				'return' => false,
			]
		);

		$current_screen       = \Mockery::mock( 'WP_Screen' );
		$current_screen->base = 'not post';

		$GLOBALS['current_screen'] = null;
		$this->assertSame( $data, $subject->ctl_sanitize_post_name( $data ) );

		$GLOBALS['current_screen'] = $current_screen;
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

		$subject = \Mockery::mock( Cyr_To_Lat_Main::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'ctl_function_exists' )->andReturn( true );

		\WP_Mock::userFunction(
			'is_plugin_active',
			[
				'args'   => [ 'classic-editor/classic-editor.php' ],
				'return' => false,
			]
		);

		$current_screen            = \Mockery::mock( 'WP_Screen' );
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
	 * Test ctl_sanitize_title() for term
	 * Name of this function must be wp_insert_term() to use debug_backtrace in the tested method
	 *
	 * @param string $title    Title to sanitize.
	 * @param string $term     Term to sanitize.
	 * @param string $expected Expected result.
	 *
	 * @test
	 * @dataProvider dp_wp_insert_term
	 * @throws ReflectionException Reflection Exception.
	 */
	public function wp_insert_term( $title, $term, $expected ) {
		global $wpdb;

		$locale     = 'ru_RU';
		$iso9_table = $this->get_conversion_table( $locale );

		$settings = \Mockery::mock( 'Cyr_To_Lat_Settings' );
		$settings->shouldReceive( 'get_table' )->andReturn( $iso9_table );

		$converter = $this->getMockBuilder( 'Cyr_To_Lat_Converter' )->disableOriginalConstructor()->getMock();

		$cli = $this->getMockBuilder( 'Cyr_To_Lat_WP_CLI' )->disableOriginalConstructor()->getMock();
		$acf = $this->getMockBuilder( 'Cyr_To_Lat_ACF' )->disableOriginalConstructor()->getMock();

		$subject = new Cyr_To_Lat_Main( $settings, $converter, $cli, $acf );

		\WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, urldecode( $title ) )->reply( false );

		$wpdb        = Mockery::mock( '\wpdb' );
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

			$wpdb = Mockery::mock( '\wpdb' );
			$wpdb->shouldReceive( 'prepare' )->zeroOrMoreTimes()
			     ->andReturn( $result );
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
	 * @return Cyr_To_Lat_Main
	 */
	private function get_subject() {
		$settings  = \Mockery::mock( 'Cyr_To_Lat_Settings' );
		$converter = \Mockery::mock( 'Cyr_To_Lat_Converter' );
		$cli       = \Mockery::mock( 'Cyr_To_Lat_WP_CLI' );
		$acf       = \Mockery::mock( 'Cyr_To_Lat_ACF' );

		$subject = new Cyr_To_Lat_Main( $settings, $converter, $cli, $acf );

		return $subject;
	}

	/**
	 * Get conversion table by locale.
	 *
	 * @link https://ru.wikipedia.org/wiki/ISO_9
	 *
	 * @param string $locale WordPress locale.
	 *
	 * @return array
	 */
	private function get_conversion_table( $locale = '' ) {
		$table = array(
			'А' => 'A',
			'Б' => 'B',
			'В' => 'V',
			'Г' => 'G',
			'Д' => 'D',
			'Е' => 'E',
			'Ё' => 'YO',
			'Ж' => 'ZH',
			'З' => 'Z',
			'И' => 'I',
			'Й' => 'J',
			'І' => 'I',
			'К' => 'K',
			'Л' => 'L',
			'М' => 'M',
			'Н' => 'N',
			'О' => 'O',
			'П' => 'P',
			'Р' => 'R',
			'С' => 'S',
			'Т' => 'T',
			'У' => 'U',
			'Ф' => 'F',
			'Х' => 'H',
			'Ц' => 'CZ',
			'Ч' => 'CH',
			'Ш' => 'SH',
			'Щ' => 'SHH',
			'Ъ' => '',
			'Ы' => 'Y',
			'Ь' => '',
			'Э' => 'E',
			'Ю' => 'YU',
			'Я' => 'YA',
			'Ѣ' => 'YE',
			'Ѳ' => 'FH',
			'Ѵ' => 'YH',
			'а' => 'a',
			'б' => 'b',
			'в' => 'v',
			'г' => 'g',
			'д' => 'd',
			'е' => 'e',
			'ё' => 'yo',
			'ж' => 'zh',
			'з' => 'z',
			'и' => 'i',
			'й' => 'j',
			'і' => 'i',
			'к' => 'k',
			'л' => 'l',
			'м' => 'm',
			'н' => 'n',
			'о' => 'o',
			'п' => 'p',
			'р' => 'r',
			'с' => 's',
			'т' => 't',
			'у' => 'u',
			'ф' => 'f',
			'х' => 'h',
			'ц' => 'cz',
			'ч' => 'ch',
			'ш' => 'sh',
			'щ' => 'shh',
			'ъ' => '',
			'ы' => 'y',
			'ь' => '',
			'э' => 'e',
			'ю' => 'yu',
			'я' => 'ya',
			'ѣ' => 'ye',
			'ѳ' => 'fh',
			'ѵ' => 'yh',
		);
		switch ( $locale ) {
			// Belorussian.
			case 'bel':
				unset( $table['И'] );
				unset( $table['и'] );
				$table['Ў'] = 'U';
				$table['ў'] = 'u';
				unset( $table['Щ'] );
				unset( $table['щ'] );
				unset( $table['Ъ'] );
				unset( $table['ъ'] );
				unset( $table['Ѣ'] );
				unset( $table['ѣ'] );
				unset( $table['Ѳ'] );
				unset( $table['ѳ'] );
				unset( $table['Ѵ'] );
				unset( $table['ѵ'] );
				break;
			// Ukrainian.
			case 'uk':
				$table['Ґ'] = 'G';
				$table['ґ'] = 'g';
				unset( $table['Ё'] );
				unset( $table['ё'] );
				$table['Є'] = 'YE';
				$table['є'] = 'ye';
				$table['И'] = 'Y';
				$table['и'] = 'y';
				$table['Ї'] = 'YI';
				$table['ї'] = 'yi';
				unset( $table['Ъ'] );
				unset( $table['ъ'] );
				unset( $table['Ы'] );
				unset( $table['ы'] );
				unset( $table['Э'] );
				unset( $table['э'] );
				unset( $table['Ѣ'] );
				unset( $table['ѣ'] );
				unset( $table['Ѳ'] );
				unset( $table['ѳ'] );
				unset( $table['Ѵ'] );
				unset( $table['ѵ'] );
				break;
			// Bulgarian.
			case 'bg_BG':
				unset( $table['Ё'] );
				unset( $table['ё'] );
				$table['Щ'] = 'STH';
				$table['щ'] = 'sth';
				$table['Ъ'] = 'A';
				$table['ъ'] = 'a';
				unset( $table['Ы'] );
				unset( $table['ы'] );
				unset( $table['Э'] );
				unset( $table['э'] );
				$table['Ѫ'] = 'О';
				$table['ѫ'] = 'о';
				break;
			// Macedonian.
			case 'mk_MK':
				$table['Ѓ'] = 'G';
				$table['ѓ'] = 'g';
				unset( $table['Ё'] );
				unset( $table['ё'] );
				$table['Ѕ'] = 'Z';
				$table['ѕ'] = 'z';
				unset( $table['Й'] );
				unset( $table['й'] );
				$table['Ј'] = 'J';
				$table['ј'] = 'j';
				unset( $table['I'] );
				unset( $table['i'] );
				$table['Ќ'] = 'K';
				$table['ќ'] = 'k';
				$table['Љ'] = 'L';
				$table['љ'] = 'l';
				$table['Њ'] = 'N';
				$table['њ'] = 'n';
				$table['Џ'] = 'DH';
				$table['џ'] = 'dh';
				unset( $table['Щ'] );
				unset( $table['щ'] );
				unset( $table['Ъ'] );
				unset( $table['ъ'] );
				unset( $table['Ы'] );
				unset( $table['ы'] );
				unset( $table['Ь'] );
				unset( $table['ь'] );
				unset( $table['Э'] );
				unset( $table['э'] );
				unset( $table['Ю'] );
				unset( $table['ю'] );
				unset( $table['Я'] );
				unset( $table['я'] );
				unset( $table['Ѣ'] );
				unset( $table['ѣ'] );
				unset( $table['Ѳ'] );
				unset( $table['ѳ'] );
				unset( $table['Ѵ'] );
				unset( $table['ѵ'] );
				break;
			// Georgian.
			case 'ka_GE':
				$table['áƒ'] = 'a';
				$table['áƒ‘'] = 'b';
				$table['áƒ’'] = 'g';
				$table['áƒ“'] = 'd';
				$table['áƒ”'] = 'e';
				$table['áƒ•'] = 'v';
				$table['áƒ–'] = 'z';
				$table['áƒ—'] = 'th';
				$table['áƒ˜'] = 'i';
				$table['áƒ™'] = 'k';
				$table['áƒš'] = 'l';
				$table['áƒ›'] = 'm';
				$table['áƒœ'] = 'n';
				$table['áƒ'] = 'o';
				$table['áƒž'] = 'p';
				$table['áƒŸ'] = 'zh';
				$table['áƒ '] = 'r';
				$table['áƒ¡'] = 's';
				$table['áƒ¢'] = 't';
				$table['áƒ£'] = 'u';
				$table['áƒ¤'] = 'ph';
				$table['áƒ¥'] = 'q';
				$table['áƒ¦'] = 'gh';
				$table['áƒ§'] = 'qh';
				$table['áƒ¨'] = 'sh';
				$table['áƒ©'] = 'ch';
				$table['áƒª'] = 'ts';
				$table['áƒ«'] = 'dz';
				$table['áƒ¬'] = 'ts';
				$table['áƒ­'] = 'tch';
				$table['áƒ®'] = 'kh';
				$table['áƒ¯'] = 'j';
				$table['áƒ°'] = 'h';
				break;
			// Kazakh.
			case 'kk':
				$table['Ә'] = 'Ae';
				$table['ә'] = 'ae';
				$table['Ғ'] = 'Gh';
				$table['ғ'] = 'gh';
				unset( $table['Ё'] );
				unset( $table['ё'] );
				$table['Қ'] = 'Q';
				$table['қ'] = 'q';
				$table['Ң'] = 'Ng';
				$table['ң'] = 'ng';
				$table['Ө'] = 'Oe';
				$table['ө'] = 'oe';
				$table['У'] = 'W';
				$table['у'] = 'w';
				$table['Ұ'] = 'U';
				$table['ұ'] = 'u';
				$table['Ү'] = 'Ue';
				$table['ү'] = 'ue';
				$table['Һ'] = 'H';
				$table['һ'] = 'h';
				$table['Ц'] = 'C';
				$table['ц'] = 'c';
				unset( $table['Щ'] );
				unset( $table['щ'] );
				unset( $table['Ъ'] );
				unset( $table['ъ'] );
				unset( $table['Ь'] );
				unset( $table['ь'] );
				unset( $table['Э'] );
				unset( $table['э'] );
				unset( $table['Ю'] );
				unset( $table['ю'] );
				unset( $table['Я'] );
				unset( $table['я'] );

				// Kazakh 2018 latin.
				$table['Á'] = 'A';
				$table['á'] = 'a';
				$table['Ǵ'] = 'G';
				$table['ǵ'] = 'g';
				$table['I'] = 'I';
				$table['ı'] = 'i';
				$table['Ń'] = 'N';
				$table['ń'] = 'n';
				$table['Ó'] = 'O';
				$table['ó'] = 'o';
				$table['Ú'] = 'O';
				$table['ú'] = 'o';
				$table['Ý'] = 'O';
				$table['ý'] = 'o';
				break;
			default:
		}

		return $table;
	}
}


