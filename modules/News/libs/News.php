<?php


/**
 * 
 */
class News {
	
	
	/**
	 * @abstract Constructor, initializes the module
	 * @return 
	 * @access public
	 */
	public function aspen_init(){
		director()->registerPageSection('news', 'News Display', 'news_display');
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
		$model = model()->open('template_placement_group');
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
}
?>