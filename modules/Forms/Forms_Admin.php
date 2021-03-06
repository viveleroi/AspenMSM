<?php

/**
 * @abstract News_Admin class
 * @author Aspen Framework modadmin
 */
class Forms_Admin {


	/**
	 * @abstract Constructor, initializes the module
	 * @access public
	 */
	public function __construct(){
		template()->addCss('style.css');
	}
	
	
	/**
	 * @abstract Displays the default template for this module.
	 * @access public
	 */
	public function view(){
		$forms = model()->open('forms')->results();
		template()->display(array('forms'=>$forms));
	}


	/**
	 * @abstract Displays and processes the add news form
	 * @access public
	 */
	public function add(){
		$form_id = model()->open('forms')->insert(array('title'=>''));
		router()->redirect('edit', array('id'=>$form_id));
	}

	
	
	/**
	 * @abstract Displays and processes the edit news form
	 * @param integer $id
	 * @access public
	 */
	public function edit($id = false){
		
		$form = new Form('forms', $id);
		if($form->isSubmitted()){
			if($form->save($id)){
				sml()->say('Form has been updated successfully.');
				router()->redirect('view');
			}
		}
		$data['form'] = $form;
 
		template()->addJs('edit.js');
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
		'email'=>post()->getEmail('email'),
		'email_to_user'=>(post()->getAlpha('email_to_user') == 'true' ? 1 : 0),
		'email_to_user_text'=>post()->getRaw('email_to_user_text'),
		'email_form_to_user'=>(post()->getAlpha('email_form_to_user') == 'true' ? 1 : 0),
		'return_page'=>post()->getInt('return_page'),
		'structure'=>$form,
		'hash'=>$hash);
		
		model()->open('forms')->update($data, post()->getInt('id'));

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
		
		$c_XML = new Xml();
		
		// begin forming the xml
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
		$xml .= '<form>'."\n";
		
		if(is_array($editor)){
			foreach($editor as $field){
				
				// input type="text"
				if($field['class'] == "input_text"){
					$xml .= sprintf('<field type="input_text" required="%s">%s</field>'."\n", $field['required'], $c_XML->encode_for_xml($field['values']));
				}
				
				// textarea
				if($field['class'] == "textarea"){
					$xml .= sprintf('<field type="textarea" required="%s">%s</field>'."\n", $field['required'], $c_XML->encode_for_xml($field['values']));
				}
				
				// input type="checkbox"
				if($field['class'] == "checkbox"){
					$xml .= sprintf('<field type="checkbox" required="%s" title="%s">'."\n", $field['required'], (isset($field['title']) ? $c_XML->encode_for_xml($field['title']) : ''));
					if(is_array($field['values'])){
						foreach($field['values'] as $input){
							$xml .= sprintf('<checkbox checked="%s">%s</checkbox>'."\n", $input['default'], $c_XML->encode_for_xml($input['value']));
						}
					}
					$xml .= '</field>'."\n";
				}
				
				// input type="radio"
				if($field['class'] == "radio"){
					$xml .= sprintf('<field type="radio" required="%s" title="%s">'."\n", $field['required'], (isset($field['title']) ? $c_XML->encode_for_xml($field['title']) : ''));
					if(is_array($field['values'])){
						foreach($field['values'] as $input){
							$xml .= sprintf('<radio checked="%s">%s</radio>'."\n", $input['default'], $c_XML->encode_for_xml($input['value']));
						}
					}
					$xml .= '</field>'."\n";
				}
				
				// select
				if($field['class'] == "select"){
					$xml .= sprintf('<field type="select" required="%s" multiple="%s" title="%s">'."\n", $field['required'], $field['multiple'], (isset($field['title']) ? $c_XML->encode_for_xml($field['title']) : ''));
					if(is_array($field['values'])){
						foreach($field['values'] as $input){
							$xml .= sprintf('<option checked="%s">%s</option>'."\n", $input['default'], $c_XML->encode_for_xml($input['value']));
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
		if(model()->open('forms')->delete($id)){
			sml()->say('Your form has successfully been deleted.');
			router()->redirect('view');
		}
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