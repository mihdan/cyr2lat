<?php
/**
 * Test_Cyr_To_Lat_Converter class file
 *
 * @package cyr-to-lat
 */

use PHPUnit\Framework\TestCase;

/**
 * Class Test_Cyr_To_Lat_Converter
 *
 * @group converter
 */
class Test_Cyr_To_Lat_Converter extends TestCase {

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
		unset( $_GET[ \Cyr_To_Lat_Converter::QUERY_ARG ] );
		unset( $_GET['_wpnonce'] );
		unset( $_POST['cyr2lat-convert'] );
		unset( $GLOBALS['wpdb'] );
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
		$classname = 'Cyr_To_Lat_Converter';

		$main          = \Mockery::mock( 'Cyr_To_Lat_Main' );
		$settings      = \Mockery::mock( 'Cyr_To_Lat_Settings' );
		$post_cp       = \Mockery::mock( 'overload:Cyr_To_Lat_Post_Conversion_Process' );
		$term_cp       = \Mockery::mock( 'overload:Cyr_To_Lat_Term_Conversion_Process' );
		$admin_notices = \Mockery::mock( 'overload:Cyr_To_Lat_Admin_Notices' );

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Set expectations for constructor calls.
		$mock->expects( $this->once() )->method( 'init_hooks' );

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock, $main, $settings );
	}

	/**
	 * Test init_hooks()
	 */
	public function test_init_hooks() {
		$subject = $this->get_subject();

		\WP_Mock::expectActionAdded( 'admin_init', [ $subject, 'process_handler' ] );
		\WP_Mock::expectActionAdded( 'admin_init', [ $subject, 'conversion_notices' ] );

		$subject->init_hooks();
		$this->assertTrue( true );
	}

	/**
	 * Test conversion_notices()
	 *
	 * @param boolean $posts_process_running   Post process is running.
	 * @param boolean $terms_process_running   Terms process is running.
	 * @param boolean $posts_process_completed Post process is completed.
	 * @param boolean $terms_process_completed Terms process is completed.
	 *
	 * @dataProvider dp_test_conversion_notices
	 */
	public function test_conversion_notices(
		$posts_process_running, $terms_process_running, $posts_process_completed, $terms_process_completed
	) {
		$main              = \Mockery::mock( 'Cyr_To_Lat_Main' );
		$settings          = \Mockery::mock( 'Cyr_To_Lat_Settings' );
		$process_all_posts = \Mockery::mock( 'Cyr_To_Lat_Post_Conversion_Process' )->shouldAllowMockingProtectedMethods();
		$process_all_terms = \Mockery::mock( 'Cyr_To_Lat_Term_Conversion_Process' )->shouldAllowMockingProtectedMethods();
		$admin_notices     = \Mockery::mock( 'Cyr_To_Lat_Admin_Notices' );

		$subject = new Cyr_To_Lat_Converter(
			$main,
			$settings,
			$process_all_posts,
			$process_all_terms,
			$admin_notices
		);

		$process_all_posts->shouldReceive( 'is_process_running' )->andReturn( $posts_process_running );
		$process_all_terms->shouldReceive( 'is_process_running' )->andReturn( $terms_process_running );

		$process_all_posts->shouldReceive( 'is_process_completed' )->andReturn( $posts_process_completed );
		$process_all_terms->shouldReceive( 'is_process_completed' )->andReturn( $terms_process_completed );

		if ( ! $posts_process_running && ! $terms_process_running ) {
			\WP_Mock::expectActionAdded( 'admin_init', [ $subject, 'start_conversion' ], 20 );
		}

		if ( $posts_process_running ) {
			$admin_notices->shouldReceive( 'add_notice' )->with(
				'Cyr To Lat converts existing post slugs in the background process.',
				'notice notice-info is-dismissible'
			);
		}

		if ( $terms_process_running ) {
			$admin_notices->shouldReceive( 'add_notice' )->with(
				'Cyr To Lat converts existing term slugs in the background process.',
				'notice notice-info is-dismissible'
			);
		}

		if ( $posts_process_completed ) {
			$admin_notices->shouldReceive( 'add_notice' )->with(
				'Cyr To Lat completed conversion of existing post slugs.',
				'notice notice-success is-dismissible'
			);
		}

		if ( $terms_process_completed ) {
			$admin_notices->shouldReceive( 'add_notice' )->with(
				'Cyr To Lat completed conversion of existing term slugs.',
				'notice notice-success is-dismissible'
			);
		}

		$subject->conversion_notices();
		$this->assertTrue( true );
	}

	/**
	 * Data provider for test_conversion_notices()
	 *
	 * @return array
	 */
	public function dp_test_conversion_notices() {
		return [
			[ false, false, false, false ],
			[ true, false, false, false ],
			[ false, true, false, false ],
			[ true, true, false, false ],
			[ false, false, true, false ],
			[ true, false, true, false ],
			[ false, true, true, false ],
			[ true, true, true, false ],
			[ false, false, false, true ],
			[ true, false, false, true ],
			[ false, true, false, true ],
			[ true, true, false, true ],
			[ false, false, true, true ],
			[ true, false, true, true ],
			[ false, true, true, true ],
			[ true, true, true, true ],
		];
	}

	/**
	 * Test start_conversion()
	 *
	 * @param boolean $convert If $_POST['cyr2lat-convert'] set.
	 *
	 * @dataProvider dp_test_start_conversion
	 */
	public function test_start_conversion( $convert ) {
		$subject = \Mockery::mock(
			'Cyr_To_Lat_Converter'
		)->makePartial();

		\WP_Mock::passthruFunction( 'check_admin_referer' );

		if ( $convert ) {
			$_POST['cyr2lat-convert'] = 'something';
			$subject->shouldReceive( 'convert_existing_slugs' )->once();
		} else {
			$subject->shouldReceive( 'convert_existing_slugs' )->never();
		}

		$subject->start_conversion();
		$this->assertTrue( true );
	}

	/**
	 * Data provider for test_start_conversion()
	 *
	 * @return array
	 */
	public function dp_test_start_conversion() {
		return [
			[ false ],
			[ true ],
		];
	}

	/**
	 * Test process_handler()
	 *
	 * @param string  $query_arg    Query arg.
	 * @param string  $nonce        Nonce.
	 * @param boolean $verify_nonce Result of verify_nonce().
	 *
	 * @dataProvider dp_test_process_handler
	 */
	public function test_process_handler( $query_arg, $nonce, $verify_nonce ) {
		$subject = \Mockery::mock( Cyr_To_Lat_Converter::class )->makePartial();

		if ( $query_arg ) {
			$_GET[ \Cyr_To_Lat_Converter::QUERY_ARG ] = '1';
		}
		if ( $nonce ) {
			$_GET['_wpnonce'] = $nonce;
		}

		if ( $query_arg && $nonce ) {
			\WP_Mock::passthruFunction( 'sanitize_key' );
			\WP_Mock::userFunction(
				'wp_verify_nonce',
				[
					'times'  => 1,
					'args'   => [ $nonce, \Cyr_To_Lat_Converter::QUERY_ARG ],
					'return' => $verify_nonce,
				]
			);
			if ( $verify_nonce ) {
				$subject->shouldReceive( 'convert_existing_slugs' )->once();
			}
		}

		$subject->process_handler();
		$this->assertTrue( true );
	}

	/**
	 * Data provider for test_process_handler()
	 *
	 * @return array
	 */
	public function dp_test_process_handler() {
		return [
			[ '', '', false ],
			[ \Cyr_To_Lat_Converter::QUERY_ARG, '', false ],
			[ \Cyr_To_Lat_Converter::QUERY_ARG, 'some_nonce_value', false ],
			[ \Cyr_To_Lat_Converter::QUERY_ARG, 'some_nonce_value', true ],
		];
	}

	/**
	 * Test convert_existing_slugs()
	 *
	 * @param array $posts Posts to convert.
	 * @param array $terms Terms to convert.
	 *
	 * @dataProvider dp_test_convert_existing_slugs
	 */
	public function test_convert_existing_slugs( $posts, $terms ) {
		global $wpdb;

		$main              = \Mockery::mock( 'Cyr_To_Lat_Main' );
		$settings          = \Mockery::mock( 'Cyr_To_Lat_Settings' );
		$process_all_posts = \Mockery::mock( 'Cyr_To_Lat_Post_Conversion_Process' );
		$process_all_terms = \Mockery::mock( 'Cyr_To_Lat_Term_Conversion_Process' );
		$admin_notices     = \Mockery::mock( 'Cyr_To_Lat_Admin_Notices' );

		$subject = new Cyr_To_Lat_Converter(
			$main,
			$settings,
			$process_all_posts,
			$process_all_terms,
			$admin_notices
		);

		$post_types    = [
			'post'       => 'post',
			'page'       => 'page',
			'attachment' => 'attachment',
		];
		$post_types_in = "'post', 'page', 'attachment'";

		$post_statuses    = [ 'publish', 'future', 'private' ];
		$post_statuses_in = "'publish', 'future', 'private'";

		$defaults = [
			'post_type'   => $post_types,
			'post_status' => $post_statuses,
		];
		$args     = $defaults;

		\WP_Mock::userFunction(
			'get_post_types',
			[ 'return' => $post_types ]
		);

		\WP_Mock::userFunction(
			'wp_parse_args',
			[ 'return' => $args ]
		);

		$main->shouldReceive( 'ctl_prepare_in' )->with( $args['post_status'] )->once()->andReturn( $post_statuses_in );
		$main->shouldReceive( 'ctl_prepare_in' )->with( $args['post_type'] )->once()->andReturn( $post_types_in );

		$wpdb                = Mockery::mock( '\wpdb' );
		$wpdb->posts         = 'wp_posts';
		$wpdb->terms         = 'wp_terms';
		$wpdb->term_taxonomy = 'wp_term_taxonomy';

		$regexp     = Cyr_To_Lat_Main::PROHIBITED_CHARS_REGEX . '+';
		$post_query = "SELECT ID, post_name FROM $wpdb->posts WHERE post_name REGEXP(%s) AND post_status IN ($post_statuses_in) AND post_type IN ($post_types_in)";
		$term_query = "SELECT t.term_id, slug, tt.taxonomy, tt.term_taxonomy_id FROM $wpdb->terms t, $wpdb->term_taxonomy tt
					WHERE t.slug REGEXP(%s) AND tt.term_id = t.term_id";

		$wpdb->shouldReceive( 'prepare' )->with( $post_query, $regexp )->once()->andReturn( '' );
		$wpdb->shouldReceive( 'prepare' )->with( $term_query, $regexp )->once()->andReturn( '' );
		$wpdb->shouldReceive( 'get_results' )->once()->andReturn( $posts );
		$wpdb->shouldReceive( 'get_results' )->once()->andReturn( $terms );

		if ( $posts ) {
			$process_all_posts->shouldReceive( 'push_to_queue' )->times( count( $posts ) );
			$process_all_posts->shouldReceive( 'save' )->andReturn( $process_all_posts );
			$process_all_posts->shouldReceive( 'dispatch' );
			$admin_notices->shouldReceive( 'add_notice' )->with(
				'Cyr To Lat started conversion of existing post slugs.',
				'notice notice-info is-dismissible'
			);
		} else {
			$admin_notices->shouldReceive( 'add_notice' )->with(
				'Cyr To Lat has not found existing post slugs for conversion.',
				'notice notice-info is-dismissible'
			);
		}

		if ( $terms ) {
			$process_all_terms->shouldReceive( 'push_to_queue' )->times( count( $terms ) );
			$process_all_terms->shouldReceive( 'save' )->andReturn( $process_all_terms );
			$process_all_terms->shouldReceive( 'dispatch' );
			$admin_notices->shouldReceive( 'add_notice' )->with(
				'Cyr To Lat started conversion of existing term slugs.',
				'notice notice-info is-dismissible'
			);
		} else {
			$admin_notices->shouldReceive( 'add_notice' )->with(
				'Cyr To Lat has not found existing term slugs for conversion.',
				'notice notice-info is-dismissible'
			);
		}

		$subject->convert_existing_slugs();
		$this->assertTrue( true );
	}

	/**
	 * Data provider for test_convert_existing_slugs()
	 *
	 * @return array
	 */
	public function dp_test_convert_existing_slugs() {
		return [
			[ null, null ],
			[ [ 'post1', 'post2' ], [ 'term1', 'term2' ] ],
		];
	}

	/**
	 * Test log()
	 *
	 * @param boolean $debug Is WP_DEBUG_LOG on.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider        dp_test_log
	 */
	public function test_log( $debug ) {
		$subject = \Mockery::mock( 'Cyr_To_Lat_Converter' )->makePartial()->shouldAllowMockingProtectedMethods();

		$test_log = 'test.log';
		$message  = 'Test message';
		if ( $debug ) {
			define( 'WP_DEBUG_LOG', true );
		}

		@unlink( $test_log );
		$error_log = ini_get( 'error_log' );
		ini_set( 'error_log', $test_log );

		$subject->log( $message );
		if ( $debug ) {
			$this->assertNotFalse( strpos( $this->get_log( $test_log ), 'Cyr-To-Lat: ' . $message ) );
		} else {
			$this->assertFalse( $this->get_log( $test_log ) );
		}

		ini_set( 'error_log', $error_log );
		@unlink( $test_log );
	}

	/**
	 * Data provider for test_log()
	 *
	 * @return array
	 */
	public function dp_test_log() {
		return [
			[ false ],
			[ true ],
		];
	}

	/**
	 * Get test subject
	 *
	 * @return Cyr_To_Lat_Converter
	 */
	private function get_subject() {
		$main              = \Mockery::mock( 'Cyr_To_Lat_Main' );
		$settings          = \Mockery::mock( 'Cyr_To_Lat_Settings' );
		$process_all_posts = \Mockery::mock( 'Cyr_To_Lat_Post_Conversion_Process' );
		$process_all_terms = \Mockery::mock( 'Cyr_To_Lat_Term_Conversion_Process' );
		$admin_notices     = \Mockery::mock( 'Cyr_To_Lat_Admin_Notices' );

		$subject = new Cyr_To_Lat_Converter(
			$main,
			$settings,
			$process_all_posts,
			$process_all_terms,
			$admin_notices
		);

		return $subject;
	}

	/**
	 * Get test log content
	 *
	 * @param string $test_log Test log filename.
	 *
	 * @return false|string
	 */
	private function get_log( $test_log ) {
		return @file_get_contents( $test_log );
	}
}
