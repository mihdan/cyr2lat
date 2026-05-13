<?php
/**
 * WooCommerceWPTestCase class file.
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
 * Base test case for integration tests that need WooCommerce.
 */
abstract class WooCommerceWPTestCase extends PluginTestCase {

	/**
	 * WooCommerce plugin path relative to WP_PLUGIN_DIR.
	 *
	 * @var string
	 */
	protected static string $plugin = 'woocommerce/woocommerce.php';

	/**
	 * Set up WooCommerce for the already bootstrapped WordPress test process.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! function_exists( 'WC' ) || ! class_exists( WC_Post_Types::class ) ) {
			self::markTestSkipped( 'WooCommerce is not loaded in the integration test environment.' );
		}

		$this->install_woocommerce_tables();
		$this->init_woocommerce();
		wp_cache_flush();
	}

	/**
	 * Tear down common WooCommerce test globals.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		if ( function_exists( 'WC' ) ) {
			if ( WC()->cart ) {
				WC()->cart->empty_cart();
			}

			if ( WC()->session ) {
				WC()->session->set( 'cart', null );
				WC()->session->set( 'removed_cart_contents', null );
			}
		}

		if ( function_exists( 'wc_clear_notices' ) ) {
			wc_clear_notices();
		}

		unset( $GLOBALS['product'] );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_REQUEST = [];

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$_POST = [];

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		unset( $GLOBALS['current_screen'] );

		wp_cache_flush();

		parent::tearDown();
	}

	/**
	 * Install WooCommerce database tables needed by product, taxonomy, and cart flows.
	 *
	 * @return void
	 */
	protected function install_woocommerce_tables(): void {
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
	protected function init_woocommerce(): void {
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

	/**
	 * Load WooCommerce cart objects.
	 *
	 * @return void
	 */
	protected function load_cart(): void {
		if ( ! function_exists( 'wc_load_cart' ) ) {
			self::markTestSkipped( 'WooCommerce cart functions are not loaded in the integration test environment.' );
		}

		wc_load_cart();
		WC()->cart->empty_cart();
		wc_clear_notices();
	}

	/**
	 * Reload cart from the WooCommerce session handler.
	 *
	 * @return void
	 */
	protected function reload_cart_from_session(): void {
		WC()->session->set( 'cart', WC()->cart->get_cart_for_session() );
		WC()->cart->set_cart_contents( [] );

		$this->cart_session()->get_cart_from_session();
	}

	/**
	 * Get WooCommerce cart session handler.
	 *
	 * @return object
	 * @noinspection OneTimeUseVariablesInspection
	 * @noinspection PhpUndefinedFieldInspection
	 */
	protected function cart_session(): object {
		$getter = function () {
			return $this->session;
		};

		return $getter->call( WC()->cart );
	}

	/**
	 * Load WooCommerce frontend template functions needed by variation form actions.
	 *
	 * @return void
	 */
	protected function load_woocommerce_template_functions(): void {
		if ( ! function_exists( 'woocommerce_variable_add_to_cart' ) ) {
			WC()->include_template_functions();
		}

		if ( ! has_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart' ) ) {
			add_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
		}
	}
}
