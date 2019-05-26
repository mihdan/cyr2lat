<?php
/**
 * Background old term slugs converting process
 *
 * @package cyr-to-lat
 */

/**
 * Class Cyr_To_Lat_Term_Conversion_Process
 */
class Cyr_To_Lat_Term_Conversion_Process extends Cyr_To_Lat_Conversion_Process {

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
	protected $action = CYR_TO_LAT_TERM_CONVERSION_ACTION;

	/**
	 * Cyr_To_Lat_Term_Conversion_Process constructor.
	 *
	 * @param Cyr_To_Lat_Main $main Plugin main class.
	 */
	public function __construct( $main ) {
		parent::__construct( $main );
		$this->locale = get_locale();
	}

	/**
	 * Task. Updates single term
	 *
	 * @param stdClass $term Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $term ) {
		global $wpdb;

		$this->term = $term;
		$slug       = urldecode( $term->slug );

		add_filter( 'locale', array( $this, 'filter_term_locale' ) );
		$sanitized_slug = sanitize_title( $slug );
		remove_filter( 'locale', array( $this, 'filter_term_locale' ) );

		if ( urldecode( $sanitized_slug ) !== $slug ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			$wpdb->update( $wpdb->terms, array( 'slug' => $sanitized_slug ), array( 'term_id' => $term->term_id ) );
			// phpcs:enable

			$this->log( __( 'Term slug converted:', 'cyr2lat' ) . ' ' . $slug . ' => ' . urldecode( $sanitized_slug ) );
		}

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		parent::complete();

		$this->log( __( 'Term slugs conversion completed.', 'cyr2lat' ) );
	}

	/**
	 * Filter term locale
	 *
	 * @return string
	 */
	public function filter_term_locale() {
		$args = array(
			'element_type' => $this->term->taxonomy,
			'element_id'   => $this->term->term_taxonomy_id,
		);

		$wpml_element_language_details = apply_filters( 'wpml_element_language_details', false, $args );

		if ( ! isset( $wpml_element_language_details->language_code ) ) {
			return $this->locale;
		}

		$language_code = $wpml_element_language_details->language_code;

		$wpml_active_languages = apply_filters( 'wpml_active_languages', false, array() );

		return isset( $wpml_active_languages[ $language_code ]['default_locale'] ) ?
			$wpml_active_languages[ $language_code ]['default_locale'] : $this->locale;
	}
}
