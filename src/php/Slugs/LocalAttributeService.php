<?php
/**
 * LocalAttributeService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

/**
 * Handles WooCommerce local product attribute slug decisions.
 */
class LocalAttributeService {

	/**
	 * Check if the title is a local attribute.
	 *
	 * @param string   $title     Title.
	 * @param callable $parse_str Request parser callback.
	 *
	 * @return bool
	 */
	public function is_local_attribute( string $title, callable $parse_str ): bool {
		// Global attribute.
		if ( 0 === strpos( $title, 'pa_' ) ) {
			return false;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing

		$action = $this->post_value( 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( 'woocommerce_do_ajax_product_import' === $action ) {
			return false;
		}

		// The `save attributes` action.
		if ( 'woocommerce_save_attributes' === $action ) {
			$data            = $this->post_value( 'data', FILTER_SANITIZE_URL );
			$attributes      = (array) call_user_func( $parse_str, urldecode( $data ) );
			$attribute_names = $attributes['attribute_names'] ?? [];

			return in_array( $title, $attribute_names, true );
		}

		// The `edit post` action.
		if ( 'editpost' === $action ) {
			$attribute_names = array_map(
				[ $this, 'sanitize_text_field' ],
				$this->post_array_value( 'attribute_names' )
			);

			return in_array( $title, $attribute_names, true );
		}

		if ( $this->doing_action( 'woocommerce_variable_add_to_cart' ) ) {
			$attributes = $this->product_attributes();

			$encoded_attr_name = strtolower( rawurlencode( mb_strtolower( $title ) ) );

			if ( isset( $attributes[ $encoded_attr_name ] ) ) {
				return true;
			}

			return false;
		}

		if ( $this->did_action( 'woocommerce_load_cart_from_session' ) ) {
			return true;
		}

		$attr_name = str_replace( 'attribute_', '', mb_strtolower( $title ) );
		$attr_name = 'attribute_' . $attr_name;

		$encoded_attr_name = rawurlencode( $attr_name );

		return $this->has_post_value( $encoded_attr_name ) || $this->has_post_value( strtolower( $encoded_attr_name ) );

		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Get sanitized POST value.
	 *
	 * @param string $key    Key.
	 * @param int    $filter Filter.
	 *
	 * @return string
	 */
	protected function post_value( string $key, int $filter ): string {
		return (string) filter_input( INPUT_POST, $key, $filter );
	}

	/**
	 * Get POST array value.
	 *
	 * @param string $key Key.
	 *
	 * @return array
	 */
	protected function post_array_value( string $key ): array {
		// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return (array) wp_unslash( $_POST[ $key ] ?? [] );
		// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	/**
	 * Sanitize text field.
	 *
	 * @param mixed $value Value.
	 *
	 * @return string
	 */
	protected function sanitize_text_field( $value ): string {
		return sanitize_text_field( $value );
	}

	/**
	 * Check whether an action is currently running.
	 *
	 * @param string $action Action.
	 *
	 * @return bool
	 */
	protected function doing_action( string $action ): bool {
		return doing_action( $action );
	}

	/**
	 * Check whether an action was fired.
	 *
	 * @param string $action Action.
	 *
	 * @return int
	 */
	protected function did_action( string $action ): int {
		return did_action( $action );
	}

	/**
	 * Get current product attributes.
	 *
	 * @return array
	 */
	protected function product_attributes(): array {
		if ( empty( $GLOBALS['product'] ) || ! is_object( $GLOBALS['product'] ) || ! method_exists( $GLOBALS['product'], 'get_attributes' ) ) {
			return [];
		}

		return (array) $GLOBALS['product']->get_attributes();
	}

	/**
	 * Check POST value existence.
	 *
	 * @param string $key Key.
	 *
	 * @return bool
	 */
	protected function has_post_value( string $key ): bool {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		return isset( $_POST[ $key ] );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}
}
