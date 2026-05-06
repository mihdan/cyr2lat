<?php
/**
 * TestLocalAttributeService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Slugs\LocalAttributeService;

/**
 * Test local attribute service.
 */
class TestLocalAttributeService extends LocalAttributeService {

	/**
	 * POST data.
	 *
	 * @var array
	 */
	private array $post_data;

	/**
	 * Current actions.
	 *
	 * @var array
	 */
	private array $current_actions;

	/**
	 * Fired actions.
	 *
	 * @var array
	 */
	private array $fired_actions;

	/**
	 * Product attributes.
	 *
	 * @var array
	 */
	private array $product_attributes;

	/**
	 * Constructor.
	 *
	 * @param array $post_data          POST data.
	 * @param array $current_actions    Current actions.
	 * @param array $fired_actions      Fired actions.
	 * @param array $product_attributes Product attributes.
	 */
	public function __construct(
		array $post_data = [],
		array $current_actions = [],
		array $fired_actions = [],
		array $product_attributes = []
	) {
		$this->post_data          = $post_data;
		$this->current_actions    = $current_actions;
		$this->fired_actions      = $fired_actions;
		$this->product_attributes = $product_attributes;
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
		return (string) ( $this->post_data[ $key ] ?? '' );
	}

	/**
	 * Get POST array value.
	 *
	 * @param string $key Key.
	 *
	 * @return array
	 */
	protected function post_array_value( string $key ): array {
		return (array) ( $this->post_data[ $key ] ?? [] );
	}

	/**
	 * Sanitize text field.
	 *
	 * @param mixed $value Value.
	 *
	 * @return string
	 */
	protected function sanitize_text_field( $value ): string {
		return (string) $value;
	}

	/**
	 * Check whether an action is currently running.
	 *
	 * @param string $action Action.
	 *
	 * @return bool
	 */
	protected function doing_action( string $action ): bool {
		return (bool) ( $this->current_actions[ $action ] ?? false );
	}

	/**
	 * Check whether an action was fired.
	 *
	 * @param string $action Action.
	 *
	 * @return int
	 */
	protected function did_action( string $action ): int {
		return (int) ( $this->fired_actions[ $action ] ?? 0 );
	}

	/**
	 * Get current product attributes.
	 *
	 * @return array
	 */
	protected function product_attributes(): array {
		return $this->product_attributes;
	}

	/**
	 * Check POST value existence.
	 *
	 * @param string $key Key.
	 *
	 * @return bool
	 */
	protected function has_post_value( string $key ): bool {
		return isset( $this->post_data[ $key ] );
	}
}
