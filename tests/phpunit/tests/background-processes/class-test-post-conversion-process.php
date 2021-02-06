<?php
/**
 * Test_Post_Conversion_Process class file
 *
 * @package cyr-to-lat
 * @group   process
 */

namespace Cyr_To_Lat;

use Mockery;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use wpdb;

/**
 * Class Test_Post_Conversion_Process
 *
 * @group process
 */
class Test_Post_Conversion_Process extends Cyr_To_Lat_TestCase {

	/**
	 * End test
	 */
	public function tearDown(): void {
		unset( $GLOBALS['wpdb'] );
		parent::tearDown();
	}

	/**
	 * Test task()
	 *
	 * @param string $post_name           Post name.
	 * @param string $transliterated_name Sanitized post name.
	 *
	 * @dataProvider dp_test_task
	 */
	public function test_task( $post_name, $transliterated_name ) {
		global $wpdb;

		$post              = (object) [
			'ID'        => 5,
			'post_name' => $post_name,
			'post_type' => 'post',
		];
		$decoded_post_name = urldecode( $post->post_name );

		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'transliterate' )->with( $decoded_post_name )->andReturn( $transliterated_name );

		if ( $transliterated_name !== $post->post_name ) {
			\WP_Mock::userFunction(
				'update_post_meta',
				[
					'args'  => [ $post->ID, '_wp_old_slug', $post->post_name ],
					'times' => 1,
				]
			);
			$wpdb        = Mockery::mock( wpdb::class );
			$wpdb->posts = 'wp_posts';
			$wpdb
				->shouldReceive( 'update' )->once()
				->with( $wpdb->posts, [ 'post_name' => $transliterated_name ], [ 'ID' => $post->ID ] );
		}

		\WP_Mock::userFunction(
			'get_locale',
			[ 'return' => 'ru_RU' ]
		);

		$subject = Mockery::mock( Post_Conversion_Process::class, [ $main ] )->makePartial()->
		shouldAllowMockingProtectedMethods();

		\WP_Mock::expectFilterAdded(
			'locale',
			[ $subject, 'filter_post_locale' ]
		);

		\WP_Mock::userFunction(
			'remove_filter',
			[
				'args'  => [ 'locale', [ $subject, 'filter_post_locale' ] ],
				'times' => 1,
			]
		);

		if ( $transliterated_name !== $post->post_name ) {
			$subject
				->shouldReceive( 'log' )
				->with( 'Post slug converted: ' . $decoded_post_name . ' => ' . $transliterated_name )
				->once();
		}

