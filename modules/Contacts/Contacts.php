<?php
/**
 * @abstract
 * @package
 * @author Michael Botsko, Botsko.net LLC
 * @uses Admin
 */
class Contacts extends Display {


	/**
	 * @abstract Constructor, initializes the module
	 * @return Install_Admin
	 * @access public
	 */
	public function __construct(){
		parent::__construct();
		director()->registerCmsSection(__CLASS__, 'contacts_display');
		director()->registerCmsSection(__CLASS__, 'contactgroup_display');
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

		$section_results = $model->query(sprintf('SELECT * FROM section_%s WHERE id = "%s"', $section_data['section_type'], $section_data['section_id']));

		if($section_results->RecordCount()){
			while($section_content = $section_results->FetchRow()){
				
				$section_content['type'] = $section_data['section_type'];
				$section_content['placement_group'] = $section_data['group_name'];

				$model = model()->open('contacts');
				$model->where('id', $section_content['contact_id']);
				$results = $model->results();
				
				$section_content['results'] = $results['RECORDS'];

				if($results['RECORDS']){
					foreach($results['RECORDS'] as $key => $contact){
						$related = $this->pullRelatedContactContent($contact['id']);
						$results['RECORDS'][$key] = array_merge($results['RECORDS'][$key], $related);
					}
				} else {
					$results = $model->quickSelectSingle('contacts', $this->APP->cms_lib->getUriBit(1));
					if($results){
						$related = $this->pullRelatedContactContent($results['id']);
						$results = array_merge($results, $related);
						$section_content['contacts'] = array($results['id']=>$results);
					} else {
						$this->APP->cms_lib->error_404();
					}
				}
				$data['section'] = $section_content;
				
				if(!$section_data['called_in_template']){
					$data['content'] = $section_content;
				}
			}
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

		$section_results = $model->query(sprintf('SELECT * FROM section_%s WHERE id = "%s"', $section_data['section_type'], $section_data['section_id']));
		
		if($section_results->RecordCount()){
			while($section_content = $section_results->FetchRow()){
				
				$section_content['type'] = $section_data['section_type'];
				$section_content['placement_group'] = $section_data['group_name'];

				// pull the groups
				$model = model()->open('contact_groups');
				$model->where('id', $section_content['group_id']);
				$groups = $model->results();
				
				if($groups['RECORDS']){
					foreach($groups['RECORDS'] as $g_id => $group){
						
						$model = model()->open('contacts');
						$model->leftJoin('contact_groups_link', 'contact_id', 'id', array('group_id'));
						$model->where('group_id', $g_id);

						if($section_content['sort_method'] == 'sort_order'){
							$model->orderBy('sort_order, last_name, first_name');
						} else {
							$model->orderBy('last_name, first_name');
						}
						
						$groups['RECORDS'][$g_id]['contacts'] = $model->results();
						
						if($groups['RECORDS'][$g_id]['contacts']['RECORDS']){
							foreach($groups['RECORDS'][$g_id]['contacts']['RECORDS'] as $key => $contact){

								$related = $this->pullRelatedContactContent($contact['id']);
								$groups['RECORDS'][$g_id]['contacts']['RECORDS'][$key] = array_merge($groups['RECORDS'][$g_id]['contacts']['RECORDS'][$key], $related);

							}
						}
					}
				}
				
				$section_content['results'] = $groups['RECORDS'];
				$data['section'] = $section_content;
				
				if(!$section_data['called_in_template']){
					$data['content'] = $section_content;
				}
			}
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
			$this->APP->display->loadSectionTemplate('modules/contacts/contacts', $section['template'], $section, $page, $bits);
		}
		if($section['type'] == 'contactgroup_display'){
			$this->APP->display->loadSectionTemplate('modules/contacts/groups', $section['template'], $section, $page, $bits);
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
		$first_name	= $this->APP->params->get->getRaw('first_name');
		$last_name	= $this->APP->params->get->getRaw('last_name');
		$specialty	= $this->APP->params->get->getRaw('specialty');

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

		$model->paginate($this->APP->params->get->getRaw('page'), $this->APP->config('search_results_per_page'));
		$model->orderBy('match_relevance DESC, last_name, first_name');
//		print $model->getBuildQuery();
		$results = $model->results();

		if($results['RECORDS']){
			foreach($results['RECORDS'] as $key => $contact){

				$related = $this->pullRelatedContactContent($contact['id']);
				$results['RECORDS'][$key] = array_merge($results['RECORDS'][$key], $related);

			}
		}

		$this->APP->search->paginator_info['records'] 	= $results['TOTAL_RECORDS_FOUND'];
		$this->APP->search->paginator_info['current'] 	= $results['CURRENT_PAGE'];
		$this->APP->search->paginator_info['per_page'] 	= $results['RESULTS_PER_PAGE'];
		$this->APP->search->paginator_info['pages'] 	= $results['TOTAL_PAGE_COUNT'];

		return $results;

	}
}
?>