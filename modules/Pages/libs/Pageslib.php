<?php


/**
 * 
 */
class Pageslib {
	
	
	/**
	 * @abstract Constructor, initializes the module
	 * @return 
	 * @access public
	 */
	public function aspen_init(){
		if(LS == 'admin'){
			director()->registerPageSection('pageslib', 'Text Content', 'basic_editor');
//			director()->registerPageSection('pageslib', 'Text with Image Content', 'imagetext_editor');
		} else {
			director()->registerCmsSection('pageslib', 'basic_editor');
//			director()->registerCmsSection('pageslib', 'imagetext_editor');
		}
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
		$model = model()->open('template_placement_groups');
		$model->where('template', $template);
		$placement_groups = $model->results();
		$templates = app()->display->sectionTemplates('modules/pages');
		
		$base = str_replace('libs', 'templates_admin', dirname(__FILE__));
		if($type == 'basic_editor'){
			include($base.DS.'section_basic.tpl.php');
		} else {
			include($base.DS.'section_imagetext.tpl.php');
		}

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
		
		$uploads = files()->upload('image_'.$section['next_id']);

		if(is_array($uploads) && isset($uploads[0]) && !empty($uploads[0])){
			foreach($uploads as $upload){
			
				$filename = $upload['file_name'];
				$thm_name = str_replace($upload['file_extension'], '_thm'.$upload['file_extension'], $upload['file_name']);
				$thm_path = str_replace($upload['file_extension'], '_thm'.$upload['file_extension'], $upload['server_file_path']);
				
				if(!empty($upload['server_file_path'])){
					
					// resize original if needed
					if(app()->config('text_image_maxwidth') || app()->config('text_image_maxheight')){
						$img_resize = Thumbnail::create($upload['server_file_path']);
						$img_resize->adaptiveResize(app()->config('text_image_maxwidth'),app()->config('text_image_maxheight'));
						$img_resize->save($upload['server_file_path']);
					}
					
					// create the smaller thumbnail
					$thm_create = Thumbnail::create($upload['server_file_path']);
					if(app()->config('text_image_crop_center')){
						$thm_create->adaptiveResize();
					}
					$thm_create->adaptiveResize(app()->config('text_image_thm_maxwidth'),app()->config('text_image_thm_maxheight'));
					$thm_create->save($thm_path);
				}
			}
		} else {
			
			$filename = $section['image_filename'];
			$thm_name = $section['image_thumbname'];
			
		}
		
		// save the data back
		$ins_id = model()->open('section_imagetext_editor')->insert(array(
			'page_id' => $page_id,
			'title' => $section['title'], 
			'content' => $section['content'], 
			'show_title' => $section['show_title'], 
			'image_filename' => $filename, 
			'image_thumbname' => $thm_name,
			'image_alt' => $section['image_alt'], 
			'template' => $section['template']
		));
		
		$sections[] = array(
			'placement_group' => $section['placement_group'],
			'type' => 'imagetext_editor',
			'called_in_template' => $section['called_in_template'],
			'id' => $ins_id);
		
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
	
		// save the data back
		$ins_id = model()->open('section_basic_editor')->insert(array(
			'page_id' => $page_id,
			'title' => $section['title'], 
			'content' => $section['content'], 
			'show_title' => $section['show_title'],  
			'template' => $section['template']
		));
		
		$sections[] = array(
			'placement_group' => $section['placement_group'],
			'type' => 'basic_editor',
			'called_in_template' => $section['called_in_template'],
			'id' => $ins_id);
		
		return $sections;
		
	}
	
	
	
		/**
	 * @abstract Returns an array of meta/content data for a section type
	 * @param array $section_data
	 * @return unknown
	 * @access public
	 */
	public function readSection($section_data){
		if($section_data['section_type'] == 'basic_editor'){
			return $this->readSection_basic($section_data);
		}
		if($section_data['section_type'] == 'imagetext_editor'){
			return $this->readSection_imagetext($section_data);
		}
	}
	
	
	/**
	 * @abstract Returns the meta/content data for section type "basic"
	 * @param array $section_data
	 * @return array
	 * @access private
	 */
	private function readSection_basic($section_data){
	
		$data = array();

		// pull the section for the database
		$section_content = model()->open('section_basic_editor', $section_data['section_id']);

		$section_content['type'] = $section_data['section_type'];
		if(isset($section_data['group_name'])){
			$section_content['placement_group'] = $section_data['group_name'];
		}
		$data['section'] = $section_content;

		if(!$section_data['called_in_template']){
			$data['content'] = $section_content;
		}
			
		return $data;
	}
	
	
	/**
	 * @abstract Returns the meta/content data for section type "image text"
	 * @param array $section_data
	 * @return array
	 * @access private
	 */
	private function readSection_imagetext($section_data){
	
		$data = array();
	
		// pull the section for the database
		$section_content = model()->open('section_imagetext_editor', $section_data['section_id']);
				
		$section_content['type'] = $section_data['section_type'];
		$section_content['placement_group'] = $section_data['group_name'];
		$data['section'] = $section_content;

		if(!$section_data['called_in_template']){
			$data['content'] = $section_content;
		}

		return $data;
	}
	
	
	/**
	 * @abstract Displays the default display of a section type
	 * @param array $section
	 * @access public
	 * @uses readSection
	 */
	public function displaySection($section, $page, $bits){
		app()->display->loadSectionTemplate('modules/pages', $section['template'], $section, $page, $bits);
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $content
	 */
	public function searchIndexer($content = false){
		$source = array();
		$source[0]['source_type'] 		= $content['type'];
		$source[0]['source_page_id'] 	= $content['page_id'];
		$source[0]['source_id'] 		= $content['id'];
		$source[0]['source_title'] 		= $content['title'];
		$source[0]['source_content'] 	= $content['content'];
		return $source;
	}
}
?>