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
		$this->APP->director->registerCmsSection(__CLASS__, 'contacts_display');
		$this->APP->director->registerCmsSection(__CLASS__, 'contactgroup_display');
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

		$section_results = $this->APP->model->query(sprintf('SELECT * FROM section_%s WHERE id = "%s"', $section_data['section_type'], $section_data['section_id']));

		if($section_results->RecordCount()){
			while($section_content = $section_results->FetchRow()){
				
				$section_content['type'] = $section_data['section_type'];
				$section_content['placement_group'] = $section_data['group_name'];

				$this->APP->model->select('contacts');
				$this->APP->model->where('id', $section_content['contact_id']);
				$results = $this->APP->model->results();
				
				$section_content['results'] = $results['RECORDS'];

				if($results['RECORDS']){
					foreach($results['RECORDS'] as $key => $contact){
						$related = $this->pullRelatedContactContent($contact['id']);
						$results['RECORDS'][$key] = array_merge($results['RECORDS'][$key], $related);
					}
				} else {
					$results = $this->APP->model->quickSelectSingle('contacts', $this->APP->cms_lib->getUriBit(1));
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

		$section_results = $this->APP->model->query(sprintf('SELECT * FROM section_%s WHERE id = "%s"', $section_data['section_type'], $section_data['section_id']));
		
		if($section_results->RecordCount()){
			while($section_content = $section_results->FetchRow()){
				
				$section_content['type'] = $section_data['section_type'];
				$section_content['placement_group'] = $section_data['group_name'];

				// pull the groups
				$this->APP->model->select('contact_groups');
				$this->APP->model->where('id', $section_content['group_id']);
				$groups = $this->APP->model->results();
				
				if($groups['RECORDS']){
					foreach($groups['RECORDS'] as $g_id => $group){
						
						$this->APP->model->select('contacts');
						$this->APP->model->leftJoin('contact_groups_link', 'contact_id', 'id', array('group_id'));
						$this->APP->model->where('group_id', $g_id);

						if($section_content['sort_method'] == 'sort_order'){
							$this->APP->model->orderBy('sort_order, last_name, first_name');
						} else {
							$this->APP->model->orderBy('last_name, first_name');
						}
						
						$groups['RECORDS'][$g_id]['contacts'] = $this->APP->model->results();
						
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
		$this->APP->model->select('contact_images');
		$this->APP->model->where('contact_id', $contact_id);
		$content['images'] = $this->APP->model->results();

		// pull languages
		$this->APP->model->select('contact_languages');
		$this->APP->model->leftJoin('contact_languages_link', 'language_id', 'id', array('contact_id'));
		$this->APP->model->where('contact_languages_link.contact_id', $contact_id);
		$content['languages'] = $this->APP->model->results();

		// pull groups
		$this->APP->model->select('contact_groups');
		$this->APP->model->leftJoin('contact_groups_link', 'group_id', 'id', array('contact_id'));
		$this->APP->model->where('contact_groups_link.contact_id', $contact_id);
		$content['groups'] = $this->APP->model->results();

		// pull specialties
		$this->APP->model->select('contact_specialties');
		$this->APP->model->leftJoin('contact_specialties_link', 'specialty_id', 'id', array('contact_id'));
		$this->APP->model->where('contact_specialties_link.contact_id', $contact_id);
		$content['specialties'] = $this->APP->model->results();

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
		$this->APP->model->select('contact_specialties');
		$this->APP->model->orderBy('specialty');
		return $this->APP->model->results();
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

		$this->APP->model->enablePagination();
		$this->APP->model->select('contacts');

		if($group){
			$this->APP->model->leftJoin('contact_groups_link', 'contact_id', 'id', array('group_id'));
			$this->APP->model->where('contact_groups_link.group_id', $group);
		}

		if($first_name){
			$this->APP->model->where('first_name', $first_name);
		}

		if($last_name){
			$this->APP->model->where('last_name', $last_name);
		}

		if($specialty){
			$this->APP->model->leftJoin('contact_specialties_link', 'contact_id', 'id', array('specialty_id'));
			$this->APP->model->where('contact_specialties_link.specialty_id', $specialty);
		} else {
			$this->APP->model->leftJoin('contact_specialties_link', 'contact_id', 'id', array('specialty_id'));
			$this->APP->model->leftJoin('contact_specialties', 'id', 'specialty_id', array('specialty'), 'contact_specialties_link');
			$this->APP->model->where('contact_specialties.specialty', $keyword);
		}

		if($keyword && !$first_name && !$last_name){
			$this->APP->model->match($keyword, false, 'AND', array('contact_specialties.specialty'));
		}

		$this->APP->model->paginate($this->APP->params->get->getRaw('page'), $this->APP->config('search_results_per_page'));
		$this->APP->model->orderBy('match_relevance DESC, last_name, first_name');
//		print $this->APP->model->getBuildQuery();
		$results = $this->APP->model->results();

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