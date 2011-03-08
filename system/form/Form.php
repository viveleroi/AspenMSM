<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Validates and handles form data.
 * @package Aspen_Framework
 */
class Form  {

	/**
	 * @var array $_form_fields Holds an array of form fields
	 * @access private
	 */
	private $_form_fields;

	/**
	 * @var array $_form_errors Holds an array of form field validation errors
	 * @access private
	 */
	private $_form_errors = array();

	/**
	 * Flags a validation error
	 * @var boolean $_error
	 * @access private
	 */
	private $_error = false;

	/**
	 * @var string $_primary_key_field Holds the field name of our primary key
	 * @access private
	 */
	private $_primary_key_field = false;

	/**
	 * @var string $param_type Holds the type of superglobal we're accessing
	 * @access private
	 */
	private $param_type = 'post';

	/**
	 * @var array Holds the schema for the current table
	 * @access private
	 */
	private $schema;

	/**
	 * @var string $table Holds the db table we're using, if any
	 * @access private
	 */
	private $table = false;


	/**
	 * Loads a single record - field names and values
	 * @param string $table
	 * @param integer $id
	 * @param array $contains
	 * @param string $field
	 * @access public
	 */
	public function __construct($table = false, $id = false, $contains = array(), $field = false){
		if($id){
			if(!defined('ADD_OR_EDIT')){
				define('ADD_OR_EDIT', 'edit');
				define('IS_EDIT_PAGE', true);
			}
			$this->loadRecord($table, $id, $contains);
		} else {
			if(!defined('ADD_OR_EDIT')){
				define('ADD_OR_EDIT', 'add');
				define('IS_EDIT_PAGE', false);
			}
			$this->loadTable($table, $contains);
		}
	}


	/**
	 * Loads a table's fields and it's schema
	 * @param string $table
	 * @param array $contains
	 * @access private
	 */
	private function loadTable($table = false, $contains = array()){

		$this->table = $table;

		if($this->table){

			$model = model()->open($this->table);
			$model->contains($contains);
			$this->_primary_key_field = $model->getPrimaryKey();
			$this->schema = $model->getSchema();

			// add fields for schema as well as related tables
			foreach($this->schema['schema'] as $field){
				$default_val = $field->has_default ? $field->default_value : '';
				$this->addField($field->name, $default_val, $default_val);
			}
			$rels = array_merge($this->schema['children'], $this->schema['parents']);
			foreach($rels as $field){
				$this->addField(ucwords($field));
			}
		}
	}


	/**
	 * Loads a single record - field names and values
	 * @param string $table
	 * @param integer $id
	 * @param array $contains
	 * @param string $field
	 * @access private
	 */
	private function loadRecord($table, $id = false, $contains = array(), $field = false){

		$this->table = $table;

		if($id && $this->table){

			$model = model()->open($this->table);
			$model->contains($contains);
			$this->_primary_key_field = $model->getPrimaryKey();
			$this->schema = $model->getSchema();

			$field = $field ? $field : $this->_primary_key_field;

			$model = model()->open();
			$model->where($this->table.'.'.$field, app()->security->dbescape($id));
			$records = $model->results();

			if($records){
				foreach($records as $record){
					foreach($record as $field => $value){
						$this->addField($field, $value, $value);
					}
				}
			} else {
				$this->loadTable($this->table);
			}
		} else {
			$this->loadTable($this->table);
		}
	}


	/**
	 * Initiates the insert/update queries on form data
	 * @param integer $id
	 * @return integer
	 * @access public
	 */
	public function save($id = false){

		$model 		= model()->open($this->table);
		$success 	= false;
		$schema		= $model->getSchema();

		// determine the primary key field
		$pid = $model->getPrimaryKey();

		// build a complete array of all incoming data
		// even if some of the fields are not in the database,
		// those may now be validated through the models
		$fields = array();
		foreach($this->_form_fields as $field => $elems){
			if($field != $pid){
				$fields[$field] = $this->cv($field);
			}
		}

		// load in any child/parent data
		foreach($schema['children'] as $table){
			$fields[ucwords($table)] = $this->cv(ucwords($table));
		}
		foreach($schema['parents'] as $table){
			$fields[ucwords($table)] = $this->cv(ucwords($table));
		}

		// If there are no form errors, then attempt to process the database action.
		// The database action will force the model validation and will return false if
		// something fails.
		// If a form error does exist, then we proceed with finding any other
		// field validation errors from the model, but without actually saving the data.
		if(!$this->error()){
			$success = $id ? $model->update($fields, $id) : $model->insert($fields);
		} else {
			$model->validate($fields, $id);
		}

		// if failed, pull all model validation errors into form array
		if(!$success){
			foreach($model->getErrors() as $field => $errors){
				foreach($errors as $error){
					$this->addError($field, $error);
				}
			}
		}

		return $success;

	}


