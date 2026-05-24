<?php
/**
 * VariationAttributeService class file.
 *
 * @package cyr-to-lat
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpInternalEntityUsedInspection */

namespace CyrToLat\Slugs;

use CyrToLat\Main;
use CyrToLat\Symfony\Polyfill\Mbstring\Mbstring;

/**
 * Handles WooCommerce variation attribute key decisions.
 */
class VariationAttributeService {

	/**
	 * Main instance.
	 *
	 * @var Main
	 */
	private Main $main;

	/**
	 * Constructor.
	 *
	 * @param Main $main Main instance.
	 */
	public function __construct( Main $main ) {
		$this->main = $main;
	}

	/**
	 * Check if the variation attribute key belongs to a global attribute taxonomy.
	 *
	 * @param string $key Key.
	 *
	 * @return bool
	 */
	public function is_global_variation_attribute_key( string $key ): bool {
		return 0 === strpos( $key, 'attribute_pa_' ) || 0 === strpos( $key, 'pa_' );
	}

	/**
	 * Get an encoded product attribute key used by WooCommerce during variation form rendering.
	 *
	 * @param string $title Title.
	 *
	 * @return string
	 */
	public function encoded_product_attribute_key( string $title ): string {
		return strtolower( rawurlencode( Mbstring::mb_strtolower( $title ) ) );
	}

	/**
	 * Get a local variation request key.
	 *
	 * @param string $title Title.
	 *
	 * @return string
	 */
	public function local_variation_request_key( string $title ): string {
		$attr_name = str_replace( 'attribute_', '', Mbstring::mb_strtolower( $title ) );

		return 'attribute_' . $attr_name;
	}

	/**
	 * Get encoded local variation request keys.
	 *
	 * @param string $title Title.
	 *
	 * @return array
	 */
	public function encoded_local_variation_request_keys( string $title ): array {
		$encoded_attr_name = rawurlencode( $this->local_variation_request_key( $title ) );

		return array_values( array_unique( [ $encoded_attr_name, strtolower( $encoded_attr_name ) ] ) );
	}

	/**
	 * Get the normalized local variation request key.
	 *
	 * @param string $title Title.
	 *
	 * @return string
	 */
	public function normalized_local_variation_request_key( string $title ): string {
		return 'attribute_' . $this->normalize_variation_attribute_key( $title );
	}

	/**
	 * Normalize local variation attribute keys on a WooCommerce variation object.
	 *
	 * @param object $variation Variation.
	 *
	 * @return bool
	 */
	public function normalize_variation_attributes( object $variation ): bool {
		return $this->normalize_variation_attributes_prop( $variation, true );
	}

	/**
	 * Normalize local variation attribute keys after reading persisted data.
	 *
	 * @param object $variation Variation.
	 *
	 * @return bool
	 */
	public function normalize_read_variation_attributes( object $variation ): bool {
		return $this->normalize_variation_attributes_prop( $variation, false );
	}

	/**
	 * Normalize local variation attribute keys in an attribute array.
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return array
	 */
	public function normalize_variation_attribute_array( array $attributes ): array {
		if ( [] === $attributes ) {
			return $attributes;
		}

		$normalized_attributes = [];

		foreach ( $attributes as $attribute_key => $attribute_value ) {
			$normalized_attributes[ $this->normalize_variation_attribute_key( (string) $attribute_key ) ] = $attribute_value;
		}

		return $normalized_attributes;
	}

	/**
	 * Normalize local variation attribute keys and values after reading persisted data.
	 *
	 * @param object $variation  Variation.
	 * @param array  $attributes Attributes.
	 *
	 * @return array
	 */
	public function normalize_read_variation_attribute_array( object $variation, array $attributes ): array {
		return $this->normalize_read_variation_attribute_array_with_meta(
			$attributes,
			$this->raw_variation_attribute_meta( $variation )
		);
	}

