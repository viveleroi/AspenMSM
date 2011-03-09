<?php
/**
 * @abstract Events Admin class - Allows an admin user to manage an events list
 * @package Aspen Framework
 * @author Michael Botsko, Botsko.net LLC
 * @uses Admin
 */
class Events_Admin {


	/**
	 * @abstract Constructor, initializes the module
	 * @return Install_Admin
	 * @access public
	 */
	public function __construct(){
		template()->addCss('style.css');
		director()->registerPageSection(__CLASS__, 'Events Display', 'events_display');
	}

	
	/**
	 * @abstract Displays our directory of events
	 * @access public
	 */
	public function view(){
		
		template()->addJs('view.js');
		
		$model = model()->open('events');
		$model->whereFuture('CONCAT(start_date," ", start_time)');
		$model->where('recurring', 0);
		$model->orderBy('id', 'DESC', 'events:list');
		$data['cur_events'] = $model->results();

		// attach groups
		if($data['cur_events']){
			foreach($data['cur_events'] as $key => $event){
				$model = model()->open('event_groups');
				$model->leftJoin('event_groups_link','group_id', 'id', array('event_id'));
				$model->where('event_groups_link.event_id', $event['id']);
				$model->orderBy('event_groups.name');
				$groups = $model->results();
	
				if($groups){
					foreach($groups as $group){
						$data['cur_events'][$key]['groups'][] = $group['name'];
					}
				}
			}
		}
		
		$model = model()->open('events');
		$model->wherePast('CONCAT(start_date," ", start_time)');
		$model->where('recurring', 0);
		$model->orderBy('id', 'DESC', 'events:list');
		$data['past_events'] = $model->results();

		// attach groups
		if($data['past_events']){
			foreach($data['past_events'] as $key => $event){
				$model = model()->open('event_groups');
				$model->leftJoin('event_groups_link','group_id', 'id', array('event_id'));
				$model->where('event_id', $event['id']);
				$model->orderBy('name');
				$groups = $model->results();
	
				if($groups){
					foreach($groups as $group){
						$data['past_events'][$key]['groups'][] = $group['name'];
					}
				}
			}
		}

		$model = model()->open('events');
		$model->where('recurring', 1);
		$model->orderBy('id', 'DESC', 'events:list');
		$data['recurring_events'] = $model->results();

		// attach groups
		if($data['recurring_events']){
			foreach($data['recurring_events'] as $key => $event){
				$model = model()->open('event_groups');
				$model->leftJoin('event_groups_link','group_id', 'id', array('event_id'));
				$model->where('event_id', $event['id']);
				$model->orderBy('name');
				$groups = $model->results();
	
				if($groups){
					foreach($groups as $group){
						$data['recurring_events'][$key]['groups'][] = $group['name'];
					}
				}
			}
		}

		template()->display($data);
		
	}
	
	
	/**
	 * @abstract Generates a timestamp from three time fields
	 * @param string $field
	 * @access private
	 */
	private function timeString($field){
		
		$hour = $form->cv($field . '_hour');
		$minute = $form->cv($field . '_minute');
		$ampm = $form->cv($field . '_ampm');

		if(!empty($hour) && !empty($minute) && !empty($ampm)){
			$time = date("H:i:s", strtotime($hour.':'.$minute.' '.$ampm));
			$form->setDefaultValue($field . '_time', $time);
		}
	}
	
	
	/**
	 * @abstract Validates incoming form data
	 * @access private
	 */
	private function validate(){
		// @a13
//		if(!$form->isFilled('title')){
//			$form->addError('title', 'You must enter a title.');
//		}
//		
//		if(!$form->isFilled('recurring') && !$form->isDate('start_date')){
//			$form->addError('start_date', 'Please enter a valid start date.');
//		}
//		
//		if($form->isFilled('end_date')){
//			if($form->isDate('end_date')){
//				if(strtotime($form->cv('start_date')) > strtotime($form->cv('end_date'))){
//					$form->addError('content', 'Please choose a starting date that occurs before the end date.');
//				}
//			} else {
//				$form->addError('end_date', 'Please enter a valid end date.');
//			}
//		}
	}


	/**
	 * @abstract Adds a new event
	 * @access public
	 */
	public function add_event(){
		
		template()->addCss('admin/datepicker.css');
		template()->addJs('admin/datepicker.js');
		template()->addJs('edit.js');

		$form = new Form('events');
		$form->setDefaultValue('start_date', date("Y-m-d"));
		$form->setDefaultValue('end_date', '');
		$form->addField('groups', array(), array());
		$form->addField('start_hour');
		$form->addField('start_minute');
		$form->addField('start_ampm');
		$form->addField('end_hour');
		$form->addField('end_minute');
		$form->addField('end_ampm');

		// proces the form if submitted
		if($form->isSubmitted()){
			
			$this->timeString('start');
			$this->timeString('end');

			if(!post()->keyExists('recurring')){
				$form->setCurrentValue('recurring', false);
			} else {
				$form->setCurrentValue('start_date', '');
				$form->setCurrentValue('end_date', '');
			}

			// validation
//			$this->validate();

			// set security rules
			// @a13
//			$model->setSecurityRule('content', 'allow_html', true);
//			$form->setCurrentValue('public', 1);

			if($id = $form->save()){

				// update groups
				// @todo update this
				$groups = $form->cv('groups');
				foreach($groups as $group){
					$sql = sprintf('INSERT INTO event_groups_link (event_id, group_id) VALUES ("%s", "%s")', $id, $group);
					$model->query($sql);
				}

				sml()->say('Event entry has successfully been added.');
				router()->redirect('view');

			} else {

				sml()->say('An error occurred. Please try again.');

			}
		}
		
		$data['form'] = $form;
		
		template()->display($data);
		
	}


