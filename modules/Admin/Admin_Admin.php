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
class Admin_Admin {
	

	/**
	 * Loads our index/default welcome/dashboard screen
	 */
	public function view(){
		
		$form = new Form('config');
		
		// add all config variables to our form
		$sql = sprintf('SELECT * FROM config');
		$model = model()->open('config');
		$records = $model->results();
		if($records){
			foreach($records as $record){
				$value = ($record['current_value'] == '' ? $record['default_value'] : $record['current_value']);
				$form->addField($record['config_key'], $value, $value);
			}
		}
		
		// process the form if submitted
		if($form->isSubmitted()){
				foreach(app()->params->getRawSource('post') as $field => $value){
					$model->update(array('current_value'=>$value), $field, 'config_key');
				}
				sml()->say('Website settings have been updated successfully.');
				router()->redirect('view');
		}
		$data['form'] = $form;
		
		$model = model()->open('pages');
		$model->where('page_is_live', 1);
		$data['pages'] = $model->results();
//		$data['mods'] = app()->moduleControls();
		$data['themes'] = $this->listThemes();
		$data['live'] = settings()->getConfig('active_theme');

		template()->addCss('style.css');
		template()->addJs('admin/jquery.qtip.js');
		template()->addJs('view.js');
		template()->display($data);
		
	}
	
	
	/**
	 *
	 * @return array 
	 */
	private function listThemes(){
		$theme_path = str_replace("modules/Admin", 'themes', dirname(__FILE__));
		$files = array();
		if(is_dir($theme_path)){
			$dir_handle = @opendir($theme_path);
			while ($file = readdir($dir_handle)) {
				if($file != "." && $file != ".." && $file != ".svn"){
					array_push($files, $file);
				}
			}
			closedir($dir_handle);
		}
		return $files;
	}
	
	
	/**
	 *
	 * @param type $theme
	 * @return type 
	 */
	public function ajax_enableTheme($theme = false){
		return settings()->setConfig('active_theme', $theme);
	}
}
?>