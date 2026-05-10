<?php
/**
 * LegacySanitizeTitleBridgeTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Main;
use CyrToLat\Slugs\LegacySanitizeTitleBridge;
use CyrToLat\Slugs\TermSlugService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
use Mockery;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class LegacySanitizeTitleBridgeTest
 *
 * @group slugs
 */
class LegacySanitizeTitleBridgeTest extends CyrToLatTestCase {

	/**
	 * Test sanitize_title() returns query context unchanged.
	 *
	 * @return void
	 */
	public function test_sanitize_title_returns_query_context_unchanged(): void {
		$subject = $this->get_subject();

		self::assertSame( 'some title', $subject->sanitize_title( 'some title', '', 'query' ) );
	}

	/**
	 * Test sanitize_title() returns ctl_pre_sanitize_title filter value.
	 *
	 * @return void
	 */
	public function test_sanitize_title_returns_pre_filter_value(): void {
		$subject = $this->get_subject();

		WP_Mock::onFilter( 'ctl_enable_legacy_sanitize_title_bridge' )->with( true, 'some title', '', '' )->reply( true );
		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, 'some title' )->reply( 'filtered title' );

		self::assertSame( 'filtered title', $subject->sanitize_title( 'some title' ) );
	}

	/**
	 * Test sanitize_title() returns an unchanged title when legacy bridge is disabled.
	 *
	 * @return void
	 */
	public function test_sanitize_title_returns_unchanged_title_when_bridge_is_disabled(): void {
		$subject = $this->get_subject();

		WP_Mock::onFilter( 'ctl_enable_legacy_sanitize_title_bridge' )->with( true, 'цвет', '', '' )->reply( false );

		self::assertSame( 'цвет', $subject->sanitize_title( 'цвет' ) );
	}

	/**
	 * Test sanitize_title() keeps explicit save context when broad bridge is disabled.
	 *
	 * @return void
	 */
	public function test_sanitize_title_keeps_explicit_save_context_when_bridge_is_disabled(): void {
		$subject = $this->get_subject();

		WP_Mock::onFilter( 'ctl_enable_legacy_sanitize_title_bridge' )->with( true, 'цвет', '', 'save' )->reply( false );
		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, 'цвет' )->reply( false );

		self::assertSame( 'Cvet', $subject->sanitize_title( 'цвет', '', 'save' ) );
	}

	/**
	 * Test sanitize_title() preserves WooCommerce attributes.
	 *
	 * @return void
	 */
	public function test_sanitize_title_preserves_wc_attribute(): void {
		$subject = $this->get_subject( true );

		WP_Mock::onFilter( 'ctl_enable_legacy_sanitize_title_bridge' )->with( true, 'цвет', '', '' )->reply( true );
		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, 'цвет' )->reply( false );

		self::assertSame( 'цвет', $subject->sanitize_title( 'цвет' ) );
	}

	/**
	 * Test sanitize_title() transliterates through callback.
	 *
	 * @return void
	 */
	public function test_sanitize_title_transliterates_through_callback(): void {
		$subject = $this->get_subject();

		WP_Mock::onFilter( 'ctl_enable_legacy_sanitize_title_bridge' )->with( true, 'цвет', '', '' )->reply( true );
		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, 'цвет' )->reply( false );

		self::assertSame( 'Cvet', $subject->sanitize_title( 'цвет' ) );
	}

	/**
	 * Test sanitize_title() logs unknown calls only when development logging is enabled.
	 *
	 * @return void
	 */
	public function test_sanitize_title_logs_unknown_call_when_development_logging_is_enabled(): void {
		$messages = [];
		$subject  = $this->get_subject(
			false,
			true
		);

		WP_Mock::onFilter( 'ctl_enable_legacy_sanitize_title_bridge' )->with( true, 'цвет', '', '' )->reply( true );
		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, 'цвет' )->reply( false );

		FunctionMocker::replace(
			'error_log',
			static function ( string $message ) use ( &$messages ): void {
				$messages[] = $message;
			}
		);

		self::assertSame( 'Cvet', $subject->sanitize_title( 'цвет' ) );
		self::assertCount( 1, $messages );
		self::assertStringContainsString( 'legacy sanitize_title bridge handled an unknown call', $messages[0] );
		self::assertStringNotContainsString( 'цвет', $messages[0] );
	}

	/**
	 * Get a test subject.
	 *
	 * @param bool $is_wc_attribute                Whether title should be preserved as WooCommerce attribute.
	 * @param bool $is_development_logging_enabled Whether the development logging is enabled.
	 *
	 * @return LegacySanitizeTitleBridge
	 */
	private function get_subject( bool $is_wc_attribute = false, bool $is_development_logging_enabled = false ): LegacySanitizeTitleBridge {
		FunctionMocker::replace(
			'defined',
			static function ( string $constant_name ) use ( $is_development_logging_enabled ): bool {
				return 'WP_DEBUG' === $constant_name && $is_development_logging_enabled;
			}
		);
		FunctionMocker::replace(
			'constant',
			static function ( string $name ) use ( $is_development_logging_enabled ): bool {
				return 'WP_DEBUG' === $name && $is_development_logging_enabled;
			}
		);

		$main = Mockery::mock( Main::class )->makePartial();

		return new LegacySanitizeTitleBridge(
			new TermSlugService( $main ),
			false,
			static function ( string $title ): string {
				return 'цвет' === $title ? 'Cvet' : $title;
			},
			static function () use ( $is_wc_attribute ): bool {
				return $is_wc_attribute;
			}
		);
	}
}