	/**
	 * @abstract Edits an event recprd
	 * @param integer $id
	 * @access public
	 */
	public function edit_event($id = false){
		
		template()->addCss('admin/datepicker.css');
		template()->addJs('admin/datepicker.js');
		template()->addJs('edit.js');

		if($id){
			
			$form = new Form('events', $id);
			$form->addField('groups', array(), array());

			if($form->cv('end_date') == '0000-00-00'){
				$form->setDefaultValue('end_date', '');
			}
			
			$start_time = strtotime($form->cv('start_time'));
			if($form->cv('start_time') != '00:00:00'){
				$form->addField('start_hour', date("h", $start_time), date("h", $start_time));
				$form->addField('start_minute', date("i", $start_time), date("i", $start_time));
				$form->addField('start_ampm', date("a", $start_time), date("a", $start_time));
			} else {
				$form->addField('start_hour');
				$form->addField('start_minute');
				$form->addField('start_ampm');
			}
			
			$end_time = strtotime($form->cv('end_time'));
			if($form->cv('end_time') != '00:00:00'){
				$form->addField('end_hour', date("h", $end_time), date("h", $end_time));
				$form->addField('end_minute', date("i", $end_time), date("i", $end_time));
				$form->addField('end_ampm', date("a", $end_time), date("a", $end_time));
			} else {
				$form->addField('end_hour');
				$form->addField('end_minute');
				$form->addField('end_ampm');
			}
			
			$data['form'] = $form;

			// proces the form if submitted
			if($form->isSubmitted()){
				
				$this->timeString('start');
				$this->timeString('end');

				if(!post()->keyExists('recurring')){
					$form->setCurrentValue('recurring', false);
				} else {
					$form->setCurrentValue('start_date', '');
					$form->setCurrentValue('end_date', '');
				}
				
				// validation
//				$this->validate();
	
				// set security rules
				// // @a13
//				$model->setSecurityRule('content', 'allow_html', true);
	
	
				if($form->save($id)){

					// update groups
					// @todo update this
					$model->delete('event_groups_link', $id, 'event_id');
					$groups = $form->cv('groups');
					foreach($groups as $group){
						$sql = sprintf('INSERT INTO event_groups_link (event_id, group_id) VALUES ("%s", "%s")', $id, $group);
						$model->query($sql);
					}

					sml()->say('Event has successfully been updated.');
					router()->redirect('view');

				} else {

					sml()->say('An error occurred. Please try again.');

				}
			}
		}

		template()->display($data);
		
	}
	
	
	/**
	 * @abstract Deletes an event record
	 * @param integer $id
	 * @access public
	 */
	public function delete($id = false){
		if(model()->open('events')->delete($id)){
			sml()->say('Event entry has successfully been deleted.');
			router()->redirect('view');
		}
	}
	
	
	/**
	 * @abstract Toggles the public/private setting of the record
	 * @param integer $id
	 * @return string
	 * @access public
	 */
	public function ajax_toggleDisplay($id){
		
		// obtain original state
		$public = 0;
		$record = $model->quickSelectSingle('events', $id);
		
		if($record){
			$public = ($record['public'] == 1 ? 0 : 1);
			$model->executeUpdate('events', array('public'=>$public), $id);
		}
		
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
		$xml .= '<response>'."\n";
		$xml .= '<direction>'.$public.'</direction>';
		$xml .= '</response>'."\n";

		header("Content-Type: text/xml");
		print $xml;
		
	}
	
	
	/**
	 * @abstract Displays page section editing form
	 * @param array $section
	 * @param integer $next_id
	 * @access public
	 */
	public function sectionEditor($type = false, $next_id = 1, $section = false, $page_id = false, $template = false){
		
		$template = $template ? $template : $form->cv('page_template');
		
		$next_id = isset($section['meta']['id']) ? $section['meta']['id'] : $next_id;
		$model = model()->open('template_placement_group');
		$model->where('template', $template);
		$placement_groups = $model->results();
		$templates = app()->display->sectionTemplates('modules/events');
		
		include(dirname(__FILE__).DS.'templates_admin'.DS.'section_events.tpl.php');
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
			
			$model->query(sprintf('
				INSERT INTO section_events_display (page_id, title, hide_expired, show_recurring, show_nonrecurring, display_num, link_to_full_page, detail_page_id, show_title, show_description, group_id, template)
				VALUES ("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s")',
					app()->security->dbescape($page_id),
					app()->security->dbescape($section['title']),
					app()->security->dbescape($section['hide_expired']),
					app()->security->dbescape($section['show_recurring']),
					app()->security->dbescape($section['show_nonrecurring']),
					app()->security->dbescape($section['display_num']),
					app()->security->dbescape($section['link_to_full_page']),
					app()->security->dbescape($section['detail_page_id']),
					app()->security->dbescape($section['show_title']),
					app()->security->dbescape($section['show_description']),
					app()->security->dbescape($section['group_id']),
					app()->security->dbescape($section['template'])));
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'events_display',
				'called_in_template' => $section['called_in_template'],
				'id' => app()->db->Insert_ID());
		
		}
		
		return $sections;
		
	}

	
	
	/**
	 * Enter description here...
	 *
	 */
	public function ajax_listGroups($id = false){

		$sql = sprintf('
			SELECT event_groups.*, IF(event_groups.id IN (SELECT group_id FROM event_groups_link WHERE event_id = "%s"), 1, 0 ) as selected
			FROM event_groups
			ORDER BY event_groups.name ASC', $id);
		$groups = $model->results(false, $sql);
		
		print json_encode( array('groups'=>$groups) );
		
	}
	
	
	/**
	 * @abstract Adds a new group
	 * @param string $name
	 */
	public function ajax_addGroup($name = false){
	
		$id = false;
		if(!empty($name)){
			$id = $model->executeInsert('event_groups', array('name'=>$name));
		}
		
		print json_encode( array('success'=>(bool)$id, 'id'=>$id, 'name'=>$name) );
		
	}
	
	
	/**
	 * @abstract Deletes a group
	 * @param integer $id
	 */
	public function ajax_deleteGroup($id = false){
	
		$result = false;
		if($id && ctype_digit($id)){
			$result = $model->delete('event_groups', $id);
			
			if($result){
				$result = $model->delete('event_groups_link', $id, 'group_id');
			}
		}
		
		print json_encode( array('success'=>(bool)$result, 'id'=>$id ));
		
	}
	
	
	/**
	 * @abstract Installs the module
	 * @param string $my_guid GUID which is automatically passed by installer
	 * @return boolean
	 */
	public function install($my_guid = false){
		
		$sql = "
			CREATE TABLE `events` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `title` text NOT NULL,
			  `description` text NOT NULL,
			  `content` text NOT NULL,
			  `recurring` tinyint(4) NOT NULL,
			  `recur_description` text NOT NULL,
			  `start_date` date NOT NULL default '0000-00-00',
			  `end_date` date NOT NULL default '0000-00-00',
			  `start_time` time NOT NULL default '00:00:00',
			  `end_time` time NOT NULL default '00:00:00',
			  `public` tinyint(1) NOT NULL default '0',
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$success = $model->query($sql);
		
		
		$sql = "
			CREATE TABLE IF NOT EXISTS `section_events_display` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `page_id` int(10) unsigned NOT NULL default '0',
			  `title` varchar(255) NOT NULL default '',
			  `hide_expired` tinyint(1) NOT NULL default '0',
			  `show_recurring` tinyint(1) NOT NULL,
			  `show_nonrecurring` tinyint(1) NOT NULL,
			  `display_num` int(11) NOT NULL default '0',
			  `link_to_full_page` tinyint(1) NOT NULL default '0',
			  `detail_page_id` int(10) unsigned NOT NULL,
			  `show_title` tinyint(1) NOT NULL default '1',
			  `show_description` tinyint(1) NOT NULL,
			  `group_id` int(10) unsigned NOT NULL,
			  `template` varchar(155) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
		$success = $model->query($sql);
		
		
		$sql = "
			CREATE TABLE `event_groups` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `name` varchar(255) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$success = $model->query($sql);
		
		
		$sql = "
			CREATE TABLE `event_groups_link` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `event_id` int(10) unsigned NOT NULL,
			  `group_id` int(10) unsigned NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$success = $model->query($sql);
		
		
		$sql = "INSERT INTO `permissions` (`user_id`, `group_id`, `interface`, `module`, `method`) VALUES (0, 2, 'Admin', 'Events', '*');";
		$success = $model->query($sql);
		
		// Autoload this class with the Pages module
		if($success){
			$success = app()->modules->registerModuleHook('c3f28790-269f-11dd-bd0b-0800200c9a66', $my_guid);
		}
		
		return $success;
		
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $my_guid
	 * @return unknown
	 */
	public function uninstall($my_guid = false){
		
		$model->query('DROP TABLE `events`');
		$model->query('DROP TABLE `section_event_display`');
		$model->query('DELETE FROM section_list WHERE type = "events_display"');
		$model->query(sprintf('DELETE FROM modules WHERE guid = "%s"', $my_guid));
		$model->query(sprintf('UPDATE modules SET autoload_with = "" WHERE autoload_with = "%s"', $my_guid));
		$model->query(sprintf('DELETE FROM permissions WHERE module = "%s"', __CLASS__));
		
		return true;
		
	}
}
?>