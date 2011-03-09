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
		$records = $model->query($sql);
		if($records->RecordCount()){
			while($record = $records->FetchRow()){

				$value = $record['current_value'] == '' ? $record['default_value'] : $record['current_value'];
				$form->addField($record['config_key'], $value, $value);
				
			}
		}
		
		// process the form if submitted
		if($form->isSubmitted()){

			if(!$form->error()){

				foreach(app()->params->getRawSource('post') as $field => $value){

					// create account
					$sql = sprintf('UPDATE config SET current_value = "%s" WHERE config_key = "%s"', $value, $field);
					$model->query($sql);
				}
				
				app()->sml->addNewMessage('Website settings have been updated successfully.');
				router()->redirect('view');
			}
		}
		
		$data['form'] = $form;
		
		$model = model()->open('pages');
		$model->where('page_is_live', 1);
		$data['pages'] = $model->results();
		$data['mods'] = app()->moduleControls();
		$data['themes'] = $this->listThemes();
		$data['live'] = settings()->getConfig('active_theme');

		template()->addView(template()->getTemplateDir().DS . 'header.tpl.php');
		template()->addView(template()->getModuleTemplateDir().DS . 'index.tpl.php');
		template()->addView(template()->getTemplateDir().DS . 'footer.tpl.php');
		template()->display($data);
		
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
		return settings()->setConfig('active_theme', $theme);
	}
}

?>