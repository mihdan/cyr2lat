<?php
/**
 * LocalAttributeService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

use CyrToLat\Main;

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
	 * Main instance.
	 *
	 * @var Main
	 */
	private Main $main;

	/**
	 * Constructor.
	 *
	 * @param Main                           $main                        Main instance.
	 * @param VariationAttributeService|null $variation_attribute_service Variation attribute service.
	 */
	public function __construct( Main $main, ?VariationAttributeService $variation_attribute_service = null ) {
		$this->main                        = $main;
		$this->variation_attribute_service = $variation_attribute_service ?? new VariationAttributeService( $main );
	}

	/**
	 * Check if the title is a local attribute.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 */
	public function is_local_attribute( string $title ): bool {
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
			return $this->is_ajax_save_attribute( $title );
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
	 * Normalize local product attribute keys on a WooCommerce product object.
	 *
	 * @param object $product Product.
	 *
	 * @return bool
	 */
	public function normalize_product_attributes( object $product ): bool {
		return $this->normalize_product_attributes_prop( $product, true );
	}

	/**
	 * Normalize local product attribute keys on a WooCommerce product object after reading persisted data.
	 *
	 * @param object $product Product.
	 *
	 * @return bool
	 */
	public function normalize_read_product_attributes( object $product ): bool {
		return $this->normalize_product_attributes_prop( $product, false );
	}

	/**
	 * Normalize local product attribute keys in an attribute array.
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return array
	 */
	public function normalize_product_attribute_array( array $attributes ): array {
		if ( [] === $attributes ) {
			return $attributes;
		}

		$normalized_attributes = [];

		foreach ( $attributes as $attribute_key => $attribute ) {
			$normalized_attributes[ $this->normalize_product_attribute_key( (string) $attribute_key, $attribute ) ] = $attribute;
		}

		return $normalized_attributes;
	}

	/**
	 * Normalize local product attribute keys on a WooCommerce product object.
	 *
	 * @param object $product      Product.
	 * @param bool   $mark_changes Whether the object should be marked as changed.
	 *
	 * @return bool
	 */
	private function normalize_product_attributes_prop( object $product, bool $mark_changes ): bool {
		if ( ! is_object( $product ) || ! method_exists( $product, 'get_attributes' ) ) {
			return false;
		}

		$attributes = $product->get_attributes( 'edit' );

		if ( ! is_array( $attributes ) || [] === $attributes ) {
			return false;
		}

		$normalized_attributes = $this->normalize_product_attribute_array( $attributes );
		$changed               = false;

		foreach ( array_keys( $attributes ) as $attribute_key ) {
			$changed = $changed || ! array_key_exists( $attribute_key, $normalized_attributes );
		}

		if ( ! $changed ) {
			return false;
		}

		return $this->set_product_attributes_prop( $product, $normalized_attributes, $mark_changes );
	}

	/**
	 * Normalize persisted product attribute metadata.
	 *
	 * @param object $product Product.
	 *
	 * @return bool
	 */
	public function normalize_product_attribute_meta( object $product ): bool {
		if ( ! method_exists( $product, 'get_id' ) ) {
			return false;
		}

		$product_id = (int) $product->get_id();

		if ( $product_id <= 0 ) {
			return false;
		}

		$attributes = get_post_meta( $product_id, '_product_attributes', true );

		if ( ! is_array( $attributes ) || [] === $attributes ) {
			return false;
		}

		$normalized_attributes = [];
		$changed               = false;

		foreach ( $attributes as $attribute_key => $attribute ) {
			$normalized_key = $this->normalize_product_attribute_meta_key( (string) $attribute_key, $attribute );

			$normalized_attributes[ $normalized_key ] = $attribute;
			$changed                                  = $changed || $normalized_key !== $attribute_key;
		}

		if ( ! $changed ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		return update_post_meta( $product_id, '_product_attributes', wp_slash( $normalized_attributes ) );
	}

	/**
	 * Normalize a product attribute key.
	 *
	 * @param string $attribute_key Attribute key.
	 * @param mixed  $attribute     Attribute.
	 *
	 * @return string
	 */
	public function normalize_product_attribute_key( string $attribute_key, $attribute ): string {
		if ( ! is_object( $attribute ) || ! method_exists( $attribute, 'is_taxonomy' ) || ! method_exists( $attribute, 'get_name' ) ) {
			return $attribute_key;
		}

		if ( $attribute->is_taxonomy() ) {
			return $attribute_key;
		}

		$name = rawurldecode( (string) $attribute->get_name() );

		if ( '' === $name ) {
			return $attribute_key;
		}

		return strtolower( $this->main->transliterate( $name ) );
	}

	/**
	 * Normalize a persisted product attribute meta key.
	 *
	 * @param string $attribute_key Attribute key.
	 * @param mixed  $attribute     Attribute metadata.
	 *
	 * @return string
	 */
	private function normalize_product_attribute_meta_key( string $attribute_key, $attribute ): string {
		if ( ! is_array( $attribute ) || ! empty( $attribute['is_taxonomy'] ) ) {
			return $attribute_key;
		}

		$name = rawurldecode( (string) ( $attribute['name'] ?? '' ) );

		if ( '' === $name ) {
			return $attribute_key;
		}

		return strtolower( $this->main->transliterate( $name ) );
	}

	/**
	 * Check AJAX save attribute request.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 */
	private function is_ajax_save_attribute( string $title ): bool {
		$data            = $this->post_value( 'data', FILTER_SANITIZE_URL );
		$attributes      = $this->wp_parse_str( urldecode( $data ) );
		$attribute_names = $attributes['attribute_names'] ?? [];

		return in_array( $title, $attribute_names, true );
	}

	// @codeCoverageIgnoreStart

	/**
	 * Polyfill of the wp_parse_str().
	 * Added for test reasons.
	 *
	 * @param string $input_string Input string.
	 *
	 * @return array
	 */
	protected function wp_parse_str( string $input_string ): array {
		wp_parse_str( $input_string, $result );

		return $result;
	}

	// @codeCoverageIgnoreEnd

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
	 * Check the variable add-to-cart attribute rendering request.
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
		$request_keys = array_merge(
			$this->variation_attribute_service->encoded_local_variation_request_keys( $title ),
			[ $this->variation_attribute_service->normalized_local_variation_request_key( $title ) ]
		);

		foreach ( array_unique( $request_keys ) as $request_key ) {
			if ( $this->has_post_value( $request_key ) || $this->has_request_value( $request_key ) ) {
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
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		return isset( $_POST[ $key ] );
	}

	/**
	 * Check request value existence.
	 *
	 * @param string $key Key.
	 *
	 * @return bool
	 */
	protected function has_request_value( string $key ): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_REQUEST[ $key ] );
	}

	/**
	 * Set normalized product attributes without calling WooCommerce's set_attributes().
	 *
	 * @param object $product    Product.
	 * @param array  $attributes Attributes.
	 * @param bool   $mark_changes Whether the object should be marked as changed.
	 *
	 * @return bool
	 */
	private function set_product_attributes_prop( object $product, array $attributes, bool $mark_changes = true ): bool {
		$setter = function ( array $attributes_to_set, bool $should_mark_changes ): void {
			if ( $should_mark_changes ) {
				$this->set_prop( 'attributes', $attributes_to_set );

				return;
			}

			$this->data['attributes'] = $attributes_to_set;

			if ( isset( $this->changes['attributes'] ) ) {
				unset( $this->changes['attributes'] );
			}
		};

		$setter = $setter->bindTo( $product, get_class( $product ) );

		if ( ! is_callable( $setter ) ) {
			return false;
		}

		$setter( $attributes, $mark_changes );

		return true;
	}

	/**
	 * Whether the value contains non-ASCII characters.
	 *
	 * @param string $value Value.
	 *
	 * @return bool
	 */
	private function has_non_ascii_chars( string $value ): bool {
		return (bool) preg_match( '/[^\x00-\x7F]/', $value );
	}
}
