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
class PagesModel extends Model {

	/**
	 * We must allow the parent constructor to run properly
	 * @param string $table
	 * @access public
	 */
	public function __construct($table = false){ parent::__construct($table); }


	/**
	 * Validates the database table input
	 * @param array $fields
	 * @param string $primary_key
	 * @return boolean
	 * @access public
	 */
	public function validate($fields = false, $primary_key = false){

		$clean = parent::validate($fields, $primary_key);

		if($clean->isEmpty('page_title')){
			$this->addError('page_title', 'You must enter a page title.');
		}

		return !$this->error();

	}


	/**
	 * Runs additional logic on the insert query
	 * @param array $fields
	 * @return mixed
	 * @access public
	 */
	public function before_insert($fields = false){
		
		$sort = $this->quickValue('MAX(page_sort_order)', 'SELECT MAX(page_sort_order) FROM pages') + 1;
		$fields['page_sort_order'] = $sort;

		// enforce a sha1 on the password
		if(empty($fields['page_link_text'])){
			$fields['page_link_text'] = $fields['page_title'];
		}

		return $fields;

	}
}
?>