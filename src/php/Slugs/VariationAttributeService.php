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
	 * Normalize local variation attribute keys on a WooCommerce variation object.
	 *
	 * @param object $variation Variation.
	 *
	 * @return bool
	 */
	public function normalize_variation_attributes( object $variation ): bool {
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

		$normalized_attributes = [];
		$changed               = false;

		foreach ( $attributes as $attribute_key => $attribute_value ) {
			$normalized_key                           = $this->normalize_variation_attribute_key( (string) $attribute_key );
			$normalized_attributes[ $normalized_key ] = $attribute_value;
			$changed                                  = $changed || $normalized_key !== $attribute_key;
		}

		if ( ! $changed ) {
			return false;
		}

		return $this->set_variation_attributes_prop( $variation, $normalized_attributes );
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

		return strtolower( $this->main->transliterate( $attribute_key ) );
	}

	/**
	 * Set normalized variation attributes without calling WooCommerce's set_attributes().
	 *
	 * @param object $variation  Variation.
	 * @param array  $attributes Attributes.
	 *
	 * @return bool
	 */
	private function set_variation_attributes_prop( object $variation, array $attributes ): bool {
		$setter = function ( array $attributes_to_set ): void {
			$this->set_prop( 'attributes', $attributes_to_set );
		};

		$setter = $setter->bindTo( $variation, get_class( $variation ) );

		if ( ! is_callable( $setter ) ) {
			return false;
		}

		$setter( $attributes );

		return true;
	}
}
