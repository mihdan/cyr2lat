<?php
/**
 * SettingsTest class file.
 *
 * @package cyr-to-lat
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

// phpcs:disable WordPress.WP.AlternativeFunctions.json_encode_json_encode

namespace CyrToLat\Tests\Unit\Settings;

use CyrToLat\Settings\Converter;
use CyrToLat\Settings\Settings;
use CyrToLat\Settings\Tables;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
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
class SettingsTest extends CyrToLatTestCase {

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
	 */
	public function test_init_and_screen_ids() {
		$screen_id = 'cyr-to-lat';

		Mockery::mock( 'overload:' . Tables::class );

		$converter = Mockery::mock( 'overload:' . Converter::class )->makePartial();
		$converter->shouldReceive( 'screen_id' )->with()->andReturn( $screen_id );

		$menu_pages_classes = [
			'Cyr To Lat' => [
				Tables::class,
				Converter::class,
			],
		];

		$expected = $menu_pages_classes;

		$subject = new Settings( $menu_pages_classes );

		$subject_menu_pages_classes = $this->get_protected_property( $subject, 'menu_pages_classes' );

		self::assertSame(
			json_encode( $expected ),
			json_encode( $subject_menu_pages_classes )
		);

		self::assertSame( [ $screen_id ], $subject->screen_ids() );
	}

	/**
	 * Test get().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_get() {
		$tables_key      = 'some tables key';
		$tables_value    = 'some table';
		$converter_key   = 'some converter key';
		$converter_value = 'some value';

		$tables = Mockery::mock( Tables::class );
		$tables->shouldReceive( 'get' )->andReturnUsing(
			function( $key, $empty_value ) use ( $tables_key, &$tables_value ) {
				if ( $key === $tables_key ) {
					return $tables_value;
				}

				if ( ! is_null( $empty_value ) ) {
					return $empty_value;
				}

				return '';
			}
		);

		$converter = Mockery::mock( Converter::class );
		$converter->shouldReceive( 'get' )->andReturnUsing(
			function( $key, $empty_value ) use ( $converter_key, $converter_value ) {
				if ( $key === $converter_key ) {
					return $converter_value;
				}

				if ( ! is_null( $empty_value ) ) {
					return $empty_value;
				}

				return '';
			}
		);

		$tables->shouldReceive( 'get_tabs' )->andReturn( [ $converter ] );
		$converter->shouldReceive( 'get_tabs' )->andReturn( null );

		$menu_pages_classes = [
			'Cyr To Lat' => [
				Tables::class,
				Converter::class,
			],
		];

		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$tabs = [ $tables, $converter ];
		$this->set_protected_property( $subject, 'tabs', $tabs );
		$this->set_protected_property( $subject, 'menu_pages_classes', $menu_pages_classes );

		self::assertSame( $tables_value, $subject->get( $tables_key ) );
		self::assertSame( $converter_value, $subject->get( $converter_key ) );
		self::assertSame( '', $subject->get( 'non-existent key' ) );

		$empty_value = 'empty value';
		self::assertSame( $empty_value, $subject->get( 'non-existent key', $empty_value ) );

		$tables_value = '';
		$empty_value  = '';
		self::assertSame( $empty_value, $subject->get( $tables_key, $empty_value ) );
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
	public function test_is_chinese_locale( string $locale, bool $expected ) {
		$subject = Mockery::mock( Settings::class )->makePartial();

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
	public static function dp_test_is_chinese_locale(): array {
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
	 * @param string|null $is_chinese_locale Current locale.
	 * @param array       $table             Conversion table.
	 * @param array       $expected          Expected result.
	 *
	 * @throws ReflectionException ReflectionException.
	 * @dataProvider dp_test_transpose_chinese_table
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function test_transpose_chinese_table( $is_chinese_locale, array $table, array $expected ) {
		$subject = Mockery::mock( Settings::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_chinese_locale' )->andReturn( $is_chinese_locale );
		$method = 'transpose_chinese_table';

		$this->set_method_accessibility( $subject, $method );

		self::assertSame( $expected, $subject->$method( $table ) );
	}

	/**
	 * Data provider for test_transpose_chinese_table
	 *
	 * @return array
	 */
	public static function dp_test_transpose_chinese_table(): array {
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
