<?php


/**
 * 
 */
class Eventslib {
	
	/**
	 *
	 * @var <type> 
	 */
	private $months = array();
	
	
	/**
	 * @abstract Constructor, initializes the module
	 * @return 
	 * @access public
	 */
	public function aspen_init(){
		if(LS == 'admin'){
			director()->registerPageSection('eventslib', 'Events Display', 'events_display');
		} else {
			director()->registerCmsSection('eventslib', 'events_display');
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
		$templates = app()->display->sectionTemplates('modules/events');
		
		$base = str_replace('libs', 'templates_admin', dirname(__FILE__));
		include($base.DS.'section_events.tpl.php');
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
			$section['hide_expired'] = isset($section['hide_expired']) ? $section['hide_expired'] : false;
			$section['show_recurring'] = isset($section['show_recurring']) ? $section['show_recurring'] : false;
			$section['show_nonrecurring'] = isset($section['show_nonrecurring']) ? $section['show_nonrecurring'] : false;
			$section['show_title'] = isset($section['show_title']) ? $section['show_title'] : false;
			$section['show_description'] = isset($section['show_description']) ? $section['show_description'] : false;
			
			model()->open('section_events_display')->insert(array(
				'page_id' => $page_id,
				'title' => $section['title'],
				'hide_expired' => $section['hide_expired'],
				'show_recurring' => $section['show_recurring'],
				'show_nonrecurring' => $section['show_nonrecurring'],
				'display_num' => $section['display_num'],
				'link_to_full_page' => $section['link_to_full_page'],
				'detail_page_id' => $section['detail_page_id'],
				'show_title' => $section['show_title'],
				'show_description' => $section['show_description'],
				'event_group_id' => $section['event_group_id'],
				'template' => $section['template']
			));
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'events_display',
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
		
		$data = array();
		
		// pull the section for the database
		$section_content = model()->open('section_events_display', $section_data['section_id']);
		
		$section_content['type'] = $section_data['section_type'];
		$section_content['link_to_full_page'] = $section_content['link_to_full_page'];
		$section_content['placement_group'] = $section_data['group_name'];

		if(!app()->cms_lib->getUriBit(1)){

			// pull events
			$model = model()->open('events');
			$model->select(array('*, YEAR(start_date) as years, MONTHNAME(start_date) as months'));
			$model->parenthStart();

			$match = 'AND';
			if($section_content['show_recurring'] && $section_content['show_nonrecurring']){
				$model->where('recurring', 1);
				$match = 'OR';
			}
			else if($section_content['show_recurring'] && !$section_content['show_nonrecurring']){
				$model->where('recurring', 1);
			}
			elseif($section_content['show_nonrecurring']){
				$model->where('recurring', 0);
			} else {
				$model->where('recurring', 2);
			}

			if($section_content['show_nonrecurring'] && $section_content['hide_expired']){
				$model->whereFuture('CONCAT(start_date," ", start_time)', false, $match);
			}

			$model->parenthEnd();

			if(isset($section_content['group_id']) && $section_content['group_id']){
				$model->leftJoin('event_groups_link', 'event_id', 'id', array('group_id'));
				$model->where('group_id', $section_content['group_id']);
			}

			if(get()->getInt('year')){
				$model->where('YEAR(start_date)', get()->getInt('year'));
			}

			$model->where('public', 1);
			$model->orderBy('start_date', 'ASC');
			if($section_content['display_num']){
				$model->limit(0, $section_content['display_num']);
			}
			$events = $model->results();
//					print $model->getLastQuery();
			$section_content['events'] = $events;

			// append month names
			if($events){
				foreach($events as $event){
					if(!empty($event['months'])){
						$this->months[ $event['months'] ] = $event['months'];
					}
				}
			}

		} else {

			$event = model()->open('events', app()->cms_lib->getUriBit(1));

			if($event){
				$section_content['events'] = array($event['id']=>$event);
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
	 * @abstract Displays the basic section
	 * @param unknown_type $section
	 * @param unknown_type $page
	 * @param unknown_type $bits
	 */
	public function displaySection($section, $page, $bits){
		app()->display->loadSectionTemplate('modules/events', $section['template'], $section, $page, $bits);
	}
	
	
	/**
	 * @abstract Parses and returns proper content for the search indexer
	 * @param array $content
	 */
	public function searchIndexer($content = false){
		$source = array();
		if($content['events']){
			foreach($content['events'] as $event){
				$source[0]['source_type'] 		= $content['type'];
				$source[0]['source_page_id'] 	= $content['page_id'];
				$source[0]['source_id'] 		= $content['id'];
				$source[0]['source_title'] 		= $event['title'];
				$source[0]['source_content'] 	= $event['content'];
			}
		}
		return $source;
	}


	/**
	 *
	 * @return <type>
	 */
	public function months(){
		return $this->months;
	}


	/**
	 *
	 * @return <type> 
	 */
	public function cur_month(){
		return strtolower(date('F'));
	}
}
?>