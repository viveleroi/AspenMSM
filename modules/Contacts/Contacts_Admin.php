<?php
/**
 * @abstract contacts Admin class - Allows an admin user to manage an events list
 * @package Aspen Framework
 * @author Michael Botsko, Botsko.net LLC
 * @uses Admin
 */
class Contacts_Admin {
	

	/**
	 * @abstract Constructor, initializes the module
	 * @return Install_Admin
	 * @access public
	 */
	public function __construct(){
		template()->addCss('style.css');
		director()->registerPageSection(__CLASS__, 'Contact Display', 'contacts_display');
		director()->registerPageSection(__CLASS__, 'Contact Group Display', 'contactgroup_display');
		app()->setConfig('enable_uploads', true); // enable uploads
	}

	
	/**
	 * @abstract Displays our contacts of events
	 * @access public
	 */
	public function view(){
		
		$model = model()->open('contacts');
		$model->orderBy('last_name');
		$data['directory_list'] = $model->results();
		
		// pull the groups
		$model = model()->open('contact_groups');
		$model->contains('contacts');
		$model->orderBy('name');
		$data['group_list'] = $model->results();
		
		template()->addJs('admin/jquery.listnav.js');
		template()->addJs('admin/jScrollPane.js');
		template()->addJs('view.js');
		template()->display($data);
		
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
		
		template()->addJs('edit.js');

		$form = new Form('contacts', $id);

		// grab existing language settings
		$model = model()->open('contact_languages_link');
		$model->where('contact_id', $id);
		$languages_records = $model->results();
		
		$languages = array();
		if($languages_records){
			$languages = Utils::extract('{n}.language_id', $languages_record);
		}
		$form->addField('languages', $languages, $languages);


		// grab existing groups settings
		$model = model()->open('contact_groups_link');
		$model->where('contact_id', $id);
		$groups_records = $model->results();

		$groups = array();
		if($groups_records){
			$groups = Utils::extract('{n}.group_id', $groups_records);
		}
		$form->addField('groups', $groups, $groups);


		// grab existing specialty settings
//		$model = model()->open('contact_specialties_link');
//		$model->where('contact_id', $id);
//		$specs_records = $model->results();
//
//		$specialties = array();
//		if($specs_records){
//			foreach($specs_records as $specs_record){
//				$specialties[] = $specs_record['specialty_id'];
//			}
//		}
//		$form->addField('specialties', $specialties, $specialties);


		// proces the form if submitted
		if($form->isSubmitted()){
			if($res_id = $form->save($id)){

				$id = $id ? $id : $res_id;
				
				// @todo cleanup all of this
				$model = model()->open('contacts');

				// update languages
				$model->delete('contact_languages_link', $id, 'contact_id');
				$languages = $form->cv('languages');
				foreach($languages as $language){
					$sql = sprintf('INSERT INTO contact_languages_link (contact_id, language_id) VALUES ("%s", "%s")', $id, $language);
					$model->query($sql);
				}

				// update groups
				$model->delete('contact_groups_link', $id, 'contact_id');
				$groups = $form->cv('groups');
				foreach($groups as $group){
					$sql = sprintf('INSERT INTO contact_groups_link (contact_id, group_id) VALUES ("%s", "%s")', $id, $group);
					$model->query($sql);
				}

				// update specialties
//				$model->delete('contact_specialties_link', $id, 'contact_id');
//				$specialties = $form->cv('specialties');
//				foreach($specialties as $specialty){
//					$sql = sprintf('INSERT INTO contact_specialties_link (contact_id, specialty_id) VALUES ("%s", "%s")', $id, $specialty);
//					$model->query($sql);
//				}

				// upload file
				app()->setConfig('upload_server_path', APPLICATION_PATH.DS.'files'.DS.'contacts'.DS.$id);
				app()->setConfig('enable_uploads', true); // enable uploads

				$uploads = files()->upload('file_path');

				// small thumb
				$thm_width = app()->config('contact_image_thm_maxwidth');
				$thm_height = app()->config('contact_image_thm_maxheight');

				// resized original
				$orig_width = app()->config('contact_image_maxwidth');
				$orig_height = app()->config('contact_image_maxheight');

				if(is_array($uploads) && !empty($uploads[0])){
					foreach($uploads as $file){

						// delete previous images
						$model = model()->open('contact_images');
						$model->where('contact_id', $id);
						$images = $model->results();

						if (is_array($images)){
							foreach($images as $image){
								$base = APPLICATION_PATH.DS.'files'.DS.'contacts'.DS.$image['contact_id'];
								files()->delete($base.DS.$image['filename_orig']);
								files()->delete($base.DS.$image['filename_thumb']);
								$model->delete('contact_images', $image['id']);
							}
						}

							// get new thumb file name, new path
						$thm_name = str_replace($file['file_extension'], '_thm'.$file['file_extension'], $file['file_name']);
						$thm_path = str_replace($file['file_name'], $thm_name, $file['server_file_path']);

						// get new file name, new path
						$orig_name = str_replace($file['file_extension'], '_orig'.$file['file_extension'], $file['file_name']);
						$orig_path = str_replace($file['file_name'], $orig_name, $file['server_file_path']);

						// load original in thumbnail
						$thm_create = new Thumbnail($file['server_file_path']);
						$thm_create->adaptiveResize($thm_width,$thm_height);
						$thm_create->save($thm_path);

						$orig_create = new Thumbnail($file['server_file_path']);
						$orig_create->adaptiveResize($orig_width,$orig_height);
						$orig_create->save($orig_path);

						// store image and thumb info to database
						$model->executeInsert('contact_images',
																array(
																	'contact_id'=>$id,
																	'filename_orig'=>$orig_name,
																	'filename_thumb'=>$thm_name,
																	'width_orig'=>$orig_width,
																	'height_orig'=>$orig_height,
																	'width_thumb'=>$thm_width,
																	'height_thumb'=>$thm_height
																));

					}
				}

			  sml()->say('Contact changes have been saved successfully.');
				router()->redirect('view');

			}
		}
		
		$data['form'] = $form;
		
		// get images
		if($id){
			$model = model()->open('contact_images');
			$model->where('contact_id', $id);
			$data['images'] = $model->results();
		} else {
			$data['images'] = false;
		}

		template()->display($data);
		
	}
	
	
	/**
	 * @abstract Deletes an event record
	 * @param integer $id
	 * @access public
	 */
	public function delete($id){
		model()->open('contacts')->delete($id);
		sml()->say('Contact has successfully been deleted.');
		router()->redirect('view');
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $id
	 */
	public function ajax_deleteImage($id){
		
		$image = model()->open('contact_images', $id);
		$base = APPLICATION_PATH.DS.'files'.DS.'contacts'.DS.$image['contact_id'];

		files()->delete($base.DS.$image['filename_orig']);
		files()->delete($base.DS.$image['filename_thumb']);
		files()->delete($base);
		
		$model->delete('contact_images', $id);
		
		print 1;
		
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
		
		if($type == 'contacts_display'){
			$templates = app()->display->sectionTemplates('modules/contacts/contacts');
			include(dirname(__FILE__).DS.'templates_admin'.DS.'section_contacts.tpl.php');
		} else {
			$templates = app()->display->sectionTemplates('modules/contacts/groups');
			include(dirname(__FILE__).DS.'templates_admin'.DS.'section_group.tpl.php');
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
	
	
	/**
	 * @abstract Adds a new group
	 * @param string $name
	 */
	public function ajax_addGroup($name = false){
	
		$id = false;
		if(!empty($name)){
			$id = $model->executeInsert('contact_groups', array('name'=>$name));
		}
		
		print json_encode( array('success'=>(bool)$id, 'id'=>$id, 'name'=>$name) );
		
	}


	/**
	 * @abstract Adds a new group
	 * @param string $name
	 */
	public function ajax_sortGroup($group_id, $ul){
		$success = false;
		$model = model()->open('contact_groups_link');
		$sql = 'UPDATE contact_groups_link SET sort_order = "%d" WHERE contact_id = "%d" AND group_id = "%s"';
		if(is_array($ul)){
			foreach($ul as $key => $contact){
				$model->query( sprintf($sql, (int)$key, (int)$contact, (int)$group_id) );
			}
			$success = true;
		}
		print json_encode( array('success'=>$success, 'group_id'=>$group_id) );
	}
	
	
	/**
	 * @abstract Deletes a group
	 * @param integer $id
	 */
	public function ajax_deleteGroup($id = false){
	
		$result = false;
		if($id && ctype_digit($id)){
			$result = model()->open('contact_groups')->delete($id);
			if($result){
				$result = model()->open('contact_groups_link')->delete($id, 'group_id');
			}
		}
		
		print json_encode( array('success'=>(bool)$result, 'id'=>$id ));
		
	}
	
	
	/**
	 * @abstract Assigns a new group to a contact dropped in UI
	 * @param integer $group
	 * @param integer $contact
	 */
	public function ajax_dropContact($group = false, $contact = false){
		
		$id 	= false;
		$result = false;
		$exists = false;
		
		if($group && $contact){
			
			// first, ensure the contact is not already in the group
			$model = model()->open('contact_groups_link');
			$model->where('contact_id', $contact);
			$model->where('group_id', $group);
			$contact_exists = $model->results();
			
			if($contact_exists){
				$exists = true;
				$result = false;
				foreach($contact_exists as $exist){
					$id = $exist['id'];
				}
			} else {
				// if not, add the new contact
				$result = $id = $model->insert(array('contact_id'=>$contact,'group_id'=>$group));
			}
			
			$contact = model()->open('contacts', $contact);
			
		}

		print json_encode( array('success'=>(bool)$result, 'id'=>$id, 'contact'=>$contact,'group'=>$group,'exists'=>$exists) );
		
	}
	
	
	/**
	 * @abstract Deletes a group
	 * @param integer $id
	 */
	public function ajax_removeContactFromGroup($id = false, $group_id = false){
	
		$result = false;
		if($id && $group_id){
			$sql = sprintf('DELETE FROM contact_groups_link WHERE contact_id = "%s" AND group_id = "%s"', $id, $group_id);
			$result = model()->open('contact_groups_link')->query($sql);
		}
		
		print json_encode( array('success'=>(bool)$result, 'id'=>$id ));
		
	}
	
	
	/**
	 * Enter description here...
	 *
	 */
	public function ajax_listLanguages($id = false){

		$sql = sprintf('
			SELECT contact_languages.*, IF(contact_languages.id IN (SELECT language_id FROM contact_languages_link WHERE contact_id = "%s"), 1, 0 ) as selected
			FROM contact_languages
			ORDER BY contact_languages.language ASC', $id);
		$languages = model()->open('contact_languages')->results(false, $sql);

		print json_encode( array('langs'=>$languages) );

	}


	/**
	 * Enter description here...
	 *
	 */
	public function ajax_listSpecialties($id = false){

		$sql = sprintf('
			SELECT contact_specialties.*, IF(contact_specialties.id IN (SELECT specialty_id FROM contact_specialties_link WHERE contact_id = "%s"), 1, 0 ) as selected
			FROM contact_specialties
			ORDER BY contact_specialties.specialty ASC', $id);
		$specialties = model()->open('contact_specialties')->results(false, $sql);

		print json_encode( array('specialties'=>$specialties) );

	}


	/**
	 * @abstract Adds a new group
	 * @param string $name
	 */
	public function ajax_addLanguage($name = false){
	
		$id = false;
		if(!empty($name)){
			$id = model()->open('contact_languages')->insert(array('language'=>$name));
		}
		
		print json_encode( array('success'=>(bool)$id, 'id'=>$id, 'name'=>$name) );
		
	}


	/**
	 * @abstract Adds a new group
	 * @param string $name
	 */
	public function ajax_addSpecialty($name = false){

		$id = false;
		if(!empty($name)){
			$id = model()->open('contact_specialties')->insert(array('specialty'=>$name));
		}

		print json_encode( array('success'=>(bool)$id, 'id'=>$id, 'name'=>$name) );

	}

	
	/**
	 * @abstract Deletes a group
	 * @param integer $id
	 */
	public function ajax_deleteLanguage($id = false){
	
		$result = false;
		if($id && ctype_digit($id)){
			$result = model()->open('contact_languages')->delete($id);
			
			if($result){
				$result = model()->open('contact_languages_link')->delete($id, 'language_id');
			}
		}
		
		print json_encode( array('success'=>(bool)$result, 'id'=>$id ));
		
	}


	/**
	 * @abstract Deletes a group
	 * @param integer $id
	 */
	public function ajax_deleteSpecialty($id = false){

		$result = false;
		if($id && ctype_digit($id)){
			$result = model()->open('contact_specialties')->delete($id);

			if($result){
				$result = model()->open('contact_specialties_link')->delete($id, 'specialty_id');
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
			CREATE TABLE `contact_groups` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `name` varchar(155) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$success = $model->query($sql);


		$sql = "
			CREATE TABLE IF NOT EXISTS `contact_groups_link` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `contact_id` int(10) unsigned NOT NULL,
			  `group_id` int(10) unsigned NOT NULL,
			  `sort_order` int(11) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$success = $model->query($sql);


		$sql = "
			CREATE TABLE `contact_images` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `contact_id` int(10) unsigned NOT NULL,
			  `filename_orig` varchar(155) NOT NULL,
			  `filename_thumb` varchar(155) NOT NULL,
			  `width_orig` int(10) unsigned NOT NULL,
			  `height_orig` int(10) unsigned NOT NULL,
			  `width_thumb` int(10) unsigned NOT NULL,
			  `height_thumb` int(10) unsigned NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		$success = $model->query($sql);


		$sql = "
			CREATE TABLE `contact_languages` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `language` varchar(155) NOT NULL default '',
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$success = $model->query($sql);
		
		$sql = "
			CREATE TABLE `contact_languages_link` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `contact_id` int(10) unsigned NOT NULL,
			  `language_id` int(10) unsigned NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$success = $model->query($sql);


		$sql = "
			CREATE TABLE `contact_specialties` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`specialty` VARCHAR( 255 ) NOT NULL
			) ENGINE = MYISAM ;";
		$success = $model->query($sql);


		$sql = "
			CREATE TABLE `contact_specialties_link` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`contact_id` INT UNSIGNED NOT NULL ,
			`specialty_id` INT UNSIGNED NOT NULL
			) ENGINE = MYISAM ;";
		$success = $model->query($sql);


		$sql = "
			CREATE TABLE `contacts` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `title` varchar(155) NOT NULL,
			  `first_name` varchar(155) NOT NULL,
			  `middle_name` varchar(155) NOT NULL,
			  `last_name` varchar(155) NOT NULL,
			  `accreditation` varchar(155) NOT NULL,
			  `job_title` varchar(255) NOT NULL,
			  `company` varchar(155) NOT NULL default '',
			  `specialty` text NOT NULL,
			  `website` varchar(255) NOT NULL default '',
			  `email` varchar(155) NOT NULL,
			  `address_1` varchar(155) NOT NULL default '',
			  `city` varchar(155) NOT NULL default '',
			  `state` varchar(2) NOT NULL default '',
			  `postal` varchar(20) NOT NULL default '',
			  `telephone` varchar(20) NOT NULL default '',
			  `telephone_2` varchar(20) NOT NULL,
			  `fax` varchar(20) NOT NULL,
			  `brief_bio` longtext NOT NULL,
			  `bio` longtext NOT NULL,
			  PRIMARY KEY  (`id`),
			  FULLTEXT KEY `FULLTEXT` (`title`,`first_name`,`last_name`,`accreditation`,`company`,`specialty`,`address_1`,`city`,`state`,`postal`,`telephone`,`bio`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$success = $model->query($sql);


		$sql = "
			CREATE TABLE `contacts_photos` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `directory_id` int(10) unsigned NOT NULL default '0',
			  `photo_path` text NOT NULL,
			  `photo_url` text NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$success = $model->query($sql);


		$sql = "
			CREATE TABLE IF NOT EXISTS `section_contactgroup_display` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `page_id` int(10) unsigned NOT NULL default '0',
			  `title` varchar(255) NOT NULL default '',
			  `show_title` tinyint(1) NOT NULL default '1',
			  `template` varchar(155) NOT NULL,
			  `group_id` int(11) NOT NULL,
			  `sort_method` enum('sort_order','alpha') NOT NULL default 'sort_order',
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
		$success = $model->query($sql);


		$sql = "
			CREATE TABLE `section_contacts_display` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `page_id` int(10) unsigned NOT NULL default '0',
			  `title` varchar(255) NOT NULL default '',
			  `show_title` tinyint(1) NOT NULL default '1',
			  `template` varchar(155) NOT NULL,
			  `contact_id` int(10) unsigned NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		$success = $model->query($sql);


		$sql = "INSERT INTO `permissions` (`user_id`, `group_id`, `interface`, `module`, `method`) VALUES (0, 2, 'Admin', 'Contacts', '*');";
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

		$model->query('DROP TABLE `contacts`');
		$model->query('DROP TABLE `section_contacts_display`');
		$model->query('DROP TABLE `section_contactgroup_display`');
		$model->query('DROP TABLE `contacts_photos`');
		$model->query('DROP TABLE `contact_languages_link`');
		$model->query('DROP TABLE `contact_languages`');
		$model->query('DROP TABLE `contact_images`');
		$model->query('DROP TABLE `contact_groups_link`');
		$model->query('DROP TABLE `contact_groups`');
		$model->query('DROP TABLE `contact_specialties_link`');
		$model->query('DROP TABLE `contact_specialties`');

		$model->query('DELETE FROM section_list WHERE type = "contact_display"');
		$model->query('DELETE FROM section_list WHERE type = "contactgroup_display"');

		return true;

	}
}
?>