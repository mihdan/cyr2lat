<?php
/**
 * TermSlugServiceTest class file.
 *
 * @package cyr-to-lat
 */

namespace CyrToLat\Tests\Unit\Slugs;

use CyrToLat\Slugs\TermSlugService;
use CyrToLat\Tests\Unit\CyrToLatTestCase;

/**
 * Class TermSlugServiceTest
 *
 * @group slugs
 */
class TermSlugServiceTest extends CyrToLatTestCase {

	/**
	 * Test default context.
	 *
	 * @return void
	 */
	public function test_default_context(): void {
		$subject = new TermSlugService();

		self::assertFalse( $subject->is_term_context() );
		self::assertSame( [], $subject->taxonomies() );
	}
}
