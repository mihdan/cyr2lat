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
	 * Current request is frontend.
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
	 * @param TermSlugService $term_slug_service                   Term slug service.
	 * @param bool            $is_frontend                         Whether current request is frontend.
	 * @param callable        $transliterate                       Transliteration callback.
	 * @param callable        $should_transliterate_pre_term_slug  Whether pre_term_slug should be transliterated callback.
	 * @param callable        $is_wc_attribute                     WooCommerce attribute preservation callback.
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
	 * Sanitize title through the legacy broad bridge.
	 *
	 * @param string|mixed $title     Sanitized title.
	 * @param string|mixed $raw_title The title prior to sanitization.
	 * @param string|mixed $context   The context for which the title is being sanitized.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
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

		return call_user_func( $this->is_wc_attribute, $title )
			? $title
			: strtolower( call_user_func( $this->transliterate, $title ) );
	}
}
