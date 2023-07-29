<?php
/**
 * Converter class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Settings;

use CyrToLat\Settings\Abstracts\SettingsBase;

/**
 * Class Converter
 *
 * Settings page "Converter".
 */
class Converter extends PluginSettingsBase {

	/**
	 * Admin script handle.
	 */
	const HANDLE = 'cyr-to-lat-converter';

	/**
	 * Converter nonce.
	 */
	const NONCE = 'cyr-to-lat-converter-nonce';

	/**
	 * Post types and statuses section id.
	 */
	const SECTION_TYPES_STATUSES = 'types-statuses';

	/**
	 * Get page title.
	 *
	 * @return string
	 */
	protected function page_title(): string {
		return __( 'Converter', 'cyr2lat' );
	}

	/**
	 * Get section title.
	 *
	 * @return string
	 */
	protected function section_title(): string {
		return 'converter';
	}

	/**
	 * Init class hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_action( 'in_admin_header', [ $this, 'in_admin_header' ] );
		add_action( 'init', [ $this, 'delayed_init_settings' ], PHP_INT_MAX );
	}

	/**
	 * Empty method. Do stuff in the delayed_init_form_fields.
	 */
	public function init_form_fields() {
		$this->form_fields = [];

		$default_post_types = [ 'post', 'page', 'nav_menu_item' ];

		$post_types = $default_post_types;

		$filtered_post_types = array_filter( (array) apply_filters( 'ctl_post_types', $post_types ) );

		$this->form_fields['background_post_types'] = [
			'label'        => __( 'Post Types', 'cyr2lat' ),
			'section'      => self::SECTION_TYPES_STATUSES,
			'type'         => 'checkbox',
			'placeholder'  => '',
			'helper'       => __( 'Post types included in the conversion.', 'cyr2lat' ),
			'supplemental' => '',
			'options'      => [],
		];

		foreach ( $post_types as $post_type ) {
			$label = $post_type;

			$this->form_fields['background_post_types']['options'][ $post_type ] = $label;
		}

		$this->form_fields['background_post_types']['default']  = $default_post_types;
		$this->form_fields['background_post_types']['disabled'] = array_diff( $default_post_types, $filtered_post_types );

		$default_post_statuses = [ 'publish', 'future', 'private' ];
		$post_statuses         = [ 'publish', 'future', 'private', 'draft', 'pending' ];

		$this->form_fields['background_post_statuses'] = [
			'label'        => __( 'Post Statuses', 'cyr2lat' ),
			'section'      => self::SECTION_TYPES_STATUSES,
			'type'         => 'checkbox',
			'placeholder'  => '',
			'helper'       => __( 'Post statuses included in the conversion.', 'cyr2lat' ),
			'supplemental' => '',
			'options'      => [],
		];

		foreach ( $post_statuses as $post_status ) {
			$label = $post_status;

			$this->form_fields['background_post_statuses']['options'][ $post_status ] = $label;
		}

		$this->form_fields['background_post_statuses']['default'] = $default_post_statuses;
	}

	/**
	 * Get convertible post types.
	 *
	 * @return array
	 */
	public static function get_convertible_post_types(): array {
		$post_types = get_post_types( [ 'public' => true ] );

		return array_merge( $post_types, [ 'nav_menu_item' => 'nav_menu_item' ] );
	}

	/**
	 * Init form fields.
	 */
	public function delayed_init_form_fields() {
		$post_types = self::get_convertible_post_types();

		$filtered_post_types = array_filter( (array) apply_filters( 'ctl_post_types', $post_types ) );

		$this->form_fields['background_post_types']['options'] = [];

		foreach ( $post_types as $post_type ) {
			$label = $post_type;

			$this->form_fields['background_post_types']['options'][ $post_type ] = $label;
		}

		$this->form_fields['background_post_types']['disabled'] = array_diff(
			$this->form_fields['background_post_types']['default'],
			$filtered_post_types
		);
	}

	/**
	 * Init form fields and settings late, on 'init' hook with PHP_INT_MAX priority,
	 * to allow all plugins to register post types.
	 *
	 * @return void
	 */
	public function delayed_init_settings() {
		$this->delayed_init_form_fields();

		$this->init_settings();
	}

	/**
	 * Show settings page.
	 */
	public function settings_page() {
		parent::settings_page();

		?>
		<form id="ctl-convert-existing-slugs" action="" method="post">
			<input type="hidden" name="ctl-convert"/>
			<?php
			wp_nonce_field( self::NONCE );
			submit_button( __( 'Convert Existing Slugs', 'cyr2lat' ), 'secondary', 'ctl-convert-button' );
			?>
		</form>
		<?php
	}

	/**
	 * Section callback.
	 *
	 * @param array $arguments Section arguments.
	 */
	public function section_callback( array $arguments ) {
		if ( self::SECTION_TYPES_STATUSES === $arguments['id'] ) {
			?>
			<h2 class="title">
				<?php
				esc_html_e( 'Existing Slugs Conversion Settings', 'cyr2lat' );
				?>
			</h2>
			<p>
				<?php
				echo wp_kses_post(
					__(
						'Existing <strong>product attribute</strong> slugs will <strong>NOT</strong> be converted.',
						'cyr2lat'
					)
				);
				?>
			</p>
			<?php
			$this->print_section_header( $arguments['id'], __( 'Post Types and Statuses', 'cyr2lat' ) );
		}
	}

	/**
	 * Print section header.
	 *
	 * @param string $id    Section id.
	 * @param string $title Section title.
	 *
	 * @return void
	 */
	private function print_section_header( string $id, string $title ) {
		?>
		<h3 class="ctl-section-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $title ); ?></h3>
		<?php
	}

	/**
	 * Output convert confirmation popup.
	 *
	 * @return void
	 */
	public function in_admin_header() {
		if ( ! $this->is_options_screen() ) {
			return;
		}

		?>
		<div id="ctl-confirm-popup">
			<div id="ctl-confirm-content">
				<p>
					<strong><?php esc_html_e( 'Important:', 'cyr2lat' ); ?></strong>
					<?php
					esc_html_e(
						'This operation is irreversible. Please make sure that you have made a backup copy of your database.',
						'cyr2lat'
					);
					?>
				</p>
				<p>
					<?php
					esc_html_e(
						'Also, you have to make a copy of your media files if the attachment post type is selected for
				conversion.',
						'cyr2lat'
					);
					?>
				</p>
				<p>
					<?php
					esc_html_e(
						'Upon conversion of attachments, please regenerate thumbnails.',
						'cyr2lat'
					);
					?>
				</p>
				<p><?php esc_html_e( 'Are you sure to continue?', 'cyr2lat' ); ?></p>
				<div id="ctl-confirm-buttons">
					<input
						type="button" id="ctl-confirm-ok" class="button button-primary"
						value="<?php esc_html_e( 'OK', 'cyr2lat' ); ?>">
					<button
						type="button" id="ctl-confirm-cancel" class="button button-secondary">
						<?php esc_html_e( 'Cancel', 'cyr2lat' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue class scripts.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script(
			self::HANDLE,
			constant( 'CYR_TO_LAT_URL' ) . '/assets/js/apps/converter.js',
			[],
			constant( 'CYR_TO_LAT_VERSION' ),
			true
		);

		wp_enqueue_style(
			self::HANDLE,
			constant( 'CYR_TO_LAT_URL' ) . "/assets/css/converter$this->min_prefix.css",
			[ SettingsBase::HANDLE ],
			constant( 'CYR_TO_LAT_VERSION' )
		);
	}
}
