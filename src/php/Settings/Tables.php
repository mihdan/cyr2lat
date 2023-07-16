<?php
/**
 * Tables class file.
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat\Settings;

use Cyr_To_Lat\Conversion_Tables;
use Cyr_To_Lat\Settings\Abstracts\SettingsBase;

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
	protected function page_title() {
		return __( 'Tables', 'cyr2lat' );
	}

	/**
	 * Get section title.
	 *
	 * @return string
	 */
	protected function section_title() {
		return 'tables';
	}

	/**
	 * Init locales.
	 */
	protected function init_locales() {
		if ( ! empty( $this->locales ) ) {
			return;
		}

		$this->locales = [
			'iso9'  => [
				'label' => __( 'ISO9 Table', 'cyr2lat' ),
			],
			'bel'   => [
				'label' => __( 'bel Table', 'cyr2lat' ),
			],
			'uk'    => [
				'label' => __( 'uk Table', 'cyr2lat' ),
			],
			'bg_BG' => [
				'label' => __( 'bg_BG Table', 'cyr2lat' ),
			],
			'mk_MK' => [
				'label' => __( 'mk_MK Table', 'cyr2lat' ),
			],
			'sr_RS' => [
				'label' => __( 'sr_RS Table', 'cyr2lat' ),
			],
			'el'    => [
				'label' => __( 'el Table', 'cyr2lat' ),
			],
			'hy'    => [
				'label' => __( 'hy Table', 'cyr2lat' ),
			],
			'ka_GE' => [
				'label' => __( 'ka_GE Table', 'cyr2lat' ),
			],
			'kk'    => [
				'label' => __( 'kk Table', 'cyr2lat' ),
			],
			'he_IL' => [
				'label' => __( 'he_IL Table', 'cyr2lat' ),
			],
			'zh_CN' => [
				'label' => __( 'zh_CN Table', 'cyr2lat' ),
			],
		];
	}

	/**
	 * Get current locale.
	 *
	 * @return string
	 */
	private function get_current_locale() {
		$current_locale = get_locale();

		return array_key_exists( $current_locale, $this->locales ) ? $current_locale : 'iso9';
	}

	/**
	 * Init form fields.
	 */
	public function init_form_fields() {
		$this->init_locales();

		$current_locale = $this->get_current_locale();

		foreach ( $this->locales as $locale => $info ) {
			$current = ( $locale === $current_locale ) ? '<br>' . __( '(current)', 'cyr2lat' ) : '';

			$this->form_fields[ $locale ] = [
				'label'        => $info['label'] . $current,
				'section'      => $locale . '_section',
				'type'         => 'table',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => Conversion_Tables::get( $locale ),
			];
		}
	}

	/**
	 * Section callback.
	 *
	 * @param array $arguments Section arguments.
	 */
	public function section_callback( $arguments ) {
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
				'optionsSaveSuccessMessage' => __( 'Options saved.', 'cyr2lat' ),
				'optionsSaveErrorMessage'   => __( 'Error saving options.', 'cyr2lat' ),
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
	 * Setup settings sections.
	 */
	public function setup_sections() {
		foreach ( $this->form_fields as $form_field ) {
			add_settings_section(
				$form_field['section'],
				$form_field['label'],
				[ $this, 'section_callback' ],
				$this->option_page()
			);
		}
	}
}
