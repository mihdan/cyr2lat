<?php
/**
 * LegacySanitizeTitleBridge class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

use CyrToLat\Main;

/**
 * Handles the remaining broad sanitize_title fallback.
 *
 * WooCommerce attribute flows are handled by {@see GlobalAttributeService::sanitize_title()}
 * before reaching this bridge, so this class only deals with the generic legacy fallback.
 */
class LegacySanitizeTitleBridge {

	/**
	 * Main plugin class.
	 *
	 * @var Main
	 */
	private Main $main;

	/**
	 * Term slug service.
	 *
	 * @var TermSlugService
	 */
	private TermSlugService $term_slug_service;

	/**
	 * Constructor.
	 *
	 * @param Main            $main              Main plugin class.
	 * @param TermSlugService $term_slug_service Term slug service.
	 */
	public function __construct(
		Main $main,
		TermSlugService $term_slug_service
	) {
		$this->main              = $main;
		$this->term_slug_service = $term_slug_service;
	}

	/**
	 * Sanitize the title through the legacy broad bridge.
	 *
	 * @param string $title     Sanitized title.
	 * @param string $raw_title The title prior to sanitization.
	 * @param string $context   The context for which the title is being sanitized.
	 *
	 * @return string
	 */
	public function sanitize_title( string $title, string $raw_title = '', string $context = '' ): string {
		if (
			! $title ||
			// Fix the bug with `_wp_old_slug` redirect.
			'query' === $context ||
			! $this->term_slug_service->should_transliterate_on_pre_term_slug_filter( $title )
		) {
			return $title;
		}

		$bridge_enabled = (bool) apply_filters(
			'ctl_enable_legacy_sanitize_title_bridge',
			true,
			$title,
			$raw_title,
			$context
		);

		if ( ! $bridge_enabled ) {
			return $title;
		}

		$title = urldecode( $title );
		$pre   = apply_filters( 'ctl_pre_sanitize_title', false, $title );

		if ( false !== $pre ) {
			return (string) $pre;
		}

		$this->maybe_log_unknown_call( $title, $raw_title, $context );

		return $this->main->transliterate( $title );
	}

	/**
	 * Maybe log an unknown broad bridge call.
	 *
	 * @param string       $title     Sanitized title.
	 * @param string|mixed $raw_title The title prior to sanitization.
	 * @param string|mixed $context   The context for which the title is being sanitized.
	 *
	 * @return void
	 */
	private function maybe_log_unknown_call( string $title, $raw_title, $context ): void {
		if ( ! ( defined( 'WP_DEBUG' ) && constant( 'WP_DEBUG' ) ) ) {
			return;
		}

		$message = sprintf(
			'Cyr To Lat legacy sanitize_title bridge handled an unknown call: context="%s", title_hash="%s", raw_title_hash="%s".',
			is_scalar( $context ) ? (string) $context : gettype( $context ),
			md5( $title ),
			md5( is_scalar( $raw_title ) ? (string) $raw_title : gettype( $raw_title ) )
		);

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $message );
	}
}
