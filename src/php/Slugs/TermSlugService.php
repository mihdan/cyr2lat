<?php
/**
 * TermSlugService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

/**
 * Handles term slug context.
 */
class TermSlugService {

	/**
	 * Term context flag.
	 *
	 * @var bool
	 */
	private bool $is_term = false;

	/**
	 * Current taxonomies.
	 *
	 * @var string[]
	 */
	private array $taxonomies = [];

	/**
	 * Whether a term context is active.
	 *
	 * @return bool
	 */
	public function is_term_context(): bool {
		return $this->is_term;
	}

	/**
	 * Get captured taxonomies.
	 *
	 * @return string[]
	 */
	public function taxonomies(): array {
		return $this->taxonomies;
	}
}
