<?php
/**
 * Test_Converter class file
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedMethodInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound

namespace Cyr_To_Lat;

use Cyr_To_Lat\Settings\Settings;
use Mockery;
use ReflectionClass;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;
use wpdb;

/**
 * Class Test_Converter
 *
 * @group converter
 */
class Test_Converter extends Cyr_To_Lat_TestCase {

	/**
	 * End test
	 */
	public function tearDown(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		unset( $_GET[ Converter::QUERY_ARG ], $_GET['_wpnonce'], $_POST['cyr2lat-convert'], $GLOBALS['wpdb'] );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Test constructor
	 *
	 * @throws ReflectionException Reflection Exception.
	 * @noinspection NullPointerExceptionInspection
	 */
	public function test_constructor() {
		$classname = Converter::class;

		$main          = Mockery::mock( Main::class );
		$settings      = Mockery::mock( Settings::class );
		$post_cp       = Mockery::mock( Post_Conversion_Process::class );
		$term_cp       = Mockery::mock( Term_Conversion_Process::class );
		$admin_notices = Mockery::mock( Admin_Notices::class );

		// Get mock, without the constructor being called.
		$mock = $this->getMockBuilder( $classname )->disableOriginalConstructor()->getMock();

		// Set expectations for constructor calls.
		$mock->expects( self::once() )->method( 'init_hooks' );

		// Now call the constructor.
		$reflected_class = new ReflectionClass( $classname );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $mock, $main, $settings, $post_cp, $term_cp, $admin_notices );

		self::assertInstanceOf( Main::class, $this->get_protected_property( $mock, 'main' ) );
		self::assertInstanceOf( Settings::class, $this->get_protected_property( $mock, 'settings' ) );
		self::assertInstanceOf( Post_Conversion_Process::class, $this->get_protected_property( $mock, 'process_all_posts' ) );
		self::assertInstanceOf( Term_Conversion_Process::class, $this->get_protected_property( $mock, 'process_all_terms' ) );
		self::assertInstanceOf( Admin_Notices::class, $this->get_protected_property( $mock, 'admin_notices' ) );
	}

