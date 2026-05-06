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
	 * Variation attribute service.
	 *
	 * @var VariationAttributeService
	 */
	private VariationAttributeService $variation_attribute_service;

	/**
	 * Constructor.
	 *
	 * @param VariationAttributeService|null $variation_attribute_service Variation attribute service.
	 */
	public function __construct( ?VariationAttributeService $variation_attribute_service = null ) {
		$this->variation_attribute_service = $variation_attribute_service ?? new VariationAttributeService();
	}

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
		if ( $this->variation_attribute_service->is_global_variation_attribute_key( $title ) ) {
			return false;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing

		$action = $this->post_value( 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( 'woocommerce_do_ajax_product_import' === $action ) {
			return false;
		}

		// The `save attributes` action.
		if ( 'woocommerce_save_attributes' === $action ) {
			return $this->is_ajax_save_attribute( $title, $parse_str );
		}

		// The `edit post` action.
		if ( 'editpost' === $action ) {
			return $this->is_edit_post_attribute( $title );
		}

		if ( $this->doing_action( 'woocommerce_variable_add_to_cart' ) ) {
			return $this->is_variable_add_to_cart_attribute( $title );
		}

		if ( $this->did_action( 'woocommerce_load_cart_from_session' ) ) {
			return true;
		}

		return $this->has_variation_request_attribute( $title );

		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Check AJAX save attribute request.
	 *
	 * @param string   $title     Title.
	 * @param callable $parse_str Request parser callback.
	 *
	 * @return bool
	 */
	private function is_ajax_save_attribute( string $title, callable $parse_str ): bool {
		$data            = $this->post_value( 'data', FILTER_SANITIZE_URL );
		$attributes      = (array) call_user_func( $parse_str, urldecode( $data ) );
		$attribute_names = $attributes['attribute_names'] ?? [];

		return in_array( $title, $attribute_names, true );
	}

	/**
	 * Check edit post attribute request.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 */
	private function is_edit_post_attribute( string $title ): bool {
		$attribute_names = array_map(
			[ $this, 'sanitize_text_field' ],
			$this->post_array_value( 'attribute_names' )
		);

		return in_array( $title, $attribute_names, true );
	}

	/**
	 * Check variable add-to-cart attribute rendering request.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 */
	private function is_variable_add_to_cart_attribute( string $title ): bool {
		$attributes = $this->product_attributes();

		$encoded_attr_name = $this->variation_attribute_service->encoded_product_attribute_key( $title );

		return isset( $attributes[ $encoded_attr_name ] );
	}

	/**
	 * Check frontend variation request attribute keys.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 */
	private function has_variation_request_attribute( string $title ): bool {
		foreach ( $this->variation_attribute_service->encoded_local_variation_request_keys( $title ) as $encoded_attr_name ) {
			if ( $this->has_post_value( $encoded_attr_name ) ) {
				return true;
			}
		}

		return false;
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
