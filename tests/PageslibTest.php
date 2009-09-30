<?php

require 'testHelper.php';

/**
 * Pageslib test case.
 */
class PageslibTest extends TestHelper {

	/**
	 * Tests Pageslib->__construct()
	 */
	public function testAdd() {
	
		//$this->markTestIncomplete ( "__construct test not implemented" );
		
		
		$this->mockFormPost(array('page_title' => 'Home'));
		
		$this->assertEquals(1, $this->sharedFixture->pages_lib->add());
	
	}
}