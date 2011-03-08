<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract Handles application and module installation
 * @package Aspen_Framework
 */
class Install {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;
	
	/**
	 * @var boolean $supported Whether or not the server is supported
	 * @access private
	 */
	private $supported;
	

	/**
	 * @abstract Constructor, initializes the module
	 * @return Install
	 * @access public
	 */
	public function __construct(){
		$this->APP = get_instance();
		$this->checkSystemCompatibility();
	}
	
	
	/**
	 * @abstract Runs a quick system prerequisite/versions check.
	 * @access private
	 */
	private function checkSystemCompatibility(){
	
		$supported = extension_loaded('mysql');
		$supported = $supported ? version_compare(PHP_VERSION, app()->config('minimum_version_php'), '>=') : false;
		 
		$this->supported = $supported;
		
	}
	
	
	/**
	 * @abstract Returns whether or not the system prereq check passed
	 * @return public
	 */
	public function isSupported(){
		return $this->supported;
	}

	
	/**
	 * @abstract Runs prerequisites check, if good sends user to config setup or account creation
	 * @access public
	 */
	public function prereq(){
		
		template()->addView(template()->getTemplateDir().DS . 'header.tpl.php');
		template()->addView(template()->getModuleTemplateDir().DS . 'index.tpl.php');
		template()->addView(template()->getTemplateDir().DS . 'footer.tpl.php');
		template()->display();
			
	}
	
	
	/**
	 * @abstract Runs prerequisites check, if good sends user to config setup or account creation
	 * @access public
	 */
	public function beginInstallProcess(){
		
		$_SESSION = array();
		session_destroy();
		
		if(!$this->isSupported()){

			template()->addView(template()->getTemplateDir().DS . 'header.tpl.php');
			template()->addView(template()->getModuleTemplateDir().DS . 'index.tpl.php');
			template()->addView(template()->getTemplateDir().DS . 'footer.tpl.php');
			template()->display();
			
		} else {
	
			// if the config file exists, proceed to creating an account
			if(app()->checkUserConfigExists()){
				app()->router->redirect('account');
			} else {
				app()->router->redirect('setup');
			}
		}
	}
	
	
	/**
	 * @abstract Users sets up their database / config file here
	 * @access public
	 */
	public function setup($retry = false){
		
		// define the form
		app()->form->addFields(array('db_username', 'db_password', 'db_database', 'db_hostname'));

		// process the form if submitted
		if(app()->form->isSubmitted('post', 'submit')){

			// validation
			if(!app()->form->isFilled('db_username')){
				app()->form->addError('db_username', 'You must enter a username');
			}
			
			if(!app()->form->isFilled('db_database')){
				app()->form->addError('db_database', 'You must enter a database name.');
			}
			
			if(!app()->form->isFilled('db_hostname')){
				app()->form->addError('db_hostname', 'You must enter a hostname or ip address.');
			}
			

			// if no error, proceed with setting up config file
			if(!app()->form->error()){
				
				// save the config to a file
				$fill = "<?php\n";
				$fill .= '$config[\'db_hostname\'] = \''.	app()->form->cv('db_hostname')	."';\n";
				$fill .= '$config[\'db_database\'] = \''.	app()->form->cv('db_database')	."';\n";
				$fill .= '$config[\'db_username\'] = \''.	app()->form->cv('db_username')	."';\n";
				$fill .= '$config[\'db_password\'] = \''.	app()->form->cv('db_password')	."';\n";
				$fill .= '?>';

				// check if we can write the config file ourselves
				if(touch(APPLICATION_PATH . DS . 'config.php')){
				
					app()->file->useFile(APPLICATION_PATH . DS . 'config.php');
					if(!app()->file->write($fill, 'w')){
					
						$this->paste_config($fill);
						exit;
					
					}
				} else {
					
					$this->paste_config($fill);
					exit;
					
				}
			}
		}
		
		// if the config file exists and db connection works, send on
		if(!app()->checkUserConfigExists() || $retry){

			template()->addView(template()->getTemplateDir().DS . 'header.tpl.php');
			template()->addView(template()->getModuleTemplateDir().DS . 'setup_config.tpl.php');
			template()->addView(template()->getTemplateDir().DS . 'footer.tpl.php');
			template()->display();
			
		} else {
			
			app()->router->redirect('account');
			
		}
	}
	
	
	/**
	 * @abstract Display config contents for creating files
	 * @access public
	 */
	public function paste_config($config){

		// check if config file exists
		if(!app()->checkUserConfigExists()){
			template()->addView(template()->getTemplateDir().DS . 'header.tpl.php');
			template()->addView(template()->getModuleTemplateDir().DS . 'paste_config.tpl.php');
			template()->addView(template()->getTemplateDir().DS . 'footer.tpl.php');
			template()->display(array('config' => $config));
		} else {
			app()->router->redirect('account');
		}
	}
	
	
	/**
	 * @abstract User creates the basic account at this point
	 * @access public
	 */
	public function account(){
		
		// If no config file present we cannot proceed - sends user back to setup config
		if(!app()->checkUserConfigExists()){
			app()->sml->addNewMessage('We were unable to find a configuration file. Please try again.');
			app()->router->redirect('setup', array('retry' => 'retry') );
		}

		// if the config exists and we're not creating an account, attempt to install the base tables
		if(!app()->form->isSubmitted('post', 'submit')){
			if(app()->db){
				
				// if no tables exist yet
				if(!count(app()->db->MetaTables('TABLES'))){
					// attempt to install our base tables
					if(!$this->installBaseTables()){
						unlink('../config.php');
						app()->sml->addNewMessage('There was an error installing database tables. Please try again.');
						app()->router->redirect('setup', array('retry' => 'retry') );
					}
				}
			} else {
			
				app()->sml->addNewMessage('We were unable to connect to the database using your current configuration. Please try again.');
				app()->router->redirect('setup', array('retry' => 'retry') );
			
			}
		}
		
		
		app()->form->addFields(array('email', 'nice_name', 'password_1', 'password_2'));

		// process the form if submitted
		if(app()->form->isSubmitted('post', 'submit')){

			// validation
			if(!app()->form->isEmail('email')){
				app()->form->addError('email', 'You must enter a valid email address.');
			}
			
			if(!app()->form->isFilled('password_1')){
				app()->form->addError('password_1', 'You must enter a password.');
			}
			
			if(!app()->form->fieldsMatch('password_1', 'password_2')){
				app()->form->addError('password_1', 'Your passwords must match.');
			}

			if(!app()->form->error()){

				// create account
				$account_sql_tmpl = 'INSERT INTO authentication (username, nice_name, password) VALUES ("%s", "%s", "%s")';
				$account_sql = sprintf($account_sql_tmpl,
											app()->form->cv('email'),
											app()->form->cv('nice_name'),
											sha1(app()->form->cv('password_1')));
			
				if($model->query($account_sql)){
				
					app()->router->redirect('success');
				
				} else {

					app()->sml->addNewMessage('We were unable to create your account. Please try again.');
				
				}
			}
		}

		template()->addView(template()->getTemplateDir().DS . 'header.tpl.php');
		template()->addView(template()->getModuleTemplateDir().DS . 'create_account.tpl.php');
		template()->addView(template()->getTemplateDir().DS . 'footer.tpl.php');
		template()->display();

	}
	
	
	/**
	 * @abstract Displays our installation success page
	 * @access public
	 */
	public function success(){
		
		template()->addView(template()->getTemplateDir().DS . 'header.tpl.php');
		template()->addView(template()->getModuleTemplateDir().DS . 'success.tpl.php');
		template()->addView(template()->getTemplateDir().DS . 'footer.tpl.php');
		template()->display();
		
	}
	
	
	/**
	 * @abstract Runs the sql needed for installation of the basic app
	 * @access private
	 */
	private function installBaseTables(){
		
		$sql = array();
		
		$sql_path = app()->router->getModulePath() . DS . 'sql' . DS. 'install.sql.php';
		// include file with all install queries
		if(file_exists($sql_path)){
			include($sql_path);
		}
		
		$success = false;
	
		foreach($sql as $query){
			$success = $model->query($query);
		}
		
		if($success){
			$this->recordCurrentBuild();
		}
	
		return $success;
	
	}
	
	
	/**
	 * @abstract Records the current build in the upgrade history table
	 * @access private
	 */
	private function recordCurrentBuild(){
		$model->executeInsert('upgrade_history', array('current_build' => app()->formatVersionNumber(app()->config('application_version')), 'upgrade_completed' => date("Y-m-d H:i:s")));
	}
	
	
	/**
	 * @abstract Displays a message that a database update is required
	 * @access public
	 */
	public function upgrade(){
		
		template()->addView(template()->getTemplateDir().DS . 'header.tpl.php');
		template()->addView(template()->getModuleTemplateDir().DS . 'upgrade.tpl.php');
		template()->addView(template()->getTemplateDir().DS . 'footer.tpl.php');
		template()->display();
		
	}
	
	
	/**
	 * @abstract Processes the actual database upgrade
	 * @access public
	 */
	public function run_upgrade(){
		
		$sql_path = app()->router->getModulePath() . DS . 'sql' . DS. 'upgrade.sql.php';
		$success = false;
		
		// include file with all upgrade queries
		if(file_exists($sql_path)){
			include($sql_path);
		}
		
		if(isset($sql) && is_array($sql)){
			
			$my_old_build = app()->latestVersion();
			
			foreach($sql as $query_build => $queries){
				
				// the query build is after my old build, then apply the upgrade
				if((int)$my_old_build < (int)$query_build){
				
					// if array, run all queries
					if(isset($queries) && is_array($queries)){
						foreach($queries as $query){
							$success = $model->query($query);
						}
					}
				}
			}
			
			// update the upgrade history
			if($success){
				
				$this->recordCurrentBuild();
				
				app()->sml->addNewMessage('Your database has been upgraded.');
				app()->router->redirect('view', false, app()->config('default_module'));
				
			}
		}
		
		app()->sml->addNewMessage('No upgrade actions were performed.');
		app()->router->redirect('view', false, app()->config('default_module'));
		
	}
	
	
	/**
	 * @abstract Registers a module (inserts a modules guid into the modules table)
	 * @param string $guid
	 * @access public
	 */
	public function install_module($guid){
		
		$modules = app()->getModulesAwaitingInstall();
		foreach($modules as $module){
			if($guid == $module['guid']){
				if($model->executeInsert('modules', array('guid' => $guid))){
					
					// refresh installed module guid list
					app()->listModules();
					
					// load the module and run the insert code
					$tmp_reg = app()->moduleRegistry($guid);

					if(isset($tmp_reg->classname)){
					
						$classname = $tmp_reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false);
						app()->loadModule($guid);
						
						// call module install process if it exists
						if(method_exists(app()->{$classname}, 'install')){
							app()->{$classname}->install($guid);
						}
						
						app()->sml->addNewMessage('The ' . $tmp_reg->classname . ' module has been installed successfully.');
						
					}
				}
			}
		}
		
		app()->router->returnToReferrer();
		
	}
	
	
	/**
	 * @abstract Used to run uninstallation code from module
	 * @param string $guid
	 * @access public
	 */
	public function uninstall_module($guid){
					
		// load the module and run the uninstall code
		$tmp_reg = app()->moduleRegistry($guid);

		if(isset($tmp_reg->classname)){
			$classname = $tmp_reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false);
			app()->loadModule($guid);
			
			// call module uninstall function if available
			if(method_exists(app()->{$classname}, 'uninstall')){
				app()->{$classname}->uninstall($guid);
			}
				
			// remove all connections from databases
			$model->query('DELETE FROM modules WHERE guid = "'.$guid.'"');
			$model->query('UPDATE modules SET autoload_with = "" WHERE autoload_with = "'.$guid.'"');
			$model->query('DELETE FROM permissions WHERE module = "'.$tmp_reg->classname.'"');
			
			app()->sml->addNewMessage('The ' . $tmp_reg->classname . ' module has been uninstalled successfully.');
				
		}
		
		app()->router->returnToReferrer();
		
	}
}
?>