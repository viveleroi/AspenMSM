<?php

/**
 * @abstract News_Admin class
 * @author Aspen Framework modadmin
 */
class News_Admin {

	
	/**
	 * @abstract Constructor, initializes the module
	 * @access public
	 */
	public function __construct(){
		template()->addCss('style.css');
		app()->setConfig('enable_uploads', true); // enable uploads
		if(router()->module() == __CLASS__){
			app()->setConfig('upload_server_path', APPLICATION_PATH.DS.'files'.DS.'news');
		}
	}
	
	
	/**
	 * @abstract Displays the default template for this module.
	 * @access public
	 */
	public function view(){
	
		$data = array();
		
		$model = model()->open('news');
		$model->orderBy('timestamp', 'DESC');
		$model->limit(0,5);
		$data['cur_news'] = $model->results();
		
		$model = model()->open('news');
		$model->orderBy('timestamp', 'DESC');
		$model->limit(5,100);
		$data['past_news'] = $model->results();
		
		if(!files()->setUploadDirectory()){
			sml()->say("The file upload directory does not appear to be writable. Please create the folder and set proper permissions.");
		}

		template()->addJs('view.js');
		template()->display($data);

	}


	/**
	 * @abstract Displays and processes the add news form
	 * @access public
	 */
	public function add(){
		$this->edit();
	}

	
	
	/**
	 * @abstract Displays and processes the edit news form
	 * @param integer $id
	 * @access public
	 */
	public function edit($id = false){

		if(!files()->setUploadDirectory()){
			sml()->say("The file upload directory does not appear to be writable. Please create the folder and set proper permissions.");
		}

		$form = new Form('news', $id);
		if(!$id){
			$form->setCurrentValue('timestamp', date("Y-m-d H:i:s"));
		}
 
		// if form has been submitted
		if($form->isSubmitted()){
			
			$file = files()->upload('pdf_filename');
			if(is_array($file) && !empty($file[0])){
				$form->setCurrentValue('pdf_filename', $file[0]['file_name']);
			}

			if($form->save($id)){
				sml()->say('News entry has successfully been updated.');
				router()->redirect('view');
			}
		}
 
		// make sure the template has access to all current values
		$data['form'] = $form;

		template()->addCss('admin/datepicker.css');
		template()->addJs('admin/datepicker.js');
		template()->addJs('edit.js');
		template()->display($data);
 
	}
	
	
	/**
	 * @abstract Deletes a single news record
	 * @param integer $id
	 * @access public
	 */
	public function delete($id = false){
		if(model()->open('news')->delete($id)){
			sml()->say('News entry has successfully been deleted.');
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
		$record = model()->open('news', $id);
		if($record){
			$public = ($record['public'] == 1 ? 0 : 1);
			model()->open('news')->update(array('public'=>$public), $id);
		}
		
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
		$xml .= '<response>'."\n";
		$xml .= '<direction>'.$public.'</direction>';
		$xml .= '</response>'."\n";

		header("Content-Type: text/xml");
		print $xml;
		
	}
	
	
	/**
	 * @abstract Installs the module
	 * @param string $my_guid GUID which is automatically passed by installer
	 * @return boolean
	 */
	public function install($my_guid = false){
		
		$sql = '
			CREATE TABLE `news` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `user_id` int(10) unsigned NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `summary` varchar(255) NOT NULL,
			  `body` longtext NOT NULL,
			  `pdf_filename` varchar(255) NOT NULL,
			  `timestamp` datetime NOT NULL,
			  `public` tinyint(1) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;';
		$success = $model->query($sql);
		
		
		$sql = "
			CREATE TABLE IF NOT EXISTS `section_news_display` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `page_id` int(10) unsigned NOT NULL default '0',
			  `title` varchar(255) NOT NULL default '',
			  `display_num` int(11) NOT NULL default '0',
			  `link_to_full_page` tinyint(1) NOT NULL,
			  `detail_page_id` int(10) unsigned NOT NULL,
			  `show_title` tinyint(1) NOT NULL default '1',
			  `show_description` tinyint(1) NOT NULL,
			  `template` varchar(155) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
		$success = $model->query($sql);
		
		
		$sql = "INSERT INTO `permissions` (`user_id`, `group_id`, `interface`, `module`, `method`) VALUES (0, 2, 'Admin', 'News', '*');";
		$success = $model->query($sql);
		
		// Autoload this class with the Pages module
		if($success){
			$success = app()->modules->registerModuleHook('c3f28790-269f-11dd-bd0b-0800200c9a66', $my_guid);
		}
		
		return $success;
		
	}
	
	
	/**
	 * @abstract Uninstalls the module
	 * @param string $my_guid GUID which is automatically passed by installer
	 * @return boolean
	 */
	public function uninstall($my_guid = false){
		
		$model->query('DROP TABLE `news`');
		$model->query('DROP TABLE `section_news_display`');
		$model->query('DELETE FROM section_list WHERE type = "news_display"');
		
		return true;
		
	}
}
?>