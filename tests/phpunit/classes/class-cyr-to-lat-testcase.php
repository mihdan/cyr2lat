<?php
/**
 * Cyr_To_Lat_TestCase class file.
 *
 * @package cyr-to-lat
 */

use PHPUnit\Framework\TestCase;
use tad\FunctionMocker\FunctionMocker;

/**
 * Class Cyr_To_Lat_TestCase
 */
abstract class Cyr_To_Lat_TestCase extends TestCase {

	/**
	 * Setup test
	 */
	public function setUp() {
		FunctionMocker::setUp();
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * End test
	 */
	public function tearDown() {
		\WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
		FunctionMocker::tearDown();
	}
}
