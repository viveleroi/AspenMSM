<?php
/**
 * @abstract
 * @package Aspen Framework
 * @author Michael Botsko, Botsko.net LLC
 * @uses Admin
 */
class Pages {

	/**
	 * @var object Holds our original application
	 * @access private
	 */
	private $APP;


	/**
	 * @abstract Constructor, initializes the module
	 * @return Install_Admin
	 * @access public
	 */
	public function __construct(){
		$this->APP = get_instance();
		director()->registerCmsSection(__CLASS__, 'basic_editor');
		director()->registerCmsSection(__CLASS__, 'imagetext_editor');
	}
	
	
	/**
	 * @abstract Returns an array of meta/content data for a section type
	 * @param array $section_data
	 * @return unknown
	 * @access public
	 */
	public function readSection($section_data){
		if($section_data['section_type'] == 'basic_editor'){
			return $this->readSection_basic($section_data);
		}
		if($section_data['section_type'] == 'imagetext_editor'){
			return $this->readSection_imagetext($section_data);
		}
	}
	
	
	/**
	 * @abstract Returns the meta/content data for section type "basic"
	 * @param array $section_data
	 * @return array
	 * @access private
	 */
	private function readSection_basic($section_data){
	
		$data = array();
	
		// pull the section for the database
		$section_results = $model->query(sprintf('SELECT * FROM section_basic_editor WHERE id = "%s"', $section_data['section_id']));
		if($section_results->RecordCount()){
			while($section_content = $section_results->FetchRow()){
				
				$section_content['type'] = $section_data['section_type'];
				$section_content['placement_group'] = $section_data['group_name'];
				$data['section'] = $section_content;
				
				if(!$section_data['called_in_template']){
					$data['content'] = $section_content;
				}
				
			}
		}

		return $data;
	}
	
	
	/**
	 * @abstract Returns the meta/content data for section type "image text"
	 * @param array $section_data
	 * @return array
	 * @access private
	 */
	private function readSection_imagetext($section_data){
	
		$data = array();
	
		// pull the section for the database
		$section_results = $model->query(sprintf('SELECT * FROM section_imagetext_editor WHERE id = "%s"', $section_data['section_id']));
		if($section_results->RecordCount()){
			while($section_content = $section_results->FetchRow()){
				
				$section_content['type'] = $section_data['section_type'];
				$section_content['placement_group'] = $section_data['group_name'];
				$data['section'] = $section_content;
				
				if(!$section_data['called_in_template']){
					$data['content'] = $section_content;
				}
				
			}
		}

		return $data;
	}
	
	
	/**
	 * @abstract Displays the default display of a section type
	 * @param array $section
	 * @access public
	 * @uses readSection
	 */
	public function displaySection($section, $page, $bits){
		app()->display->loadSectionTemplate('modules/pages', $section['template'], $section, $page, $bits);
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $content
	 */
	public function searchIndexer($content = false){
		$source = array();
		$source[0]['source_type'] 		= $content['type'];
		$source[0]['source_page_id'] 	= $content['page_id'];
		$source[0]['source_id'] 		= $content['id'];
		$source[0]['source_title'] 		= $content['title'];
		$source[0]['source_content'] 	= $content['content'];
		return $source;
	}
}
?>