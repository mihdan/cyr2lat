<?php
/**
 * FilenameServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Settings\Settings;
use CyrToLat\Slugs\FilenameService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use CyrToLat\Transliteration\SlugContext;
use CyrToLat\Transliteration\Transliterator;
use Mockery;
use WP_Mock;

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

	/**
	 * Test sanitize_filename().
	 *
	 * @return void
	 */
	public function test_sanitize_filename(): void {
		$filename = 'Й.JPG';
		$table    = $this->get_conversion_table( 'ru_RU' );
		$subject  = $this->create_subject( $table );

		WP_Mock::userFunction(
			'seems_utf8',
			[
				'args'   => [ $filename ],
				'return' => true,
			]
		);

		WP_Mock::onFilter( 'ctl_pre_sanitize_filename' )->with( false, $filename )->reply( false );
		WP_Mock::expectFilter( 'ctl_table', $table );

		self::assertSame( 'j.jpg', $subject->sanitize_filename( $filename, '' ) );
	}

	/**
	 * Test sanitize_filename() returns ctl_pre_sanitize_filename filter value if set.
	 *
	 * @return void
	 */
	public function test_pre_sanitize_filename_filter_set(): void {
		$filename          = 'filename.jpg';
		$filtered_filename = 'filtered-filename.jpg';
		$transliterator    = Mockery::mock( Transliterator::class );
		$transliterator->shouldNotReceive( 'transliterate' );

		$subject = new FilenameService( $transliterator );

		WP_Mock::onFilter( 'ctl_pre_sanitize_filename' )->with( false, $filename )->reply( $filtered_filename );

		self::assertSame( $filtered_filename, $subject->sanitize_filename( $filename, '' ) );
	}

	/**
	 * Create test subject.
	 *
	 * @param array $table Conversion table.
	 *
	 * @return FilenameService
	 */
	private function create_subject( array $table ): FilenameService {
		$settings = Mockery::mock( Settings::class );
		$settings->shouldReceive( 'get_table' )->andReturn( $table );
		$settings->shouldReceive( 'is_chinese_locale' )->andReturn( false );

		return new FilenameService( new Transliterator( $settings ) );
	}
}