	/**
	 * Determines whether or not a form has been submitted
	 * @param string $method
	 * @param strong $field
	 * @return boolean
	 * @access public
	 */
	public function isSubmitted($method = 'post', $field = false){

		$submitted = false;

		// verify field isset - we dont care about the value
		$data = app()->params->getRawSource($method);
		if(is_array($data) && count($data)){
			if($field){
				$submitted = app()->{$method}->keyExists($field);
			} else {
				$submitted = true;
			}
		}

		if($submitted){
			if($method == 'post'){
				$this->loadPOST();
			}
			if($method == 'get'){
				$this->loadGET();
			}

			// if token authorization is enabled, we must authenticate
			if(app()->config('require_form_token_auth')){

				$sess_token = session()->getAlnum('form_token');

				if(empty($sess_token) || $sess_token != app()->{$method}->getAlnum('token')){
					$_SESSION['form_token'] = false;
					$this->addError('token', 'An invalid token was provided. The form was not processed.');
				}
			}
		}

		return $submitted;
	}


	/**
	 * Adds a new field to our form schema
	 * @param string $name
	 * @param mixed $default_value
	 * @param mixed $current_value
	 * @access public
	 */
	public function addField($name, $default_value = '', $current_value = false){
		$this->_form_fields[$name] = array(
											'default_value' => $default_value,
											'post_value' => false,
											'current_value' => ($current_value ? $current_value : $default_value));
	}


	/**
	 * Adds each item in an array as a field
	 * @param array $fields
	 * @access public
	 */
	public function addFields($fields){
		if(is_array($fields)){
			foreach($fields as $name){
				$this->addField($name);
			}
		}
	}


	/**
	 * Sets a default value of a form field
	 * @param string $field
	 * @param mixed $value
	 * @access public
	 */
	public function setDefaultValue($field = false, $value = false){
		if($field){
			$this->_form_fields[$field]['default_value'] = $value;
			$this->_form_fields[$field]['current_value'] = $value;
		}
	}


	/**
	 * Returns the default value for a field
	 * @param string $field
	 * @param boolean $escape
	 * @return mixed
	 * @access public
	 */
	public function getDefaultValue($field, $escape = true){
		$value = false;
		if(isset($this->_form_fields[$field])){
			$value = $this->_form_fields[$field]['default_value'];
			$value = $escape ? app()->security->dbescape($value, model()->getSecurityRule($field, 'allow_html')) : $value;
		}
		return $value;
	}


	/**
	 * Resets all form fields to their default values
	 * @access public
	 */
	public function resetDefaults(){
		if(is_array($this->_form_fields)){
			foreach($this->_form_fields as $name => $value){
				$this->_form_fields[$name]['current_value'] = $this->_form_fields[$name]['default_value'];
			}
		}
	}


	/**
	 * Sets the current value of a form field
	 * @param string $field
	 * @param mixed $value
	 * @access public
	 */
	public function setCurrentValue($field = false, $value = false){
		if($field){
			$this->_form_fields[$field]['post_value'] = $value;
			$this->_form_fields[$field]['current_value'] = $value;
		}
	}


	/**
	 * Returns the current value for a field
	 * @param string $field
	 * @param boolean $escape
	 * @return mixed
	 * @access public
	 */
	public function cv($field, $escape = false){
		$value = false;
		if(isset($this->_form_fields[$field])){
			$value = $this->_form_fields[$field]['current_value'];
		}
		return $escape ? app()->security->dbescape($value, model()->getSecurityRule($field, 'allow_html')) : $value;
	}


	/**
	 * Returns a selected attributed iif the value matches one provided
	 * @param string $field
	 * @param mixed $match
	 * @return string
	 * @access public
	 */
	public function selected($field, $match){
		return $this->match_base($field, $match, ' selected="selected"');
	}


	/**
	 * Returns a checked attributed iif the value matches one provided
	 * @param string $field
	 * @param mixed $match
	 * @return string
	 * @access public
	 */
	public function checked($field, $match = false){
		return $this->match_base($field, $match, ' checked="checked"');
	}


	/**
	 * Performs base matching for selected/checked fields
	 * @param string $field
	 * @param mixed $match
	 * @param string $str
	 * @return string
	 */
	protected function match_base($field, $match, $str){
		$val = $this->cv($field);
		// if field is the name of a child/parent table
		if($val && (in_array(strtolower($field), $this->schema['children'])
				|| in_array(strtolower($field), $this->schema['parents']))){
			return array_key_exists($match, $val) || in_array($match, $val) ? $str : '';
		}
		elseif(is_array($val)){
			return in_array($match, $val) ? $str : '';
		}
		elseif(!$match) {
			return $val ? $str : '';
		} else {
			return $val == $match ? $str : '';
		}
	}


	/**
	 * Returns an array of all fields and their current values
	 * @return array
	 * @access public
	 */
	public function getCurrentValues(){
		$current_values = array();
		if(is_array($this->_form_fields)){
			foreach($this->_form_fields as $field => $bits){
				$current_values[$field] = $this->cv($field);
			}
		}
		return Peregrine::sanitize($current_values);
	}



	/**
	 * Imports all values for current fields from POST data
	 * @access private
	 */
	public function loadPOST(){
		$this->loadIncomingValues('post');
	}


	/**
	 * Imports all values for current fields from GET data
	 * @access private
	 */
	public function loadGET(){
		$this->loadIncomingValues('get');
	}


