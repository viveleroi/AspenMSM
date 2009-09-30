<?php

/**
 * Admin Admin class
 *
 * Displays the index/default page for the admin
 * section. In this case, it's our welcome screen /
 * dashboard
 *
 * @package Aspen Framework
 * @author Michael Botsko, Botsko.net LLC
 * @uses Admin
 */
class Admin_Admin extends App {

	/**
	 * @var object Holds our original application
	 * @access public
	 */
	public $APP;


	/**
	 * @abstract Constructor, initializes the module
	 * @return Admin_Admin
	 * @access public
	 */
	public function Admin_Admin(){
		$this->APP = get_instance();
	}


	/**
	 * Loads our index/default welcome/dashboard screen
	 */
	public function view(){
		
		// add all config variables to our form
		$sql = sprintf('SELECT * FROM config');
		$records = $this->APP->model->query($sql);
		if($records->RecordCount()){
			while($record = $records->FetchRow()){

				$value = $record['current_value'] == '' ? $record['default_value'] : $record['current_value'];
				$this->APP->form->addField($record['config_key'], $value, $value);
				
			}
		}
		
		// process the form if submitted
		if($this->APP->form->isSubmitted()){

			if(!$this->APP->form->error()){

				foreach($this->APP->params->getRawSource('post') as $field => $value){

					// create account
					$sql = sprintf('UPDATE config SET current_value = "%s" WHERE config_key = "%s"', $value, $field);
					$this->APP->model->query($sql);
				}
				
				$this->APP->sml->addNewMessage('Website settings have been updated successfully.');
				$this->APP->router->redirect('view');
			}
		}
		
		$data['values'] = $this->APP->form->getCurrentValues();
		
		$this->APP->model->select('pages');
		$this->APP->model->where('page_is_live', 1);
		$data['pages'] = $this->APP->model->results();
		$data['mods'] = $this->APP->moduleControls();
		$data['themes'] = $this->listThemes();
		$data['live'] = $this->APP->settings->getConfig('active_theme');

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'index.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);
		
	}
	
	
	private function listThemes(){
		
		$theme_path = str_replace("modules/Admin", 'themes', dirname(__FILE__));

		$files = array();
		
		if(is_dir($theme_path)){

			// open the folder
			$dir_handle = @opendir($theme_path);
	
			// loop through the files
			while ($file = readdir($dir_handle)) {
	
				if($file != "." && $file != ".." && $file != ".svn"){
	
					// push the date folder into the array
					array_push($files, $file);
	
				}
			}
	
			// close
			closedir($dir_handle);
			
		}
		
		return $files;
		
	}
	
	
	public function ajax_enableTheme($theme = false){
		return $this->APP->settings->setConfig('active_theme', $theme);
	}
}

?>