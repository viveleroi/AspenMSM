<?php

/**
 * Pages Admin class
 *
 * Displays/manages website pages
 *
 * @package AspenCMS_Lite
 * @author Michael Botsko, Botsko.net LLC
 * @uses Admin
 */
class Pages_Admin {

	/**
	 * @var object Holds our original application
	 * @access private
	 */
	private $APP;

	/**
	 * @var object Holds an array of pages for nesting purposes
	 * @access private
	 */
	private $pages;
	
	/**
	 * @var integer Holds a count of sections for the current page
	 * @access public
	 */
	public $section_count = 0;

	
	/**
	 * @abstract Constructor, initializes the module
	 * @return Pages_Admin
	 * @access public
	 */
	public function __construct(){
		$this->APP = get_instance();
		$this->APP->director->registerPageSection(__CLASS__, 'Text Content', 'basic_editor');
/* 		$this->APP->director->registerPageSection(__CLASS__, 'Text with Image Content', 'imagetext_editor'); */
	}


	/**
	 * @abstract Displays a list of pages
	 * @access public
	 */
	public function view(){
		
		$this->APP->model->select('pages');
		$this->APP->model->orderBy('page_sort_order', 'ASC', 'pages:list');
		$this->pages = $this->APP->model->results();
		$this->pages = $this->pages['RECORDS'];

		if($this->pages){
			$nested = array();
			foreach($this->pages as $page){
				$list = $this->nestPages($page);
				if(count($list) > 0){
					$nested[] = $list;
				}
			}
			
			$data['pages'] = $nested;
			
		} else {
			$data['pages'] = $this->pages;
		}


		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'index.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);
		
	}
	
	
	
