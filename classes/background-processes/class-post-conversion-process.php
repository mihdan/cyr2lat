<?php
/**
 * Background old post slugs converting process
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat;

use stdClass;

/**
 * Class Post_Conversion_Process
 */
class Post_Conversion_Process extends Conversion_Process {

	/**
	 * Site locale.
	 *
	 * @var string
	 */
	private $locale;

	/**
	 * Current post to convert.
	 *
	 * @var stdClass
	 */
	private $post;

	/**
	 * Process action name
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * Post_Conversion_Process constructor.
	 *
	 * @param Main $main Plugin main class.
	 */
	public function __construct( $main ) {
		parent::__construct( $main );

		$this->action = constant( 'CYR_TO_LAT_POST_CONVERSION_ACTION' );
		$this->locale = get_locale();
	}

	/**
	 * Task. Updates single post
	 *
	 * @param stdClass $post Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $post ) {
		global $wpdb;

		$this->post = $post;
		$post_name  = urldecode( $post->post_name );

		add_filter( 'locale', [ $this, 'filter_post_locale' ] );
		$transliterated_name = $this->main->transliterate( $post_name );
		remove_filter( 'locale', [ $this, 'filter_post_locale' ] );

		if ( $transliterated_name !== $post_name ) {
			update_post_meta( $post->ID, '_wp_old_slug', $post_name );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update( $wpdb->posts, [ 'post_name' => rawurlencode( $transliterated_name ) ], [ 'ID' => $post->ID ] );

			$this->log( __( 'Post slug converted:', 'cyr2lat' ) . ' ' . $post_name . ' => ' . $transliterated_name );
		}

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		parent::complete();

		$this->log( __( 'Post slugs conversion completed.', 'cyr2lat' ) );
	}

	/**
	 * Filter post locale
	 *
	 * @return string
	 */
	public function filter_post_locale() {
		$wpml_post_language_details = apply_filters( 'wpml_post_language_details', false, $this->post->ID );

		return isset( $wpml_post_language_details['locale'] ) ? $wpml_post_language_details['locale'] : $this->locale;
	}
}
