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
class Index_Admin extends Module {


	/**
	 * Loads our index/default welcome/dashboard screen
	 */
	public function view(){

		template()->display();
		
	}
}

?>