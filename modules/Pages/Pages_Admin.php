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
class Pages_Admin extends Module {

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
	 * @abstract Displays a list of pages
	 * @access public
	 */
	public function view(){
		
		template()->addCss('style.css');
		template()->addJs('admin/jtree.js');
		template()->addJs('view.js');
		
		$model = model()->open('pages');
		$model->orderBy('page_sort_order', 'ASC', 'pages:list');
		$this->pages = $model->results();
		$this->pages = $this->pages;

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
		
		template()->display($data);
		
	}
	
	
	
	/**
	 * @abstract Add a new page
	 * @access public
	 */
	public function add(){

		$form = new Form('pages');
		if($form->isSubmitted()){
			if($page_id = $form->save()){
				sml()->say('Your page has been created successfully.');
				router()->redirect('edit', array('id' => $page_id));
			} else {
				sml()->say('An error occurred. Please try again.');
			}
		}
		
		$data['form']		= $form;
		$data['templates'] 	= $this->scanTemplateList();

		template()->display($data);
		
	}
	
	
	/**
	 * @abstract Edits a page and all content sections
	 * @param integer $id
	 */
	public function edit($id){
		
		template()->addCss('style.css');
		template()->addJs('admin/datepicker.js');
		template()->addJs('edit.js');
		template()->addJsVar('last_id', app()->Pages_Admin->section_count);
		
		$data['templates'] 			= $this->scanTemplateList();
		$data['available_sections'] = director()->getPageSections();

		$form = new Form('pages', $id);
		
		// load sections
		$data['sections'] = array();
		
		// pull all references to sections for this page
		$model = model()->open('section_list');
		$model->where('page_id', $id);
		$sections = $model->results();

		if($sections){
			foreach($sections as $section){
				$section_results = model()->open('section_'.strtolower($section['section_type']), $section['section_id']);
				$data['sections'][$section['id']]['meta'] = $section;
				$data['sections'][$section['id']]['content'] = $section_results;
			}
			$this->section_count = count($data['sections']) - 1;
		}
		
		// add in section field names so our form handler can see them
		foreach(director()->getPageSections() as $section_field){
			$form->addField($section_field['option_value']);
		}

		// process the form if submitted
		if($form->isSubmitted()){
				
			// set checkboxes to false if they're not sent from browser
			// @todo is this really needed?
			if(!post()->keyExists('show_in_menu')){
				$form->setCurrentValue('show_in_menu', false);
			}
			if(!post()->keyExists('page_is_live')){
				$form->setCurrentValue('page_is_live', false);
			}
			if(!post()->keyExists('is_parent_default')){
				$form->setCurrentValue('is_parent_default', false);
			}
			if(!post()->keyExists('login_required')){
				$form->setCurrentValue('login_required', false);
			}

			// update page information
			if($form->save($id)){
				
				$model = model()->open('section_list');

				// remove all current sections references
				$model->delete($id, 'page_id');

				$sections = director()->savePageSections($id);

				// store references to the specifc sections
				foreach($sections as $key => $section){
					$model->insert(array(
						'page_id'=>$id,
						'section_type'=>$section['type'],
						'section_id'=>$section['id'],
						'sort_order'=>$key,
						'called_in_template'=>$section['called_in_template'],
						'placement_group'=>$section['placement_group']
					));
				}

				sml()->say('Page changes have been saved successfully. ' .
										template()->link('Edit Again', 'edit', array($id)));
				router()->redirect('view');

			} else {
				sml()->say('An error occurred. Please try again.');
			}
		}

		$data['form'] = $form;

		template()->display($data);

	}

	
	
	/**
	 * @abstract Removes a section
	 * @param integer $id
	 */
	public function ajax_removeSection($id){
		$section = model()->open('section_list', $id);
		if(is_array($section)){
			$section = model()->open('section_'.$section['section_type'])->delete($section['section_id']);
			$section = model()->open('section_list')->delete($id);
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
									template()->xhtmlUrl('edit', array('id' => $page['page']['page_id'])));
						
						$html .= sprintf('<a class="delete confirm" href="%s" title="Are you sure you want to delete this page and all it\'s content?">Delete</a>',
										template()->xhtmlUrl('delete', array('id' => $page['page']['page_id'])));
	

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
							$html .= sprintf('<option value="%d"%s>%s</option>', $page['page']['page_id'], $selected, DataDisplay::truncateString($page['page']['page_title'],35));
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
			app()->db->Execute(sprintf("DELETE FROM pages WHERE page_id = %s", app()->security->dbescape((int)$id)));
			app()->db->Execute(sprintf("UPDATE pages SET parent_id = 0 WHERE parent_id = %s", app()->security->dbescape((int)$id)));
			sml()->say('Page has been deleted successfully.');
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
		$page = model()->open('pages')->quickSelectSingle($id, 'page_id');
		
		if($page){
		
			$new_page = $page['page_is_live'] == 1 ? 0 : 1;
			
			app()->db->Execute(
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
		
		$model = model()->open('pages');
		$model->where('parent_id' , $id);
		$pages = $model->results();
		
		if($pages){
			foreach($pages as $page){
				
				$xml .= '<child_page_id>'.$page['page_id'].'</child_page_id>';
				
				app()->db->Execute(
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

		$model = model()->open('pages');
		$model->orderBy('page_sort_order', 'ASC', 'pages:list');
		$this->pages = $model->results();

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
		
		$pages = get()->getRaw('list');
		$order = 1;

		foreach($pages as $page){
			
			// reset the parent associations
			app()->db->Execute(sprintf('UPDATE pages SET parent_id = "0", page_sort_order = "%s" WHERE page_id = "%s"', $order, $page['id']));
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
				if(!app()->db->Execute($sql)){
					print app()->db->ErrorMsg();
				}
				
				$order++;
				
				if(isset($child['children'])){
					$this->nestPageProcess($child);
				}
			}
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
			director()->loadPageSection($section, $next_id, false, $page_id, $template);
		}
	}
	
	
	/**
	 * @abstract Returns a list of placement groups
	 * @param string $template
	 * @access public
	 */
	public function ajax_getPlacementGroups($template = false){
		
		$model = model()->open('template_placement_groups');
		$model->where('template', $template);
		$placement_groups = $model->results();
		
		$groups = '';
		if($placement_groups){
			foreach($placement_groups as $pg){
				$groups .= sprintf('<group>%s</group>', app()->xml->encode_for_xml($pg['group_name']));
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
		
		$files = files()->dirList($path);
		$page_templates = array ();
	
		foreach($files as $file){
	
			$dir = $path . '/' . $file;

			// if the file found is a directory, look inside it
			if(is_dir($dir)){
			
				$subfiles = files()->dirList($dir);
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