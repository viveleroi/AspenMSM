<?php


/**
 * 
 */
class Newslib {
	
	
	/**
	 * @abstract Constructor, initializes the module
	 * @return 
	 * @access public
	 */
	public function aspen_init(){
		if(LS == 'admin'){
			director()->registerPageSection('newslib', 'News Display', 'news_display');
		} else {
			director()->registerCmsSection('newslib', 'news_display');
			director()->registerCmsSection('newslib', 'newsarch_display');
		}
	}

	
	/**
	 * @abstract Displays page section editing form
	 * @param array $section
	 * @param integer $next_id
	 * @access public
	 */
	public function sectionEditor($type = false, $next_id = 1, $section = false, $page_id = false, $template = false, $form = false){
		
		$template = $template ? $template : $form->cv('page_template');
		
		$next_id = isset($section['meta']['id']) ? $section['meta']['id'] : $next_id;
		$model = model()->open('template_placement_groups');
		$model->where('template', $template);
		$placement_groups = $model->results();
		$templates = app()->display->sectionTemplates('modules/news');
		
		$base = str_replace('libs', 'templates_admin', dirname(__FILE__));
		include($base.DS.'section_news.tpl.php');
	}
	
		
	/**
	 * @abstract Saves event display content to the database
	 * @param string $type
	 * @param integer $id
	 * @return array
	 * @access public
	 */
	public function saveSection($section, $page_id){
		
		$sections = array();
						
		// loop new section and add into the db
		if(is_array($section)){
				
			$section['detail_page_id'] = isset($section['detail_page_id']) ? $section['detail_page_id'] : false;
			$section['link_to_full_page'] = isset($section['link_to_full_page']) ? $section['link_to_full_page'] : false;
			$section['show_title'] = isset($section['show_title']) ? $section['show_title'] : false;
			$section['show_description'] = isset($section['show_description']) ? $section['show_description'] : false;

			model()->open('section_news_display')->insert(array(
				'page_id' => $page_id,
				'title' => $section['title'],
				'display_num' => $section['display_num'],
				'link_to_full_page' => $section['link_to_full_page'],
				'detail_page_id' => $section['detail_page_id'],
				'show_title' => $section['show_title'],
				'show_description' => $section['show_description'],
				'template' => $section['template']
			));
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'news_display',
				'called_in_template' => $section['called_in_template'],
				'id' => app()->db->Insert_ID());
		}
		
		return $sections;
		
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
		$section_content = model()->open('section_news_display', $section_data['section_id']);
	
		$section_content['type'] = $section_data['section_type'];
		$section_content['link_to_full_page'] = $section_content['link_to_full_page'];
		if(isset($section_data['group_name'])){
			$section_content['placement_group'] = $section_data['group_name'];
		}
		
		// pull news
		$model = model()->open('news');
		$model->where('public', 1);
		$model->orderBy('timestamp', 'DESC');
		if($section_content['display_num']){
			$model->limit(0, $section_content['display_num']);
		}
		$news = $model->results();

		// if a specific id is set, ensure it exists or 404
		if(app()->cms_lib->getUriBit(1)){
			if(is_array($news)){
				if(!isset($news[ app()->cms_lib->getUriBit(1) ])){
					app()->cms_lib->error_404();
				}
			}
		}

		$section_content['news'] = $news;
		$data['section'] = $section_content;

		if(!$section_data['called_in_template']){
			$data['content'] = $section_content;
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
		$section_results = model()->open('section_newsarch_display', $section_data['section_id']);

		$section_content['type'] = $section_data['section_type'];
		if(isset($section_data['group_name'])){
			$section_content['placement_group'] = $section_data['group_name'];
		}

		// pull news
		$model = model()->open('news');
		$model->where('public', 1);
		$model->orderBy('id', 'ASC');
		if($section_content['display_num']){
			$model->limit(0, $section_content['display_num']);
		}
		$news = $model->results();

		$section_content['news'] = $news;
		$data['section'] = $section_content;

		if(!$section_data['called_in_template']){
			$data['content'] = $section_content;
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
		app()->display->loadSectionTemplate('modules/news', $section['template'], $section, $page, $bits);
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