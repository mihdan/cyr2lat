<?php
/**
 * Test_Cyr_To_Lat_Requirements class file
 *
 * @package cyr-to-lat
 */

use tad\FunctionMocker\FunctionMocker;

/**
 * Class Test_Cyr_To_Lat_Requirements
 *
 * @group requirements
 */
class Test_Cyr_To_Lat_Requirements extends Cyr_To_Lat_TestCase {

	/**
	 * Test if are_requirements_met() returns true when requirements met.
	 */
	public function test_requirements_met() {
		FunctionMocker::replace(
			'phpversion',
			CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION
		);

		$subject = new Cyr_To_Lat_Requirements();

		\WP_Mock::expectActionNotAdded( 'admin_notices', [ $subject, 'php_requirement_message' ] );

		$this->assertTrue( $subject->are_requirements_met() );
	}

	/**
	 * Test if are_requirements_met() returns false when requirements not met.
	 */
	public function test_requirements_not_met() {
		$required_version = explode( '.', CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION );
		$wrong_version    = array_slice( $required_version, 0, 2 );
		$wrong_version    = (float) implode( '.', $wrong_version );
		$wrong_version    = $wrong_version - 0.1;
		$wrong_version    = number_format( $wrong_version, 1, '.', '' );

		FunctionMocker::replace(
			'phpversion',
			$wrong_version
		);


		$subject = new Cyr_To_Lat_Requirements();

		\WP_Mock::expectActionAdded( 'admin_notices', [ $subject, 'php_requirement_message' ] );

		$this->assertFalse( $subject->are_requirements_met() );
	}

	/**
	 * Test php_requirement_message()
	 */
	public function test_requirement_message() {
		\WP_Mock::userFunction(
			'plugin_basename',
			[
				'args'   => [ CYR_TO_LAT_FILE ],
				'return' => 'cyr2lat/cyr-to-lat.php',
			]
		);
		\WP_Mock::userFunction(
			'load_plugin_textdomain',
			[
				'args' => [ 'cyr2lat', false, 'cyr2lat/languages/' ],
			]
		);
		\WP_Mock::passthruFunction( '__' );
		\WP_Mock::passthruFunction( 'esc_html' );

		$message  = 'Your server is running PHP version ' . phpversion() . ' but Cyr To Lat ' . CYR_TO_LAT_VERSION . ' requires at least ' . CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION . '.';
		$expected = <<<MOCK
			<div class="message error">
				<p>
					$message				</p>
			</div>
			
MOCK;

		$subject = new Cyr_To_Lat_Requirements();

		ob_start();
		$subject->php_requirement_message();
		$this->assertSame( $expected, ob_get_clean() );
	}
}
