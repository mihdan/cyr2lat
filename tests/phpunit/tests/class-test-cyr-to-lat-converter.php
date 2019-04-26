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
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * End test
	 */
	public function tearDown(): void {
		unset( $_GET[ \Cyr_To_Lat_Converter::QUERY_ARG ] );
		unset( $_GET['_wpnonce'] );
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

		\WP_Mock::expectFilterAdded(
			CYR_TO_LAT_PREFIX . '_' . CYR_TO_LAT_POST_CONVERSION_ACTION . '_memory_exceeded',
			[ $subject, 'memory_exceeded_filter' ]
		);
		\WP_Mock::expectFilterAdded(
			CYR_TO_LAT_PREFIX . '_' . CYR_TO_LAT_TERM_CONVERSION_ACTION . '_memory_exceeded',
			[ $subject, 'memory_exceeded_filter' ]
		);

		\WP_Mock::expectFilterAdded(
			CYR_TO_LAT_PREFIX . '_' . CYR_TO_LAT_POST_CONVERSION_ACTION . '_time_exceeded',
			[ $subject, 'time_exceeded_filter' ]
		);
		\WP_Mock::expectFilterAdded(
			CYR_TO_LAT_PREFIX . '_' . CYR_TO_LAT_TERM_CONVERSION_ACTION . '_time_exceeded',
			[ $subject, 'time_exceeded_filter' ]
		);

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

		$process_all_posts->expects( 'is_process_running' )->andReturn( $posts_process_running );
		$process_all_terms->expects( 'is_process_running' )->andReturn( $terms_process_running );

		$process_all_posts->expects( 'is_process_completed' )->andReturn( $posts_process_completed );
		$process_all_terms->expects( 'is_process_completed' )->andReturn( $terms_process_completed );

		if ( ! $posts_process_running && ! $terms_process_running ) {
			\WP_Mock::expectActionAdded( 'admin_init', [ $subject, 'start_conversion' ], 20 );
		}

		if ( $posts_process_running ) {
			$admin_notices->expects( 'add_notice' )->with(
				'Cyr To Lat converts existing post slugs in the background process.',
				'notice notice-info is-dismissible'
			);
		}

		if ( $terms_process_running ) {
			$admin_notices->expects( 'add_notice' )->with(
				'Cyr To Lat converts existing term slugs in the background process.',
				'notice notice-info is-dismissible'
			);
		}

		if ( $posts_process_completed ) {
			$admin_notices->expects( 'add_notice' )->with(
				'Cyr To Lat completed conversion of existing post slugs.',
				'notice notice-success is-dismissible'
			);
		}

		if ( $terms_process_completed ) {
			$admin_notices->expects( 'add_notice' )->with(
				'Cyr To Lat completed conversion of existing term slugs.',
				'notice notice-success is-dismissible'
			);
		}

		$subject->conversion_notices();
		$this->assertTrue( true );
	}

	/**
	 * Data provider for test_conversion_notices()
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
}


