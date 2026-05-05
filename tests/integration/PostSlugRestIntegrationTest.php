<?php
/**
 * PostSlugRestIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Integration;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Test_REST_TestCase;

/**
 * Class PostSlugRestIntegrationTest
 *
 * @group integration
 * @group rest
 */
class PostSlugRestIntegrationTest extends WP_Test_REST_TestCase {

	private const CPT = 'cyr2lat_book';

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	private static int $admin_id;

	/**
	 * Previous REQUEST_URI value.
	 *
	 * @var string|null
	 */
	private ?string $previous_request_uri = null;

	/**
	 * Previous SCRIPT_NAME value.
	 *
	 * @var string|null
	 */
	private ?string $previous_script_name = null;

	/**
	 * Previous rest_route query value.
	 *
	 * @var string|null
	 */
	private ?string $previous_rest_route = null;

	/**
	 * Create shared fixtures.
	 *
	 * @param object $factory WordPress test factory.
	 *
	 * @return void
	 */
	public static function wpSetUpBeforeClass( object $factory ): void {
		self::$admin_id = $factory->user->create(
			[
				'role' => 'administrator',
			]
		);
	}

	/**
	 * Set up the REST request context.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->remember_rest_environment();
		$this->simulate_plain_permalink_rest_request( '/wp/v2/posts' );

		cyr_to_lat()->init_all();
		wp_set_current_user( self::$admin_id );

		$this->register_test_post_type();
		$this->reset_rest_server();
	}

	/**
	 * Tear down REST globals.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		$this->restore_rest_environment();
		unregister_post_type( self::CPT );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		unset( $GLOBALS['wp_rest_server'] );

		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/**
	 * Test creating a post via REST with an empty slug and Cyrillic title.
	 *
	 * @return void
	 */
	public function test_rest_creates_post_with_cyrillic_title_and_empty_slug(): void {
		$data = $this->dispatch_successful_request(
			'POST',
			'/wp/v2/posts',
			[
				'title'  => 'й',
				'status' => 'publish',
			]
		);

		self::assertSame( 'j', $data['slug'] );
		self::assertSame( 'j', get_post( $data['id'] )->post_name );
	}

	/**
	 * Test creating a page via REST with an empty slug and Cyrillic title.
	 *
	 * @return void
	 */
	public function test_rest_creates_page_with_cyrillic_title_and_empty_slug(): void {
		$data = $this->dispatch_successful_request(
			'POST',
			'/wp/v2/pages',
			[
				'title'  => 'й',
				'status' => 'publish',
			]
		);

		self::assertSame( 'j', $data['slug'] );
		self::assertSame( 'j', get_post( $data['id'] )->post_name );
	}

	/**
	 * Test creating a REST-enabled custom post type item with an empty slug and Cyrillic title.
	 *
	 * @return void
	 */
	public function test_rest_creates_custom_post_type_with_cyrillic_title_and_empty_slug(): void {
		$data = $this->dispatch_successful_request(
			'POST',
			'/wp/v2/cyr2lat-books',
			[
				'title'  => 'й',
				'status' => 'publish',
			]
		);

		self::assertSame( 'j', $data['slug'] );
		self::assertSame( 'j', get_post( $data['id'] )->post_name );
	}

	/**
	 * Test draft-to-publish behavior through REST.
	 *
	 * @return void
	 */
	public function test_rest_draft_exposes_generated_slug_and_publish_stores_final_slug(): void {
		$draft = $this->dispatch_successful_request(
			'POST',
			'/wp/v2/posts',
			[
				'title'  => 'й',
				'status' => 'draft',
			]
		);

		self::assertSame( '', $draft['slug'] );
		self::assertSame( 'j', $draft['generated_slug'] );
		self::assertSame( '', get_post( $draft['id'] )->post_name );

		$published = $this->dispatch_successful_request(
			'POST',
			'/wp/v2/posts/' . $draft['id'],
			[
				'status' => 'publish',
			]
		);

		self::assertSame( 'j', $published['slug'] );
		self::assertSame( 'j', get_post( $draft['id'] )->post_name );
	}

	/**
	 * Test updating a post via REST with an explicit Cyrillic slug.
	 *
	 * @return void
	 */
	public function test_rest_update_transliterates_explicit_cyrillic_slug(): void {
		$post_id = self::factory()->post->create(
			[
				'post_status' => 'publish',
				'post_title'  => 'Initial',
				'post_name'   => 'initial',
			]
		);

		$data = $this->dispatch_successful_request(
			'POST',
			'/wp/v2/posts/' . $post_id,
			[
				'slug' => 'й',
			]
		);

		self::assertSame( 'j', $data['slug'] );
		self::assertSame( 'j', get_post( $post_id )->post_name );
	}

