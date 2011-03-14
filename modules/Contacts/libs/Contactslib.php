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
		if(LS == 'admin'){
			director()->registerPageSection('contactslib', 'Contact Display', 'contacts_display');
			director()->registerPageSection('contactslib', 'Contact Group Display', 'contactgroup_display');
		} else {
			director()->registerCmsSection('contactslib', 'contacts_display');
			director()->registerCmsSection('contactslib', 'contactgroup_display');
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
				INSERT INTO section_contacts_display (page_id, title, show_title, template, contact_id)
				VALUES ("%s", "%s", "%s", "%s", "%s")',
					app()->security->dbescape($page_id),
					app()->security->dbescape($section['title']),
					app()->security->dbescape($section['show_title']),
					app()->security->dbescape($section['template']),
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
				INSERT INTO section_contactgroup_display (page_id, title, show_title, template, group_id, sort_method)
				VALUES ("%s", "%s", "%s", "%s", "%s", "%s")',
					app()->security->dbescape($page_id),
					app()->security->dbescape($section['title']),
					app()->security->dbescape($section['show_title']),
					app()->security->dbescape($section['template']),
					app()->security->dbescape($section['group_id']),
					app()->security->dbescape($section['sort_method'])));
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'contactgroup_display',
				'called_in_template' => $section['called_in_template'],
				'id' => app()->db->Insert_ID());
		
		}
		
		return $sections;
		
	}
	
	
	/**
	 * @abstract Returns the section information from the db
	 * @param array $section_data
	 * @return array
	 */
	public function readSection($section_data){
		if($section_data['section_type'] == 'contacts_display'){
			return $this->readSection_contact($section_data);
		}
		if($section_data['section_type'] == 'contactgroup_display'){
			return $this->readSection_groups($section_data);
		}
	}
	
	
	/**
	 * @abstract Returns the section information from the db
	 * @param array $section_data
	 * @return array
	 */
	public function readSection_contact($section_data){
		
		$data = array();

		$section_content = model()->open('section_'.$section_data['section_type'], $section_data['section_id']);
				
		$section_content['type'] = $section_data['section_type'];
		$section_content['placement_group'] = $section_data['group_name'];

		$model = model()->open('contacts');
		$model->where('id', $section_content['contact_id']);
		$results = $model->results();

		$section_content['results'] = $results;

		if($results){
			foreach($results as $key => $contact){
				$related = $this->pullRelatedContactContent($contact['id']);
				$results[$key] = array_merge($results[$key], $related);
			}
		} else {
			$results = model()->open('contacts', app()->cms_lib->getUriBit(1));
			if($results){
				$related = $this->pullRelatedContactContent($results['id']);
				$results = array_merge($results, $related);
				$section_content['contacts'] = array($results['id']=>$results);
			} else {
				app()->cms_lib->error_404();
			}
		}
		$data['section'] = $section_content;

		if(!$section_data['called_in_template']){
			$data['content'] = $section_content;
		}
	
		return $data;
	}
	
	
	/**
	 * @abstract Returns the section information from the db
	 * @param array $section_data
	 * @return array
	 */
	public function readSection_groups($section_data){
		
		$data = array();

		$section_content = model()->open('section_'.$section_data['section_type'], $section_data['section_id']);
				
		$section_content['type'] = $section_data['section_type'];
		$section_content['placement_group'] = $section_data['group_name'];

		// pull the groups
		$model = model()->open('contact_groups');
		$model->where('id', $section_content['group_id']);
		$groups = $model->results();

		if($groups){
			foreach($groups as $g_id => $group){

				$model = model()->open('contacts');
				$model->leftJoin('contact_groups_link', 'contact_id', 'id', array('group_id'));
				$model->where('group_id', $g_id);

				if($section_content['sort_method'] == 'sort_order'){
					$model->orderBy('sort_order, last_name, first_name');
				} else {
					$model->orderBy('last_name, first_name');
				}

				$groups[$g_id]['contacts'] = $model->results();

				if($groups[$g_id]['contacts']){
					foreach($groups[$g_id]['contacts'] as $key => $contact){

						$related = $this->pullRelatedContactContent($contact['id']);
						$groups[$g_id]['contacts'][$key] = array_merge($groups[$g_id]['contacts'][$key], $related);

					}
				}
			}
		}

		$section_content['results'] = $groups;
		$data['section'] = $section_content;

		if(!$section_data['called_in_template']){
			$data['content'] = $section_content;
		}

		return $data;

	}


	/**
	 * @abtract Pulls related contact info
	 * @param <type> $contact_id
	 * @return <type>
	 */
	private function pullRelatedContactContent($contact_id){

		$content = array();

		// pull images
		$model = model()->open('contact_images');
		$model->where('contact_id', $contact_id);
		$content['images'] = $model->results();

		// pull languages
		$model = model()->open('contact_languages');
		$model->leftJoin('contact_languages_link', 'language_id', 'id', array('contact_id'));
		$model->where('contact_languages_link.contact_id', $contact_id);
		$content['languages'] = $model->results();

		// pull groups
		$model = model()->open('contact_groups');
		$model->leftJoin('contact_groups_link', 'group_id', 'id', array('contact_id'));
		$model->where('contact_groups_link.contact_id', $contact_id);
		$content['groups'] = $model->results();

		// pull specialties
		$model = model()->open('contact_specialties');
		$model->leftJoin('contact_specialties_link', 'specialty_id', 'id', array('contact_id'));
		$model->where('contact_specialties_link.contact_id', $contact_id);
		$content['specialties'] = $model->results();

		return $content;

	}

	
	/**
	 * @abstract
	 * @param array $section
	 * @param array $page
	 * @param array $bits
	 */
	public function displaySection($section, $page, $bits){
		if($section['type'] == 'contacts_display'){
			app()->display->loadSectionTemplate('modules/contacts/contacts', $section['template'], $section, $page, $bits);
		}
		if($section['type'] == 'contactgroup_display'){
			app()->display->loadSectionTemplate('modules/contacts/groups', $section['template'], $section, $page, $bits);
		}
	}


	/**
	 *
	 * @return <type> 
	 */
	public function specialtyList(){
		$model = model()->open('contact_specialties');
		$model->orderBy('specialty');
		return $model->results();
	}
	
	
	/**
	 * @abstract Performs a keyword search on the content index
	 * @return mixed
	 */
	public function search($keyword = false, $add_params = false){

		$group		= false;
		$first_name	= get()->getRaw('first_name');
		$last_name	= get()->getRaw('last_name');
		$specialty	= get()->getRaw('specialty');

		if(is_array($add_params)){
			foreach($add_params as $var => $value){
				$$var = $value;
			}
		}

		$model->enablePagination();
		$model = model()->open('contacts');

		if($group){
			$model->leftJoin('contact_groups_link', 'contact_id', 'id', array('group_id'));
			$model->where('contact_groups_link.group_id', $group);
		}

		if($first_name){
			$model->where('first_name', $first_name);
		}

		if($last_name){
			$model->where('last_name', $last_name);
		}

		if($specialty){
			$model->leftJoin('contact_specialties_link', 'contact_id', 'id', array('specialty_id'));
			$model->where('contact_specialties_link.specialty_id', $specialty);
		} else {
			$model->leftJoin('contact_specialties_link', 'contact_id', 'id', array('specialty_id'));
			$model->leftJoin('contact_specialties', 'id', 'specialty_id', array('specialty'), 'contact_specialties_link');
			$model->where('contact_specialties.specialty', $keyword);
		}

		if($keyword && !$first_name && !$last_name){
			$model->match($keyword, false, 'AND', array('contact_specialties.specialty'));
		}

		$model->paginate(get()->getRaw('page'), app()->config('search_results_per_page'));
		$model->orderBy('match_relevance DESC, last_name, first_name');
//		print $model->getBuildQuery();
		$results = $model->results();

		if($results){
			foreach($results as $key => $contact){

				$related = $this->pullRelatedContactContent($contact['id']);
				$results[$key] = array_merge($results[$key], $related);

			}
		}

		app()->search->paginator_info['records'] 	= $results['TOTAL_RECORDS_FOUND'];
		app()->search->paginator_info['current'] 	= $results['CURRENT_PAGE'];
		app()->search->paginator_info['per_page'] 	= $results['RESULTS_PER_PAGE'];
		app()->search->paginator_info['pages'] 	= $results['TOTAL_PAGE_COUNT'];

		return $results;

	}
}
?>