	/**
	 * Test init_hooks()
	 */
	public function test_init_hooks() {
		$subject = $this->get_subject();

		WP_Mock::expectActionAdded( 'admin_init', [ $subject, 'process_handler' ] );
		WP_Mock::expectActionAdded( 'admin_init', [ $subject, 'conversion_notices' ] );

		$subject->init_hooks();
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
		$main              = Mockery::mock( Main::class );
		$settings          = Mockery::mock( Settings::class );
		$process_all_posts = Mockery::mock( Post_Conversion_Process::class )->shouldAllowMockingProtectedMethods();
		$process_all_terms = Mockery::mock( Term_Conversion_Process::class )->shouldAllowMockingProtectedMethods();
		$admin_notices     = Mockery::mock( Admin_Notices::class );

		// phpcs:disable Generic.Commenting.DocComment.MissingShort
		/** @noinspection PhpParamsInspection */
		$subject = new Converter(
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
			WP_Mock::expectActionAdded( 'admin_init', [ $subject, 'start_conversion' ], 20 );
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
		$subject = Mockery::mock( Converter::class )->makePartial();

		WP_Mock::passthruFunction( 'check_admin_referer' );

		if ( $convert ) {
			$_POST['ctl-convert'] = 'something';
			$subject->shouldReceive( 'convert_existing_slugs' )->once();
		} else {
			$subject->shouldReceive( 'convert_existing_slugs' )->never();
		}

		$subject->start_conversion();
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
		$subject = Mockery::mock( Converter::class )->makePartial();

		if ( $query_arg ) {
			$_GET[ Converter::QUERY_ARG ] = '1';
		}

		if ( $nonce ) {
			$_GET['_wpnonce'] = $nonce;
		}

		if ( $query_arg && $nonce ) {
			WP_Mock::passthruFunction( 'sanitize_key' );
			WP_Mock::userFunction(
				'wp_verify_nonce',
				[
					'times'  => 1,
					'args'   => [ $nonce, Converter::QUERY_ARG ],
					'return' => $verify_nonce,
				]
			);
			if ( $verify_nonce ) {
				$subject->shouldReceive( 'convert_existing_slugs' )->once();
			}
		}

		$subject->process_handler();
	}

	/**
	 * Data provider for test_process_handler()
	 *
	 * @return array
	 */
	public function dp_test_process_handler() {
		return [
			[ '', '', false ],
			[ Converter::QUERY_ARG, '', false ],
			[ Converter::QUERY_ARG, 'some_nonce_value', false ],
			[ Converter::QUERY_ARG, 'some_nonce_value', true ],
		];
	}

	/**
	 * Test convert_existing_slugs()
	 *
	 * @param array $posts              Posts to convert.
	 * @param array $terms              Terms to convert.
	 * @param bool  $include_attachment Include attachment as post type.
	 *
	 * @dataProvider dp_test_convert_existing_slugs
	 */
	public function test_convert_existing_slugs( $posts, $terms, $include_attachment ) {
		global $wpdb;

		$main              = Mockery::mock( Main::class );
		$settings          = Mockery::mock( Settings::class );
		$process_all_posts = Mockery::mock( Post_Conversion_Process::class );
		$process_all_terms = Mockery::mock( Term_Conversion_Process::class );
		$admin_notices     = Mockery::mock( Admin_Notices::class );

		$subject = new Converter(
			$main,
			$settings,
			$process_all_posts,
			$process_all_terms,
			$admin_notices
		);

		if ( $include_attachment ) {
			$post_types             = [ 'post', 'page', 'notification', 'attachment' ];
			$convertible_post_types = [ 'post', 'page', 'product', 'attachment' ];
			$post_types_in          = "'post', 'page', 'attachment'";
		} else {
			$post_types             = [ 'post', 'page', 'notification' ];
			$convertible_post_types = [ 'post', 'page', 'product' ];
			$post_types_in          = "'post', 'page'";
		}

		$post_statuses    = [ 'publish', 'future', 'private' ];
		$post_statuses_in = "'publish', 'future', 'private'";

		$defaults = [
			'post_type'   => array_intersect( $post_types, $convertible_post_types ),
			'post_status' => $post_statuses,
		];

		$args = $defaults;

		FunctionMocker::replace( '\Cyr_To_Lat\Settings\Converter::get_convertible_post_types', $convertible_post_types );

		$settings->shouldReceive( 'get' )->with( 'background_post_types' )->andReturn( $post_types );
		$settings->shouldReceive( 'get' )->with( 'background_post_statuses' )->andReturn( $post_statuses );

		WP_Mock::userFunction( 'wp_parse_args' )->with( [], $defaults )->andReturn( $args );

		$main->shouldReceive( 'prepare_in' )->with( $args['post_status'] )->once()->andReturn( $post_statuses_in );
		$main->shouldReceive( 'prepare_in' )->with( $args['post_type'] )->once()->andReturn( $post_types_in );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb                = Mockery::mock( wpdb::class );
		$wpdb->posts         = 'wp_posts';
		$wpdb->terms         = 'wp_terms';
		$wpdb->term_taxonomy = 'wp_term_taxonomy';

		$regexp = $subject::ALLOWED_CHARS_REGEX;

		if ( $include_attachment ) {
			$post_query =
				"SELECT ID, post_name, post_type FROM $wpdb->posts " .
				'WHERE LOWER(post_name) NOT REGEXP(%s) ' .
				"AND ((post_status IN ($post_statuses_in) AND post_type IN ($post_types_in)) OR (post_status = 'inherit' AND post_type = 'attachment'))";
		} else {
			$post_query =
				"SELECT ID, post_name, post_type FROM $wpdb->posts " .
				'WHERE LOWER(post_name) NOT REGEXP(%s) ' .
				"AND (post_status IN ($post_statuses_in) AND post_type IN ($post_types_in))";
		}

		$term_query =
			"SELECT t.term_id, slug, tt.taxonomy, tt.term_taxonomy_id FROM $wpdb->terms t, $wpdb->term_taxonomy tt
					WHERE LOWER(t.slug) NOT REGEXP(%s) AND tt.taxonomy NOT REGEXP ('^pa_.*$') AND tt.term_id = t.term_id";

		$post_query_prepared = str_replace( '%s', "'" . $subject::ALLOWED_CHARS_REGEX . "'", $post_query );
		$term_query_prepared = str_replace( '%s', "'" . $subject::ALLOWED_CHARS_REGEX . "'", $term_query );

		$wpdb->shouldReceive( 'prepare' )->with( '%s', $regexp )->once()
			->andReturn( "'" . $subject::ALLOWED_CHARS_REGEX . "'" );
		$wpdb->shouldReceive( 'prepare' )->with( $term_query, $regexp )->once()->andReturn( $term_query_prepared );

		$wpdb->shouldReceive( 'get_results' )->with( $post_query_prepared )->once()->andReturn( $posts );
		$wpdb->shouldReceive( 'get_results' )->with( $term_query_prepared )->once()->andReturn( $terms );

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
	}

	/**
	 * Data provider for test_convert_existing_slugs()
	 *
	 * @return array
	 */
	public function dp_test_convert_existing_slugs() {
		return [
			'no posts/terms'               => [ null, null, true ],
			'posts, terms, attachments'    => [ [ 'post1', 'post2' ], [ 'term1', 'term2' ], true ],
			'posts, terms, no attachments' => [ [ 'post1', 'post2' ], [ 'term1', 'term2' ], false ],
		];
	}

	/**
	 * Test log()
	 *
	 * @param boolean $debug Is WP_DEBUG_LOG on.
	 *
	 * @dataProvider        dp_test_log
	 */
	public function test_log( $debug ) {
		$subject = Mockery::mock( Converter::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$message = 'Test message';

		FunctionMocker::replace(
			'defined',
			function ( $name ) use ( $debug ) {
				if ( 'WP_DEBUG_LOG' === $name ) {
					return $debug;
				}

				return null;
			}
		);

		FunctionMocker::replace(
			'constant',
			function ( $name ) use ( $debug ) {
				if ( 'WP_DEBUG_LOG' === $name ) {
					return $debug;
				}

				return null;
			}
		);

		$log = [];
		FunctionMocker::replace(
			'error_log',
			function ( $message ) use ( &$log ) {
				$log[] = $message;
			}
		);

		$subject->log( $message );
		if ( $debug ) {
			self::assertSame( [ 'Cyr To Lat: ' . $message ], $log );
		} else {
			self::assertSame( [], $log );
		}
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
	 * @return Converter
	 */
	private function get_subject() {
		$main              = Mockery::mock( Main::class );
		$settings          = Mockery::mock( Settings::class );
		$process_all_posts = Mockery::mock( Post_Conversion_Process::class );
		$process_all_terms = Mockery::mock( Term_Conversion_Process::class );
		$admin_notices     = Mockery::mock( Admin_Notices::class );

		return new Converter(
			$main,
			$settings,
			$process_all_posts,
			$process_all_terms,
			$admin_notices
		);
	}
}
