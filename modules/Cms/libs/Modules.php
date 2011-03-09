<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */


/**
 * Shortcut to return an instance of our original app
 * @return object
 */
function &modules(){
	return app()->modules;
}

/**
 * @abstract Manages modules, registries, etc
 * @package Aspen_Framework
 */
class Modules {

	
	/**
	 * @abstract Loads any modules that are hooked to current module
	 * @param string $guid
	 * @access public
	 */
//	public function callModuleHooks($guid = false){
//		
//		if($guid && app()->checkDbConnection()){
//			
//			$autoload = array();
//			
//			// find any modules with autoload set to current guid
//			$model = model()->open('modules');
//			$model->where('autoload_with', $guid);
//			$modules = $model->results();
//			
//			if($modules){
//				foreach($modules as $module){
//					$autoload[] = $module['guid'];
//				}
//			}
//			
//			// if modules found, let's load them!
//			if(count($autoload) > 0){
//				foreach($autoload as $load_guid){
//					app()->loadModule($load_guid);
//				}
//			}
//		}
//	}
	
	
	/**
	 * @abstract Identifies a module as autoload when parent module is loaded
	 * @param string $parent_guid
	 * @param string $depen_guid
	 * @return boolean
	 * @access public
	 */
//	public function registerModuleHook($parent_guid = false, $depen_guid = false){
//
//		$sql = sprintf('UPDATE modules SET autoload_with = "%s" WHERE guid = "%s"',
//							app()->security->dbescape($parent_guid),
//							app()->security->dbescape($depen_guid));
//		
//		return $model->query($sql);
//		
//	}
	
	
	/**
	 * @abstract Returns an array of module GUIDs that are not part of the standard install
	 * @return array
	 */
	public function getNonBaseModules(){

		$base = array(
			'c3f28790-269f-11dd-bd0b-0800200c9a66', // pages
			'007b300a-fe0c-4f7b-b36f-ef458c32753a', // install
			'652d519c-b7f3-11dc-8314-0800200c9a66', // index
			'eee1d8c0-d50a-11dc-95ff-0800200c9a66', // users
			'2f406120-3f1e-11dd-ae16-0800200c9a66' // cms
		);
		$nonbase = array();

		$all = app()->getModuleRegistry();
		if($all){
			foreach($all as $mod){
				if(!in_array(strtolower($mod->guid), $base)){
					$nonbase[] = (string)$mod->guid;
				}
			}
		}

		return $nonbase;
		
	}
	
	
	/**
	 * @abstract Returns an array of module GUIDs that are not part of the standard install, whether they're installed or not
	 * @return array
	 */
//	public function getAllNonBaseModules(){
//		
//		$nonbase = array();
//		
//		foreach(app()->getModuleRegistry() as $module){
//		
//			// find any modules with autoload set to current guid
//			$model = model()->open('modules');
//			$model->where('guid', (string)$module->guid);
//			$modules = $model->results();
//	
//			if($modules){
//				foreach($modules as $nonbasemod){
//					if(!$nonbasemod['is_base_module']){
//						$nonbase[] = $module->guid;
//					}
//				}
//			} else {
//				$nonbase[] = $module->guid;
//			}
//		}
//		
//		return $nonbase;
//		
//	}
	
	
		/**
	 * @abstract Generates menu items for non-base modules
	 * @return string
	 * @access public
	 */
	public function generateNonBaseModuleLinks(){
		
		$nonbase = $this->getNonBaseModules();
		$menu = '';

		foreach($nonbase as $guid){

			$reg = app()->moduleRegistry($guid);

			if(is_object($reg)){
				if(isset($reg->disable_menu) && $reg->disable_menu){
				} else {
					
					$link = template()->link($reg->name, 'view', false, $reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false));
					
					if(!empty($link)){
						$menu .= '<li'.(router()->here($reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false)) ? ' class="at"' : '').'>';
						$menu .= $link;
						$menu .= '</li>';
					}
				}
			}
		}

		return $menu;

	}
}
?>