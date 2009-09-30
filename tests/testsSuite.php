<?php

require('../system/loader.inc.php');
require_once 'PageslibTest.php';
require_once 'SecurityTest.php';

/**
 * Static test suite.
 */
class testsSuite extends PHPUnit_Framework_TestSuite {
	
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		// no code here because we need to catch the system pre-headers for session_start() in aspen
	}
	
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		$this->sharedFixture = null;
	}
	
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
	
		$this->sharedFixture = load_framework();
	
		// wipe the appropriate tables
		$this->sharedFixture->model->query("TRUNCATE `pages`");
		$this->sharedFixture->model->query("TRUNCATE `section_basic_editor`");
		$this->sharedFixture->model->query("TRUNCATE `section_list`");
		$this->sharedFixture->model->query("TRUNCATE `template_placement_group`");
	
		// add our test suite
		$this->setName ( 'testsSuite' );
		$this->addTestSuite ( 'PageslibTest' );
		$this->addTestSuite ( 'SecurityTest' );
	
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ( );
	}
}