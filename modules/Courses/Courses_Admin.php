<?php

/**
 * @abstract Handles forms for user accounts
 * @package Aspen_Framework
 * @author Michael Botsko
 * @copyright 2008 Trellis Development, LLC
 * @uses User
 */
class Courses_Admin {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;


	/**
	 * @abstract Constructor, initializes the module
	 * @access public
	 */
	public function __construct(){ $this->APP = get_instance(); }
	
	public function __construct(){
		$this->APP = get_instance();
		$this->APP->director->registerPageSection(__CLASS__, 'Course Display', 'course_display');
		$this->APP->director->registerPageSection(__CLASS__, 'Course List Display', 'course_list_display');
		$this->APP->setConfig('enable_uploads', true); // enable uploads
	}
	

	/**
	 * @abstract Displays the list of users
	 * @access public
	 */
	public function view(){

		$this->APP->model->select('authentication');
		$this->APP->model->orderBy('username', 'ASC');
		$data['users'] = $this->APP->model->results();

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'index.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);

	}

	
	/**
	 * @abstract Displays and processes the add a new user form
	 * @access public
	 */
	public function add(){

		if($this->APP->user->add()){
			$this->APP->sml->addNewMessage('User account has been created successfully.');
			$this->APP->router->redirect('view');
		}
		
		$data['groups'] = $this->APP->user->groupList();
		$data['values'] = $this->APP->form->getCurrentValues();

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'add.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);

	}


	/**
	 * @abstract Displays and processes the edit user form
	 * @access public
	 * @param $id The id of the user record
	 */
	public function edit($id){

		if($this->APP->user->edit($id)){
			$this->APP->sml->addNewMessage('User account changes have been saved successfully.');
			$this->APP->router->redirect('view');
		}
		
		$data['groups'] = $this->APP->user->groupList();
		$data['values'] = $this->APP->form->getCurrentValues();

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'edit.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);

	}



	/**
	 * @abstract Deletes a user record
	 * @param integer $id The record id of the user
	 * @access public
	 */
	public function delete($id = false){
		if($this->APP->user->delete($id)){
			$this->APP->router->redirect('view');
		}
	}




}
?>