	/**
	 * Test updating a post via REST with an explicit Latin/manual slug.
	 *
	 * @return void
	 */
	public function test_rest_update_preserves_explicit_latin_slug(): void {
		$post_id = self::factory()->post->create(
			[
				'post_status' => 'publish',
				'post_title'  => 'й',
				'post_name'   => 'j',
			]
		);

		$data = $this->dispatch_successful_request(
			'POST',
			'/wp/v2/posts/' . $post_id,
			[
				'slug' => 'manual-slug',
			]
		);

		self::assertSame( 'manual-slug', $data['slug'] );
		self::assertSame( 'manual-slug', get_post( $post_id )->post_name );
	}

	/**
	 * Test that changing the title does not overwrite an existing manual slug.
	 *
	 * @return void
	 */
	public function test_rest_update_title_does_not_overwrite_manual_slug(): void {
		$post_id = self::factory()->post->create(
			[
				'post_status' => 'publish',
				'post_title'  => 'Initial',
				'post_name'   => 'manual-slug',
			]
		);

		$data = $this->dispatch_successful_request(
			'POST',
			'/wp/v2/posts/' . $post_id,
			[
				'title' => 'й',
			]
		);

		self::assertSame( 'manual-slug', $data['slug'] );
		self::assertSame( 'manual-slug', get_post( $post_id )->post_name );
	}

	/**
	 * Test duplicate Cyrillic titles receive unique Latin slugs.
	 *
	 * @return void
	 */
	public function test_rest_duplicate_cyrillic_titles_receive_unique_latin_slugs(): void {
		$first = $this->dispatch_successful_request(
			'POST',
			'/wp/v2/posts',
			[
				'title'  => 'й',
				'status' => 'publish',
			]
		);

		$second = $this->dispatch_successful_request(
			'POST',
			'/wp/v2/posts',
			[
				'title'  => 'й',
				'status' => 'publish',
			]
		);

		self::assertSame( 'j', $first['slug'] );
		self::assertSame( 'j-2', $second['slug'] );
	}

	/**
	 * Remember current REST-related globals.
	 *
	 * @return void
	 */
	private function remember_rest_environment(): void {
		$this->previous_request_uri = isset( $_SERVER['REQUEST_URI'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
			: null;
		$this->previous_script_name = isset( $_SERVER['SCRIPT_NAME'] )
			? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) )
			: null;
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$this->previous_rest_route = isset( $_GET['rest_route'] )
			? sanitize_text_field( wp_unslash( $_GET['rest_route'] ) )
			: null;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Simulate a plain-permalink REST request so plugin request detection enables backend hooks.
	 *
	 * @param string $route REST route.
	 *
	 * @return void
	 * @noinspection PhpSameParameterValueInspection
	 */
	private function simulate_plain_permalink_rest_request( string $route ): void {
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		$_SERVER['REQUEST_URI'] = '/index.php?rest_route=' . rawurlencode( $route );
		$_SERVER['SCRIPT_NAME'] = 'index.php';
		$_GET['rest_route']     = $route;
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Restore REST-related globals.
	 *
	 * @return void
	 */
	private function restore_rest_environment(): void {
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( null === $this->previous_request_uri ) {
			unset( $_SERVER['REQUEST_URI'] );
		} else {
			$_SERVER['REQUEST_URI'] = $this->previous_request_uri;
		}

		if ( null === $this->previous_script_name ) {
			unset( $_SERVER['SCRIPT_NAME'] );
		} else {
			$_SERVER['SCRIPT_NAME'] = $this->previous_script_name;
		}

		if ( null === $this->previous_rest_route ) {
			unset( $_GET['rest_route'] );
		} else {
			$_GET['rest_route'] = $this->previous_rest_route;
		}
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Register a REST-enabled custom post type for coverage.
	 *
	 * @return void
	 */
	private function register_test_post_type(): void {
		register_post_type(
			self::CPT,
			[
				'public'       => true,
				'rest_base'    => 'cyr2lat-books',
				'show_in_rest' => true,
				'supports'     => [ 'title', 'editor' ],
			]
		);
	}

	/**
	 * Reset the REST server and register routes for this test.
	 *
	 * @return void
	 */
	private function reset_rest_server(): void {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_rest_server'] = new WP_REST_Server();

		do_action( 'rest_api_init' );
	}

	/**
	 * Dispatch a REST request expected to succeed.
	 *
	 * @param string $method HTTP method.
	 * @param string $route  REST route.
	 * @param array  $params Request parameters.
	 *
	 * @return array<string, mixed>
	 * @noinspection PhpSameParameterValueInspection
	 */
	private function dispatch_successful_request( string $method, string $route, array $params ): array {
		$request = new WP_REST_Request( $method, $route );
		$request->set_body_params( $params );

		$response = rest_do_request( $request );

		self::assertInstanceOf( WP_REST_Response::class, $response );
		self::assertGreaterThanOrEqual( 200, $response->get_status() );
		self::assertLessThan( 300, $response->get_status() );

		return $response->get_data();
	}
}
