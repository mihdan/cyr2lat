<?php
/**
 * SettingsTest class file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

// phpcs:disable WordPress.WP.AlternativeFunctions.json_encode_json_encode

namespace Cyr_To_Lat\Tests\Settings;

use Cyr_To_Lat\Settings\Tables;
use Cyr_To_Lat\Settings\Converter;
use Cyr_To_Lat\Settings\Settings;
use Cyr_To_Lat\Cyr_To_Lat_TestCase;
use Mockery;
use PHPUnit\Runner\Version;
use ReflectionClass;
use ReflectionException;
use WP_Mock;

/**
 * Class SettingsTest
 *
 * @group settings
 * @group settings-main
 */
class SettingsTest extends Cyr_To_Lat_TestCase {

	/**
	 * Test constructor.
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_constructor() {
		$class_name = Settings::class;

		$subject = Mockery::mock( $class_name )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'init' )->once();

		$reflected_class = new ReflectionClass( $class_name );
		$constructor     = $reflected_class->getConstructor();
		$constructor->invoke( $subject );
	}

	/**
	 * Test init().
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @throws ReflectionException ReflectionException.
	 * @noinspection        JsonEncodingApiUsageInspection
	 */
	public function test_init_and_screen_ids() {
		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$tables    = Mockery::mock( 'overload:' . Tables::class );
		$converter = Mockery::mock( 'overload:' . Converter::class )->makePartial();
		$screen_id = 'cyr-to-lat';
		$converter->shouldReceive( 'screen_id' )->with()->andReturn( $screen_id );

		$expected = [ $tables ];

		$subject->init();
		$menu_pages = $this->get_protected_property( $subject, 'menu_pages' );
		self::assertSame(
			json_encode( $expected ),
			json_encode( $menu_pages )
		);

		self::assertSame( [ $screen_id ], $subject->screen_ids() );
	}

	/**
	 * Test get().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_get() {
		$option   = 'some option';
		$expected = 'some value';

		$tables = Mockery::mock( Tables::class );
		$tables->shouldReceive( 'get' )->once()->andReturn( $expected );
		$menu_pages = [ $tables ];

		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$this->set_protected_property( $subject, 'menu_pages', $menu_pages );

		self::assertSame( $expected, $subject->get( $option ) );
	}

	/**
	 * Test get_table()
	 */
	public function test_get_table() {
		$subject    = Mockery::mock( Settings::class )->makePartial();
		$locale     = 'not_existing_locale';
		$iso9_table = $this->get_conversion_table( $locale );

		$subject->shouldReceive( 'get' )->with( $locale )->andReturn( '' );
		$subject->shouldReceive( 'get' )->with( 'iso9' )->andReturn( $iso9_table );

		WP_Mock::userFunction(
			'get_locale',
			[
				'return' => $locale,
			]
		);

		if (
			class_exists( Version::class ) &&
			version_compare( substr( Version::id(), 0, 1 ), '7', '>=' )
		) {
			WP_Mock::expectFilter( 'ctl_locale', $locale );
		}

		self::assertSame( $iso9_table, $subject->get_table() );
	}

	/**
	 * Test is_chinese_locale()
	 *
	 * @param string  $locale   Current locale.
	 * @param boolean $expected Expected result.
	 *
	 * @dataProvider dp_test_is_chinese_locale
	 */
	public function test_is_chinese_locale( $locale, $expected ) {
		$subject = new Settings();

		WP_Mock::userFunction(
			'get_locale',
			[
				'return' => $locale,
			]
		);

		self::assertSame( $expected, $subject->is_chinese_locale() );
	}

	/**
	 * Data provider for test_is_chinese_locale
	 *
	 * @return array
	 */
	public function dp_test_is_chinese_locale() {
		return [
			[ 'zh_CN', true ],
			[ 'zh_HK', true ],
			[ 'zh_SG', true ],
			[ 'zh_TW', true ],
			[ 'some locale', false ],
		];
	}

	/**
	 * Test transpose_chinese_table()
	 *
	 * @param string $is_chinese_locale Current locale.
	 * @param array  $table             Conversion table.
	 * @param array  $expected          Expected result.
	 *
	 * @dataProvider dp_test_transpose_chinese_table
	 */
	public function test_transpose_chinese_table( $is_chinese_locale, $table, $expected ) {
		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_chinese_locale' )->andReturn( $is_chinese_locale );

		self::assertSame( $expected, $subject->transpose_chinese_table( $table ) );
	}

	/**
	 * Data provider for test_transpose_chinese_table
	 *
	 * @return array
	 */
	public function dp_test_transpose_chinese_table() {
		return [
			[
				false,
				[ 'я' => 'ya' ],
				[ 'я' => 'ya' ],
			],
			[
				true,
				[ 'A' => '啊阿吖嗄锕' ],
				[
					'啊' => 'A',
					'阿' => 'A',
					'吖' => 'A',
					'嗄' => 'A',
					'锕' => 'A',
				],
			],
		];
	}
}
