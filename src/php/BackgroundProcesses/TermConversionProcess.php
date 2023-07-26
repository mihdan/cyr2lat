<?php
/**
 * Background old term slugs converting process
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\BackgroundProcesses;

use CyrToLat\Main;
use stdClass;

/**
 * Class TermConversionProcess
 */
class TermConversionProcess extends ConversionProcess {

	/**
	 * Site locale.
	 *
	 * @var string
	 */
	private $locale;

	/**
	 * Current term to convert.
	 *
	 * @var stdClass
	 */
	private $term;

	/**
	 * Process action name
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * TermConversionProcess constructor.
	 *
	 * @param Main $main Plugin main class.
	 */
	public function __construct( Main $main ) {
		$this->action = constant( 'CYR_TO_LAT_TERM_CONVERSION_ACTION' );
		$this->locale = get_locale();

		parent::__construct( $main );
	}

	/**
	 * Task. Updates single term
	 *
	 * @param stdClass $term Queue item to iterate over.
	 *
	 * @return boolean
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	protected function task( $term ) {
		global $wpdb;

		$this->term = $term;
		$slug       = urldecode( $term->slug );

		add_filter( 'locale', [ $this, 'filter_term_locale' ] );
		$transliterated_slug = $this->main->transliterate( $slug );
		remove_filter( 'locale', [ $this, 'filter_term_locale' ] );

		if ( $transliterated_slug !== $slug ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update( $wpdb->terms, [ 'slug' => rawurlencode( $transliterated_slug ) ], [ 'term_id' => $term->term_id ] );

			$this->log( __( 'Term slug converted:', 'cyr2lat' ) . ' ' . $slug . ' => ' . $transliterated_slug );
		}

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		parent::complete();

		wp_cache_flush();

		$this->log( __( 'Term slugs conversion completed.', 'cyr2lat' ) );
	}

	/**
	 * Filter term locale
	 *
	 * @return string|mixed
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function filter_term_locale() {
		// Polylang filter.
		if ( class_exists( 'Polylang' ) ) {
			$pll_pll_get_term_language = pll_get_term_language( $this->term->term_taxonomy_id );

			if ( false !== $pll_pll_get_term_language ) {
				return $pll_pll_get_term_language;
			}

			return $this->locale;
		}

		// WPML filter.
		$args = [
			'element_type' => $this->term->taxonomy,
			'element_id'   => $this->term->term_taxonomy_id,
		];

		$wpml_element_language_details = apply_filters( 'wpml_element_language_details', false, $args );

		if ( ! isset( $wpml_element_language_details->language_code ) ) {
			return $this->locale;
		}

		$language_code = $wpml_element_language_details->language_code;

		$wpml_active_languages = apply_filters( 'wpml_active_languages', false, [] );

		return $wpml_active_languages[ $language_code ]['default_locale'] ?? $this->locale;
	}
}
