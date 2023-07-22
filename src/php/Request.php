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
	 * Is allowed request for plugin to work.
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
		return defined( 'WP_CLI' ) && constant( 'WP_CLI' );
	}

	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * Case #1: After WP_REST_Request initialisation
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
		if ( filter_input( INPUT_GET, 'rest_route', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
			return true;
		}

		// Case #3.
		global $wp_rewrite;

		if ( null === $wp_rewrite ) {
			// @codeCoverageIgnoreStart
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$wp_rewrite = new WP_Rewrite();
			// @codeCoverageIgnoreEnd
		}

		// Case #4.
		return (bool) $this->get_rest_route();
	}

	/**
	 * Get REST route.
	 * Returns route if it is a REST request, otherwise empty string.
	 *
	 * @return string
	 */
	protected function get_rest_route(): string {
		$current_path = (string) wp_parse_url( trailingslashit( add_query_arg( [] ) ), PHP_URL_PATH );
		$rest_path    = (string) wp_parse_url( trailingslashit( rest_url() ), PHP_URL_PATH );

		$is_rest = 0 === strpos( $current_path, $rest_path );

		return $is_rest ? substr( $current_path, strlen( $rest_path ) ) : '';
	}

	/**
	 * If current request is POST.
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
}
