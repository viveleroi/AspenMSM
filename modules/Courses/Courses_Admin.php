<?php
/**
 * @abstract courses Admin class - Allows an admin user to manage courses
 * @package Aspen Framework
 * @author Jason Verburg, Point Creative, Inc.
 * @uses Admin
 */
class Courses_Admin {

	/**
	 * @var object Holds our original application
	 * @access private
	 */
	private $APP;


	/**
	 * @abstract Constructor, initializes the module
	 * @return Install_Admin
	 * @access public
	 */
	public function __construct(){
		$this->APP = get_instance();
		director()->registerPageSection(__CLASS__, 'Course Display', 'course_display');
		director()->registerPageSection(__CLASS__, 'Course List Display', 'courselist_display');
		$this->APP->setConfig('enable_uploads', true); // enable uploads
	}

	
	/**
	 * @abstract Displays our menus and menu items
	 * @access public
	 */
	public function view(){
		
		$model = model()->open('courses');
		$model->orderBy('title');
		$data['course_list'] = $model->results();
		
		// pull the menus
		$model = model()->open('course_groups');
		$groups = $model->results();
		
		if($groups){
			foreach($groups as $g_id => $group){
				
				$model = model()->open('courses');
				$model->leftJoin('course_groups_link', 'course_id', 'id', array('group_id'));
				$model->where('group_id', $g_id);
				$model->orderBy('name');
				$groups[$g_id]['courses'] = $model->results();
				
			}
		}
		
		$data['course_groups'] = $groups;

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'index.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);
		
	}


	/**
	 * @abstract Adds a new contact
	 * @access public
	 */
	public function add(){
		$this->edit();
	}
	

