<?php

/**
 * CMS class
 * @abstract Displays website pages
 * @package WhackCMS
 * @author Michael Botsko, Botsko.net LLC
 */
class Cms extends Bootstrap {


	/**
	 * @abstract Constructor, initializes the module
	 * @return Cms
	 * @access public
	 */
	public function __construct($config){
		
		parent::__construct($config);
		
		// ensure the cms has been installed
		if(!$this->isInstalled()){
			print 'This application must be installed before you may use it. Please follow the documentation for installation.';
			exit;
		}
		
		// determine if the current user is an admin
		define('IS_ADMIN', $this->user->userHasGlobalAccess());
		
		// load pages module, and any module hooks
		$this->loadModule('c3f28790-269f-11dd-bd0b-0800200c9a66');
		
		// run all init commands from library
		$this->cms_lib->load();
		
		// load the template, display the page
		$this->displayPage();
		
	}
	
	
	/**
	 * @abstract Displays our selected template file
	 * @access private
	 */
	private function displayPage(){
		if(file_exists($this->cms_lib->getDisplayFile())){
			include($this->cms_lib->getDisplayFile());
		} else {
			print 'The selected template for this page could not be loaded.';
		}
	}
	

	/**
	 * @abstract Returns a specific URI element in the uri array /PageName/Val/Val = 1/2/3
	 * @param integer $key
	 * @return string
	 */
	public function getUriBit($key){
		return $this->cms_lib->getUriBit($key);
	}
	
	
	/**
	 * @abstract Shows nav children when you're at parent only
	 * @param boolean $toggle
	 * @access public
	 */
	public function nav_show_children_on_at($toggle){
		$this->cms_lib->nav_show_children_on_at = $toggle;
	}
	
	/**
	 * @abstract Shows nav children when you're at parent only
	 * @param boolean $toggle
	 * @access public
	 */
	public function nav_max_nesting_level($level = 25){
		$this->cms_lib->nav_max_nesting_level = $level;
	}
	
	
	/**
	 * @abstract Shows nav children when you're at parent only
	 * @param boolean $toggle
	 * @access public
	 */
	public function nav_reset_nesting_count(){
		$this->cms_lib->nav_max_nesting_level = 25;
	}
	
	/**
	 * @abstract Nest children in parent menu
	 * @param boolean $toggle
	 * @access public
	 */
	public function nav_nest_children($toggle){
		$this->cms_lib->nav_nest_children = $toggle;
	}

	
	/**
	 * @abstract Generates a url for a particular page
	 * @param string $page_id ID of page to generate link to
	 * @param boolean $force_no_rewrite Force no rewrite
	 * @param array $params Any additional parameters to pass
	 * @return string
	 * @access public
	 */
	public function url($page_id = false, $force_no_rewrite = false, $params = false){
		return $this->cms_lib->url($page_id, $force_no_rewrite, $params);
	}
	
	
	/**
	 * @abstract Generates a url for the current page with all params
	 * @param boolean $force_no_rewrite Force no rewrite
	 * @return string
	 * @access public
	 */
	public function selfUrl($force_no_rewrite = false){
		return $this->cms_lib->selfUrl($force_no_rewrite);
	}
	
	
	/**
	 * @abstract Determines if we're at a specific page
	 * @param string $page_title
	 * @param integer $page_id
	 * @return mixed
	 * @access public
	 */
	public function at($page_id, $allow_at_class_bubbling = true){
		return $this->cms_lib->at($page_id, $allow_at_class_bubbling);
	}
	
	
	/**
	 * @abstract Returns boolean if at children of current page
	 * @param integer $id
	 * @return boolean
	 * @access public
	 */
	public function at_children($id = 0){
		return $this->cms_lib->at_children($id);
	}
	
	
	/**
	 * @abstract Generates navigation in an UL format
	 * @param integer $parent_id
	 * @param string $ul_id
	 * @return string
	 * @access public
	 */
	public function navigation($parent_id = 0, $ul_id = 'nav', $show_parent_link = true, $show_parent_link_nest = false, $show_ul = true){
		return $this->cms_lib->navigation($parent_id, $ul_id, $show_parent_link, $show_parent_link_nest, $show_ul);
	}

	
	/**
	 * @abstract Generates navigation for current parent, if at
	 * @param string $ul_id
	 * @return string
	 * @access public
	 */
	public function sub_navigation($ul_id = false, $show_parent_link = true){
		return $this->cms_lib->sub_navigation($ul_id, $show_parent_link);
	}
	