	/**
	 * Normalize local variation attribute keys on a WooCommerce variation object.
	 *
	 * @param object $variation    Variation.
	 * @param bool   $mark_changes Whether the object should be marked as changed.
	 *
	 * @return bool
	 */
	private function normalize_variation_attributes_prop( object $variation, bool $mark_changes ): bool {
		if ( ! is_object( $variation ) || ! method_exists( $variation, 'get_attributes' ) ) {
			return false;
		}

		if ( method_exists( $variation, 'get_type' ) && 'variation' !== $variation->get_type() ) {
			return false;
		}

		$attributes = $variation->get_attributes( 'edit' );

		if ( ! is_array( $attributes ) || [] === $attributes ) {
			return false;
		}

		$raw_attribute_meta    = $mark_changes ? [] : $this->raw_variation_attribute_meta( $variation );
		$normalized_attributes = $mark_changes
			? $this->normalize_variation_attribute_array( $attributes )
			: $this->normalize_read_variation_attribute_array_with_meta( $attributes, $raw_attribute_meta );
		$changed               = false;

		foreach ( $attributes as $attribute_key => $attribute_value ) {
			$normalized_key = $this->normalize_variation_attribute_key( (string) $attribute_key );
			$changed        = $changed || ! array_key_exists( $attribute_key, $normalized_attributes ) || $normalized_attributes[ $normalized_key ] !== $attribute_value;
		}

		if ( ! $changed ) {
			return false;
		}

		return $this->set_variation_attributes_prop( $variation, $normalized_attributes, $mark_changes );
	}

	/**
	 * Normalize local variation attribute keys and values using raw meta.
	 *
	 * @param array $attributes         Attributes.
	 * @param array $raw_attribute_meta Raw attribute meta.
	 *
	 * @return array
	 */
	private function normalize_read_variation_attribute_array_with_meta( array $attributes, array $raw_attribute_meta ): array {
		$normalized_attributes = [];

		foreach ( $attributes as $attribute_key => $attribute_value ) {
			$normalized_key   = $this->normalize_variation_attribute_key( (string) $attribute_key );
			$normalized_value = '' === $attribute_value
				? $this->matching_raw_variation_attribute_value(
					$this->normalized_local_variation_request_key( $normalized_key ),
					$raw_attribute_meta
				)
				: $attribute_value;

			if (
				isset( $normalized_attributes[ $normalized_key ] ) &&
				'' !== $normalized_attributes[ $normalized_key ] &&
				'' === $normalized_value
			) {
				continue;
			}

			$normalized_attributes[ $normalized_key ] = $normalized_value;
		}

		return $normalized_attributes;
	}

	/**
	 * Normalize frontend available variation attribute keys.
	 *
	 * @param array|mixed $variation_data Available variation data.
	 * @param object      $variation      Variation product object.
	 *
	 * @return array|mixed
	 */
	public function normalize_available_variation_attributes( $variation_data, object $variation ) {
		if ( ! is_array( $variation_data ) || empty( $variation_data['attributes'] ) || ! is_array( $variation_data['attributes'] ) ) {
			return $variation_data;
		}

		$normalized_attributes = [];
		$raw_attribute_meta    = $this->raw_variation_attribute_meta( $variation );

		foreach ( $variation_data['attributes'] as $attribute_key => $attribute_value ) {
			$normalized_key   = $this->normalized_local_variation_request_key( (string) $attribute_key );
			$normalized_value = '' === $attribute_value
				? $this->matching_raw_variation_attribute_value( $normalized_key, $raw_attribute_meta )
				: $attribute_value;

			if (
				isset( $normalized_attributes[ $normalized_key ] ) &&
				'' !== $normalized_attributes[ $normalized_key ] &&
				'' === $normalized_value
			) {
				continue;
			}

			$normalized_attributes[ $normalized_key ] = $normalized_value;
		}

		$variation_data['attributes'] = $normalized_attributes;

		return $variation_data;
	}

