<?php

/**
 * @abstract News_Admin class
 * @author Aspen Framework modadmin
 */
class Forms_Admin {

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
		$this->APP->director->registerPageSection(__CLASS__, 'Form Display', 'form_display');
	}
	
	
	/**
	 * @abstract Displays the default template for this module.
	 * @access public
	 */
	public function view(){
	
		$data = array();
 
		$this->APP->model->select('forms');
		$data['forms'] = $this->APP->model->results();

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
		$form_id = $this->APP->model->executeInsert('forms', array('title' => ''));
		router()->redirect('edit', array('id'=>$form_id));
	}

	
	
	/**
	 * @abstract Displays and processes the edit news form
	 * @param integer $id
	 * @access public
	 */
	public function edit($id = false){

		$this->APP->form->loadRecord('forms', $id);
 
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

				// insert a new record with available data
				if($this->APP->form->save($id)){
					$this->APP->sml->addNewMessage('Form has been updated successfully.');
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
	 * @abstract Saves the serialized form structure to the db
	 * @param integer $id
	 * @param string $title
	 * @param string $ul
	 * @access public
	 */
	public function ajax_saveForm(){
		
		$form = $this->APP->security->dbescape( $this->APP->params->post->getRaw('ul') );
		$form = serialize($form);
		$hash = sha1($form);

		$data = array(
		'title'=>$this->APP->params->post->getRaw('title'),
		'email'=>$this->APP->params->post->getRaw('email'),
		'email_to_user'=>($this->APP->params->post->getAlpha('email_to_user') == 'true' ? 1 : 0),
		'email_to_user_text'=>$this->APP->params->post->getRaw('email_to_user_text'),
		'email_form_to_user'=>($this->APP->params->post->getAlpha('email_form_to_user') == 'true' ? 1 : 0),
		'return_page'=>$this->APP->params->post->getInt('return_page'),
		'structure'=>$form,
		'hash'=>$hash);
		
		$this->APP->model->executeUpdate('forms', $data, $this->APP->params->post->getInt('id'));

	}
	
	
	/**
	 * @abstract Loads the form structure from db, and produces XML
	 * @param integer $id
	 * @access public
	 */
	public function ajax_loadForm($id){
		
		// pull the record from the db
		$form = $this->APP->model->quickSelectSingle('forms', $id);
		
		$editor = false;
		if(sha1($form['structure']) == $form['hash']){
			$editor = unserialize($form['structure']);
		}
		
		// begin forming the xml
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
		$xml .= '<form>'."\n";
		
		if(is_array($editor)){
			foreach($editor as $field){
				
				// input type="text"
				if($field['class'] == "input_text"){
					$xml .= sprintf('<field type="input_text" required="%s">%s</field>'."\n", $field['required'], $this->APP->xml->encode_for_xml($field['values']));
				}
				
				// textarea
				if($field['class'] == "textarea"){
					$xml .= sprintf('<field type="textarea" required="%s">%s</field>'."\n", $field['required'], $this->APP->xml->encode_for_xml($field['values']));
				}
				
				// input type="checkbox"
				if($field['class'] == "checkbox"){
					$xml .= sprintf('<field type="checkbox" required="%s" title="%s">'."\n", $field['required'], (isset($field['title']) ? $this->APP->xml->encode_for_xml($field['title']) : ''));
					if(is_array($field['values'])){
						foreach($field['values'] as $input){
							$xml .= sprintf('<checkbox checked="%s">%s</checkbox>'."\n", $input['default'], $this->APP->xml->encode_for_xml($input['value']));
						}
					}
					$xml .= '</field>'."\n";
				}
				
				// input type="radio"
				if($field['class'] == "radio"){
					$xml .= sprintf('<field type="radio" required="%s" title="%s">'."\n", $field['required'], (isset($field['title']) ? $this->APP->xml->encode_for_xml($field['title']) : ''));
					if(is_array($field['values'])){
						foreach($field['values'] as $input){
							$xml .= sprintf('<radio checked="%s">%s</radio>'."\n", $input['default'], $this->APP->xml->encode_for_xml($input['value']));
						}
					}
					$xml .= '</field>'."\n";
				}
				
				// select
				if($field['class'] == "select"){
					$xml .= sprintf('<field type="select" required="%s" multiple="%s" title="%s">'."\n", $field['required'], $field['multiple'], (isset($field['title']) ? $this->APP->xml->encode_for_xml($field['title']) : ''));
					if(is_array($field['values'])){
						foreach($field['values'] as $input){
							$xml .= sprintf('<option checked="%s">%s</option>'."\n", $input['default'], $this->APP->xml->encode_for_xml($input['value']));
						}
					}
					$xml .= '</field>'."\n";
				}
			}
		}
		
		$xml .= '</form>'."\n";

		header("Content-Type: text/xml");
		print $xml;

	}
	
	
	/**
	 * @abstract Deletes a single news record
	 * @param integer $id
	 * @access public
	 */
	public function delete($id = false){
		if($this->APP->model->delete('forms', $id)){
			$this->APP->sml->addNewMessage('Your form has successfully been deleted.');
			router()->redirect('view');
		}
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
		$this->APP->model->select('template_placement_group');
		$this->APP->model->where('template', $template);
		$placement_groups = $this->APP->model->results();
		
		include(dirname(__FILE__).DS.'templates_admin'.DS.'section_form.tpl.php');
	
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
			
			$this->APP->model->query(sprintf('
				INSERT INTO section_form_display (page_id, title, form_id, show_title)
				VALUES ("%s", "%s", "%s", "%s")',
					$this->APP->security->dbescape($page_id),
					$this->APP->security->dbescape($section['title']),
					$this->APP->security->dbescape($section['form_id']),
					$this->APP->security->dbescape($section['show_title'])));
					
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'form_display',
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
			CREATE TABLE `forms` (
			  `id` int(11) unsigned NOT NULL auto_increment,
			  `title` varchar(255) NOT NULL,
			  `email` varchar(155) NOT NULL,
			  `email_to_user` tinyint(1) NOT NULL,
			  `email_to_user_text` text NOT NULL,
			  `email_form_to_user` tinyint(1) NOT NULL,
			  `return_page` int(10) unsigned NOT NULL,
			  `structure` longtext NOT NULL,
			  `hash` varchar(50) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		$success = $this->APP->model->query($sql);
		
		
		$sql = "
			CREATE TABLE `section_form_display` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `page_id` int(10) unsigned NOT NULL default '0',
			  `title` varchar(255) NOT NULL default '',
			  `form_id` int(11) NOT NULL default '0',
			  `show_title` tinyint(1) NOT NULL default '1',
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		$success = $this->APP->model->query($sql);
		
		$sql = "INSERT INTO `permissions` (`user_id`, `group_id`, `interface`, `module`, `method`) VALUES (0, 2, 'Admin', 'Forms', '*');";
		$success = $this->APP->model->query($sql);
		
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
		
		$this->APP->model->query('DROP TABLE `forms`');
		$this->APP->model->query('DROP TABLE `section_form_display`');
		$this->APP->model->query('DELETE FROM section_list WHERE type = "form_display"');
		
		return true;
		
	}
}
?>