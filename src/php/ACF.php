<?php
/**
 * ACF Support.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat;

use CyrToLat\Settings\Settings;

/**
 * Class ACF
 */
class ACF {

	/**
	 * Plugin settings.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * ACF constructor.
	 *
	 * @param Settings $settings Plugin settings.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;

		$this->init_hooks();
	}

	/**
	 * Init class hooks.
	 */
	public function init_hooks() {
		add_action( 'acf/field_group/admin_enqueue_scripts', [ $this, 'enqueue_script' ] );
	}

	/**
	 * Enqueue script in ACF field group page.
	 *
	 * @return void
	 */
	public function enqueue_script() {
		global $cyr_to_lat_plugin;

		$min   = $cyr_to_lat_plugin->min_suffix();
		$table = $this->settings->get_table();

		wp_enqueue_script(
			'cyr-to-lat-acf-field-group',
			constant( 'CYR_TO_LAT_URL' ) . "/assets/js/acf-field-group$min.js",
			[],
			constant( 'CYR_TO_LAT_VERSION' ),
			true
		);

		$object = [ 'table' => $table ];

		wp_localize_script( 'cyr-to-lat-acf-field-group', 'CyrToLatAcfFieldGroup', $object );
	}
}
