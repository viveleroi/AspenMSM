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
		director()->registerPageSection(__CLASS__, 'News Display', 'news_display');
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
		
		template()->addCss('admin/datepicker.css');
		template()->addJs('admin/datepicker.js');
		template()->addJs('edit.js');
 
		$form = new Form('news');
		$form->setCurrentValue('timestamp', date("Y-m-d H:i:s"));
 
		// if form has been submitted
		if($form->isSubmitted()){
			
			// @a13
//			if(!$form->isFilled('title')){
//				$form->addError('body', 'You must enter a title.');
//			}
//			
//			if(!$form->isFilled('body')){
//				$form->addError('body', 'You must enter some content.');
//			}
			
			$file = files()->upload('pdf_filename');

			$form->setCurrentValue('user_id', session()->getInt('user_id'));
			$form->setCurrentValue('public', 1);

			if(isset($file[0]) && is_array($file[0])){
				$form->setCurrentValue('pdf_filename', $file[0]['file_name']);
			}

			// set html security rules
			// @a13
//			$model->setSecurityRule('body', 'allow_html', true);

			// insert a new record with available data
			if($form->save()){
				// if successful insert, redirect to the list
				sml()->say('News entry has successfully been added.');
				router()->redirect('view');
			}
			
		}
 
		// make sure the template has access to all current values
		$data['form'] = $form;

		template()->display($data);
 
	}

	
	
	/**
	 * @abstract Displays and processes the edit news form
	 * @param integer $id
	 * @access public
	 */
	public function edit($id = false){
		
		template()->addCss('admin/datepicker.css');
		template()->addJs('admin/datepicker.js');
		template()->addJs('edit.js');
		
		if(!files()->setUploadDirectory()){
			sml()->say("The file upload directory does not appear to be writable. Please create the folder and set proper permissions.");
		}

		$form = new Form('news', $id);
 
		// if form has been submitted
		if($form->isSubmitted()){
			
			// @a13
//			if(!$form->isFilled('title')){
//				$form->addError('body', 'You must enter a title.');
//			}
//			
//			if(!$form->isFilled('body')){
//				$form->addError('body', 'You must enter some content.');
//			}
			
			
			$file = files()->upload('pdf_filename');
			if(is_array($file) && !empty($file[0])){
				$form->setCurrentValue('pdf_filename', $file[0]['file_name']);
			}

			// set html security rules
			// @a13
//			$model->setSecurityRule('body', 'allow_html', true);

			// insert a new record with available data
			if($form->save($id)){
				// if successful insert, redirect to the list
				sml()->say('News entry has successfully been updated.');
				router()->redirect('view');
			}
		}
 
		// make sure the template has access to all current values
		$data['form'] = $form;

		template()->display($data);
 
	}
	
	
	/**
	 * @abstract Deletes a single news record
	 * @param integer $id
	 * @access public
	 */
	public function delete($id = false){
		if(model()->open('news')->delete($id, 'news_id')){
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
		$record = model()->open('news')->quickSelectSingle($id, 'news_id');
		
		if($record){
			$public = ($record['public'] == 1 ? 0 : 1);
			model()->open('news')->update(array('public'=>$public), $id, 'news_id');
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
	public function sectionEditor($type = false, $next_id = 1, $section = false, $page_id = false, $template = false, $form = false){
		
		$template = $template ? $template : $form->cv('page_template');
		
		$next_id = isset($section['meta']['id']) ? $section['meta']['id'] : $next_id;
		$model = model()->open('template_placement_group');
		$model->where('template', $template);
		$placement_groups = $model->results();
		$templates = app()->display->sectionTemplates('modules/news');
		
		include(dirname(__FILE__).DS.'templates_admin'.DS.'section_news.tpl.php');
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
			$section['show_title'] = isset($section['show_title']) ? $section['show_title'] : false;
			$section['show_description'] = isset($section['show_description']) ? $section['show_description'] : false;
			
			model()->open('section_news_display')->query(sprintf('
				INSERT INTO section_news_display (page_id, title, display_num, link_to_full_page, detail_page_id, show_title, show_description, template)
				VALUES ("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s")',
					app()->security->dbescape($page_id),
					app()->security->dbescape($section['title']),
					app()->security->dbescape($section['display_num']),
					app()->security->dbescape($section['link_to_full_page']),
					app()->security->dbescape($section['detail_page_id']),
					app()->security->dbescape($section['show_title']),
					app()->security->dbescape($section['show_description']),
					app()->security->dbescape($section['template'])));
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'news_display',
				'called_in_template' => $section['called_in_template'],
				'id' => app()->db->Insert_ID());
		}
		
		return $sections;
		
	}
	
	
	/**
	 * @abstract Installs the module
	 * @param string $my_guid GUID which is automatically passed by installer
	 * @return boolean
	 */
	public function install($my_guid = false){
		
		$sql = '
			CREATE TABLE `news` (
			  `news_id` int(10) unsigned NOT NULL auto_increment,
			  `user_id` int(10) unsigned NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `summary` varchar(255) NOT NULL,
			  `body` longtext NOT NULL,
			  `pdf_filename` varchar(255) NOT NULL,
			  `timestamp` datetime NOT NULL,
			  `public` tinyint(1) NOT NULL,
			  PRIMARY KEY  (`news_id`)
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