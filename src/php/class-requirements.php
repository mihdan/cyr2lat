<?php
/**
 * Class to check requirements of the plugin.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Cyr_To_Lat;

use Cyr_To_Lat\Settings\Settings;
use WP_Filesystem_Direct;

if ( ! class_exists( __NAMESPACE__ . '\Requirements' ) ) {

	/**
	 * Class Requirements
	 */
	class Requirements {

		/**
		 * Settings.
		 *
		 * @var Settings
		 */
		protected $settings;

		/**
		 * Admin notices.
		 *
		 * @var Admin_Notices
		 */
		protected $admin_notices;

		/**
		 * WP file system direct.
		 *
		 * @var WP_Filesystem_Direct
		 */
		protected $wp_filesystem;

		/**
		 * Restrict notices to Cyr To Lat settings admin screens.
		 *
		 * @var array
		 */
		protected $screen_ids;

		/**
		 * Requirements constructor.
		 *
		 * @param Settings             $settings      Settings.
		 * @param Admin_Notices        $admin_notices Admin notices.
		 * @param WP_Filesystem_Direct $wp_filesystem File system.
		 */
		public function __construct( $settings, $admin_notices, $wp_filesystem = null ) {
			$this->settings   = $settings;
			$this->screen_ids = [ 'screen_ids' => $this->settings->screen_ids() ];

			$this->admin_notices = $admin_notices;

			// @codeCoverageIgnoreStart
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			// @codeCoverageIgnoreEnd

			if ( ! WP_Filesystem() ) {
				return;
			}

			$this->wp_filesystem = $wp_filesystem;
			if ( ! $this->wp_filesystem ) {
				$this->wp_filesystem = new WP_Filesystem_Direct( null );
			}
		}

		/**
		 * Check if requirements are met.
		 *
		 * @return bool
		 */
		public function are_requirements_met() {
			$is_php_version_required    = $this->is_php_version_required();
			$is_max_input_vars_required = $this->is_max_input_vars_required();

			if ( ! $is_php_version_required ) {
				add_action( 'admin_init', [ $this, 'deactivate_plugin' ] );
			}

			return $is_php_version_required && $is_max_input_vars_required;
		}

		/**
		 * Deactivate plugin.
		 */
		public function deactivate_plugin() {
			if ( is_plugin_active( plugin_basename( constant( 'CYR_TO_LAT_FILE' ) ) ) ) {
				deactivate_plugins( plugin_basename( constant( 'CYR_TO_LAT_FILE' ) ) );
				// phpcs:disable WordPress.Security.NonceVerification.Recommended
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
				// phpcs:enable WordPress.Security.NonceVerification.Recommended

				$this->admin_notices->add_notice(
					__( 'Cyr To Lat plugin has been deactivated.', 'cyr2lat' ),
					'notice notice-info is-dismissible'
				);
			}
		}

		/**
		 * Check php version.
		 *
		 * @return bool
		 * @noinspection ConstantCanBeUsedInspection
		 */
		private function is_php_version_required() {
			if ( version_compare( constant( 'CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION' ), phpversion(), '>' ) ) {
				/* translators: 1: Current PHP version number, 2: Cyr To Lat version, 3: Minimum required PHP version number */
				$message = sprintf( __( 'Your server is running PHP version %1$s but Cyr To Lat %2$s requires at least %3$s.', 'cyr2lat' ), phpversion(), constant( 'CYR_TO_LAT_VERSION' ), constant( 'CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION' ) );

				$this->admin_notices->add_notice( $message, 'notice notice-error' );

				return false;
			}

			return true;
		}

		/**
		 * Check max_input_vars.
		 *
		 * @return bool
		 */
		private function is_max_input_vars_required() {
			if ( constant( 'CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS' ) > ini_get( 'max_input_vars' ) ) {
				if ( $this->wp_filesystem ) {
					$this->try_to_fix_max_input_vars();
				} else {
					$this->admin_notices->add_notice(
						__( 'Unable to get filesystem access.', 'cyr2lat' ),
						'notice notice-error',
						$this->screen_ids
					);
					$this->ask_to_increase_max_input_vars();

					return true;
				}
			}

			if ( constant( 'CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS' ) > ini_get( 'max_input_vars' ) ) {
				$mtime     = $this->wp_filesystem->mtime( $this->get_user_ini_filename() );
				$ini_ttl   = (int) ini_get( 'user_ini.cache_ttl' );
				$time_left = ( $mtime + $ini_ttl ) - time();

				if ( 0 < $time_left ) {
					/* translators: 1: max_input_vars value, 2: Cyr To Lat version, 3: Minimum required max_input_vars */
					$message = sprintf( __( 'Your server is running PHP with max_input_vars=%1$d but Cyr To Lat %2$s requires at least %3$d.', 'cyr2lat' ), ini_get( 'max_input_vars' ), constant( 'CYR_TO_LAT_VERSION' ), constant( 'CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS' ) );

					$message .= '<br>';
					/* translators: 1: .user.ini filename */
					$message .= sprintf( __( 'We have updated settings in %s.', 'cyr2lat' ), realpath( $this->get_user_ini_filename() ) );
					$message .= '<br>';
					/* translators: 1: Wait time in seconds */
					$message .= sprintf( __( 'Please try again in %d s.', 'cyr2lat' ), $time_left );

					$this->admin_notices->add_notice( $message, 'notice notice-error', $this->screen_ids );
				} else {
					$this->ask_to_increase_max_input_vars();
				}

				return true;
			}

			return true;
		}

		/**
		 * Try to fix max_input_vars.
		 *
		 * @noinspection PhpStrFunctionsInspection
		 */
		protected function try_to_fix_max_input_vars() {
			$user_ini_filename = $this->get_user_ini_filename();

			$content = $this->wp_filesystem->get_contents( $user_ini_filename );

			$content     = str_replace( [ "\r\n", "\r" ], "\n", $content );
			$content_arr = explode( "\n", $content );

			array_map(
				static function ( $line ) use ( &$value ) {
					if ( preg_match( '/(?<![; ])\s*?(max_input_vars).*?=\D*?(\d+)/i', $line, $matches ) ) {
						$value = (int) $matches[2];
					}
				},
				$content_arr
			);

			if ( $value >= constant( 'CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS' ) ) {
				return;
			}

			$content_arr = array_filter(
				$content_arr,
				static function ( $line ) {
					return false === strpos( $line, 'max_input_vars' );
				}
			);
			if ( [ '' ] === $content_arr ) {
				$content_arr = [];
			}
			$content_arr[] = 'max_input_vars = ' . constant( 'CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS' );
			$content       = implode( PHP_EOL, $content_arr );

			$this->wp_filesystem->put_contents( $user_ini_filename, $content );
		}

		/**
		 * Get .user.ini filename.
		 *
		 * @return string
		 * @noinspection PhpPureAttributeCanBeAddedInspection
		 */
		private function get_user_ini_filename() {
			return ABSPATH . 'wp-admin/' . ini_get( 'user_ini.filename' );
		}

		/**
		 * Asl user to increase max_input_vars.
		 */
		private function ask_to_increase_max_input_vars() {
			$message = __( 'Please increase max input vars limit up to 1500.', 'cyr2lat' );

			$message .= '<br>';
			$message .= __( 'See: <a href="http://sevenspark.com/docs/ubermenu-3/faqs/menu-item-limit" target="_blank">Increasing max input vars limit.</a>', 'cyr2lat' );

			$this->admin_notices->add_notice(
				$message,
				'notice notice-error',
				[ 'screen_ids' => $this->settings->screen_ids() ]
			);
		}
	}
}
