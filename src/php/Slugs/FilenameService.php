<?php
/**
 * FilenameService class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Slugs;

use CyrToLat\Transliteration\SlugContext;
use CyrToLat\Transliteration\Transliterator;

/**
 * Handles filename transliteration.
 */
class FilenameService {

	/**
	 * Transliterator.
	 *
	 * @var Transliterator
	 */
	private Transliterator $transliterator;

	/**
	 * Constructor.
	 *
	 * @param Transliterator $transliterator Transliterator.
	 */
	public function __construct( Transliterator $transliterator ) {
		$this->transliterator = $transliterator;
	}

	/**
	 * Transliterate a filename.
	 *
	 * @param string $filename Filename.
	 *
	 * @return string
	 */
	public function transliterate_filename( string $filename ): string {
		return $this->transliterator->transliterate(
			$filename,
			new SlugContext( SlugContext::TYPE_FILENAME )
		);
	}
}
