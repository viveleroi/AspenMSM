<?php

/**
 * Security test case.
 */
class SecurityTest extends PHPUnit_Framework_TestCase {

	/**
	 * Tests Security->clean_db_input()
	 *  Passes a value which is a string, with html inside, html disallowed
	 */
	public function testClean_db_input_stringNoHtmlNoQuotes() {
		$this->assertEquals('abcd', $this->sharedFixture->security->clean_db_input('<b>abcd</b>')); // no html
	}
	
	/**
	 * Tests Security->clean_db_input()
	 *  Passes a value which is a string, with html inside, html disallowed, single quotes
	 */
	public function testClean_db_input_stringNoHtmlSingleQuotes() {
		$this->assertEquals('\\\'abcd\\\'', $this->sharedFixture->security->clean_db_input('<b>\'abcd\'</b>'));
	}
	
	/**
	 * Tests Security->clean_db_input()
	 *  Passes a value which is a string, with html inside, html disallowed, double quotes
	 */
	public function testClean_db_input_stringNoHtmlDoubleQuotes() {
		$this->assertEquals('\\"abcd\\"', $this->sharedFixture->security->clean_db_input('<b>"abcd"</b>'));
	}
	
	/**
	 * Tests Security->clean_db_input()
	 * Passes a value which is a string, with html inside, html allowed
	 */
	public function testClean_db_input_stringHtml() {
		$this->assertEquals('<b>abcd</b>', $this->sharedFixture->security->clean_db_input('<b>abcd</b>', true)); // html allowed
	}
	
	/**
	 * Tests Security->clean_db_input()
	 * Passes a value which is an array, with html inside, html disallowed
	 */
	public function testClean_db_input_arrayNoHtml() {
		$this->assertEquals(array('\\"abcd\\"'), $this->sharedFixture->security->clean_db_input(array('<b>"abcd"</b>')));
	}
	
	/**
	 * Tests Security->clean_db_input()
	 * Passes a value which is an array, with html inside, html allowed
	 */
	public function testClean_db_input_arrayHtml() {
		$this->assertEquals(array('<b>\\"abcd\\"</b>'), $this->sharedFixture->security->clean_db_input(array('<b>"abcd"</b>'), true));
	}
	
	/**
	 * Tests Security->clean_slashes()
	 */
	public function testClean_slashes() {
		$this->assertEquals('"abcd"', $this->sharedFixture->security->clean_slashes('\"abcd\"'));
	}

	
	/**
	 * Tests Security->dbescape()
	 */
	public function testDbescape() {
		
		// escape quotes with html
		//$this->assertEquals('<b>\"abcd\"</b>', $this->sharedFixture->security->clean_db_input('<b>\"abcd\"</b>', true));
		
		// escape quotes in array with html
		//$this->assertEquals(array('<b>\\"abcd\\"</b>'), $this->sharedFixture->security->clean_db_input(array('<b>\\"abcd\\"</b>'), true));
	
	}

}