		$this->assertFalse( $subject->task( $post ) );
	}

	/**
	 * Data provider for test_task()
	 */
	public function dp_test_task() {
		return [
			[ 'post_name', 'post_name' ],
			[ 'post_name', 'transliterated_name' ],
			[ '%d0%bd%d0%be%d0%b2%d1%8b%d0%b9', 'novyj' ],
		];
	}

	/**
	 * Test task() for attachment
	 *
	 * @param string $post_name           Post name.
	 * @param string $transliterated_name Sanitized post name.
	 *
	 * @dataProvider dp_test_task_for_attachment
	 */
	public function test_task_for_attachment( $post_name, $transliterated_name ) {
		global $wpdb;

		$post = (object) [
			'ID'        => 5,
			'post_name' => $post_name,
			'post_type' => 'attachment',
		];

		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'transliterate' )->with( $post_name )->andReturn( $transliterated_name );

		$subject = Mockery::mock( Post_Conversion_Process::class, [ $main ] )->makePartial()
		                  ->shouldAllowMockingProtectedMethods();

		if ( $transliterated_name !== $post->post_name ) {
			\WP_Mock::userFunction(
				'update_post_meta',
				[
					'args'  => [ $post->ID, '_wp_old_slug', $post->post_name ],
					'times' => 1,
				]
			);
			$wpdb        = Mockery::mock( wpdb::class );
			$wpdb->posts = 'wp_posts';
			$wpdb
				->shouldReceive( 'update' )->once()
				->with( $wpdb->posts, [ 'post_name' => $transliterated_name ], [ 'ID' => $post->ID ] );

			$subject->shouldReceive( 'rename_attachment' )->with( $post->ID )->once();
			$subject->shouldReceive( 'rename_thumbnails' )->with( $post->ID )->once();
			$subject->shouldReceive( 'update_attachment_metadata' )->with( $post->ID )->once();
		} else {
			$subject->shouldReceive( 'rename_attachment' )->never();
			$subject->shouldReceive( 'rename_thumbnails' )->never();
			$subject->shouldReceive( 'update_attachment_metadata' )->never();
		}

		\WP_Mock::userFunction(
			'get_locale',
			[ 'return' => 'ru_RU' ]
		);

		\WP_Mock::expectFilterAdded(
			'locale',
			[ $subject, 'filter_post_locale' ]
		);

		\WP_Mock::userFunction(
			'remove_filter',
			[
				'args'  => [ 'locale', [ $subject, 'filter_post_locale' ] ],
				'times' => 1,
			]
		);

		if ( $transliterated_name !== $post->post_name ) {
			$subject
				->shouldReceive( 'log' )
				->with( 'Post slug converted: ' . $post->post_name . ' => ' . $transliterated_name )
				->once();
		}

		$this->assertFalse( $subject->task( $post ) );
	}

	/**
	 * Data provider for test_task_for_attachment()
	 */
	public function dp_test_task_for_attachment() {
		return [
			[ 'post_name', 'post_name' ],
			[ 'post_name', 'transliterated_name' ],
		];
	}

	/**
	 * Test rename_attachment() when no attachment file exists
	 */
	public function test_rename_attachment_when_no_file() {
		$post_id = 5;

		\WP_Mock::userFunction( 'get_attached_file' )->with( $post_id )->andReturn( false );

		$subject = Mockery::mock( Post_Conversion_Process::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject
			->shouldReceive( 'log' )
			->with( 'Cannot convert attachment file for attachment id: ' . $post_id )
			->once();

		$subject->rename_attachment( $post_id );
	}

	/**
	 * Test rename_attachment()
	 *
	 * @param bool $rename  Result of rename_file().
	 * @param bool $updated Result of update_attached_file().
	 *
	 * @dataProvider dp_test_rename_attachment
	 */
	public function test_rename_attachment( $rename, $updated ) {
		$post_id             = 5;
		$file                = '/var/www/test/wp-content/uploads/2020/05/Скамейка.jpg';
		$transliterated_file = '/var/www/test/wp-content/uploads/2020/05/Skamejka.jpg';

		\WP_Mock::userFunction( 'get_attached_file' )->with( $post_id )->andReturn( $file );

		$subject = Mockery::mock( Post_Conversion_Process::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'get_transliterated_file' )->with( $file )->once()->andReturn( $transliterated_file );
		$subject->shouldReceive( 'rename_file' )->with( $file, $transliterated_file )->once()->andReturn( $rename );

		if ( $rename ) {
			\WP_Mock::userFunction( 'update_attached_file' )->with( $post_id, $transliterated_file )
			        ->andReturn( $updated );
		} else {
			\WP_Mock::userFunction( 'update_attached_file' )->never();
		}

		if ( $updated ) {
			$subject
				->shouldReceive( 'log' )
				->with( 'Attachment file converted: ' . $file . ' => ' . $transliterated_file )->once();
		}

		$subject->rename_attachment( $post_id );
	}

	/**
	 * Data provider for test_rename_attachment()
	 *
	 * @return array
	 */
	public function dp_test_rename_attachment() {
		return [
			[ false, false ],
			[ true, false ],
			[ true, true ],
		];
	}

	/**
	 * Test rename_thumbnails()
	 */
	public function test_rename_thumbnails() {
		$post_id = 5;
		$sizes   = [ 'thumbnail', 'medium', 'large' ];
		$abspath = '/var/www/test/';

		$thumbnail_src = [
			'http://test.test/wp-content/uploads/2020/05/Скамейка-150x150.jpg',
		];
		$medium_src    = [
			'http://test.test/wp-content/uploads/2020/05/Skamejka.jpg',
		];
		$large_src     = [
			'http://test.test/wp-content/uploads/2020/05/Skamejka.jpg',
		];

		$thumbnail_relative = '/wp-content/uploads/2020/05/Скамейка-150x150.jpg';
		$medium_relative    = '/wp-content/uploads/2020/05/Skamejka.jpg';
		$large_relative     = '/wp-content/uploads/2020/05/Skamejka.jpg';

		$thumbnail_file = '/var/www/test/wp-content/uploads/2020/05/Скамейка-150x150.jpg';
		$medium_file    = '/var/www/test/wp-content/uploads/2020/05/Skamejka.jpg';
		$large_file     = '/var/www/test/wp-content/uploads/2020/05/Skamejka.jpg';

		$transliterated_thumbnail_file = '/var/www/test/wp-content/uploads/2020/05/Skamejka-150x150.jpg';

		\WP_Mock::userFunction( 'get_intermediate_image_sizes' )->with()->once()->andReturn( $sizes );
		\WP_Mock::userFunction( 'wp_get_attachment_image_src' )->with( $post_id, 'thumbnail' )->once()
		        ->andReturn( $thumbnail_src );
		\WP_Mock::userFunction( 'wp_get_attachment_image_src' )->with( $post_id, 'medium' )->once()
		        ->andReturn( $medium_src );
		\WP_Mock::userFunction( 'wp_get_attachment_image_src' )->with( $post_id, 'large' )->once()
		        ->andReturn( $large_src );

		FunctionMocker::replace(
			'constant',
			function ( $name ) use ( $abspath ) {
				if ( 'ABSPATH' === $name ) {
					return $abspath;
				}

				return null;
			}
		);
		\WP_Mock::userFunction( 'untrailingslashit' )->with( $abspath )->andReturnUsing(
			function ( $string ) {
				return rtrim( $string, '/' );
			}
		);

		\WP_Mock::userFunction( 'wp_make_link_relative' )->with( $thumbnail_src[0] )->once()
		        ->andReturn( $thumbnail_relative );
		\WP_Mock::userFunction( 'wp_make_link_relative' )->with( $medium_src[0] )->once()
		        ->andReturn( $medium_relative );
		\WP_Mock::userFunction( 'wp_make_link_relative' )->with( $large_src[0] )->once()
		        ->andReturn( $large_relative );

		$subject = Mockery::mock( Post_Conversion_Process::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'get_transliterated_file' )->with( $thumbnail_file )->once()
		        ->andReturn( $transliterated_thumbnail_file );
		$subject->shouldReceive( 'get_transliterated_file' )->with( $medium_file )->once()
		        ->andReturn( $medium_file );
		$subject->shouldReceive( 'get_transliterated_file' )->with( $large_file )->once()
		        ->andReturn( $large_file );

		$subject->shouldReceive( 'rename_file' )->with( $thumbnail_file, $transliterated_thumbnail_file )->once()
		        ->andReturn( true );
		$subject->shouldReceive( 'rename_file' )->with( $medium_file, $medium_file )->once()
		        ->andReturn( false );
		$subject->shouldReceive( 'rename_file' )->with( $large_file, $large_file )->once()
		        ->andReturn( false );

		$subject->shouldReceive( 'log' )
		        ->with( 'Thumbnail file renamed: ' . $thumbnail_file . ' => ' . $transliterated_thumbnail_file )
		        ->once();
		$subject->shouldReceive( 'log' )
		        ->with( 'Cannot rename thumbnail file: ' . $medium_file )->once();
		$subject->shouldReceive( 'log' )
		        ->with( 'Cannot rename thumbnail file: ' . $large_file )->once();


		$subject->rename_thumbnails( $post_id );
	}

	/**
	 * Test update_attachment_metadata()
	 */
	public function test_update_attachment_metadata() {
		$attachment_id       = 5;
		$meta                = [
			'width'  => 260,
			'height' => 194,
			'file'   => '2020/05/Скамейка.jpg',
			'sizes'  => [
				'thumbnail' => [
					'file'      => 'Скамейка-150x150.jpg',
					'width'     => 150,
					'height'    => 150,
					'mime-type' => 'image/jpeg',
				],
			],
		];
		$transliterated_meta = [
			'width'  => 260,
			'height' => 194,
			'file'   => '2020/05/Skamejka.jpg',
			'sizes'  => [
				'thumbnail' => [
					'file'      => 'Skamejka-150x150.jpg',
					'width'     => 150,
					'height'    => 150,
					'mime-type' => 'image/jpeg',
				],
			],
		];

		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'transliterate' )->with( $meta['file'] )->andReturn( $transliterated_meta['file'] );
		$main->shouldReceive( 'transliterate' )->with( $meta['sizes']['thumbnail']['file'] )
		     ->andReturn( $transliterated_meta['sizes']['thumbnail']['file'] );

		$subject = Mockery::mock( Post_Conversion_Process::class, [ $main ] )->makePartial()
		                  ->shouldAllowMockingProtectedMethods();

		\WP_Mock::userFunction( 'wp_get_attachment_metadata' )->with( $attachment_id )->once()->andReturn( $meta );
		\WP_Mock::userFunction( 'wp_update_attachment_metadata' )->with( $attachment_id, $transliterated_meta )->once();

		$subject->update_attachment_metadata( $attachment_id );
	}

	/**
	 * Test get_transliterated_file()
	 */
	public function test_get_transliterated_file() {
		$file                = '/var/www/test/wp-content/uploads/2020/05/Скамейка.jpg';
		$transliterated_file = '/var/www/test/wp-content/uploads/2020/05/Skamejka.jpg';

		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'transliterate' )->with( 'Скамейка' )->andReturn( 'Skamejka' );

		$subject = Mockery::mock( Post_Conversion_Process::class, [ $main ] )->makePartial()
		                  ->shouldAllowMockingProtectedMethods();

		$this->assertSame( $transliterated_file, $subject->get_transliterated_file( $file ) );
	}

	/**
	 * Test rename_file()
	 */
	public function test_rename_file() {
		$file     = '/var/www/test/wp-content/uploads/2020/05/Скамейка.jpg';
		$new_file = '/var/www/test/wp-content/uploads/2020/05/Skamejka.jpg';

		$subject = Mockery::mock( Post_Conversion_Process::class )->makePartial()
		                  ->shouldAllowMockingProtectedMethods();

		FunctionMocker::replace(
			'rename',
			function( $oldname, $newname ) use ( $file, $new_file ) {
				if ( $oldname === $file && $newname === $new_file ) {
					return true;
				}

				return false;
			}
		);

		$this->assertNull( $subject->rename_file( $file, $file ) );
		$this->assertTrue( $subject->rename_file( $file, $new_file ) );
	}

	/**
	 * Test complete()
	 */
	public function test_complete() {
		$subject = Mockery::mock( Post_Conversion_Process::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'log' )->with( 'Post slugs conversion completed.' )->once();

		\WP_Mock::userFunction(
			'wp_next_scheduled',
			[
				'return' => null,
				'times'  => 1,
			]
		);

		\WP_Mock::userFunction(
			'set_site_transient',
			[
				'times' => 1,
			]
		);

		$subject->complete();
	}

	/**
	 * Tests filter_post_locale()
	 *
	 * @param array  $wpml_post_language_details Post language details.
	 * @param string $locale                     Site locale.
	 * @param string $expected                   Expected result.
	 *
	 * @dataProvider dp_test_filter_post_locale
	 * @throws ReflectionException Reflection exception.
	 */
	public function test_filter_post_locale( $wpml_post_language_details, $locale, $expected ) {
		$post = (object) [
			'ID' => 5,
		];

		\WP_Mock::onFilter( 'wpml_post_language_details' )->with( false, $post->ID )->reply( $wpml_post_language_details );

		\WP_Mock::userFunction(
			'get_locale',
			[
				'return' => $locale,
			]
		);

		$main    = Mockery::mock( Main::class );
		$subject = new Post_Conversion_Process( $main );
		$this->set_protected_property( $subject, 'post', $post );
		$this->assertSame( $expected, $subject->filter_post_locale() );
	}

	/**
	 * Data provider for test_filter_post_locale()
	 *
	 * @return array
	 */
	public function dp_test_filter_post_locale() {
		return [
			[ null, 'ru_RU', 'ru_RU' ],
			[ [], 'ru_RU', 'ru_RU' ],
			[ [ 'some' => 'uk' ], 'ru_RU', 'ru_RU' ],
			[ [ 'locale' => 'uk' ], 'ru_RU', 'uk' ],
		];
	}
}
