<?php
/**
 * SlugContext class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Transliteration;

/**
 * Describes the source and target of a transliteration request.
 */
class SlugContext {

	public const TYPE_UNKNOWN                = 'unknown';
	public const TYPE_POST                   = 'post';
	public const TYPE_TERM                   = 'term';
	public const TYPE_FILENAME               = 'filename';
	public const TYPE_WC_GLOBAL_ATTRIBUTE    = 'wc_global_attribute';
	public const TYPE_WC_LOCAL_ATTRIBUTE     = 'wc_local_attribute';
	public const TYPE_WC_VARIATION_ATTRIBUTE = 'wc_variation_attribute';

	public const SOURCE_UNKNOWN  = 'unknown';
	public const SOURCE_ADMIN    = 'admin';
	public const SOURCE_FRONTEND = 'frontend';
	public const SOURCE_AJAX     = 'ajax';
	public const SOURCE_REST     = 'rest';
	public const SOURCE_CLI      = 'cli';

	/**
	 * Context type.
	 *
	 * @var string
	 */
	private string $type;

	/**
	 * Context source.
	 *
	 * @var string
	 */
	private string $source;

	/**
	 * Object ID.
	 *
	 * @var int|null
	 */
	private ?int $object_id;

	/**
	 * Object type, such as post type or taxonomy.
	 *
	 * @var string
	 */
	private string $object_type;

	/**
	 * Locale.
	 *
	 * @var string
	 */
	private string $locale;

	/**
	 * Raw source label.
	 *
	 * @var string
	 */
	private string $source_label;

	/**
	 * Constructor.
	 *
	 * @param string   $type         Context type.
	 * @param string   $source       Context source.
	 * @param int|null $object_id    Object ID.
	 * @param string   $object_type  Object type.
	 * @param string   $locale       Locale.
	 * @param string   $source_label Raw source label.
	 */
	public function __construct(
		string $type = self::TYPE_UNKNOWN,
		string $source = self::SOURCE_UNKNOWN,
		?int $object_id = null,
		string $object_type = '',
		string $locale = '',
		string $source_label = ''
	) {
		$this->type         = $type;
		$this->source       = $source;
		$this->object_id    = $object_id;
		$this->object_type  = $object_type;
		$this->locale       = $locale;
		$this->source_label = $source_label;
	}

	/**
	 * Get context type.
	 *
	 * @return string
	 */
	public function type(): string {
		return $this->type;
	}

	/**
	 * Get context source.
	 *
	 * @return string
	 */
	public function source(): string {
		return $this->source;
	}

	/**
	 * Get object ID.
	 *
	 * @return int|null
	 */
	public function object_id(): ?int {
		return $this->object_id;
	}

	/**
	 * Get object type.
	 *
	 * @return string
	 */
	public function object_type(): string {
		return $this->object_type;
	}

	/**
	 * Get locale.
	 *
	 * @return string
	 */
	public function locale(): string {
		return $this->locale;
	}

	/**
	 * Get raw source label.
	 *
	 * @return string
	 */
	public function source_label(): string {
		return $this->source_label;
	}
}
