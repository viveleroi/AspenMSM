<?php
/**
 * @abstract Events Admin class - Allows an admin user to manage an events list
 * @package Aspen Framework
 * @author Michael Botsko, Botsko.net LLC
 * @uses Admin
 */
class News {

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
		director()->registerCmsSection(__CLASS__, 'news_display');
		director()->registerCmsSection(__CLASS__, 'newsarch_display');
	}
	
	
	/**
	 * @abstract Returns an array of meta/content data for a section type
	 * @param array $section_data
	 * @return unknown
	 * @access public
	 */
	public function readSection($section_data){
		if($section_data['section_type'] == 'news_display'){
			return $this->readSection_news($section_data);
		}
		if($section_data['section_type'] == 'newsarch_display'){
			return $this->readSection_newsarch($section_data);
		}
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $section_data
	 * @return unknown
	 */
	public function readSection_news($section_data){
		
		$data = array();
		
		// pull the section for the database
		$section_results = $model->query(sprintf('SELECT * FROM section_news_display WHERE id = "%s"', $section_data['section_id']));
		
		if($section_results->RecordCount()){
			while($section_content = $section_results->FetchRow()){
				$section_content['type'] = $section_data['section_type'];
				$section_content['link_to_full_page'] = $section_content['link_to_full_page'];
				$section_content['placement_group'] = $section_data['group_name'];
				
				// pull news
				$model = model()->open('news');
				$model->where('public', 1);
				$model->orderBy('timestamp', 'DESC');
				if($section_content['display_num']){
					$model->limit(0, $section_content['display_num']);
				}
				$news = $model->results();
				
				// if a specific id is set, ensure it exists or 404
				if($this->APP->cms_lib->getUriBit(1)){
					if(is_array($news)){
						if(!isset($news['RECORDS'][ $this->APP->cms_lib->getUriBit(1) ])){
							$this->APP->cms_lib->error_404();
						}
					}
				}
				
				$section_content['news'] = $news['RECORDS'];
				$data['section'] = $section_content;
				
				if(!$section_data['called_in_template']){
					$data['content'] = $section_content;
				}
			}
		}
		
		return $data;

	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $section_data
	 * @return unknown
	 */
	public function readSection_newsarch($section_data){
		
		$data = array();
		
		// pull the section for the database
		$section_results = $model->query(sprintf('SELECT * FROM section_newsarch_display WHERE id = "%s"', $section_data['section_id']));
		
		if($section_results->RecordCount()){
			while($section_content = $section_results->FetchRow()){
				$section_content['type'] = $section_data['section_type'];
				$section_content['placement_group'] = $section_data['group_name'];
				
				// pull news
				$model = model()->open('news');
				$model->where('public', 1);
				$model->orderBy('news_id', 'ASC');
				if($section_content['display_num']){
					$model->limit(0, $section_content['display_num']);
				}
				$news = $model->results();
				
				$section_content['news'] = $news['RECORDS'];
				$data['section'] = $section_content;
				
				if(!$section_data['called_in_template']){
					$data['content'] = $section_content;
				}
			}
		}
		
		return $data;

	}
	
	
	/**
	 * @abstract Displays the basic section
	 * @param unknown_type $section
	 * @param unknown_type $page
	 * @param unknown_type $bits
	 */
	public function displaySection($section, $page, $bits){
		$this->APP->display->loadSectionTemplate('modules/news', $section['template'], $section, $page, $bits);
	}
	
	
	/**
	 * @abstract Parses and returns proper content for the search indexer
	 * @param array $content
	 */
	public function searchIndexer($content = false){
		$source = array();
		if($content['news']){
			foreach($content['news'] as $news){
				$source[0]['source_type'] 		= $content['type'];
				$source[0]['source_page_id'] 	= $content['page_id'];
				$source[0]['source_id'] 		= $content['id'];
				$source[0]['source_title'] 		= $news['title'];
				$source[0]['source_content'] 	= $news['summary'] . ' ' . $news['body'] . ' ' . $news['pdf_filename'];
			}
		}
		return $source;
	}
}
?>