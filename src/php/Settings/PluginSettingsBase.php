<?php
/**
 * PluginSettingsBase class file.
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat\Settings;

use Cyr_To_Lat\Settings\Abstracts\SettingsBase;

/**
 * Class PluginSettingsBase
 *
 * Extends general SettingsBase suitable for any plugin with current plugin related methods.
 */
abstract class PluginSettingsBase extends SettingsBase {

	/**
	 * Get plugin base name.
	 *
	 * @return string
	 */
	protected function plugin_basename() {
		return plugin_basename( constant( 'CYR_TO_LAT_FILE' ) );
	}

	/**
	 * Get plugin url.
	 *
	 * @return string
	 */
	protected function plugin_url() {
		return constant( 'CYR_TO_LAT_URL' );
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	protected function plugin_version() {
		return constant( 'CYR_TO_LAT_VERSION' );
	}

	/**
	 * Get settings link label.
	 *
	 * @return string
	 */
	protected function settings_link_label() {
		return __( 'View Cyr To Lat settings', 'cyr2lat' );
	}

	/**
	 * Get settings link text.
	 *
	 * @return string
	 */
	protected function settings_link_text() {
		return __( 'Settings', 'cyr2lat' );
	}

	/**
	 * Get text domain.
	 *
	 * @return string
	 */
	protected function text_domain() {
		return 'cyr2lat';
	}
}
