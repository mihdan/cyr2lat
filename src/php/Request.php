<?php
/**
 * Determine request type.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat;

use WP_Rewrite;

/**
 * Class Request
 */
class Request {

	/**
	 * Whether it is allowed request for the plugin to work.
	 *
	 * @return bool
	 */
	public function is_allowed(): bool {
		$allowed =
			! $this->is_frontend() ||
			( $this->is_frontend() && $this->is_post() ) ||
			$this->is_cli();

		return (bool) apply_filters( 'ctl_allow', $allowed );
	}

	/**
	 * Is frontend.
	 *
	 * @return bool
	 */
	public function is_frontend(): bool {
		return ! ( wp_doing_ajax() || is_admin() || $this->is_cli() || $this->is_rest() );
	}

	/**
	 * Check if it is a CLI request
	 *
	 * @return bool
	 */
	public function is_cli(): bool {
		return defined( 'WP_CLI' ) && constant( 'WP_CLI' ) && class_exists( 'WP_CLI', false );
	}

	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * Case #1: After WP_REST_Request initialization
	 * Case #2: Support "plain" permalink settings
	 * Case #3: It can happen that WP_Rewrite is not yet initialized,
	 *          so do this (wp-settings.php)
	 * Case #4: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @return bool
	 * @author matzeeable
	 */
	public function is_rest(): bool {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		// Case #1.
		if ( defined( 'REST_REQUEST' ) && constant( 'REST_REQUEST' ) ) {
			return true;
		}

		// Case #2.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$rest_route  = self::filter_input( INPUT_GET, 'rest_route' );
		$script_name = self::filter_input( INPUT_SERVER, 'SCRIPT_NAME' );

		if ( 0 === strpos( $rest_route, '/' ) && 'index.php' === basename( $script_name ) ) {
			return true;
		}

		// Case #3.
		global $wp_rewrite;

		$initial_wp_rewrite = $wp_rewrite;

		if ( null === $wp_rewrite ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$wp_rewrite = new WP_Rewrite();
		}

		// Case #4.
		$current_url = (string) wp_parse_url( add_query_arg( [] ), PHP_URL_PATH );
		$rest_url    = wp_parse_url( trailingslashit( rest_url() ), PHP_URL_PATH );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_rewrite = $initial_wp_rewrite;

		return 0 === strpos( $current_url, $rest_url );
	}

	/**
	 * If the current request is POST.
	 *
	 * @return bool
	 */
	public function is_post(): bool {
		$request_method = filter_var(
			isset( $_SERVER['REQUEST_METHOD'] ) ? wp_unslash( $_SERVER['REQUEST_METHOD'] ) : '',
			FILTER_SANITIZE_FULL_SPECIAL_CHARS
		);

		return 'POST' === $request_method;
	}

	/**
	 * Filter input in WP style.
	 * Nonce must be checked in the calling function.
	 *
	 * @param int    $type     Input type.
	 * @param string $var_name Variable name.
	 *
	 * @return array|string
	 */
	public static function filter_input( int $type, string $var_name ) {
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		switch ( $type ) {
			case INPUT_GET:
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return isset( $_GET[ $var_name ] ) ? self::sanitize_data( $_GET[ $var_name ] ) : '';
			case INPUT_POST:
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				return isset( $_POST[ $var_name ] ) ? self::sanitize_data( $_POST[ $var_name ] ) : '';
			case INPUT_SERVER:
				return isset( $_SERVER[ $var_name ] ) ? self::sanitize_data( $_SERVER[ $var_name ] ) : '';
			case INPUT_COOKIE:
				return isset( $_COOKIE[ $var_name ] ) ? self::sanitize_data( $_COOKIE[ $var_name ] ) : '';
			default:
				return '';
		}
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	}

	/**
	 * Sanitize data.
	 *
	 * @param array|string $data Data to sanitize.
	 *
	 * @return array|string
	 */
	private static function sanitize_data( $data ) {
		if ( is_array( $data ) ) {
			return array_map( [ self::class, 'sanitize_data' ], $data );
		}

		return is_scalar( $data ) ? sanitize_text_field( wp_unslash( $data ) ) : '';
	}
}