	/**
	 * @abstract Edits an event recprd
	 * @param integer $id
	 * @access public
	 */
	public function edit($id = false){

			$this->APP->form->loadRecord('courses', $id);
			
			// grab existing groups settings
			$model = model()->open('course_groups_link');
			$model->where('course_id', $id);
			$group_records = $model->results();
			
			$groups = array();
			if($group_records){
				foreach($group_records as $course_record){
					$groups[] = $course_record['group_id'];
				}
			}
			
			$this->APP->form->addField('groups', $groups, $groups);

			// proces the form if submitted
			if($this->APP->form->isSubmitted()){
				
				// validation
				if(!$this->APP->form->isFilled('title')){
					$this->APP->form->addError('title', 'You must enter a course title.');
				}
	
				// if we have no errors, process sql
				if(!$this->APP->form->error()){
					if($res_id = $this->APP->form->save($id)){
					
						$id = $id ? $id : $res_id;
						
						// update course groups
						$model->delete('course_groups_link', $id, 'course_id');
						$groups = $this->APP->form->cv('groups');
						foreach($groups as $group){
							$sql = sprintf('INSERT INTO course_groups_link (course_id, group_id) VALUES ("%s", "%s")', $id, $group);
							$model->query($sql);
						}
				      
						// if successful insert, redirect to the list
						$this->APP->sml->addNewMessage('The course has successfully been saved.');
						router()->redirect('view');
	
					}
				}
			}
		
		$data['values'] = $this->APP->form->getCurrentValues();

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'edit.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);
		
	}
	
	
	/**
	 * @abstract Deletes a menu item
	 * @param integer $id
	 * @access public
	 */
	public function delete($id){
		$model->delete('courses', $id);
		$this->APP->sml->addNewMessage('The course has successfully been deleted.');
		router()->redirect('view');
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
		$record = $model->quickSelectSingle('courses', $id, 'id');
		
		if($record){
			$public = ($record['public'] == 1 ? 0 : 1);
			$model->executeUpdate('courses', array('public'=>$public), $id, 'id');
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
		
		$template = $template ? $template : $this->APP->form->cv('page_template');
		
		$next_id = isset($section['meta']['id']) ? $section['meta']['id'] : $next_id;
		$model = model()->open('template_placement_group');
		$model->where('template', $template);
		$placement_groups = $model->results();
		
		$templates = $this->APP->display->sectionTemplates('modules/courses');
		include(dirname(__FILE__).DS.'templates_admin'.DS.'section_menu.tpl.php');
		
		
		if($type == 'course_display'){
			$templates = $this->APP->display->sectionTemplates('modules/courses');
			include(dirname(__FILE__).DS.'templates_admin'.DS.'section_course.tpl.php');
		} else {
			$templates = $this->APP->display->sectionTemplates('modules/courses');
			include(dirname(__FILE__).DS.'templates_admin'.DS.'section_courselist.tpl.php');
		}
		
	}

	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $section
	 * @param unknown_type $page_id
	 * @return unknown
	 */
	public function saveSection($section, $page_id){

		// loop new section and add into the db
		if(is_array($section)){
			if($section['section_type'] == 'course_display'){
				return $this->saveSection_course($section, $page_id);
			}
			
			if($section['section_type'] == 'courselist_display'){
				return $this->saveSection_list($section, $page_id);
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
	public function saveSection_course($section, $page_id){
		
		$sections = array();

		// loop new section and add into the db
		if(is_array($section)){
			
			$section['show_title'] = isset($section['show_title']) ? $section['show_title'] : false;
			
			$model->query(sprintf('
				INSERT INTO section_course_display (page_id, title, show_title, template, course_id)
				VALUES ("%s", "%s", "%s", "%s", "%s")',
					$this->APP->security->dbescape($page_id),
					$this->APP->security->dbescape($section['title']),
					$this->APP->security->dbescape($section['show_title']),
					$this->APP->security->dbescape($section['template']),
					$this->APP->security->dbescape($section['course_id'])));
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'course_display',
				'called_in_template' => $section['called_in_template'],
				'id' => $this->APP->db->Insert_ID());
		
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
	public function saveSection_list($section, $page_id){
		
		$sections = array();

		// loop new section and add into the db
		if(is_array($section)){
			
			$section['show_title'] = isset($section['show_title']) ? $section['show_title'] : false;
			
			$model->query(sprintf('
				INSERT INTO section_courselist_display (page_id, title, show_title, template, group_id)
				VALUES ("%s", "%s", "%s", "%s", "%s")',
					$this->APP->security->dbescape($page_id),
					$this->APP->security->dbescape($section['title']),
					$this->APP->security->dbescape($section['show_title']),
					$this->APP->security->dbescape($section['template']),
					$this->APP->security->dbescape($section['group_id'])));
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'courselist_display',
				'called_in_template' => $section['called_in_template'],
				'id' => $this->APP->db->Insert_ID());
		
		}
		
		return $sections;
		
	}
	

	
	
	/**
	 * @abstract Installs the module
	 * @param string $my_guid GUID which is automatically passed by installer
	 * @return boolean
	 */
	public function install($my_guid = false){
		
		$sql = "
			CREATE TABLE `course_groups` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `name` varchar(155) NOT NULL,
			  `description` varchar(255) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;";
		$success = $model->query($sql);
		
		
		$sql = "
			CREATE TABLE `course_groups_link` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `course_id` int(10) unsigned NOT NULL,
			  `group_id` int(10) unsigned NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;";
		$success = $model->query($sql);
		
		
		$sql = "
			CREATE TABLE `courses` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `title` varchar(155) NOT NULL,
			  `code` varchar(155) NOT NULL,
			  `duration` varchar(155) NOT NULL,
			  `pricing_single` varchar(155) NOT NULL,
			  `pricing_few` varchar(155) NOT NULL,
			  `pricing_many` varchar(155) NOT NULL,
			  `date` datetime NOT NULL,
			  `location` varchar(155) NOT NULL,
			  `seating` varchar(155) NOT NULL,
			  `summary` longtext NOT NULL,
			  `body` longtext NOT NULL,
			  `pdf_filename` varchar(255) NOT NULL,
			  `public` tinyint(1) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$success = $model->query($sql);
		
		
		$sql = "
			CREATE TABLE `section_course_display` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `page_id` int(10) unsigned NOT NULL default '0',
			  `title` varchar(255) NOT NULL default '',
			  `show_title` tinyint(1) NOT NULL default '1',
			  `template` varchar(155) NOT NULL,
			  `course_id` int(11) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		$success = $model->query($sql);
		
		$sql = "
			CREATE TABLE `section_courselist_display` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `page_id` int(10) unsigned NOT NULL default '0',
			  `title` varchar(255) NOT NULL default '',
			  `show_title` tinyint(1) NOT NULL default '1',
			  `template` varchar(155) NOT NULL,
			  `group_id` int(11) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		$success = $model->query($sql);
		
		
		$sql = "INSERT INTO `permissions` (`user_id`, `group_id`, `interface`, `module`, `method`) VALUES (0, 2, 'Admin', 'Menus', '*');";
		$success = $model->query($sql);
		
		// Autoload this class with the Pages module
		if($success){
			$success = $this->APP->modules->registerModuleHook('c3f28790-269f-11dd-bd0b-0800200c9a66', $my_guid);
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
		
		$model->query('DROP TABLE `course_groups`');
		$model->query('DROP TABLE `course_groups_link`');
		$model->query('DROP TABLE `courses`');
		$model->query('DROP TABLE `section_course_display`');
		$model->query('DROP TABLE `section_courselist_display`');
		
		$model->query('DELETE FROM section_list WHERE type = "course_display"');
		$model->query('DELETE FROM section_list WHERE type = "courselist_display"');
		
		return true;
		
	}
}
?>