	/**
     * @abstract Shows an individual content section, outside of it's group
     * @param string $section_title Title of section to display
     * @access public
     */
	public function show_section($section_title = false){
		$this->cms_lib->show_section($section_title);
	}
	
	
	/**
     * @abstract Returns an individual content section, outside of it's group
     * @param string $section_title Title of section to display
     * @return array
     * @access public
     */
	public function get_section($section_title = false){
		return $this->cms_lib->get_section($section_title);
	}
	
	
	/**
     * @abstract Returns first content section for current section list
     * @return array
     * @access public
     */
	public function show_first_section(){
		return $this->cms_lib->show_first_section();
	}
	
	
	/**
     * @abstract Loads all content for a page or specific page group
     * @param string $placement_group
     * @return array
     * @access public
     */
	public function getContent($placement_group = false){
		return $this->cms_lib->getContent($placement_group);
	}
	
	
	/**
	 * @abstract Returns current page array
	 * @return array
	 * @access public
	 */
	public function getPageData(){
		return $this->cms_lib->getPageData();
	}
	
	
	/**
     * @abstract Returns page title with website title appended if enabled
     * @return string
     * @access public
     */
	public function page_title($append_website_title = true){
		return $this->cms_lib->page_title($append_website_title);
	}
	
	
	/**
     * @abstract Returns css id attribute text (lowercased, spaces->underscores)
     * @return string
     * @access public
     */
	public function page_id(){
		return $this->cms_lib->page_id();
	}
	
	
	/**
     * @abstract Returns css classes of parents (lowercased, spaces->underscores)
     * @return string
     * @access public
     */
	public function parent_classes(){
		return $this->cms_lib->parent_classes();
	}
	
	
	/**
     * @abstract Returns base website title
     * @return string
     * @access public
     */
	public function website_title(){
		return $this->cms_lib->website_title();
	}
	
	
	/**
     * @abstract Returns page title for H1 elements
     * @return string
     * @access public
     */
	public function page_header(){
		return $this->cms_lib->page_header();
	}
	
	
	/**
     * @abstract Returns absolute theme url
     * @return string
     * @access public
     */
	public function getThemeUrl(){
		return $this->cms_lib->getThemeUrl();
	}
	

	/**
     * @abstract Returns absolute theme path
     * @return string
     * @access public
     */
	public function getThemePath(){
		return $this->cms_lib->getThemePath();
	}
	
	
	/**
     * @abstract Returns meta keywords for current page
     * @return string
     * @access public
     */
	public function page_meta_keywords(){
		return $this->cms_lib->page_meta_keywords();
	}
	
	
	/**
     * @abstract Returns meta description
     * @return string
     * @access public
     */
	public function page_meta_description(){
		return $this->cms_lib->page_meta_description();
	}
	
	
	/**
     * @abstract Prints all content for the current page.
     * @return string
     * @access public
     */
	public function display_content($section = false){
		return $this->cms_lib->display_content($section);
	}
	
	
	/**
	 * @abstract Processes the user login (for front-end pages only)
	 * @access public
	 */
	public function process_login(){
	
		if($this->form->isSubmitted()){
			if($this->user->authenticate()){
				$login = $this->params->session->getRaw('cms_post_login_redirect');
				$login = (empty($login) ? $this->cms_lib->url($this->config('login_post_page_id')) : $login);
				header("Location: " . (empty($login) ? 'index.php' : $login) );
				exit;
			}
		}
	}
	
	
	/**
	 * @abstract Processes the user logout (for front-end pages only)
	 * @access public
	 */
	public function process_logout(){
		$this->user->logout();
		header("Location: " . $this->cms_lib->url(1));
		exit;
	}
	
	
	/**
	 * @abstract Process forgotten password
	 * @access public
	 */
	public function process_forgot(){
		
		if($this->form->isSubmitted()){
			if($this->user->forgot() == 1){
				$this->sml->addNewMessage('Your password has been reset. Please check your email.');
			}
			elseif($this->user->forgot() == -1){
				$this->sml->addNewMessage('We were unable to find any accounts matching that username.');
			}
			
			$login = $this->cms_lib->url($this->config('login_page_id'));
			header("Location: " . (empty($login) ? 'index.php' : $login) );
			exit;
		}
	}
	
	
	/**
	 * @abstract Processes the registration
	 * @access public
	 */
	public function process_registration(){
	
		if($this->form->isSubmitted()){
			
			if($this->user->add()){
		
				// send registration email
				$this->mail->AddAddress($this->form->cv('username'));
				$this->mail->From      	= $this->config('email_sender');
				$this->mail->FromName  	= $this->config('email_sender_name');
				$this->mail->Mailer    	= "mail";
				$this->mail->ContentType= 'text/html';
				$this->mail->Subject   	= $this->website_title() . " Registration Confirmation";
				
				$body = $this->config('registration_email_body');
				$body = str_replace('{website}', $this->website_title(), $body);
				$body = str_replace('{user}', $this->form->cv('username'), $body);
				$body = str_replace('{pass}', $this->params->post->getRaw('password'), $body);
				
				$this->mail->Body = $body;
				$this->mail->Send();
				$this->mail->ClearAddresses();
				
				// send to thanks page
				$thanks = $this->cms_lib->url($this->config('registration_thanks_page_id'));
				header("Location: " . (empty($thanks) ? 'index.php' : $thanks) );
				exit;
			}
		}
	}
	
	
	/**
	 * @abstract Performs a keyword search on the content index
	 * @return mixed
	 */
	public function search($add_params = false){
		return $this->search->search($add_params);
	}
}
?>