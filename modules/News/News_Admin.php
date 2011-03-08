<?php

/**
 * @abstract News_Admin class
 * @author Aspen Framework modadmin
 */
class News_Admin {

	/**
	 * @var object $APP Holds a reference to our application
	 * @access private
	 */
	private $APP;

	
	/**
	 * @abstract Constructor, initializes the module
	 * @access public
	 */
	public function __construct(){
		$this->APP = get_instance();
		director()->registerPageSection(__CLASS__, 'News Display', 'news_display');
		$this->APP->setConfig('enable_uploads', true); // enable uploads
		if(router()->module() == __CLASS__){
			$this->APP->setConfig('upload_server_path', APPLICATION_PATH.DS.'files'.DS.'news');
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
		
		if(!$this->APP->file->setUploadDirectory()){
			$this->APP->sml->addNewMessage("The file upload directory does not appear to be writable. Please create the folder and set proper permissions.");
		}

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'index.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);

	}


	/**
	 * @abstract Displays and processes the add news form
	 * @access public
	 */
	public function add(){
 
		$this->APP->form->loadTable('news');
		$this->APP->form->setCurrentValue('timestamp', date("Y-m-d H:i:s"));
 
		// if form has been submitted
		if($this->APP->form->isSubmitted()){
			
			if(!$this->APP->form->isFilled('title')){
				$this->APP->form->addError('body', 'You must enter a title.');
			}
			
			if(!$this->APP->form->isFilled('body')){
				$this->APP->form->addError('body', 'You must enter some content.');
			}
			
			// if we have no errors, save the record
			if(!$this->APP->form->error()){
			
				$file = $this->APP->file->upload('pdf_filename');
				
				$this->APP->form->setCurrentValue('user_id', session()->getInt('user_id'));
				$this->APP->form->setCurrentValue('public', 1);
				
				if(isset($file[0]) && is_array($file[0])){
					$this->APP->form->setCurrentValue('pdf_filename', $file[0]['file_name']);
				}
				
				// set html security rules
				$model->setSecurityRule('body', 'allow_html', true);
	
				// insert a new record with available data
				if($this->APP->form->save()){
					// if successful insert, redirect to the list
					$this->APP->sml->addNewMessage('News entry has successfully been added.');
					router()->redirect('view');
				}
			}
		}
 
		// make sure the template has access to all current values
		$data['values'] = $this->APP->form->getCurrentValues();
 
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'add.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);
 
	}

	
	
	/**
	 * @abstract Displays and processes the edit news form
	 * @param integer $id
	 * @access public
	 */
	public function edit($id = false){
		
		if(!$this->APP->file->setUploadDirectory()){
			$this->APP->sml->addNewMessage("The file upload directory does not appear to be writable. Please create the folder and set proper permissions.");
		}

		$this->APP->form->loadRecord('news', $id);
 
		// if form has been submitted
		if($this->APP->form->isSubmitted()){
			
			if(!$this->APP->form->isFilled('title')){
				$this->APP->form->addError('body', 'You must enter a title.');
			}
			
			if(!$this->APP->form->isFilled('body')){
				$this->APP->form->addError('body', 'You must enter some content.');
			}
			
			// if we have no errors, save the record
			if(!$this->APP->form->error()){
			
				$file = $this->APP->file->upload('pdf_filename');
				if(is_array($file) && !empty($file[0])){
					$this->APP->form->setCurrentValue('pdf_filename', $file[0]['file_name']);
				}
				
				// set html security rules
				$model->setSecurityRule('body', 'allow_html', true);

				// insert a new record with available data
				if($this->APP->form->save($id)){
					// if successful insert, redirect to the list
					$this->APP->sml->addNewMessage('News entry has successfully been updated.');
					router()->redirect('view');
				}
			}
		}
 
		// make sure the template has access to all current values
		$data['values'] = $this->APP->form->getCurrentValues();
 
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'edit.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);
 
	}
	
	
	/**
	 * @abstract Deletes a single news record
	 * @param integer $id
	 * @access public
	 */
	public function delete($id = false){
		if($model->delete('news', $id)){
			$this->APP->sml->addNewMessage('News entry has successfully been deleted.');
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
		$record = $model->quickSelectSingle('news', $id, 'news_id');
		
		if($record){
			$public = ($record['public'] == 1 ? 0 : 1);
			$model->executeUpdate('news', array('public'=>$public), $id, 'news_id');
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
		$templates = $this->APP->display->sectionTemplates('modules/news');
		
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
			
			$model->query(sprintf('
				INSERT INTO section_news_display (page_id, title, display_num, link_to_full_page, detail_page_id, show_title, show_description, template)
				VALUES ("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s")',
					$this->APP->security->dbescape($page_id),
					$this->APP->security->dbescape($section['title']),
					$this->APP->security->dbescape($section['display_num']),
					$this->APP->security->dbescape($section['link_to_full_page']),
					$this->APP->security->dbescape($section['detail_page_id']),
					$this->APP->security->dbescape($section['show_title']),
					$this->APP->security->dbescape($section['show_description']),
					$this->APP->security->dbescape($section['template'])));
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'news_display',
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
			$success = $this->APP->modules->registerModuleHook('c3f28790-269f-11dd-bd0b-0800200c9a66', $my_guid);
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