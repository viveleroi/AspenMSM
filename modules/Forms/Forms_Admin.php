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
		director()->registerPageSection(__CLASS__, 'Form Display', 'form_display');
	}
	
	
	/**
	 * @abstract Displays the default template for this module.
	 * @access public
	 */
	public function view(){
	
		$data = array();
 
		$model = model()->open('forms');
		$data['forms'] = $model->results();

		template()->addView(template()->getTemplateDir().DS . 'header.tpl.php');
		template()->addView(template()->getModuleTemplateDir().DS . 'index.tpl.php');
		template()->addView(template()->getTemplateDir().DS . 'footer.tpl.php');
		template()->display($data);

	}


	/**
	 * @abstract Displays and processes the add news form
	 * @access public
	 */
	public function add(){
		$form_id = $model->executeInsert('forms', array('title' => ''));
		router()->redirect('edit', array('id'=>$form_id));
	}

	
	
	/**
	 * @abstract Displays and processes the edit news form
	 * @param integer $id
	 * @access public
	 */
	public function edit($id = false){

		$form = new Form('forms', $id);
 
		// if form has been submitted
		if($form->isSubmitted()){
			
			if(!$form->isFilled('title')){
				$form->addError('body', 'You must enter a title.');
			}
			
			if(!$form->isFilled('body')){
				$form->addError('body', 'You must enter some content.');
			}
			
			// if we have no errors, save the record
			if(!$form->error()){

				// insert a new record with available data
				if($form->save($id)){
					sml()->say('Form has been updated successfully.');
					router()->redirect('view');
				}
			}
		}
 
		// make sure the template has access to all current values
		$data['form'] = $form;
 
		template()->addView(template()->getTemplateDir().DS . 'header.tpl.php');
		template()->addView(template()->getModuleTemplateDir().DS . 'edit.tpl.php');
		template()->addView(template()->getTemplateDir().DS . 'footer.tpl.php');
		template()->display($data);
 
	}
	
	
	/**
	 * @abstract Saves the serialized form structure to the db
	 * @param integer $id
	 * @param string $title
	 * @param string $ul
	 * @access public
	 */
	public function ajax_saveForm(){
		
		$form = app()->security->dbescape( post()->getRaw('ul') );
		$form = serialize($form);
		$hash = sha1($form);

		$data = array(
		'title'=>post()->getRaw('title'),
		'email'=>post()->getRaw('email'),
		'email_to_user'=>(post()->getAlpha('email_to_user') == 'true' ? 1 : 0),
		'email_to_user_text'=>post()->getRaw('email_to_user_text'),
		'email_form_to_user'=>(post()->getAlpha('email_form_to_user') == 'true' ? 1 : 0),
		'return_page'=>post()->getInt('return_page'),
		'structure'=>$form,
		'hash'=>$hash);
		
		$model->executeUpdate('forms', $data, post()->getInt('id'));

	}
	
	
	/**
	 * @abstract Loads the form structure from db, and produces XML
	 * @param integer $id
	 * @access public
	 */
	public function ajax_loadForm($id){
		
		// pull the record from the db
		$form = model()->open('forms', $id);
		
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
					$xml .= sprintf('<field type="input_text" required="%s">%s</field>'."\n", $field['required'], app()->xml->encode_for_xml($field['values']));
				}
				
				// textarea
				if($field['class'] == "textarea"){
					$xml .= sprintf('<field type="textarea" required="%s">%s</field>'."\n", $field['required'], app()->xml->encode_for_xml($field['values']));
				}
				
				// input type="checkbox"
				if($field['class'] == "checkbox"){
					$xml .= sprintf('<field type="checkbox" required="%s" title="%s">'."\n", $field['required'], (isset($field['title']) ? app()->xml->encode_for_xml($field['title']) : ''));
					if(is_array($field['values'])){
						foreach($field['values'] as $input){
							$xml .= sprintf('<checkbox checked="%s">%s</checkbox>'."\n", $input['default'], app()->xml->encode_for_xml($input['value']));
						}
					}
					$xml .= '</field>'."\n";
				}
				
				// input type="radio"
				if($field['class'] == "radio"){
					$xml .= sprintf('<field type="radio" required="%s" title="%s">'."\n", $field['required'], (isset($field['title']) ? app()->xml->encode_for_xml($field['title']) : ''));
					if(is_array($field['values'])){
						foreach($field['values'] as $input){
							$xml .= sprintf('<radio checked="%s">%s</radio>'."\n", $input['default'], app()->xml->encode_for_xml($input['value']));
						}
					}
					$xml .= '</field>'."\n";
				}
				
				// select
				if($field['class'] == "select"){
					$xml .= sprintf('<field type="select" required="%s" multiple="%s" title="%s">'."\n", $field['required'], $field['multiple'], (isset($field['title']) ? app()->xml->encode_for_xml($field['title']) : ''));
					if(is_array($field['values'])){
						foreach($field['values'] as $input){
							$xml .= sprintf('<option checked="%s">%s</option>'."\n", $input['default'], app()->xml->encode_for_xml($input['value']));
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
		if($model->delete('forms', $id)){
			sml()->say('Your form has successfully been deleted.');
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
		
		$template = $template ? $template : $form->cv('page_template');
		
		$next_id = isset($section['meta']['id']) ? $section['meta']['id'] : $next_id;
		$model = model()->open('template_placement_group');
		$model->where('template', $template);
		$placement_groups = $model->results();
		
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
			
			$model->query(sprintf('
				INSERT INTO section_form_display (page_id, title, form_id, show_title)
				VALUES ("%s", "%s", "%s", "%s")',
					app()->security->dbescape($page_id),
					app()->security->dbescape($section['title']),
					app()->security->dbescape($section['form_id']),
					app()->security->dbescape($section['show_title'])));
					
					
			$sections[] = array(
				'placement_group' => $section['placement_group'],
				'type' => 'form_display',
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
		$success = $model->query($sql);
		
		
		$sql = "
			CREATE TABLE `section_form_display` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `page_id` int(10) unsigned NOT NULL default '0',
			  `title` varchar(255) NOT NULL default '',
			  `form_id` int(11) NOT NULL default '0',
			  `show_title` tinyint(1) NOT NULL default '1',
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		$success = $model->query($sql);
		
		$sql = "INSERT INTO `permissions` (`user_id`, `group_id`, `interface`, `module`, `method`) VALUES (0, 2, 'Admin', 'Forms', '*');";
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
		
		$model->query('DROP TABLE `forms`');
		$model->query('DROP TABLE `section_form_display`');
		$model->query('DELETE FROM section_list WHERE type = "form_display"');
		
		return true;
		
	}
}
?>