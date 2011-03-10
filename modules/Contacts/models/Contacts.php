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
class ContactsModel extends Model {

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

		if($clean->isEmpty('first_name')){
			$this->addError('first_name', 'You must enter a first name.');
		}
		
		if($clean->isEmpty('last_name')){
			$this->addError('last_name', 'You must enter a last name.');
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
		$fields['website'] = 'http://'.str_replace('http://', '', $fields['website']);
		return $fields;
	}
	
	
	/**
	 * Runs additional logic on the update query
	 * @param array $fields
	 * @return mixed
	 * @access public
	 */
	public function before_update($fields = false){
		$fields['website'] = 'http://'.str_replace('http://', '', $fields['website']);
		return $fields;
	}
}
?>