	/**
	 * Normalize a variation attribute key.
	 *
	 * @param string $attribute_key Attribute key.
	 *
	 * @return string
	 */
	public function normalize_variation_attribute_key( string $attribute_key ): string {
		if ( $this->is_global_variation_attribute_key( $attribute_key ) ) {
			return 0 === strpos( $attribute_key, 'attribute_' ) ? substr( $attribute_key, 10 ) : $attribute_key;
		}

		$attribute_key = str_replace( 'attribute_', '', $attribute_key );
		$attribute_key = rawurldecode( $attribute_key );

		if ( '' === $attribute_key ) {
			return $attribute_key;
		}

		return $this->main->sanitize_explicit_slug( $attribute_key );
	}

	/**
	 * Get raw variation attribute metadata.
	 *
	 * @param object $variation Variation product object.
	 *
	 * @return array<string, string>
	 */
	private function raw_variation_attribute_meta( object $variation ): array {
		if ( ! method_exists( $variation, 'get_id' ) ) {
			return [];
		}

		$variation_id = (int) $variation->get_id();

		if ( $variation_id <= 0 ) {
			return [];
		}

		$meta   = get_post_meta( $variation_id );
		$result = [];

		foreach ( $meta as $meta_key => $values ) {
			if ( ! is_string( $meta_key ) || 0 !== strpos( $meta_key, 'attribute_' ) ) {
				continue;
			}

			$result[ $meta_key ] = (string) ( $values[0] ?? '' );
		}

		return $result;
	}

	/**
	 * Find a non-empty raw variation attribute value matching a normalized request key.
	 *
	 * @param string               $normalized_key Normalized request key.
	 * @param array<string,string> $raw_meta       Raw variation attribute metadata.
	 *
	 * @return string
	 */
	private function matching_raw_variation_attribute_value( string $normalized_key, array $raw_meta ): string {
		foreach ( $raw_meta as $raw_key => $raw_value ) {
			if ( '' === $raw_value ) {
				continue;
			}

			if ( $this->normalized_local_variation_request_key( $raw_key ) === $normalized_key ) {
				return $raw_value;
			}
		}

		return '';
	}

	/**
	 * Check whether a product has a saved local variation attribute with the given display name.
	 *
	 * @param string $attribute_name Attribute display name.
	 * @param int    $product_id     Product ID.
	 *
	 * @return bool
	 */
	public function is_saved_local_variation_attribute_name( string $attribute_name, int $product_id ): bool {
		if ( $product_id <= 0 ) {
			return false;
		}

		$attributes = get_post_meta( $product_id, '_product_attributes', true );

		if ( ! is_array( $attributes ) ) {
			return false;
		}

		foreach ( $attributes as $attribute ) {
			if ( ! $this->is_local_variation_attribute_meta( $attribute ) ) {
				continue;
			}

			if ( rawurldecode( (string) ( $attribute['name'] ?? '' ) ) === $attribute_name ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether product attribute metadata describes a local variation attribute.
	 *
	 * @param mixed $attribute Attribute metadata.
	 *
	 * @return bool
	 */
	private function is_local_variation_attribute_meta( $attribute ): bool {
		return is_array( $attribute ) && empty( $attribute['is_taxonomy'] ) && ! empty( $attribute['is_variation'] );
	}

	/**
	 * Set normalized variation attributes without calling WooCommerce's set_attributes().
	 *
	 * @param object $variation    Variation.
	 * @param array  $attributes   Attributes.
	 * @param bool   $mark_changes Whether the object should be marked as changed.
	 *
	 * @return bool
	 */
	private function set_variation_attributes_prop( object $variation, array $attributes, bool $mark_changes = true ): bool {
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

		$setter = $setter->bindTo( $variation, get_class( $variation ) );

		if ( ! is_callable( $setter ) ) {
			return false;
		}

		$setter( $attributes, $mark_changes );

		return true;
	}
}
