<?php

/**
 * @abstract Handles forms for user accounts
 * @package Aspen_Framework
 * @author Michael Botsko
 * @copyright 2008 Trellis Development, LLC
 * @uses User
 */
class Customers_Admin {

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
	
	
	/**
	 * @abstract Runs the authentication process on the login form data
	 * @access public
	 */
	public function authenticate(){
		if(user()->authenticate()){
		
			$redirect = session()->getRaw('post-login_redirect');
			$redirect = empty($redirect) ? router()->interfaceUrl() : $redirect;
		
			header("Location: " . $redirect);
			exit;
		} else {
			user()->login_failed();
		}
	}

	
	/**
	 * @abstract Processes a logout
	 * @access public
	 */
	public function logout(){
		user()->logout();
		header("Location: " . router()->interfaceUrl());
		exit;
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

		if(user()->add()){
			$this->APP->sml->addNewMessage('User account has been created successfully.');
			router()->redirect('view');
		}
		
		$data['groups'] = user()->groupList();
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

		if(user()->edit($id)){
			$this->APP->sml->addNewMessage('User account changes have been saved successfully.');
			router()->redirect('view');
		}
		
		$data['groups'] = user()->groupList();
		$data['values'] = $this->APP->form->getCurrentValues();

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'edit.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);

	}
	
	
	/**
	 * @abstract Displays and processes the my account form
	 * @access public
	 */
	public function my_account(){

		if(user()->my_account()){
			$this->APP->sml->addNewMessage('Your account has been updated successfully.');
			router()->redirect('view', false, 'Index');
		}

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'my_account.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display();

	}


	/**
	 * @abstract Deletes a user record
	 * @param integer $id The record id of the user
	 * @access public
	 */
	public function delete($id = false){
		if(user()->delete($id)){
			router()->redirect('view');
		}
	}


	/**
	 * @abstract Displays a permission denied error message
	 * @access public
	 */
	public function denied(){
		$this->APP->template->addView($this->APP->template->getTemplateDir() . '/header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'denied.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir() . '/footer.tpl.php');
		$this->APP->template->display();
	}
	

	/**
	 * @abstract Displays the user login page
	 * @access public
	 */
	public function login(){
		
		user()->login();
		
		$this->APP->template->addView($this->APP->template->getTemplateDir() . '/header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'login.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir() . '/footer.tpl.php');
		$this->APP->template->display();
	}

	
	/**
	 * @abstract Displays and processes the forgotten password reset form
	 * @access public
	 */
	public function forgot(){

		if(user()->forgot() == 1){
			$this->APP->sml->addNewMessage('Your password has been reset. Please check your email.');
			router()->redirect('login');
		}
		elseif(user()->forgot() == -1){
			$this->APP->sml->addNewMessage('We were unable to find any accounts matching that username.');
			router()->redirect('forgot');
		}

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'forgot.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display();

	}
}
?>