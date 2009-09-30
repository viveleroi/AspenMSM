<?php

/**
 * Pageslib test case.
 */
class TestHelper extends PHPUnit_Framework_TestCase {


	public function mockFormPost($post) {
		
		$_POST = $post;
		$_POST['submit'] = 'submit';
		$this->sharedFixture->params->refreshCage('post');
	
	}
}