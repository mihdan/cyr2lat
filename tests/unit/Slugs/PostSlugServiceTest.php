<?php
/**
 * PostSlugServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Main;
use CyrToLat\Slugs\PostSlugService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use Mockery;

/**
 * Class PostSlugServiceTest
 *
 * @group slugs
 */
class PostSlugServiceTest extends CyrToLatTestCase {

	/**
	 * Test filter_post_data() generates post_name from the title.
	 *
	 * @return void
	 */
	public function test_filter_post_data_generates_empty_post_name_from_title(): void {
		$main = $this->get_main_mock();
		$main->shouldReceive( 'sanitize_explicit_slug' )
			->andReturnUsing(
				static function ( string $slug ): string {
					return 'й' === $slug ? 'j' : $slug;
				}
			);
		$subject = new PostSlugService( $main );
		$data    = [
			'post_name'   => '',
			'post_title'  => 'й',
			'post_status' => 'publish',
		];

		$filtered = $subject->filter_post_data( $data );

		self::assertSame( 'j', $filtered['post_name'] );
	}

	/**
	 * Test filter_post_data() keeps empty post_name without title.
	 *
	 * @return void
	 */
	public function test_filter_post_data_keeps_empty_post_name_without_title(): void {
		$subject = new PostSlugService( $this->get_main_mock() );
		$data    = [
			'post_name'   => '',
			'post_title'  => '',
			'post_status' => 'publish',
		];

		self::assertSame( $data, $subject->filter_post_data( $data ) );
	}

	/**
	 * Test filter_post_data() normalizes explicit Cyrillic post_name.
	 *
	 * @return void
	 */
	public function test_filter_post_data_normalizes_explicit_cyrillic_post_name(): void {
		$main = $this->get_main_mock();
		$main->shouldReceive( 'sanitize_explicit_slug' )
			->andReturnUsing(
				static function ( string $slug ): string {
					return 'й' === $slug ? 'j' : $slug;
				}
			);
		$subject = new PostSlugService( $main );
		$data    = [
			'post_name'   => 'й',
			'post_title'  => 'Title',
			'post_status' => 'publish',
		];

		$filtered = $subject->filter_post_data( $data );

		self::assertSame( 'j', $filtered['post_name'] );
	}

	/**
	 * Test filter_post_data() normalizes encoded Cyrillic post_name.
	 *
	 * @return void
	 */
	public function test_filter_post_data_normalizes_encoded_cyrillic_post_name(): void {
		$main = $this->get_main_mock();
		$main->shouldReceive( 'sanitize_explicit_slug' )
			->andReturnUsing(
				static function ( string $slug ): string {
					return 'й' === $slug ? 'j' : $slug;
				}
			);
		$subject = new PostSlugService( $main );
		$data    = [
			'post_name'   => '%d0%b9',
			'post_title'  => 'Title',
			'post_status' => 'publish',
		];

		$filtered = $subject->filter_post_data( $data );

		self::assertSame( 'j', $filtered['post_name'] );
	}

	/**
	 * Test filter_post_data() preserves encoded ASCII post_name.
	 *
	 * @return void
	 */
	public function test_filter_post_data_preserves_encoded_ascii_post_name(): void {
		$main = $this->get_main_mock();
		$main->shouldReceive( 'sanitize_explicit_slug' )
			->andReturnUsing(
				static function ( string $slug ): string {
					return $slug . '-changed';
				}
			);
		$subject = new PostSlugService( $main );
		$data    = [
			'post_name'   => 'hello%20world',
			'post_title'  => 'Title',
			'post_status' => 'publish',
		];

		self::assertSame( $data, $subject->filter_post_data( $data ) );
	}

	/**
	 * Test filter_post_data() preserves manual Latin post_name.
	 *
	 * @return void
	 */
	public function test_filter_post_data_preserves_manual_latin_post_name(): void {
		$subject = new PostSlugService( $this->get_main_mock() );
		$data    = [
			'post_name'   => 'manual-slug',
			'post_title'  => 'й',
			'post_status' => 'publish',
		];

		self::assertSame( $data, $subject->filter_post_data( $data ) );
	}

	/**
	 * Test filter_post_data() skips transient post saves.
	 *
	 * @param array $data Post data.
	 *
	 * @return void
	 * @dataProvider dp_test_filter_post_data_skips_transient_post_saves
	 */
	public function test_filter_post_data_skips_transient_post_saves( array $data ): void {
		$subject = new PostSlugService( $this->get_main_mock() );

		self::assertSame( $data, $subject->filter_post_data( $data ) );
	}

	/**
	 * Data provider for test_filter_post_data_skips_transient_post_saves().
	 *
	 * @return array
	 */
	public static function dp_test_filter_post_data_skips_transient_post_saves(): array {
		return [
			'auto-draft'      => [
				[
					'post_name'   => '',
					'post_title'  => 'й',
					'post_status' => 'auto-draft',
				],
			],
			'revision status' => [
				[
					'post_name'   => '',
					'post_title'  => 'й',
					'post_status' => 'revision',
				],
			],
			'revision type'   => [
				[
					'post_name'   => '',
					'post_title'  => 'й',
					'post_status' => 'inherit',
					'post_type'   => 'revision',
				],
			],
		];
	}

	/**
	 * Get a Main mock instance.
	 *
	 * @return Main
	 */
	private function get_main_mock(): Main {
		return Mockery::mock( Main::class )->makePartial();
	}
}
