<?php
/**
 * LegacySanitizeTitleBridge class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

/**
 * Handles the remaining broad sanitize_title fallback.
 */
class LegacySanitizeTitleBridge {

	/**
	 * Term slug service.
	 *
	 * @var TermSlugService
	 */
	private TermSlugService $term_slug_service;

	/**
	 * The current request is frontend.
	 *
	 * @var bool
	 */
	private bool $is_frontend;

	/**
	 * Transliteration callback.
	 *
	 * @var callable
	 */
	private $transliterate;

	/**
	 * Whether pre_term_slug should be transliterated callback.
	 *
	 * @var callable
	 */
	private $should_transliterate_pre_term_slug;

	/**
	 * WooCommerce attribute preservation callback.
	 *
	 * @var callable
	 */
	private $is_wc_attribute;

	/**
	 * Constructor.
	 *
	 * @param TermSlugService $term_slug_service                  Term slug service.
	 * @param bool            $is_frontend                        Whether current request is frontend.
	 * @param callable        $transliterate                      Transliteration callback.
	 * @param callable        $should_transliterate_pre_term_slug Whether pre_term_slug should be transliterated callback.
	 * @param callable        $is_wc_attribute                    WooCommerce attribute preservation callback.
	 */
	public function __construct(
		TermSlugService $term_slug_service,
		bool $is_frontend,
		callable $transliterate,
		callable $should_transliterate_pre_term_slug,
		callable $is_wc_attribute
	) {
		$this->term_slug_service                  = $term_slug_service;
		$this->is_frontend                        = $is_frontend;
		$this->transliterate                      = $transliterate;
		$this->should_transliterate_pre_term_slug = $should_transliterate_pre_term_slug;
		$this->is_wc_attribute                    = $is_wc_attribute;
	}

	/**
	 * Sanitize the title through the legacy broad bridge.
	 *
	 * @param string|mixed $title     Sanitized title.
	 * @param string|mixed $raw_title The title prior to sanitization.
	 * @param string|mixed $context   The context for which the title is being sanitized.
	 *
	 * @return string|mixed
	 */
	public function sanitize_title( $title, $raw_title = '', $context = '' ) {
		if (
			! $title ||
			// Fix the bug with `_wp_old_slug` redirect.
			'query' === $context ||
			! call_user_func( $this->should_transliterate_pre_term_slug, (string) $title )
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

		if ( ! $bridge_enabled && ! $this->is_known_explicit_context( $context ) ) {
			return $title;
		}

		$title = urldecode( (string) $title );
		$pre   = apply_filters( 'ctl_pre_sanitize_title', false, $title );

		if ( false !== $pre ) {
			return $pre;
		}

		$term = $this->term_slug_service->maybe_preserve_existing_encoded_slug(
			$title,
			$this->is_frontend
		);

		if ( false !== $term ) {
			return $term;
		}

		if ( call_user_func( $this->is_wc_attribute, $title ) ) {
			return $title;
		}

		if ( $bridge_enabled ) {
			$this->maybe_log_unknown_call( $title, $raw_title, $context );
		}

		return call_user_func( $this->transliterate, $title );
	}

	/**
	 * Whether the context is handled by explicit WordPress/WooCommerce slug paths.
	 *
	 * @param string|mixed $context The context for which the title is being sanitized.
	 *
	 * @return bool
	 */
	private function is_known_explicit_context( $context ): bool {
		return 'save' === $context;
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
