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

		if ( 'woocommerce_add_attributes_and_variations' === $action ) {
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
	 * Sanitize a title that belongs to a WooCommerce local attribute flow.
	 *
	 * @param string $title     Sanitized title.
	 * @param string $raw_title Raw title prior to sanitization.
	 * @param string $context   Sanitization context.
	 *
	 * @return string|null Final title when handled, `null` otherwise.
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function sanitize_title( string $title, string $raw_title = '', string $context = '' ): ?string {
		if ( '' === $title || 'query' === $context ) {
			return null;
		}

		if ( ! function_exists( 'WC' ) ) {
			return null;
		}

		$decoded = rawurldecode( $title );

		if ( '' === $decoded || ! $this->has_non_ascii_chars( $decoded ) ) {
			return null;
		}

		$saved_admin_variation_key = $this->saved_local_variation_attribute_key_for_admin_variation( $decoded );

		if ( null !== $saved_admin_variation_key ) {
			return $saved_admin_variation_key;
		}

		if ( ! $this->is_local_attribute( $decoded ) && ! $this->is_saved_variation_product_attribute_name( $decoded ) ) {
			return null;
		}

		return $this->main->sanitize_explicit_slug( $decoded );
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

		$normalized_attributes = $mark_changes
			? $this->normalize_product_attribute_array( $attributes )
			: $this->normalize_read_product_attribute_array( $product, $attributes );
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

		$legacy_key = $this->normalize_legacy_product_attribute_key( $attribute_key );

		if ( null !== $legacy_key ) {
			return $legacy_key;
		}

		$name = rawurldecode( (string) $attribute->get_name() );

		if ( '' === $name ) {
			return $attribute_key;
		}

		return $this->main->sanitize_explicit_slug( $name );
	}

	/**
	 * Normalize product attribute keys after reading persisted product data.
	 *
	 * @param object $product    Product.
	 * @param array  $attributes Attributes.
	 *
	 * @return array
	 */
	public function normalize_read_product_attribute_array( object $product, array $attributes ): array {
		if ( [] === $attributes ) {
			return $attributes;
		}

		$legacy_keys           = $this->legacy_product_attribute_keys_by_name( $product );
		$normalized_attributes = [];

		foreach ( $attributes as $attribute_key => $attribute ) {
			$normalized_attributes[ $this->normalize_read_product_attribute_key( (string) $attribute_key, $attribute, $legacy_keys ) ] = $attribute;
		}

		return $normalized_attributes;
	}

	/**
	 * Normalize a read product attribute key.
	 *
	 * @param string $attribute_key Attribute key.
	 * @param mixed  $attribute     Attribute.
	 * @param array  $legacy_keys   Legacy keys indexed by attribute display names.
	 *
	 * @return string
	 */
	private function normalize_read_product_attribute_key( string $attribute_key, $attribute, array $legacy_keys ): string {
		if ( ! is_object( $attribute ) || ! method_exists( $attribute, 'is_taxonomy' ) || ! method_exists( $attribute, 'get_name' ) ) {
			return $attribute_key;
		}

		if ( $attribute->is_taxonomy() ) {
			return $attribute_key;
		}

		$name = rawurldecode( (string) $attribute->get_name() );

		if ( isset( $legacy_keys[ $name ] ) ) {
			return $legacy_keys[ $name ];
		}

		return $this->normalize_product_attribute_key( $attribute_key, $attribute );
	}

	/**
	 * Get normalized legacy product attribute keys indexed by display names.
	 *
	 * @param object $product Product.
	 *
	 * @return array
	 */
	private function legacy_product_attribute_keys_by_name( object $product ): array {
		if ( ! method_exists( $product, 'get_id' ) ) {
			return [];
		}

		$product_id = (int) $product->get_id();

		if ( $product_id <= 0 ) {
			return [];
		}

		$attributes = get_post_meta( $product_id, '_product_attributes', true );

		if ( ! is_array( $attributes ) || [] === $attributes ) {
			return [];
		}

		$legacy_keys = [];

		foreach ( $attributes as $attribute_key => $attribute ) {
			if ( ! is_array( $attribute ) || ! empty( $attribute['is_taxonomy'] ) ) {
				continue;
			}

			$legacy_key = $this->normalize_legacy_product_attribute_key( (string) $attribute_key );

			if ( null === $legacy_key ) {
				continue;
			}

			$name = rawurldecode( (string) ( $attribute['name'] ?? '' ) );

			if ( '' === $name ) {
				continue;
			}

			$legacy_keys[ $name ] = $legacy_key;
		}

		return $legacy_keys;
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

		$legacy_key = $this->normalize_legacy_product_attribute_key( $attribute_key );

		if ( null !== $legacy_key ) {
			return $legacy_key;
		}

		$name = rawurldecode( (string) ( $attribute['name'] ?? '' ) );

		if ( '' === $name ) {
			return $attribute_key;
		}

		return $this->main->sanitize_explicit_slug( $name );
	}

	/**
	 * Normalize a legacy product attribute key.
	 *
	 * @param string $attribute_key Attribute key.
	 *
	 * @return string|null
	 */
	private function normalize_legacy_product_attribute_key( string $attribute_key ): ?string {
		$decoded_key = rawurldecode( $attribute_key );

		if ( '' === $decoded_key || ! $this->has_non_ascii_chars( $decoded_key ) ) {
			return null;
		}

		return $this->main->sanitize_explicit_slug( $decoded_key );
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

		$encoded_attr_name    = $this->variation_attribute_service->encoded_product_attribute_key( $title );
		$normalized_attr_name = $this->variation_attribute_service->normalize_variation_attribute_key( $title );

		return isset( $attributes[ $encoded_attr_name ] ) || isset( $attributes[ $normalized_attr_name ] );
	}

	/**
	 * Check frontend variation request attribute keys.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 */
	private function has_variation_request_attribute( string $title ): bool {
		foreach ( $this->variation_attribute_service->encoded_local_variation_request_keys( $title ) as $request_key ) {
			if ( $this->has_post_value( $request_key ) || $this->has_request_value( $request_key ) ) {
				return true;
			}
		}

		if ( ! $this->has_any_variation_request_value() ) {
			return false;
		}

		$request_key = $this->variation_attribute_service->normalized_local_variation_request_key( $title );

		return $this->has_post_value( $request_key ) || $this->has_request_value( $request_key );
	}

	/**
	 * Check whether the current request includes any variation attribute key.
	 *
	 * @return bool
	 */
	protected function has_any_variation_request_value(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
		foreach ( array_merge( array_keys( $_POST ?? [] ), array_keys( $_REQUEST ?? [] ) ) as $key ) {
			if ( is_string( $key ) && 0 === strpos( $key, 'attribute_' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether a value is a saved local variation attribute name for the current admin product request.
	 *
	 * @param string $title Title.
	 *
	 * @return bool
	 */
	private function is_saved_variation_product_attribute_name( string $title ): bool {
		$action = $this->post_value( 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! in_array( $action, [ 'woocommerce_add_variation', 'woocommerce_link_all_variations' ], true ) ) {
			return false;
		}

		$product_id = (int) $this->post_value( 'post_id', FILTER_SANITIZE_NUMBER_INT );

		if ( $product_id <= 0 ) {
			return false;
		}

		return $this->variation_attribute_service->is_saved_local_variation_attribute_name( $title, $product_id );
	}

	/**
	 * Return a saved local variation attribute key while WooCommerce renders admin variation rows.
	 *
	 * WooCommerce matches variation row dropdown values with
	 * sanitize_title( $attribute->get_name() ). Old products can keep URL-encoded
	 * local attribute keys in meta, so returning the persisted key keeps existing
	 * variation values selected without migrating data during render.
	 *
	 * @param string $title Attribute display title.
	 *
	 * @return string|null
	 */
	private function saved_local_variation_attribute_key_for_admin_variation( string $title ): ?string {
		$action = $this->post_value( 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( 'woocommerce_load_variations' !== $action ) {
			return null;
		}

		$product_id = (int) $this->post_value( 'product_id', FILTER_SANITIZE_NUMBER_INT );

		if ( $product_id <= 0 ) {
			return null;
		}

		$attributes = get_post_meta( $product_id, '_product_attributes', true );

		if ( ! is_array( $attributes ) ) {
			return null;
		}

		foreach ( $attributes as $attribute_key => $attribute ) {
			if ( ! is_array( $attribute ) || ! empty( $attribute['is_taxonomy'] ) || empty( $attribute['is_variation'] ) ) {
				continue;
			}

			if ( rawurldecode( (string) ( $attribute['name'] ?? '' ) ) === $title ) {
				return $this->normalize_legacy_product_attribute_key( (string) $attribute_key );
			}
		}

		return null;
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
		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! isset( $_POST[ $key ] ) || is_array( $_POST[ $key ] ) ) {
			return '';
		}

		$value = wp_unslash( $_POST[ $key ] );
		// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return (string) filter_var( $value, $filter );
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
