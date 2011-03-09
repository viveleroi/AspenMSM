<?php
/**
 * @abstract Events Admin class - Allows an admin user to manage an events list
 * @package Aspen Framework
 * @author Michael Botsko, Botsko.net LLC
 * @uses Admin
 */
class Events {

	/**
	 * @var object Holds our original application
	 * @access private
	 */
	private $APP;

	/**
	 *
	 * @var <type> 
	 */
	private $months = array();


	/**
	 * @abstract Constructor, initializes the module
	 * @return Install_Admin
	 * @access public
	 */
	public function __construct(){
		$this->APP = get_instance();
		director()->registerCmsSection(__CLASS__, 'events_display');
	}
	
	
	/**
	 * @abstract Returns the section information from the db
	 * @param array $section_data
	 * @return array
	 */
	public function readSection($section_data){
		
		$data = array();
		
		// pull the section for the database
		$section_results = $model->query(sprintf('SELECT * FROM section_events_display WHERE id = "%s"', $section_data['section_id']));
		
		if($section_results->RecordCount()){
			while($section_content = $section_results->FetchRow()){
				$section_content['type'] = $section_data['section_type'];
				$section_content['link_to_full_page'] = $section_content['link_to_full_page'];
				$section_content['placement_group'] = $section_data['group_name'];

				if(!app()->cms_lib->getUriBit(1)){
				
					// pull events
					$model = model()->open('events', array('*, YEAR(start_date) as years, MONTHNAME(start_date) as months'));

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

					$event = $model->quickSelectSingle('events', app()->cms_lib->getUriBit(1));

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