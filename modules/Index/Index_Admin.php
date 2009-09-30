<?php

/**
 * Index Admin class
 *
 * Displays the index/default page for the admin
 * section. In this case, it's our welcome screen /
 * dashboard
 *
 * @package Aspen Framework
 * @author Michael Botsko, Botsko.net LLC
 * @uses Admin
 */
class Index_Admin {

	/**
	 * @var object Holds our original application
	 * @access public
	 */
	public $APP;


	/**
	 * @abstract Constructor, initializes the module
	 * @return Index_Admin
	 * @access public
	 */
	public function Index_Admin(){
		$this->APP = get_instance();
	}


	/**
	 * Loads our index/default welcome/dashboard screen
	 */
	public function view(){

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'index.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display();
		
	}
}

?>