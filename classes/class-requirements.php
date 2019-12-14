<?php
/**
 * Class to check requirements of the plugin.
 *
 * @package cyr-to-lat
 */

namespace Cyr_To_Lat;

if ( ! class_exists( __NAMESPACE__ . '\Requirements' ) ) {

	/**
	 * Class Requirements
	 */
	class Requirements {

		/**
		 * Check if requirements are met.
		 *
		 * @return bool
		 */
		public function are_requirements_met() {
			return $this->is_php_version_required();
		}

		/**
		 * Check php version.
		 *
		 * @return bool
		 */
		private function is_php_version_required() {
			if ( version_compare( CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION, phpversion(), '>' ) ) {
				add_action( 'admin_notices', [ $this, 'php_requirement_message' ] );

				return false;
			}

			return true;
		}

		/**
		 * Show notice with php requirement.
		 */
		public function php_requirement_message() {
			load_plugin_textdomain(
				'cyr2lat',
				false,
				dirname( plugin_basename( CYR_TO_LAT_FILE ) ) . '/languages/'
			);

			/* translators: 1: Current PHP version number, 2: Cyr To Lat version, 3: Minimum required PHP version number */
			$message = sprintf( __( 'Your server is running PHP version %1$s but Cyr To Lat %2$s requires at least %3$s.', 'cyr2lat' ), phpversion(), CYR_TO_LAT_VERSION, CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION );
			?>
			<div class="message error">
				<p>
					<?php echo esc_html( $message ); ?>
				</p>
			</div>
			<?php
		}
	}
}