	/**
	 * @abstract Add a new page
	 * @access public
	 */
	public function add(){

		$this->APP->form->loadTable('pages');

		// process the form if submitted
		if($this->APP->form->isSubmitted()){
			
			$this->APP->form->setCurrentValue('page_sort_order', ($this->APP->model->quickValue('SELECT MAX(page_sort_order) FROM pages', 'MAX(page_sort_order)') + 1));

			// form field validation
			if(!$this->APP->form->isFilled('page_title')){
				$this->APP->form->addError('page_title', 'You must enter a page title.');
			}


			// if we have no errors, save the record
			if(!$this->APP->form->error()){
				
				// set the link text field to the page title if blank
				if(!$this->APP->form->isFilled('page_link_text')){
					$this->APP->form->setCurrentValue('page_link_text', $this->APP->form->cv('page_title'));
				}
				
				if($page_id = $this->APP->form->save()){

					$this->APP->sml->addNewMessage('Your page has been created successfully.');
					router()->redirect('edit', array('id' => $page_id));

				} else {

					$this->APP->sml->addNewMessage('An error occurred. Please try again.');

				}
			}
		}
		
		$data['values'] 	= $this->APP->form->getCurrentValues();
		$data['templates'] 	= $this->scanTemplateList();

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'add.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);
		
	}
	
	
	/**
	 * @abstract Edits a page and all content sections
	 * @param integer $id
	 */
	public function edit($id){
		
		$data['templates'] 			= $this->scanTemplateList();
		$data['available_sections'] = $this->APP->director->getPageSections();

		$this->APP->form->loadRecord('pages', $id);
		
		// load sections
		$data['sections'] = array();
		
		// pull all references to sections for this page
		$sections = $this->APP->model->query(sprintf('SELECT * FROM section_list WHERE page_id = "%s" ORDER BY id', $id));
		if($sections->RecordCount()){
			while($section = $sections->FetchRow()){
				
				// pull the section for the database
				$section_results = $this->APP->model->query(sprintf('SELECT * FROM section_%s WHERE id = "%s"',
																strtolower($section['section_type']), $section['section_id']));
				if($section_results->RecordCount()){
					while($section_content = $section_results->FetchRow()){
						$data['sections'][$section['id']]['meta'] = $section;
						$data['sections'][$section['id']]['content'] = $section_content;
					}
				}
			}
			$this->section_count = count($data['sections']) - 1;
		}
		
		// add in section field names so our form handler can see them
		foreach($this->APP->director->getPageSections() as $section_field){
			$this->APP->form->addField($section_field['option_value']);
		}

		// process the form if submitted
		if($this->APP->form->isSubmitted()){

			// form field validation
			if(!$this->APP->form->isFilled('page_title')){
				$this->APP->form->addError('page_title', 'You must enter a page title.');
			}

			// if we have no errors, process sql
			if(!$this->APP->form->error()){
				
				// set checkboxes to false if they're not sent from browser
				if(!$this->APP->params->post->keyExists('show_in_menu')){
					$this->APP->form->setCurrentValue('show_in_menu', false);
				}
				if(!$this->APP->params->post->keyExists('page_is_live')){
					$this->APP->form->setCurrentValue('page_is_live', false);
				}
				if(!$this->APP->params->post->keyExists('is_parent_default')){
					$this->APP->form->setCurrentValue('is_parent_default', false);
				}
				if(!$this->APP->params->post->keyExists('login_required')){
					$this->APP->form->setCurrentValue('login_required', false);
				}

				// update page information
				if($this->APP->form->save($id)){
					
					// remove all current sections references
					$this->APP->model->query(sprintf('DELETE FROM section_list WHERE page_id = "%s"', $id));
					
					$sections = $this->APP->director->savePageSections($id);
						
					// store references to the specifc sections
					foreach($sections as $key => $section){
						$sql = sprintf('
							INSERT INTO section_list (page_id, section_type, section_id, sort_order, called_in_template, placement_group)
							VALUES ("%s", "%s", "%s", "%s", "%s", "%s")',
								$this->APP->security->dbescape($id),
								$this->APP->security->dbescape($section['type']),
								$this->APP->security->dbescape($section['id']),
								$key,
								$this->APP->security->dbescape($section['called_in_template']),
								$this->APP->security->dbescape($section['placement_group']));
							
						$this->APP->model->query($sql);
					}

					$this->APP->sml->addNewMessage('Page changes have been saved successfully. ' .
											$this->APP->template->createLink('Edit Again', 'edit', array('id'=>$id)));
					router()->redirect('view');
					
				} else {

					$this->APP->sml->addNewMessage('An error occurred. Please try again.');

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
	 * @abstract Saves text area content to the database
	 * @param array $section
	 * @param integer $page_id
	 * @return array
	 * @access public
	 */
	public function saveSection($section, $page_id){

		// loop new section and add into the db
		if(is_array($section)){
			if($section['section_type'] == 'basic_editor'){
				return $this->saveSection_basic($section, $page_id);
			}
			
			if($section['section_type'] == 'imagetext_editor'){
				return $this->saveSection_imagetext($section, $page_id);
			}
		}
	}
	
	
	/**
	 * @abstract Saves single image and text area content to the database
	 * @param array $section
	 * @param integer $page_id
	 * @return array
	 * @access public
	 */
	private function saveSection_imagetext($section, $page_id){
		
		$section['show_title'] = isset($section['show_title']) ? $section['show_title'] : false;
		
		// upload the image and create an image
		$filename = '';
		$thm_name = '';
		
		$uploads = $this->APP->file->upload('image_'.$section['next_id']);

		if(is_array($uploads) && isset($uploads[0]) && !empty($uploads[0])){
			foreach($uploads as $upload){
			
				$filename = $upload['file_name'];
				$thm_name = str_replace($upload['file_extension'], '_thm'.$upload['file_extension'], $upload['file_name']);
				$thm_path = str_replace($upload['file_extension'], '_thm'.$upload['file_extension'], $upload['server_file_path']);
				
				if(!empty($upload['server_file_path'])){
					
					// resize original if needed
					if($this->APP->config('text_image_maxwidth') || $this->APP->config('text_image_maxheight')){
						$img_resize = Thumbnail::create($upload['server_file_path']);
						$img_resize->adaptiveResize($this->APP->config('text_image_maxwidth'),$this->APP->config('text_image_maxheight'));
						$img_resize->save($upload['server_file_path']);
					}
					
					// create the smaller thumbnail
					$thm_create = Thumbnail::create($upload['server_file_path']);
					if($this->APP->config('text_image_crop_center')){
						$thm_create->adaptiveResize();
					}
					$thm_create->adaptiveResize($this->APP->config('text_image_thm_maxwidth'),$this->APP->config('text_image_thm_maxheight'));
					$thm_create->save($thm_path);
				}
			}
		} else {
			
			$filename = $section['image_filename'];
			$thm_name = $section['image_thumbname'];
			
		}
		
		
		// save the data back
		$this->APP->model->query(sprintf('
			INSERT INTO section_imagetext_editor (page_id, title, date_created, content, show_title, image_filename, image_thumbname, image_alt, template)
			VALUES ("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s")',
				$this->APP->security->dbescape($page_id),
				$this->APP->security->dbescape($section['title']),
				date("Y-m-d H:i:s"),
				$this->APP->security->dbescape($section['content'], true),
				$this->APP->security->dbescape($section['show_title']),
				$filename,
				$thm_name,
				$this->APP->security->dbescape($section['image_alt']),
				$this->APP->security->dbescape($section['template'])
				));
		
		$sections[] = array(
			'placement_group' => $section['placement_group'],
			'type' => 'imagetext_editor',
			'called_in_template' => $section['called_in_template'],
			'id' => $this->APP->db->Insert_ID());
		
		return $sections;
		
	}
	
	
	/**
	 * @abstract Saves basic text area content to the database
	 * @param array $section
	 * @param integer $page_id
	 * @return array
	 * @access public
	 */
	private function saveSection_basic($section, $page_id){
		
		$section['show_title'] = isset($section['show_title']) ? $section['show_title'] : false;
			
		$this->APP->model->query(sprintf('
			INSERT INTO section_basic_editor (page_id, title, date_created, content, show_title, template)
			VALUES ("%s", "%s", "%s", "%s", "%s", "%s")',
				$this->APP->security->dbescape($page_id),
				$this->APP->security->dbescape($section['title']),
				date("Y-m-d H:i:s"),
				$this->APP->security->dbescape($section['content'], true),
				$this->APP->security->dbescape($section['show_title']),
				$this->APP->security->dbescape($section['template'])));
		
		$sections[] = array(
			'placement_group' => $section['placement_group'],
			'type' => 'basic_editor',
			'called_in_template' => $section['called_in_template'],
			'id' => $this->APP->db->Insert_ID());
		
		return $sections;
		
	}
	
	
	/**
	 * @abstract Removes a section
	 * @param integer $id
	 */
	public function ajax_removeSection($id){
		$section = $this->APP->model->quickSelectSingle('section_list', $id);
		if(is_array($section)){
			$section = $this->APP->model->delete('section_'.$section['section_type'], $section['section_id']);
			$section = $this->APP->model->delete('section_list', $id);
		}
	}
	
	
	/**
	 * @abstract Displays a page, and calls itself for any page children
	 * @param array $pages An array of a page and its children
	 * @param string $ul The ul string to use for the current level
	 * @return string
	 * @access public
	 */
	public function displayPage($pages, $ul = '<ul id="page-list">'){

			if(is_array($pages)){
					
				$html = $ul ? $ul . "\n" : '';
			
				foreach($pages as $page){
					if(isset($page['page'])){
						$hidden = $page['page']['show_in_menu'] ? '' : ' nomenu';
						$login = $page['page']['login_required'] ? ' login' : '';
						
						$html .= sprintf('<li id="page-%s"><div class="page-highlight%s">', $page['page']['page_id'], $hidden, $login);
						$html .= sprintf('<span class="drag%s">Drag</span>', $login);
						$html .= '<span class="page-title">' . $page['page']['page_title'] . '</span>';
						$html .= '<span class="btns-right">';
						$html .= sprintf('<a class="edit" href="%s" title="Click to Edit this page">Edit</a>',
									$this->APP->template->xhtmlUrl('edit', array('id' => $page['page']['page_id'])));
						
						$html .= sprintf('<a class="delete confirm" href="%s" title="Are you sure you want to delete this page and all it\'s content?">Delete</a>',
										$this->APP->template->xhtmlUrl('delete', array('id' => $page['page']['page_id'])));
	

						$html .= sprintf('<a class="vis_toggle %s" href="#" title="Click to %s this page" id="vis_toggle_%s">%s</a>',
										($page['page']['page_is_live'] ? 'live' : 'private'),
										($page['page']['page_is_live'] ? 'Hide' : 'Show'),
										$page['page']['page_id'],
										$page['page']['page_id'],
										($page['page']['page_is_live'] ? 'Hide' : 'Show')
										);
										
												$html .= '</span></div>';
				
						if(isset($page['children'])){
							$html .= $this->displayPage($page['children'], "\n" . '<ul>');
						}
				
						$html .= '</li>' . "\n";
					}
				}
			
				$html .= $ul ? '</ul>' . "\n" : false;
				
				return $html;
				
		}
	}


	/**
	 * @abstract Displays a page, and calls itself for any page children
	 * @param array $pages An array of a page and its children
	 * @param string $ul The ul string to use for the current level
	 * @return string
	 * @access public
	 */
	public function pageOptionGroups($pages = false, $group = false, $opt_selected = false, $editing_page_id = false, $parents = array()){

			$pages = $pages ? $pages : $this->loadPages();

			if(is_array($pages)){

				$html = $group ? '<optgroup>' : '';

				foreach($pages as $page){

					if(isset($page['page'])){

						// unavailable parents
						if($page['page']['parent_id']){
							$parents = array_merge(array($page['page']['parent_id']), $parents);
						}

						if($editing_page_id !== $page['page']['page_id'] && !in_array($editing_page_id, $parents)){
							$selected = $opt_selected == $page['page']['page_id'] ? ' selected="selected"' : '';
							$html .= sprintf('<option value="%d"%s>%s</option>', $page['page']['page_id'], $selected, $this->APP->template->truncateString($page['page']['page_title'],35));
						}

						if(isset($page['children'])){
							$html .= $this->pageOptionGroups($page['children'], true, $opt_selected, $editing_page_id, $parents);
						}
					}
				}

				$html .= $group ? '</optgroup>' : '';

				return $html;

		}
	}

	
	/**
	 * @abstract Deletes a page
	 * @param integer $id
	 * @access public
	 */
	public function delete($id = false){
		if($id){
			$this->APP->db->Execute(sprintf("DELETE FROM pages WHERE page_id = %s", $this->APP->security->dbescape((int)$id)));
			$this->APP->db->Execute(sprintf("UPDATE pages SET parent_id = 0 WHERE parent_id = %s", $this->APP->security->dbescape((int)$id)));
			$this->APP->sml->addNewMessage('Page has been deleted successfully.');
			router()->redirect('view');
		}
	}
	
	
	/**
	 * @abstract Saves page display setting
	 * @param integer $id
	 * @access public
	 */
	public function ajax_toggleDisplay($id = false){
		
		
		// obtain original state
		$page = $this->APP->model->quickSelectSingle('pages', $id, 'page_id');
		
		if($page){
		
			$new_page = $page['page_is_live'] == 1 ? 0 : 1;
			
			$this->APP->db->Execute(
				sprintf('UPDATE pages SET page_is_live = "%s" WHERE page_id = "%s"', $new_page, $id));
			
			$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
			$xml .= '<response>'."\n";
			$xml .= '<direction>'.$new_page.'</direction>';
			$xml .= '<page_id>'.$id.'</page_id>';
			$xml .= '<children>'."\n";
			$xml .= $this->toggleDisplayOfChildren($id, $new_page);
			$xml .= '</children>'."\n";
			$xml .= '</response>'."\n";

			header("Content-Type: text/xml");
			print $xml;
		}
	}
	
	
	/**
	 * @abstract Toggle display of any page children
	 * @param integer $id
	 * @param integer $new_page
	 * @return string
	 * @access private
	 */
	private function toggleDisplayOfChildren($id, $new_page){
		
		$xml = '';
		
		$this->APP->model->select('pages');
		$this->APP->model->where('parent_id' , $id);
		$pages = $this->APP->model->results();
		
		if($pages['RECORDS']){
			foreach($pages['RECORDS'] as $page){
				
				$xml .= '<child_page_id>'.$page['page_id'].'</child_page_id>';
				
				$this->APP->db->Execute(
					sprintf('UPDATE pages SET page_is_live = "%s" WHERE page_id = "%s" OR parent_id = "%s"', $new_page, $page['page_id'], $page['page_id']));
					
					// continue update for next generation of children
					$xml .= $this->toggleDisplayOfChildren($page['page_id'], $new_page);
				
			}
		}
		
		return $xml;
		
	}


	/**
	 *
	 * @return <type> 
	 */
	private function loadPages(){

		$this->APP->model->select('pages');
		$this->APP->model->orderBy('page_sort_order', 'ASC', 'pages:list');
		$this->pages = $this->APP->model->results();
		$this->pages = $this->pages['RECORDS'];

		if($this->pages){
			$nested = array();
			foreach($this->pages as $page){
				$list = $this->nestPages($page);
				if(count($list) > 0){
					$nested[] = $list;
				}
			}

			return $nested;

		}
	}

	
	/**
	 * @abstract Returns an array of nested pages from a single level array
	 * @param array $page Original array of results
	 * @param integer $current_id Id of the current page
	 * @return array
	 * @access private
	 */
	private function nestPages($page, $current_id = 0){
		
		$nested = array();
		
		if($current_id == $page['parent_id']){
			$nested['page'] = $page;

			$children = array();
			foreach($this->pages as $page_search){
				if($page_search['parent_id'] == $page['page_id'] && $page['page_id'] != $page['parent_id']){
					$children[] = $this->nestPages($page_search, $page['page_id']);
				}
			}

			if(count($children) > 0){
				$nested['children'] = $children;
			}
		}
		
		return $nested;
		
	}
	
	
	
	/**
	 * @abstract Updates the database with all nested pages from the ajax sort request
	 * @access public
	 */
	public function ajax_nestPages(){
		
		$pages = $this->APP->params->get->getRaw('list');
		$order = 1;

		foreach($pages as $page){
			
			// reset the parent associations
			$this->APP->db->Execute(sprintf('UPDATE pages SET parent_id = "0", page_sort_order = "%s" WHERE page_id = "%s"', $order, $page['id']));
			$order++;
			$this->nestPageProcess($page);
			
		}
	}
	
	
	/**
	 * @abstract Helps update the database for all children for a specific task from ajax sort request
	 * @param array $arr An array of ids passed from ajax
	 * @access private
	 */
	private function nestPageProcess($arr){

		if(isset($arr['children'])){
			
			$order = 1;
			
			// go through the children
			foreach($arr['children'] as $child){

				$sql = sprintf('UPDATE pages SET parent_id = "%s", page_sort_order = "%s" WHERE page_id = "%s"', $arr['id'], $order, $child['id']);
				if(!$this->APP->db->Execute($sql)){
					print $this->APP->db->ErrorMsg();
				}
				
				$order++;
				
				if(isset($child['children'])){
					$this->nestPageProcess($child);
				}
			}
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
		$templates = $this->APP->display->sectionTemplates('modules/pages');
		
		if($type == 'basic_editor'){
			include(dirname(__FILE__).DS.'templates_admin'.DS.'section_basic.tpl.php');
		} else {
			include(dirname(__FILE__).DS.'templates_admin'.DS.'section_imagetext.tpl.php');
		}

	}
	
	
	/**
	 * @abstract Loads a new section from ajax request
	 * @param string $section
	 * @param integer $next_id
	 * @param integer $page_id
	 * @param string $template
	 * @access public
	 */
	public function ajax_loadBlankSection($section = false, $next_id = 0, $page_id = false, $template = false){
		if($section){
			$this->APP->director->loadPageSection($section, $next_id, false, $page_id, $template);
		}
	}
	
	
	/**
	 * @abstract Returns a list of placement groups
	 * @param string $template
	 * @access public
	 */
	public function ajax_getPlacementGroups($template = false){
		
		$this->APP->model->select('template_placement_group');
		$this->APP->model->where('template', $template);
		$placement_groups = $this->APP->model->results();
		
		$groups = '';
		if($placement_groups['RECORDS']){
			foreach($placement_groups['RECORDS'] as $pg){
				$groups .= sprintf('<group>%s</group>', $this->APP->xml->encode_for_xml($pg['group_name']));
			}
		}
		
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
		$xml .= '<response>'."\n";
		$xml .= $groups;
		$xml .= '</response>'."\n";

		header("Content-Type: text/xml");
		print $xml;
			
	}
	
	
	/**
	 * @abstract Scans the theme directory for available tempaltes
	 * @return array
	 * @access public
	 */
	public function scanTemplateList(){
	
		$path = APPLICATION_PATH . '/themes/' . settings()->getConfig('active_theme');
		
		$files = $this->APP->file->dirList($path);
		$page_templates = array ();
	
		foreach($files as $file){
	
			$dir = $path . '/' . $file;

			// if the file found is a directory, look inside it
			if(is_dir($dir)){
			
				$subfiles = $this->APP->file->dirList($dir);
				foreach($subfiles as $subfile){
					if(strpos($subfile, 'tpl.php')){
						$fileinfo = $this->parseTemplateFile($dir, $subfile);
						if($fileinfo){
							array_push($page_templates, $fileinfo);
						}
					}
				}
			}
			// otherwise it's just a file so we'll parse it
			else {
	
				$fileinfo = $this->parseTemplateFile($path, $file);
				if($fileinfo){
					array_push($page_templates, $fileinfo);
				}
			}
		}
	
		// sort the values
		$volume = array();
		foreach ($page_templates as $key => $row) {
			$volume[$key]  = $row['NAME'];
		}
		array_multisort($volume, $page_templates);
	
		return $page_templates;
		
	}
  
 
	/**
	 * @abstract Parses template files for meta information
	 * @param string $dir
	 * @param string $file
	 * @return array
	 * @access private
	 */
	private function parseTemplateFile($dir, $file){
		
		$template_data = implode( '', file( $dir.'/'.$file ));
		preg_match( "|Template:(.*)|i", $template_data, $name );
		
		if(isset($name[1])){
			if (!empty($name[1])){
				$fileinfo = array('NAME' => $name[1], 'FILENAME' => $file, 'DIRECTORY'=>$dir);
				return $fileinfo;
			} else {
				return false;
			}
		}
		return false;
	}
}
?>