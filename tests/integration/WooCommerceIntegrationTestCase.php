<?php
/**
 * WooCommerceIntegrationTestCase class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Integration;

use WC_Install;
use WP_UnitTestCase;

/**
 * Base test case for integration tests that need the real WooCommerce plugin.
 */
abstract class WooCommerceIntegrationTestCase extends WP_UnitTestCase {

	/**
	 * WooCommerce plugin path relative to WP_PLUGIN_DIR.
	 *
	 * @var string
	 */
	protected static string $plugin = 'woocommerce/woocommerce.php';

	/**
	 * Whether WooCommerce was activated by this test case.
	 *
	 * @var bool
	 */
	protected static bool $plugin_active = false;

	/**
	 * Whether WooCommerce is available in this local test environment.
	 *
	 * @var bool
	 */
	protected static bool $woocommerce_available = true;

	/**
	 * Message used when WooCommerce is not available.
	 *
	 * @var string
	 */
	protected static string $woocommerce_skip_message = '';

	/**
	 * WooCommerce plugin file used by tests.
	 *
	 * @var string
	 */
	protected static string $plugin_file = '';

	/**
	 * Whether WooCommerce can be activated by WordPress' activate_plugin().
	 *
	 * @var bool
	 */
	protected static bool $use_wordpress_activation = false;

	/**
	 * Tear down after class.
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void {
		if ( static::$plugin_active ) {
			deactivate_plugins( static::$plugin );
			static::$plugin_active = false;
		}

		parent::tearDownAfterClass();
	}

	/**
	 * Set up test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! static::$woocommerce_available ) {
			self::markTestSkipped( static::$woocommerce_skip_message );
		}

		static::load_plugin_functions();
		static::ensure_woocommerce_plugin_is_available();

		if ( ! static::$woocommerce_available ) {
			self::markTestSkipped( static::$woocommerce_skip_message );
		}

		if ( ! static::$plugin_active ) {
			if ( static::$use_wordpress_activation ) {
				$result = activate_plugin( static::$plugin );

				if ( is_wp_error( $result ) ) {
					static::$woocommerce_available    = false;
					static::$woocommerce_skip_message = $result->get_error_message();

					self::markTestSkipped( static::$woocommerce_skip_message );
				}
			} else {
				static::activate_external_plugin();
			}

			static::$plugin_active = true;
		}

		if ( ! function_exists( 'WC' ) || ! function_exists( 'wc_create_attribute' ) ) {
			self::markTestSkipped( 'WooCommerce is not loaded in the integration test environment.' );
		}

		static::install_woocommerce_tables();
		static::init_woocommerce();
	}

	/**
	 * Load WordPress plugin admin functions.
	 *
	 * @return void
	 */
	protected static function load_plugin_functions(): void {
		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
	}

	/**
	 * Ensure WooCommerce is available for the integration test case.
	 *
	 * @return void
	 */
	protected static function ensure_woocommerce_plugin_is_available(): void {
		$target_file = trailingslashit( WP_PLUGIN_DIR ) . static::$plugin;

		if ( file_exists( $target_file ) ) {
			static::$plugin_file              = $target_file;
			static::$use_wordpress_activation = true;

			return;
		}

		$plugin_file = getenv( 'CYR2LAT_WC_PLUGIN_FILE' );
		$plugin_file = $plugin_file && file_exists( $plugin_file )
			? $plugin_file
			: 'C:/laragon/www/test/wp-content/plugins/woocommerce/woocommerce.php';

		if ( ! file_exists( $plugin_file ) ) {
			static::$woocommerce_available    = false;
			static::$woocommerce_skip_message = 'WooCommerce plugin file is not available in the local integration test environment.';

			return;
		}

		static::$plugin_file              = $plugin_file;
		static::$use_wordpress_activation = false;
	}

	/**
	 * Activate an external local WooCommerce checkout for this test process.
	 *
	 * @return void
	 */
	protected static function activate_external_plugin(): void {
		if ( ! function_exists( 'WC' ) ) {
			require_once static::$plugin_file;
		}

		$active_plugins = (array) get_option( 'active_plugins', [] );

		if ( in_array( static::$plugin, $active_plugins, true ) ) {
			return;
		}

		do_action( 'activate_plugin', static::$plugin, false );
		do_action( 'activate_' . static::$plugin, false );

		$active_plugins[] = static::$plugin;
		sort( $active_plugins );
		update_option( 'active_plugins', $active_plugins );

		do_action( 'activated_plugin', static::$plugin, false );
	}

	/**
	 * Install WooCommerce database tables needed by wc_create_attribute().
	 *
	 * @return void
	 */
	protected static function install_woocommerce_tables(): void {
		if ( class_exists( WC_Install::class ) ) {
			WC_Install::create_tables();
			update_option( 'woocommerce_version', WC()->version );
		}
	}

	/**
	 * Initialize WooCommerce after activation in the already bootstrapped WordPress test process.
	 *
	 * @return void
	 */
	protected static function init_woocommerce(): void {
		if ( ! did_action( 'woocommerce_init' ) ) {
			WC()->init();
		}
	}
}
