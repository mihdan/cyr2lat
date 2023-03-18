<?php
/**
 * Tables class file.
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat\Settings;

use Cyr_To_Lat\Conversion_Tables;

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
	 * Get screen id.
	 *
	 * @return string
	 */
	public function screen_id() {
		return 'settings_page_cyr-to-lat';
	}

	/**
	 * Get option group.
	 *
	 * @return string
	 */
	protected function option_group() {
		return 'cyr_to_lat_group';
	}

	/**
	 * Get option page.
	 *
	 * @return string
	 */
	protected function option_page() {
		return 'cyr-to-lat';
	}

	/**
	 * Get option name.
	 *
	 * @return string
	 */
	protected function option_name() {
		return 'cyr_to_lat_settings';
	}

	/**
	 * Get page title.
	 *
	 * @return string
	 */
	protected function page_title() {
		return __( 'Tables', 'cyr2lat' );
	}

	/**
	 * Get menu title.
	 *
	 * @return string
	 */
	protected function menu_title() {
		return __( 'Cyr To Lat', 'cyr2lat' );
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

		$this->form_fields = [];

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
	 * Show settings page.
	 */
	public function settings_page() {
		?>
		<div class="wrap">
			<h1>
				<?php
				// Admin panel title.
				esc_html_e( 'Cyr To Lat Plugin Options', 'cyr2lat' );
				?>
			</h1>

			<form
				id="ctl-options"
				class="ctl-<?php echo esc_attr( $this->section_title() ); ?>"
				action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>"
				method="post">
				<?php
				do_settings_sections( $this->option_page() ); // Sections with options.
				settings_fields( $this->option_group() ); // Hidden protection fields.
				submit_button();
				?>
			</form>

			<div id="appreciation">
				<h2>
					<?php echo esc_html( __( 'Your Appreciation', 'cyr2lat' ) ); ?>
				</h2>
				<a
					target="_blank"
					href="https://wordpress.org/support/view/plugin-reviews/cyr2lat?rate=5#new-post">
					<?php echo esc_html( __( 'Leave a ★★★★★ plugin review on WordPress.org', 'cyr2lat' ) ); ?>
				</a>
			</div>
		</div>
		<?php
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
		global $cyr_to_lat_plugin;

		if ( ! $this->is_options_screen() ) {
			return;
		}

		$min = $cyr_to_lat_plugin->min_suffix();

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
			constant( 'CYR_TO_LAT_URL' ) . "/assets/css/tables$min.css",
			[],
			constant( 'CYR_TO_LAT_VERSION' )
		);
	}

	/**
	 * Setup settings sections.
	 */
	public function setup_sections() {
		if ( ! $this->is_options_screen() ) {
			return;
		}

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
