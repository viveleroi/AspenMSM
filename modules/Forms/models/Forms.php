<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0.1-18
 */

/**
 * This class manages our mysql sql query generation
 * @package Aspen_Framework
 */
class FormsModel extends Model {

	/**
	 * We must allow the parent constructor to run properly
	 * @param string $table
	 * @access public
	 */
	public function __construct($table = false){
		parent::__construct($table);
		$this->setSecurityRule('bio', 'allow_html', true);
	}


	/**
	 * Validates the database table input
	 * @param array $fields
	 * @param string $primary_key
	 * @return boolean
	 * @access public
	 */
	public function validate($fields = false, $primary_key = false){

		$clean = parent::validate($fields, $primary_key);

		if($clean->isEmpty('title')){
			$this->addError('title', 'You must enter a title.');
		}
		
//		if($clean->isEmpty('body')){
//			$this->addError('body', 'You must enter some content.');
//		}

		return !$this->error();

	}
}
?>