<?php
/**
 * FilenameServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Slugs\FilenameService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use CyrToLat\Transliteration\SlugContext;
use CyrToLat\Transliteration\Transliterator;
use Mockery;

/**
 * Class FilenameServiceTest
 *
 * @group slugs
 */
class FilenameServiceTest extends CyrToLatTestCase {

	/**
	 * Test transliterate_filename().
	 *
	 * @return void
	 */
	public function test_transliterate_filename(): void {
		$transliterator = Mockery::mock( Transliterator::class );
		$transliterator
			->shouldReceive( 'transliterate' )
			->once()
			->with(
				'й.jpg',
				Mockery::on(
					static function ( SlugContext $context ): bool {
						return SlugContext::TYPE_FILENAME === $context->type();
					}
				)
			)
			->andReturn( 'j.jpg' );

		$subject = new FilenameService( $transliterator );

		self::assertSame( 'j.jpg', $subject->transliterate_filename( 'й.jpg' ) );
	}
}
