<?php

class Display {

	/**
	 * @var object Holds our original application
	 * @access private
	 */
	protected $APP;

	/**
	 * @var string Holds the section type which is used in the db, tables, page sections
	 */
	protected $section_type = false;


	/**
	 * @abstract Constructor, initializes the module
	 * @return Install_Admin
	 * @access public
	 */
	public function __construct($section_type = false, $class = false){
		$this->APP = get_instance();

		if($section_type && $class){
			$this->section_type = $section_type;
			director()->registerCmsSection($class, $section_type);
		}
	}


	/**
	 * @abstract Returns the section data from the proper table.
	 * @param array $section_data Meta information provided by Director
	 * @return array
	 * @access public
	 */
	public function readSection($section_data){

		$data = array();

		$section_results = $model->query(sprintf('SELECT * FROM section_%s WHERE id = "%s"', $section_data['section_type'], $section_data['section_id']));

		if($section_results->RecordCount()){
			while($section_content = $section_results->FetchRow()){

				$section_content = $section_data;
/*
				$model = model()->open('events');
				//$model->where('public', 1);

				if(isset($section_content['display_num']) && $section_content['display_num']){
					$model->limit(0, $section_content['display_num']);
				}

				$results = $model->results();

				$section_content['results'] = $results;

*/
				$section_content['results'] = false;
				$data['section'] = $section_content;

				if(!$section_data['called_in_template']){
					$data['content'] = $section_content;
				}
			}
		}

		return $data;

	}


	/**
	 * @abstract
	 * @param array $section
	 * @param array $page
	 * @param array $bits
	 * @access public
	 */
	public function displaySection($section, $page, $bits){

		print_r($section);
		print_r($page);
		print_r($bits);

	}


	/**
	 * Enter description here...
	 *
	 * @param unknown_type $rel_path
	 * @param unknown_type $template
	 * @param unknown_type $content
	 * @param unknown_type $page
	 * @param unknown_type $bits
	 */
	public function loadSectionTemplate($rel_path = false, $template_name, $content, $page, $bits){
		$templates = $this->sectionTemplates($rel_path);
		foreach($templates as $template){
			if(trim($template['FILENAME']) == trim($template_name)){
				include($template['DIRECTORY'].DS.$template['FILENAME']);
			}
		}
	}


	/**
	 * @abstract Scans the theme directory for available tempaltes
	 * @return array
	 * @access public
	 */
	public function sectionTemplates($rel_path = false, $recursive = true){

		$path = APPLICATION_PATH . '/themes/' . $this->APP->settings->getConfig('active_theme');
		$path .= $rel_path ? '/' . $rel_path : '';

		$files = $this->APP->file->dirList($path);
		$page_templates = array ();

		foreach($files as $file){

			$dir = $path . '/' . $file;

			// if the file found is a directory, look inside it
			if(is_dir($dir)){
				if($recursive){
					$subfiles = $this->APP->file->dirList($dir);
					foreach($subfiles as $subfile){
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