<?php


/**
 * 
 */
class Formslib {
	
	
	/**
	 * @abstract Constructor, initializes the module
	 * @return 
	 * @access public
	 */
	public function aspen_init(){
		director()->registerPageSection('formslib', 'Form Display', 'form_display');
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
		
		$base = str_replace('libs', 'templates_admin', dirname(__FILE__));
		include($base.DS.'section_form.tpl.php');
	
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
				
			$section['link_to_full_page'] = isset($section['link_to_full_page']) ? $section['link_to_full_page'] : false;
			$section['show_title'] = isset($section['show_title']) ? $section['show_title'] : false;
			
			model()->open('section_form_display')->query(sprintf('
				INSERT INTO section_form_display (page_id, title, form_id, show_title)
				VALUES ("%s", "%s", "%s", "%s")',
					app()->security->dbescape($page_id),
					app()->security->dbescape($section['title']),
					app()->security->dbescape($section['form_id']),
					app()->security->dbescape($section['show_title'])));
					
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'form_display',
				'called_in_template' => $section['called_in_template'],
				'id' => app()->db->Insert_ID());
		}
		
		return $sections;
		
	}
}
?>