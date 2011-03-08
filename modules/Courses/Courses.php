<?php
/**
 * @abstract
 * @package
 * @author Jason Verburg, Point Creative, Inc.
 * @uses Admin
 */
class Menus extends Display {


	/**
	 * @abstract Constructor, initializes the module
	 * @return Install_Admin
	 * @access public
	 */
	public function __construct(){
		//parent::__construct('menu_display', __CLASS__);
		parent::__construct();
		director()->registerCmsSection(__CLASS__, 'menu_display');
	}
	
	
	/**
	 * @abstract Returns the section information from the db
	 * @param array $section_data
	 * @return array
	 */
	
	public function readSection($section_data){
		
		$data = array();

		$section_results = $model->query(sprintf('SELECT * FROM section_%s WHERE id = "%s"', $section_data['section_type'], $section_data['section_id']));

		if($section_results->RecordCount()){
			while($section_content = $section_results->FetchRow()){
				
				$section_content['type'] = $section_data['section_type'];
				$section_content['placement_group'] = $section_data['group_name'];

				// pull the groups
				$menu = $model->quickSelectSingle('menu_groups', $section_content['group_id']);
					
				$model = model()->open('menu_items');
				$model->leftJoin('menu_link', 'item_id', 'id', array('menu_id'));
				$model->where('menu_id', $menu['id']);
				$model->orderBy('item');
				$menu['menu_items'] = $model->results();
				
				$section_content['results'] = $menu;
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
	 */
	public function displaySection($section, $page, $bits){
		$section['template'] = 'basic.tpl.php';
		$this->APP->display->loadSectionTemplate('modules/menus/basic', $section['template'], $section, $page, $bits);
		
	}


}
?>