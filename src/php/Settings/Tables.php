<?php
/**
 * Tables class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Settings;

use CyrToLat\ConversionTables;
use CyrToLat\Settings\Abstracts\SettingsBase;

/**
 * Class Tables
 *
 * Settings page "Tables" (main).
 */
class Tables extends PluginSettingsBase {

	/**
	 * Admin script handle.
	 */
	const HANDLE = 'cyr-to-lat-tables';

	/**
	 * Script localization object.
	 */
	const OBJECT = 'Cyr2LatTablesObject';

	/**
	 * Save table ajax action.
	 */
	const SAVE_TABLE_ACTION = 'cyr-to-lat-save-table';

	/**
	 * Served locales.
	 *
	 * @var array
	 */
	protected $locales = [];

	/**
	 * Get page title.
	 *
	 * @return string
	 */
	protected function page_title(): string {
		return __( 'Tables', 'cyr2lat' );
	}

	/**
	 * Get section title.
	 *
	 * @return string
	 */
	protected function section_title(): string {
		return 'tables';
	}

	/**
	 * Init class hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_action( 'wp_ajax_' . self::SAVE_TABLE_ACTION, [ $this, 'save_table' ] );
	}

	/**
	 * Get locales.
	 *
	 * @return array
	 */
	public function get_locales(): array {
		return $this->locales;
	}

	/**
	 * Init locales.
	 */
	protected function init_locales() {
		if ( ! empty( $this->locales ) ) {
			return;
		}

		$this->locales = [
			'iso9'  => __( 'Default', 'cyr2lat' ) . '<br>ISO9',
			'bel'   => __( 'Belarusian', 'cyr2lat' ) . '<br>bel',
			'uk'    => __( 'Ukrainian', 'cyr2lat' ) . '<br>uk',
			'bg_BG' => __( 'Bulgarian', 'cyr2lat' ) . '<br>bg_BG',
			'mk_MK' => __( 'Macedonian', 'cyr2lat' ) . '<br>mk_MK',
			'sr_RS' => __( 'Serbian', 'cyr2lat' ) . '<br>sr_RS',
			'el'    => __( 'Greek', 'cyr2lat' ) . '<br>el',
			'hy'    => __( 'Armenian', 'cyr2lat' ) . '<br>hy',
			'ka_GE' => __( 'Georgian', 'cyr2lat' ) . '<br>ka_GE',
			'kk'    => __( 'Kazakh', 'cyr2lat' ) . '<br>kk',
			'he_IL' => __( 'Hebrew', 'cyr2lat' ) . '<br>he_IL',
			'zh_CN' => __( 'Chinese (China)', 'cyr2lat' ) . '<br>zh_CN',
		];
	}

	/**
	 * Get current locale.
	 *
	 * @return string
	 */
	public function get_current_locale(): string {
		$current_locale = (string) apply_filters( 'ctl_locale', get_locale() );

		return array_key_exists( $current_locale, $this->locales ) ? $current_locale : 'iso9';
	}

	/**
	 * Init form fields.
	 */
	public function init_form_fields() {
		$this->init_locales();

		$current_locale = $this->get_current_locale();

		foreach ( $this->locales as $locale => $info ) {
			$info = ( $locale === $current_locale ) ? $info . '<br>' . __( '(current)', 'cyr2lat' ) : $info;

			$this->form_fields[ $locale ] = [
				'title'        => $info,
				'section'      => $locale . '_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => ConversionTables::get( $locale ),
			];
		}
	}

	/**
	 * Section callback.
	 *
	 * @param array $arguments Section arguments.
	 */
	public function section_callback( array $arguments ) {
		$locale = str_replace( '_section', '', $arguments['id'] );

		if ( $this->get_current_locale() === $locale ) {
			echo '<div id="ctl-current"></div>';
		}
	}

	/**
	 * Enqueue class scripts.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script(
			self::HANDLE,
			constant( 'CYR_TO_LAT_URL' ) . '/assets/js/apps/tables.js',
			[],
			constant( 'CYR_TO_LAT_VERSION' ),
			true
		);

		wp_localize_script(
			self::HANDLE,
			self::OBJECT,
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'action'  => self::SAVE_TABLE_ACTION,
				'nonce'   => wp_create_nonce( self::SAVE_TABLE_ACTION ),
			]
		);

		wp_enqueue_style(
			self::HANDLE,
			constant( 'CYR_TO_LAT_URL' ) . "/assets/css/tables$this->min_prefix.css",
			[ SettingsBase::HANDLE ],
			constant( 'CYR_TO_LAT_VERSION' )
		);
	}

	/**
	 * Save table.
	 *
	 * @return void
	 */
	public function save_table() {
		// Run a security check.
		if ( ! check_ajax_referer( self::SAVE_TABLE_ACTION, 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Your session has expired. Please reload the page.', 'cyr2lat' ) );
		}

		// Check for permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'cyr2lat' ) );
		}

		$new_settings = isset( $_POST['cyr_to_lat_settings'] ) ?
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			wp_unslash( $_POST['cyr_to_lat_settings'] ) :
			[];

		// We have only one table returned, so this is loop is executed once.
		foreach ( $new_settings as $new_key => $new_value ) {
			$key   = sanitize_text_field( $new_key );
			$value = [];

			foreach ( $new_value as $k => $v ) {
				$value[ sanitize_text_field( $k ) ] = sanitize_text_field( $v );
			}

			$this->update_option( $key, $value );
		}

		wp_send_json_success( esc_html__( 'Options saved.', 'cyr2lat' ) );
	}
}
