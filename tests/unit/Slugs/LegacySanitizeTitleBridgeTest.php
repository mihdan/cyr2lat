<?php
/**
 * LegacySanitizeTitleBridgeTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Slugs\LegacySanitizeTitleBridge;
use CyrToLat\Slugs\TermSlugService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;
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

		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, 'some title' )->reply( 'filtered title' );

		self::assertSame( 'filtered title', $subject->sanitize_title( 'some title' ) );
	}

	/**
	 * Test sanitize_title() preserves WooCommerce attributes.
	 *
	 * @return void
	 */
	public function test_sanitize_title_preserves_wc_attribute(): void {
		$subject = $this->get_subject( true );

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

		WP_Mock::onFilter( 'ctl_pre_sanitize_title' )->with( false, 'цвет' )->reply( false );

		self::assertSame( 'cvet', $subject->sanitize_title( 'цвет' ) );
	}

	/**
	 * Get a test subject.
	 *
	 * @param bool $is_wc_attribute Whether title should be preserved as WooCommerce attribute.
	 *
	 * @return LegacySanitizeTitleBridge
	 */
	private function get_subject( bool $is_wc_attribute = false ): LegacySanitizeTitleBridge {
		return new LegacySanitizeTitleBridge(
			new TermSlugService(),
			false,
			static function ( string $title ): string {
				return 'цвет' === $title ? 'Cvet' : $title;
			},
			static function (): bool {
				return true;
			},
			static function () use ( $is_wc_attribute ): bool {
				return $is_wc_attribute;
			}
		);
	}
}
