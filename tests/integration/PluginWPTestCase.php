<?php
/**
 * PluginWPTestCase class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Integration;

use WP_UnitTestCase;

/**
 * Base test case for integration tests that need an additional real WordPress plugin.
 */
abstract class PluginWPTestCase extends WP_UnitTestCase {

	/**
	 * Plugin path relative to WP_PLUGIN_DIR.
	 *
	 * @var string
	 */
	protected static string $plugin = '';

	/**
	 * Plugins activated by this test case layer.
	 *
	 * @var array<string, bool>
	 */
	protected static array $plugin_active = [];

	/**
	 * Plugin availability in this local test environment.
	 *
	 * @var array<string, bool>
	 */
	protected static array $plugin_available = [];

	/**
	 * Messages used when plugins are not available.
	 *
	 * @var array<string, string>
	 */
	protected static array $plugin_skip_messages = [];

	/**
	 * Tear down after class.
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void {
		$plugin_key = static::plugin_key();

		if ( static::$plugin_active[ $plugin_key ] ?? false ) {
			deactivate_plugins( static::$plugin );
			unset( static::$plugin_active[ $plugin_key ] );
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

		$plugin_key = static::plugin_key();

		if ( false === ( static::$plugin_available[ $plugin_key ] ?? true ) ) {
			self::markTestSkipped( static::$plugin_skip_messages[ $plugin_key ] );
		}

		static::load_plugin_functions();
		static::ensure_plugin_is_available();

		if ( false === ( static::$plugin_available[ $plugin_key ] ?? true ) ) {
			self::markTestSkipped( static::$plugin_skip_messages[ $plugin_key ] );
		}

		if ( ! ( static::$plugin_active[ $plugin_key ] ?? false ) ) {
			static::activate_configured_plugin();
			static::$plugin_active[ $plugin_key ] = true;
		}
	}

	/**
	 * Get a plugin state key.
	 *
	 * @return string
	 */
	protected static function plugin_key(): string {
		return static::$plugin ?: static::class;
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
	 * Ensure the plugin is available for the integration test case.
	 *
	 * @return void
	 */
	protected static function ensure_plugin_is_available(): void {
		$plugin_key = static::plugin_key();

		if ( ! static::$plugin ) {
			static::$plugin_available[ $plugin_key ]     = false;
			static::$plugin_skip_messages[ $plugin_key ] = 'Plugin path is not configured for this integration test.';

			return;
		}

		if ( ! file_exists( trailingslashit( WP_PLUGIN_DIR ) . static::$plugin ) ) {
			static::$plugin_available[ $plugin_key ]     = false;
			static::$plugin_skip_messages[ $plugin_key ] = 'Plugin is not installed in the WordPress integration test environment.';
		}
	}

	/**
	 * Activate the configured plugin.
	 *
	 * @return void
	 */
	protected static function activate_configured_plugin(): void {
		$plugin_key = static::plugin_key();
		$result     = activate_plugin( static::$plugin );

		if ( is_wp_error( $result ) ) {
			static::$plugin_available[ $plugin_key ]     = false;
			static::$plugin_skip_messages[ $plugin_key ] = $result->get_error_message();

			self::markTestSkipped( static::$plugin_skip_messages[ $plugin_key ] );
		}
	}
}
