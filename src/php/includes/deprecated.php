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
		const CLASSES = [
			'CyrToLat\Pro\Admin\Entries\DefaultScreen' => '1.8.2'
		];

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 */
		public function __construct() {

			self::trigger_error();
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param string $name Property name.
		 */
		public function __get( $name ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param string $name  Property name.
		 * @param mixed  $value Property value.
		 */
		public function __set( $name, $value ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param string $name Property name.
		 */
		public function __isset( $name ) {

			self::trigger_error( $name );
		}


		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param string $name      Method name.
		 * @param array  $arguments List of arguments.
		 */
		public function __call( $name, $arguments ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param string $name      Method name.
		 * @param array  $arguments List of arguments.
		 */
		public static function __callStatic( $name, $arguments ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param string $element_name Property or method name.
		 */
		private static function trigger_error( $element_name = '' ) {

			$current_class   = static::class;
			$removed_element = $current_class;

			if ( $element_name ) {
				$removed_element .= '::' . $element_name;
			}

			$version = ! empty( self::CLASSES[ $current_class ] ) ? self::CLASSES[ $current_class ] : CyrToLat_VERSION;

			trigger_error(
				sprintf(
					'%1$s has been removed in %2$s of the CyrToLat plugin',
					esc_html( $removed_element ),
					esc_html( $version )
				),
				E_USER_WARNING
			);
		}
	}
}

namespace CyrToLat\Forms {

	use CyrToLat\Removed;

	class Loader extends Removed {}
}

namespace {
	/**
	 * To be compatible with both WP 4.9 (that can run on PHP 5.2+) and WP 5.3+ (PHP 5.6+)
	 * we need to rewrite some core WP classes and tweak our own skins to not use PHP 5.6 splat operator (...$args)
	 * that were introduced in WP 5.3 in \WP_Upgrader_Skin::feedback().
	 * This alias is a safeguard to those developers who decided to use our internal class CyrToLat_Install_Silent_Skin,
	 * which we deleted.
	 *
	 * @since 1.5.6.1
	 */
	class_alias( 'CyrToLat\Helpers\PluginSilentUpgraderSkin', 'CyrToLat_Install_Silent_Skin' );

	/**
	 * Legacy `CyrToLat_Addons` class was refactored and moved to the new `CyrToLat\Pro\Admin\Pages\Addons` class.
	 * This alias is a safeguard to those developers who use our internal class CyrToLat_Addons,
	 * which we deleted.
	 *
	 * @since 1.6.7
	 */
	class_alias( CyrToLat()->is_pro() ? 'CyrToLat\Pro\Admin\Pages\Addons' : 'CyrToLat\Lite\Admin\Pages\Addons', 'CyrToLat_Addons' );

	/**
	 * This alias is a safeguard to those developers who decided to use our internal class CyrToLat_Smart_Tags,
	 * which we deleted.
	 *
	 * @since 1.6.7
	 */
	class_alias( CyrToLat()->is_pro() ? 'CyrToLat\Pro\SmartTags\SmartTags' : 'CyrToLat\SmartTags\SmartTags', 'CyrToLat_Smart_Tags' );

	/**
	 * This alias is a safeguard to those developers who decided to use our internal class \CyrToLat\Providers\Loader,
	 * which we deleted.
	 *
	 * @since 1.7.3
	 */
	class_alias( '\CyrToLat\Providers\Providers', '\CyrToLat\Providers\Loader' );

	/**
	 * Legacy `\CyrToLat\Admin\Notifications` class was refactored and moved to the new `\CyrToLat\Admin\Notifications\Notifications` class.
	 * This alias is a safeguard to those developers who use our internal class \CyrToLat\Admin\Notifications,
	 * which we deleted.
	 *
	 * @since 1.7.5
	 */
	class_alias( '\CyrToLat\Admin\Notifications\Notifications', '\CyrToLat\Admin\Notifications' );

	/**
	 * Legacy `\CyrToLat_Field_Payment_Checkbox` class was refactored and moved to the new `\CyrToLat\Forms\Fields\PaymentCheckbox\Field` class.
	 * This alias is a safeguard to those developers who use our internal class \CyrToLat_Field_Payment_Checkbox,
	 * which we deleted.
	 *
	 * @since 1.8.2
	 */
	class_alias( '\CyrToLat\Forms\Fields\PaymentCheckbox\Field', '\CyrToLat_Field_Payment_Checkbox' );

	/**
	 * Legacy `\CyrToLat_Field_Payment_Multiple` class was refactored and moved to the new `\CyrToLat\Forms\Fields\PaymentMultiple\Field` class.
	 * This alias is a safeguard to those developers who use our internal class \CyrToLat_Field_Payment_Multiple,
	 * which we deleted.
	 *
	 * @since 1.8.2
	 */
	class_alias( '\CyrToLat\Forms\Fields\PaymentMultiple\Field', '\CyrToLat_Field_Payment_Multiple' );

	/**
	 * Legacy `\CyrToLat_Field_Payment_Single` class was refactored and moved to the new `\CyrToLat\Forms\Fields\PaymentSingle\Field` class.
	 * This alias is a safeguard to those developers who use our internal class \CyrToLat_Field_Payment_Single,
	 * which we deleted.
	 *
	 * @since 1.8.2
	 */
	class_alias( '\CyrToLat\Forms\Fields\PaymentSingle\Field', '\CyrToLat_Field_Payment_Single' );

	/**
	 * Legacy `\CyrToLat_Field_Payment_Total` class was refactored and moved to the new `\CyrToLat\Forms\Fields\PaymentTotal\Field` class.
	 * This alias is a safeguard to those developers who use our internal class \CyrToLat_Field_Payment_Total,
	 * which we deleted.
	 *
	 * @since 1.8.2
	 */
	class_alias( '\CyrToLat\Forms\Fields\PaymentTotal\Field', '\CyrToLat_Field_Payment_Total' );

	/**
	 * Legacy `\CyrToLat_Field_Payment_Select` class was refactored and moved to the new `\CyrToLat\Forms\Fields\PaymentSelect\Field` class.
	 * This alias is a safeguard to those developers who use our internal class \CyrToLat_Field_Payment_Select,
	 * which we deleted.
	 *
	 * @since 1.8.2
	 */
	class_alias( '\CyrToLat\Forms\Fields\PaymentSelect\Field', '\CyrToLat_Field_Payment_Select' );

	/**
	 * Legacy `\CyrToLat\Migrations` class was refactored and moved to the new `\CyrToLat\Migrations\Migrations` class.
	 * This alias is a safeguard to those developers who use our internal class \CyrToLat\Migrations, which we deleted.
	 *
	 * @since 1.7.5
	 */
	class_alias( '\CyrToLat\Migrations\Migrations', '\CyrToLat\Migrations' );

	if ( CyrToLat()->is_pro() ) {
		/**
		 * Legacy `\CyrToLat\Pro\Migrations` class was refactored and moved to the new `\CyrToLat\Pro\Migrations\Migrations` class.
		 * This alias is a safeguard to those developers who use our internal class \CyrToLat\Migrations, which we deleted.
		 *
		 * @since 1.7.5
		 */
		class_alias( '\CyrToLat\Pro\Migrations\Migrations', '\CyrToLat\Pro\Migrations' );

		/**
		 * Legacy `\CyrToLat\Pro\Integrations\TranslationsPress\Translations` class was refactored and moved to the new
		 * `\CyrToLat\Pro\Integrations\Translations\Translations` class.
		 * This alias is a safeguard to those developers who use our internal class \CyrToLat\Pro\Integrations\TranslationsPress, which we deleted.
		 *
		 * @since 1.8.2.2
		 */
		class_alias( '\CyrToLat\Pro\Integrations\Translations\Translations', '\CyrToLat\Pro\Integrations\TranslationsPress\Translations' );
	}

	/**
	 * Legacy `\CyrToLat_Frontend` class was refactored and moved to the new `\CyrToLat\Frontend\Frontend` class.
	 * This alias is a safeguard to those developers who use our internal class \CyrToLat_Frontend, which we deleted.
	 *
	 * @since 1.8.1
	 */
	class_alias( '\CyrToLat\Frontend\Frontend', '\CyrToLat_Frontend' );

	/**
	 * Get notification state, whether it's opened or closed.
	 *
	 * @since      1.4.1
	 * @deprecated 1.4.8
	 *
	 * @param int $notification_id Notification ID.
	 *
	 * @param int $form_id         Form ID.
	 *
	 * @return string
	 */
	function CyrToLat_builder_notification_get_state( $form_id, $notification_id ) {

		_deprecated_function( __FUNCTION__, '1.4.8 of the CyrToLat addon', 'CyrToLat_builder_settings_block_get_state()' );

		return CyrToLat_builder_settings_block_get_state( $form_id, $notification_id, 'notification' );
	}

	/**
	 * Convert bytes to megabytes (or in some cases KB).
	 *
	 * @since      1.0.0
	 * @deprecated 1.6.2
	 *
	 * @param int $bytes Bytes to convert to a readable format.
	 *
	 * @return string
	 */
	function CyrToLat_size_to_megabytes( $bytes ) {

		_deprecated_function( __FUNCTION__, '1.6.2 of the CyrToLat plugin', 'size_format()' );

		return size_format( $bytes );
	}
}

namespace CyrToLat\Pro\Admin\Entries {

	/**
	 * Default Entries screen showed a chart and the form entries stats.
	 * Replaced with "CyrToLat\Pro\Admin\Entries\Overview".
	 *
	 * @since 1.5.5
	 * @deprecated 1.8.2
	 */
	class DefaultScreen extends \CyrToLat\Removed {}
}