	/**
	 * Imports all values for current fields from incoming GET/POST data
	 * @access private
	 */
	public function loadIncomingValues($method = 'post'){

		$this->param_type 	= $method;
		$field_model 		= false;

		if(is_array($this->_form_fields)){
			foreach($this->_form_fields as $field => $bits){

				// identify field from schema
				$field_model = false;
				if(isset($this->schema['schema'][strtoupper($field)])){
					$field_model = $this->schema['schema'][strtoupper($field)];
				}

				// determine security method
				$param_access_type	= 'getRaw';
				if(is_object($field_model) && isset($field_model->type)){
					if(in_array($field_model->type, app()->config('mysql_field_group_dec'))){
						$param_access_type = 'getFloat';
					}
					elseif(in_array($field_model->type, app()->config('mysql_field_group_int'))){
						$param_access_type = 'getDigits';
					}
				}


				// get core array, so we can verify if it's even set
				$source = app()->params->getRawSource($this->param_type);
				$get_val = app()->{$this->param_type}->{$param_access_type}($field);

				// if array key not set, we use the current value
				if(!array_key_exists($field, $source)){
					// if the field model is false, this really isn't a field
					// so we can set it to false
					if(!$field_model){
						$this->_form_fields[$field]['current_value'] = false;
					} else {
						$this->_form_fields[$field]['current_value'] = $this->_form_fields[$field]['default_value'];
					}
				} else {

					// if array key set and a primary field, set post and current to default
					if($field == $this->_primary_key_field){
						$this->_form_fields[$field]['post_value'] = $this->_form_fields[$field]['default_value'];
						$this->_form_fields[$field]['current_value'] = $this->_form_fields[$field]['post_value'];
					// otherwise, set value to incoming
					} else {
						$this->_form_fields[$field]['post_value'] = $get_val;
						$this->_form_fields[$field]['current_value'] = $this->_form_fields[$field]['post_value'];
					}
				}
			}
		}
	}


	/**
	 * Triggers a validation error message
	 * @param string $field
	 * @param string $message
	 * @access public
	 */
	public function addError($field, $message){
		$this->_error = true;
		if(isset($this->_form_errors[$field]) && is_array($this->_form_errors[$field])){
			array_push($this->_form_errors[$field], $message);
		} else {
			$this->_form_errors[$field] = array($message);
		}
	}


	/**
	 * Returns an array of current form errors
	 * @return array
	 * @access public
	 */
	public function getErrors($custom_sort = false){
		// if no custom sort provided, we need to sort the errors
		// according to their schema position
		if(!$custom_sort && app()->isInstalled() && $this->table){
			$custom_sort = array();
			$model = model()->open($this->table);
			$schema = $model->getSchema();
			foreach($schema['schema'] as $field){
				$custom_sort[$field->name] = false;
			}
		} else {
			// currently, custom sort defined as vals not keys so we must flip
			if(is_array($custom_sort)){
				$custom_sort = array_flip($custom_sort);
			}
		}
		if(is_array($custom_sort)){
			$diff = array_diff_key($this->_form_errors, $custom_sort);
			$custom_sort = array_merge($custom_sort, $diff);

			// sort errors for known fields
			$final_errors = array();
			foreach($custom_sort as $field => $val){
				$errors = $this->getFieldErrors($field);
				if($errors){
					$final_errors[$field] = $errors;
				}
			}
		} else {
			$final_errors = $this->_form_errors;
		}
		return $final_errors;
	}


	/**
	 * Returns an array of errors for a specific field
	 * @param string $field
	 * @return mixed
	 */
	public function getFieldErrors($field = false){
		if($field && array_key_exists($field, $this->_form_errors)){
			return $this->_form_errors[$field];
		}
		return false;
	}


	/**
	 * Prints out form error messages using html wrapping defined in config
	 * @access public
	 */
	public function printErrors($custom_sort = false){
		$lines = '';
		if($this->error()){
			foreach($this->getErrors($custom_sort) as $errors){
				foreach($errors as $field => $error){
					$lines .= sprintf(app()->config('form_error_line_html'), $error);
				}
			}
			printf(app()->config('form_error_wrapping_html'), $lines);
		}
		print '';
	}


	/**
	 * Returns a boolean whether there is an error or not
	 * @return boolean
	 * @access public
	 */
	public function error(){
		return $this->_error;
	}


	/**
	 * AJAX HELPERS AND RESPONDERS
	 */


	/**
	 * Basic ajax form handling responder. Submits the form and returns
	 * useful json-encoded data for the client-side handlers.
	 *
	 * @param string $method
	 * @param integer $id
	 * @return string
	 * @access public
	 */
	public function ajaxResponder($method = 'post', $id = false){
		$res = false;
		if($this->isSubmitted($method)){
			$res = $this->save($id);
		}
		return json_encode( array(
			'success'	=> $res,
			'msg'		=> text('db:success:'.$this->table.':ajax-say'),
			'errors'	=> $this->getErrors(),
			'id'		=> $id,
			'method'	=> $method,
			'raw_data'	=> app()->params->getRawSource($method)
		) );
	}
}
?>