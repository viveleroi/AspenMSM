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

		$form = new Form('contacts', $id, array('contact_languages','contact_groups','contact_specialties'));

		// proces the form if submitted
		if($form->isSubmitted()){
			if($res_id = $form->save($id)){

				$id = $id ? $id : $res_id;

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
						model()->open('contact_images')->insert(array(
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
	 * @abstract Adds a new group
	 * @param string $name
	 */
	public function ajax_addGroup($name = false){
	
		$id = false;
		if(!empty($name)){
			$id = model()->open('contact_groups')->insert(array('name'=>$name));
		}
		
		print json_encode( array('success'=>(bool)$id, 'id'=>$id, 'name'=>$name) );
		
	}


	/**
	 * @abstract Adds a new group
	 * @param string $name
	 */
	public function ajax_sortGroup($contact_group_id, $ul){
		$success = false;
		$model = model()->open('contact_groups_link');
		$sql = 'UPDATE contact_groups_link SET sort_order = "%d" WHERE contact_id = "%d" AND contact_group_id = "%s"';
		if(is_array($ul)){
			foreach($ul as $key => $contact){
				$model->query( sprintf($sql, (int)$key, (int)$contact, (int)$contact_group_id) );
			}
			$success = true;
		}
		print json_encode( array('success'=>$success, 'contact_group_id'=>$contact_group_id) );
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
				$result = model()->open('contact_groups_link')->delete($id, 'contact_group_id');
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
			$model->where('contact_group_id', $group);
			$contact_exists = $model->results();
			
			if($contact_exists){
				$exists = true;
				$result = false;
				foreach($contact_exists as $exist){
					$id = $exist['id'];
				}
			} else {
				// if not, add the new contact
				$result = $id = $model->insert(array('contact_id'=>$contact,'contact_group_id'=>$group));
			}
			
			$contact = model()->open('contacts', $contact);
			
		}

		print json_encode( array('success'=>(bool)$result, 'id'=>$id, 'contact'=>$contact,'group'=>$group,'exists'=>$exists) );
		
	}
	
	
	/**
	 * @abstract Deletes a group
	 * @param integer $id
	 */
	public function ajax_removeContactFromGroup($id = false, $contact_group_id = false){
	
		$result = false;
		if($id && $contact_group_id){
			$sql = sprintf('DELETE FROM contact_groups_link WHERE contact_id = "%s" AND contact_contact_group_id = "%s"', $id, $contact_group_id);
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
			SELECT contact_languages.*, IF(contact_languages.id IN (SELECT contact_language_id FROM contact_languages_link WHERE contact_id = "%s"), 1, 0 ) as selected
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
			SELECT contact_specialties.*, IF(contact_specialties.id IN (SELECT contact_specialty_id FROM contact_specialties_link WHERE contact_id = "%s"), 1, 0 ) as selected
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
			  `contact_group_id` int(10) unsigned NOT NULL,
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
			  `contact_group_id` int(11) NOT NULL,
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


		$sql = "INSERT INTO `permissions` (`user_id`, `contact_group_id`, `interface`, `module`, `method`) VALUES (0, 2, 'Admin', 'Contacts', '*');";
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