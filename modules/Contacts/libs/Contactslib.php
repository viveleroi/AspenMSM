<?php


/**
 * 
 */
class Contactslib {
	
	
	/**
	 * @abstract Constructor, initializes the module
	 * @return 
	 * @access public
	 */
	public function aspen_init(){
		director()->registerPageSection('contactslib', 'Contact Display', 'contacts_display');
		director()->registerPageSection('contactslib', 'Contact Group Display', 'contactgroup_display');
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
		if($type == 'contacts_display'){
			$templates = app()->display->sectionTemplates('modules/contacts/contacts');
			include($base.DS.'section_contacts.tpl.php');
		} else {
			$templates = app()->display->sectionTemplates('modules/contacts/groups');
			include($base.DS.'section_group.tpl.php');
		}
		
	}
	
		
	/**
	 * @abstract Saves event display content to the database
	 * @param string $type
	 * @param integer $id
	 * @return array
	 * @access public
	 */
	public function saveSection($section, $page_id){

		// loop new section and add into the db
		if(is_array($section)){
			if($section['section_type'] == 'contacts_display'){
				return $this->saveSection_contact($section, $page_id);
			}
			
			if($section['section_type'] == 'contactgroup_display'){
				return $this->saveSection_group($section, $page_id);
			}
		}
	}
	
	
/**
	 * Enter description here...
	 *
	 * @param unknown_type $section
	 * @param unknown_type $page_id
	 * @return unknown
	 */
	public function saveSection_contact($section, $page_id){
		
		$sections = array();

		// loop new section and add into the db
		if(is_array($section)){
			
			$section['show_title'] = isset($section['show_title']) ? $section['show_title'] : false;
			
			model()->open('section_contacts_display')->query(sprintf('
				INSERT INTO section_contacts_display (page_id, title, show_title, template, link_to_full_page, detail_page_id, contact_id)
				VALUES ("%s", "%s", "%s", "%s", "%s", "%s", "%s")',
					app()->security->dbescape($page_id),
					app()->security->dbescape($section['title']),
					app()->security->dbescape($section['show_title']),
					app()->security->dbescape($section['template']),
					app()->security->dbescape($section['link_to_full_page']),
					app()->security->dbescape($section['detail_page_id']),
					app()->security->dbescape($section['contact_id'])));
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'contacts_display',
				'called_in_template' => $section['called_in_template'],
				'id' => app()->db->Insert_ID());
		
		}
		
		return $sections;
		
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $section
	 * @param unknown_type $page_id
	 * @return unknown
	 */
	public function saveSection_group($section, $page_id){
		
		$sections = array();

		// loop new section and add into the db
		if(is_array($section)){
			
			$section['show_title'] = isset($section['show_title']) ? $section['show_title'] : false;
			
			model()->open('section_contactgroup_display')->query(sprintf('
				INSERT INTO section_contactgroup_display (page_id, title, show_title, template, group_id, link_to_full_page, detail_page_id, sort_method)
				VALUES ("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s")',
					app()->security->dbescape($page_id),
					app()->security->dbescape($section['title']),
					app()->security->dbescape($section['show_title']),
					app()->security->dbescape($section['template']),
					app()->security->dbescape($section['group_id']),
					app()->security->dbescape($section['link_to_full_page']),
					app()->security->dbescape($section['detail_page_id']),
					app()->security->dbescape($section['sort_method'])));
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'contactgroup_display',
				'called_in_template' => $section['called_in_template'],
				'id' => app()->db->Insert_ID());
		
		}
		
		return $sections;
		
	}
}
?>