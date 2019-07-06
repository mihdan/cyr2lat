<?php
/**
 * ACF Support.
 *
 * @package cyr-to-lat
 */

/**
 * Class Cyr_To_Lat_ACF
 */
class Cyr_To_Lat_ACF {

	/**
	 * Plugin settings.
	 *
	 * @var Cyr_To_Lat_Settings
	 */
	private $settings;

	/**
	 * Cyr_To_Lat_ACF constructor.
	 *
	 * @param Cyr_To_Lat_Settings $settings Plugin settings.
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;

		$this->init_hooks();
	}

	/**
	 * Init class hooks.
	 */
	public function init_hooks() {
		add_action( 'acf/field_group/admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
	}

	/**
	 * Enqueue script in ACF field group page.
	 */
	public function enqueue_script() {
		$table = $this->settings->get_table();

		wp_enqueue_script(
			'cyr-to-lat-acf-field-group',
			CYR_TO_LAT_URL . '/js/acf-field-group.js',
			array(),
			CYR_TO_LAT_VERSION,
			true
		);

		$object = array(
			'table' => $table,
		);

		wp_localize_script( 'cyr-to-lat-acf-field-group', 'CyrToLatAcfFieldGroup', $object );
	}
}
