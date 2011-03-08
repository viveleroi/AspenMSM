<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */


/**
 * Provides a method of writing to a log file.
 * @package Aspen_Framework
 */
class Log  {

	/**
	 * @var boolean $on Whether or not logging  is enabled
	 * @access private
	 */
	private $on = false;

	/**
	 * @var boolean $dir The directory path to our log files
	 * @access private
	 */
	private $dir = false;

	/**
	 * @var boolean $full_path Contains the full path to our current log file
	 * @access private
	 */
	private $full_path = false;


	/**
	 * Sets up the directories and files as necessary
	 * @access public
	 */
	public function enable(){

		$loaded = false;

		$this->on 		= app()->config('enable_logging');
		$this->dir 		= app()->config('log_dir');
		$this->level 	= app()->config('log_verbosity');

		if($this->on && $this->dir){

			// verify directory exists and is writeable
			if(!$this->checkDirectory()){
				$this->on = false;
				error()->raise(1,
					'Logging is enabled, but directory is not writeable. Dir: ' . $this->dir, __FILE__, __LINE__);
			}

			// create a log file
			if(!$this->createLogFile()){
				$this->on = false;
				error()->raise(1,
					'Failed creating new log file.', __FILE__, __LINE__);
			}

			if($this->on){
				$loaded = true;
			}
		}


		if($loaded){

			$this->write('Logging has been activated at ' . Date::formatMicrotime(Date::microtime(EXECUTION_START)) . '.', 'w');

			if($this->level == 1){
				$this->logCoreInfo();
			}
		}
	}


	/**
	 * Checks for a valid directory, attempts to create
	 * @return boolean
	 * @access private
	 */
	private function checkDirectory(){

		$dir_ok = false;

		if($this->dir){
			if(is_dir($this->dir) && is_writeable($this->dir)){
				$dir_ok = true;
			} else {
				$dir_ok = mkdir($this->dir);
			}
		}

		return $dir_ok;

	}


	/**
	 * Uses or creates new log files
	 * @return boolean
	 * @access private
	 */
	private function createLogFile(){

		$new_filename = 'log';

		if(app()->config('timestamp_log_file')){
			$new_filename .= '-' . Date::formatMicrotime(Date::microtime(EXECUTION_START));
		}

		$this->full_path = $this->dir . DS . $new_filename;

		if(!$fileexists = file_exists($this->full_path)){
			$fileexists = touch($this->full_path);
		}

		return $fileexists;

	}


	/**
	 * Writes a new message to the log file
	 * @param string $message
	 * @access public
	 */
	public function write($message = '(empty message)', $mode = 'a'){
		if($this->on){
			files()->useFile($this->full_path);

			if(is_array($message) || is_object($message)){
				files()->write( print_r($message, true) . "\n", $mode);
			} else {
				files()->write( preg_replace('/[\t]+/', '', trim($message)) . "\n", $mode);
			}
		}
	}


	/**
	 * Writes a breaking line
	 * @access public
	 */
	public function hr(){
		$this->write('++======================================================++');
	}

	/**
	 * Writes a new section header
	 * @access public
	 */
	public function section($title = 'Section'){
		$this->write('');
		$this->write('++======================================================++');
		$this->write('++  ' . $title);
		$this->write('++======================================================++');
	}


	/**
	 * Sets the proper print string for a var
	 * @param mixed $value
	 * @return string
	 * @access private
	 */
	private function logValue($value){
		if(is_array($value) || is_object($value)){
			return serialize($value);
		}
		return $value;
	}


	/**
	 * Logs all core aspen framework data to the logfile
	 * @access private
	 */
	private function logCoreInfo(){
		if($this->on){

			// record all constants
			$this->section('Constants');
			$defines = get_defined_constants(true);
			foreach($defines['user'] as $define => $value){
				$this->write('Constant ' . $define . ' was set to a value of: ' . $this->logValue($value));
			}

			// record all configurations
			$this->section('Configurations');
			$config = app()->getConfig();
			foreach($config as $config => $value){
				$this->write('Config ' . $config . ' was set to a value of: ' . $this->logValue($value));
			}

			$this->section('Loaded System Libraries');
			$lib = app()->getLoadedLibraries();
			foreach($lib as $class){
				$this->write('Library Class ' . $class['classname'] . ' was loaded.');
			}

			$this->section('Session Data');
			$session = app()->params->getRawSource('session');
			$this->write('Session_id: ' . session_id());
			foreach($session as $key => $value){
				$this->write('$_SESSION[\''.$key.'\'] = ' . $this->logValue($value));
			}

			$this->section('POST Data');
			$post = app()->params->getRawSource('post');
			foreach($post as $key => $value){
				$this->write('$_POST[\''.$key.'\'] = ' . $this->logValue($value));
			}

			$this->section('GET Data');
			$get = app()->params->getRawSource('get');
			foreach($get as $key => $value){
				$this->write('$_GET[\''.$key.'\'] = ' . $this->logValue($value));
			}

			$this->section('SERVER Data');
			$server = app()->params->getRawSource('server');
			foreach($server as $key => $value){
				$this->write('$_SERVER[\''.$key.'\'] = ' . $this->logValue($value));
			}

			$this->section('FILES Data');
			$server = app()->params->getRawSource('files');
			foreach($server as $key => $value){
				$this->write('$_FILES[\''.$key.'\'] = ' . $this->logValue($value));
			}

			// save all urls/paths to log for debugging
			$this->section('Router Urls & Paths');
			$this->write('Router::domainUrl set to: ' . router()->domainUrl());
			$this->write('Router::appUrl set to: ' . router()->appUrl());
			$this->write('Router::getPath set to: ' . router()->getPath());
			$this->write('Router::interfaceUrl set to: ' . router()->interfaceUrl());
			//$this->write('Router::moduleUrl set to: ' . router()->moduleUrl());
			$this->write('Router::staticUrl set to: ' . router()->staticUrl());
			$this->write('Router::fullUrl set to: ' . router()->fullUrl());

			$this->section('Bootstrap');
			$this->write('Installed checks returned ' . (app()->isInstalled() ? 'true' : 'false'));

			if(app()->checkUserConfigExists()){
				$this->write('Found user config file.');
			} else {
				$this->write('User config was NOT FOUND.');
			}
		}
	}
}
?>