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
class EventsModel extends Model {

	/**
	 * We must allow the parent constructor to run properly
	 * @param string $table
	 * @access public
	 */
	public function __construct($table = false){
		parent::__construct($table);
		$this->setSecurityRule('content', 'allow_html', true);
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
		
		if(!$clean->isEmpty('recurring') && !$clean->isDate('start_date')){
			$this->addError('start_date', 'Please enter a valid start date.');
		}
		
		if(!$this->isEmpty('end_date')){
			if($this->isDate('end_date')){
				if(strtotime($this->getRaw('start_date')) > strtotime($this->getRaw('end_date'))){
					$this->addError('content', 'Please choose a starting date that occurs before the end date.');
				}
			} else {
				$this->addError('end_date', 'Please enter a valid end date.');
			}
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
//		$fields['website'] = 'http://'.str_replace('http://', '', $fields['website']);
		return $fields;
	}
}
?>