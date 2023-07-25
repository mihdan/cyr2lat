<?php

// phpcs:ignoreFile Generic.Files.OneObjectStructurePerFile.MultipleFound

namespace CyrToLat {
	/**
	 * The removed class helps prevent fatal errors for clients
	 * that use some of the classes we are about to remove.
	 * Use the class extending instead of class_alias function.
	 *
	 * @since 6.0.0
	 */
	class Removed {

		/**
		 * List of removed classes in the next format:
		 * Fully-Qualified Class Name => version.
		 *
		 * @since 1.8.0
		 */
//		const CLASSES = [
//			'CyrToLat\Pro\Admin\Entries\DefaultScreen' => '1.8.2'
//		];

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 6.0.0
		 */
		public function __construct() {

			self::trigger_error();
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 6.0.0
		 *
		 * @param string $name Property name.
		 */
		public function __get( string $name ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 6.0.0
		 *
		 * @param string $name  Property name.
		 * @param mixed  $value Property value.
		 */
		public function __set( string $name, $value ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 6.0.0
		 *
		 * @param string $name Property name.
		 */
		public function __isset( string $name ) {

			self::trigger_error( $name );
		}


		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 6.0.0
		 *
		 * @param string $name      Method name.
		 * @param array  $arguments List of arguments.
		 */
		public function __call( string $name, array $arguments ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 6.0.0
		 *
		 * @param string $name      Method name.
		 * @param array  $arguments List of arguments.
		 */
		public static function __callStatic( string $name, array $arguments ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 6.0.0
		 *
		 * @param string $element_name Property or method name.
		 */
		private static function trigger_error( string $element_name = '' ) {

			$current_class   = static::class;
			$removed_element = $current_class;

			if ( $element_name ) {
				$removed_element .= '::' . $element_name;
			}

			$version = defined( $current_class . '::DEPRECATED' ) ? constant( $current_class .'::DEPRECATED' ) : '';
			$version = $version ?: CYR_TO_LAT_VERSION;

			trigger_error(
				sprintf(
					'%1$s has been removed in version %2$s of the CyrToLat plugin.',
					esc_html( $removed_element ),
					esc_html( $version )
				),
				E_USER_WARNING
			);
		}
	}
}

namespace Cyr_To_Lat {

	use CyrToLat\Removed;

	// Main classes.
	class ACF extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class Admin_Notices extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class Conversion_Tables extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class Converter extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class Main extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class Request extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class Requirements extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class WP_CLI extends Removed {
		const DEPRECATED = '6.0.0';
	}

	// Background Processes classes.
	class Conversion_Process extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class Post_Conversion_Process extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class Term_Conversion_Process extends Removed {
		const DEPRECATED = '6.0.0';
	}
}

namespace Cyr_To_Lat\Settings {

	use CyrToLat\Removed;

	class Converter extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class PluginSettingsBase extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class Settings extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class Tables extends Removed {
		const DEPRECATED = '6.0.0';
	}
}

namespace Cyr_To_Lat\Settings\Abstracts {

	use CyrToLat\Removed;

	class SettingsBase extends Removed {
		const DEPRECATED = '6.0.0';
	}

	class SettingsInterface extends Removed {
		const DEPRECATED = '6.0.0';
	}
}
