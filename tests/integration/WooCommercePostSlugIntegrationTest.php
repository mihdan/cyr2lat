<?php
/**
 * WooCommercePostSlugIntegrationTest class file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedFunctionInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace CyrToLat\Tests\Integration;

use WC_Install;
use WC_Post_Types;

/**
 * Class WooCommercePostSlugIntegrationTest
 *
 * @group integration
 * @group woocommerce
 */
class WooCommercePostSlugIntegrationTest extends PluginWPTestCase {

	/**
	 * WooCommerce plugin path relative to WP_PLUGIN_DIR.
	 *
	 * @var string
	 */
	protected static string $plugin = 'woocommerce/woocommerce.php';

	/**
	 * Set up an allowed admin product request context.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! function_exists( 'WC' ) || ! class_exists( WC_Post_Types::class ) ) {
			self::markTestSkipped( 'WooCommerce post type classes are not loaded in the integration test environment.' );
		}

		$this->install_woocommerce_tables();
		$this->init_woocommerce();

		set_current_screen( 'post' );
		cyr_to_lat()->init_all();
		add_filter( 'ctl_enable_legacy_sanitize_title_bridge', '__return_false' );
	}

	/**
	 * Tear down test globals.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		remove_filter( 'ctl_enable_legacy_sanitize_title_bridge', '__return_false' );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		unset( $GLOBALS['current_screen'] );

		parent::tearDown();
	}

	/**
	 * Test that wp_insert_post() creates a product slug from a Cyrillic title.
	 *
	 * @return void
	 */
	public function test_wp_insert_post_creates_product_slug_from_cyrillic_title(): void {
		self::assertTrue( post_type_exists( 'product' ) );

		$post_id = wp_insert_post(
			[
				'post_type'   => 'product',
				'post_status' => 'publish',
				'post_title'  => 'й',
			],
			true
		);

		$this->assertNotWPError( $post_id );
		self::assertSame( 'j', get_post( $post_id )->post_name );
	}

	/**
	 * Install WooCommerce database tables needed by product flows.
	 *
	 * @return void
	 */
	private function install_woocommerce_tables(): void {
		if ( class_exists( WC_Install::class ) ) {
			WC_Install::create_tables();
			update_option( 'woocommerce_version', WC()->version );
		}
	}

	/**
	 * Initialize WooCommerce and restore post type/taxonomy lifecycle actions in the PHPUnit process.
	 *
	 * @return void
	 */
	private function init_woocommerce(): void {
		WC()->init();

		WC_Post_Types::register_taxonomies();
		WC_Post_Types::register_post_types();

		if ( ! did_action( 'woocommerce_after_register_taxonomy' ) ) {
			do_action( 'woocommerce_after_register_taxonomy' );
		}

		if ( ! did_action( 'woocommerce_after_register_post_type' ) ) {
			do_action( 'woocommerce_after_register_post_type' );
		}
	}
}
