<?php


/**
 * Shortcut to return an instance of our original app
 * @return object
 */
function &director(){
	return app()->director;
}

/**
 * @abstract This class manages CMS module extensions and page sections
 * @author Michael Botsko
 * @copyright 2008 Botsko.net, LLC
 */
class Director {

	/**
	 * @var array Holds an array of all available sections
	 */
	private $sections = array();


	/**
	 * @abstract Registers a page section for loading within the editing tool
	 * @param unknown_type $module
	 * @param unknown_type $option_text
	 * @param unknown_type $option_value
	 * @access public
	 */
	public function registerPageSection($module = false, $option_text = false, $option_value = false){
		$section = array();
		$section['module'] 			= $module;
		$section['option_value'] 	= $option_value;
		$section['option_text'] 	= $option_text;
		$this->sections[$section['option_value']] = $section;
	}
	
	
	/**
	 * @abstract Returns the entire array of page sections
	 * @return unknown
	 * @access public
	 */
	public function getPageSections(){
		return $this->sections;
	}
	
	
	/**
	 * @abstract Calls the section editor function which provides a full editor
	 * @param unknown_type $type
	 * @param unknown_type $next_id
	 */
	public function loadPageSection($type = false, $next_id = false, $section_data = false, $page_id = false, $template = false, $form = false){
		$load_sec = false;
		// load the proper module/section info that matches
		foreach($this->sections as $section){
			if($section['option_value'] == $type){
				$load_sec = $section;
			}
		}
		// if we have a matched module, load the proper function
		if($load_sec){
			if(isset(app()->{$load_sec['module']}) && method_exists(app()->{$load_sec['module']}, 'sectionEditor')){
				app()->{$load_sec['module']}->sectionEditor($type, $next_id, $section_data, $page_id, $template, $form);
			}
		}
	}
	
	
	/**
	 * @abstract Saves page section data to the main section list, unique data to special tables
	 * @param unknown_type $id
	 * @return unknown
	 */
	public function savePageSections($id){
		
		$sections = array();
		
		$post = post()->getRawSource();

		if(isset($post['page_sections']) && is_array($post['page_sections'])){
		
			// first, wipe all section content so that our saves are new
			foreach($post['page_sections'] as $section){
				if(array_key_exists($section['section_type'], $this->sections)){
					model()->open('section_'.$section['section_type'])->delete($id, 'page_id');
				}
			}
		
			// then, loop and save all sections
			foreach($post['page_sections'] as $val){
				if(array_key_exists($val['section_type'], $this->sections)){
					$section = $this->sections[$val['section_type']];
					if(isset(app()->{$section['module']}) && method_exists(app()->{$section['module']}, 'saveSection')){
						$new_sections = app()->{$section['module']}->saveSection($val, $id);
						$sections = array_merge($sections, $new_sections);
					}
				}
			}
		}

		// return an array of section details, for saving in the section list table
		return $sections;
		
	}
	
	
	/**
	 * @abstract Registers a new section type for display within the cms
	 * @param unknown_type $module
	 * @param unknown_type $type
	 */
	public function registerCmsSection($module = false, $type = false){
		$section = array();
		$section['module'] 				= $module;
		$section['type'] 				= $type;
		$this->sections[] = $section;
	}
	
	
	/**
	 * @abstract Reads page section data into an array for use within the cms
	 * @param unknown_type $section_data
	 * @return unknown
	 */
	public function readPageSections($section_data){
		
		$sections = array();

		foreach($this->sections as $section){
			if($section_data['section_type'] == $section['type']){
				if(isset(app()->{$section['module']}) && method_exists(app()->{$section['module']}, 'readSection')){
					$new_sections = app()->{$section['module']}->readSection($section_data);
					$sections = array_merge($sections, $new_sections);
				}
			}
		}
		var_dump($sections);
		return $sections;
		
	}
	
	
	/**
	 * @abstract Default display of cms page sections
	 * @param unknown_type $content
	 * @param unknown_type $page
	 * @param unknown_type $bits
	 * @return unknown
	 * @uses readPageSections
	 */
	public function displayPageSections($content, $page, $bits){
		
		if(!app()->cms_lib->error()){
		
			$sections = array();
			
			foreach($this->sections as $section){
				if($content['type'] == $section['type']){
					if(isset(app()->{$section['module']}) && method_exists(app()->{$section['module']}, 'displaySection')){
						app()->{$section['module']}->displaySection($content, $page, $bits);
					}
				}
			}
			
			return $sections;
		}
		return false;
	}
	
	
	/**
	 * @abstract Calls the section editor function which provides a full editor
	 * @param unknown_type $type
	 * @param unknown_type $next_id
	 */
	public function loadIndexer($type = false, $content = false){
		
		$load_sec = false;
		$output = false;

		// load the proper module/section info that matches
		foreach($this->sections as $section){
			if($section['type'] == $type){
				$load_sec = $section;
			}
		}
		
		// if we have a matched module, load the proper function
		if($load_sec){
			if(isset(app()->{$load_sec['module']}) && method_exists(app()->{$load_sec['module']}, 'searchIndexer')){
				$output = app()->{$load_sec['module']}->searchIndexer($content);
			}
		}
		
		// if content,
		if(is_array($output)){
			foreach($output as $key => $index){
				
				$clean = preg_replace('/[\n\r]/', ' ', rtrim(strip_tags($index['source_content'])));
				$output[$key]['source_content'] = $clean;
				
			}
		}
		
		return $output;
		
	}
	
	
	/**
	 * @abstract Calls a module search function which replaces the primary
	 * @param unknown_type $type
	 * @param unknown_type $keyword
	 */
	public function moduleSearch($type = false, $keyword = false, $add_params = false){
		
		$load_sec = false;
		$results = false;

		// load the proper module/section info that matches
		foreach($this->sections as $section){
			if(strtolower($section['module']) == strtolower($type)){
				$load_sec = $section;
			}
		}
		
		// if we have a matched module, load the proper function
		if($load_sec){
			if(isset(app()->{$load_sec['module']}) && method_exists(app()->{$load_sec['module']}, 'search')){
				$results = app()->{$load_sec['module']}->search($keyword, $add_params);
			}
		}
		
		return $results;
		
	}
}
?>