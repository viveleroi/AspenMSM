<?php

/**
 * This configuration file holds application-specific configuration
 * options. These override and extend the default system configurations.
 * Database and server-specific configurations must go into the config.php file.
 */

	// application name
	$config['application_name'] = 'AspenMSM';
	
	// the current application master GUID
	$config['application_guid'] = 'a4cd8de0-197b-11de-8c30-0800200c9a66';
	
	// application version
	$config['application_version'] = '1.0.0';

	// application build
	$config['application_build'] = '';
	
	// default module if none specific in URL
	if(LOADING_SECTION == 'Admin'){
		$config['default_module'] = 'Pages_Admin';
	}
	
	// text w/image thumbnail size
	$config['text_image_thm_maxwidth'] = 100;
	$config['text_image_thm_maxheight'] = 100;
	
	// contact thumbnail size
	$config['contact_image_thm_maxwidth'] = 200;
	$config['contact_image_thm_maxheight'] = 200;
	
	// contact image size
	$config['contact_image_maxwidth'] = 400;
	$config['contact_image_maxheight'] = 400;
	
	// text w/image original max size
	$config['text_image_maxwidth'] = 500;
	$config['text_image_maxheight'] = 500;
	
	// allowed file exts
	$config['allowed_file_extensions'] = array('jpg', 'jpeg', 'png', 'gif', 'pdf');

	// pages that allow extra url elements
	$config['pages_allowing_url_extensions'] = array();

	// search results per page
	$config['search_results_per_page'] = 25;

	// define override pages for specific module searches
	// ex: array('contacts'=>41);
	$config['search_pages'] = array();
	
	// login page template
	$config['login_page_id'] = 2;
	
	// Logout page template
	$config['logout_page_id'] = 3;
	
	// default post-login page template
	$config['login_post_page_id'] = 1;
	
	// Registration body email
	$config['registration_email_body'] = 'Thank you for registering with {website}. Your username is <strong>{user}</strong>. Your password is {pass}.';
	
	// default post-login page template
	$config['registration_thanks_page_id'] = 5;
	
	// session message wrapping html
	$config['sml_message_html'] = '<div class="alert"><div class="success">' . '%s' . '</div></div>';
	
	// form error message wrapping html
	$config['form_error_wrapping_html'] = '<div class="alert"><ul class="warning">%s</ul></div>';
	
	// bypass apache_get_modules function check for mod_rewrite
	$config['bypass_apache_modrewrite_check'] = true;
	
	// require user authentication for admin subdirectory application
	DEFINE('USER_AUTH_ADMIN', true);
	
	// the current file upload path - only set to override default
	$config['upload_server_path'] = str_replace("system", '', dirname(__FILE__)) . '/files';
	
	// enable rewrite
	$config['enable_url_rewrite'] = true;
	
	// from name on outgoing system emails
	$config['email_sender_name'] = 'AspenMSM';
	
	// allow checking for database upgrades
	$config['watch_pending_db_upgrade'] = true;
	
	/* CMS Functionality Configurations */
	$config['include_parent_in_subnav'] = true;
	
	// define allowed elements/attributes for html input
	$allowed = array();
	$allowed[] = 'ol,ul,li,br,strong,em,div,h1,h2,h3,h4,h5,h6,em,thead,tfoot,tbody,tr,td,th,caption,dt,dd,sup,sub';
	$allowed[] = 'span[class]';
	$allowed[] = 'p[class]';
	$allowed[] = 'a[href|title|name|id|class]';
	$allowed[] = 'dl[class]';
	$allowed[] = 'table[class|cellspacing]';
	$allowed[] = 'tr[class]';
	$allowed[] = 'th[class]';
	$allowed[] = 'td[class]';
	$allowed[] = 'img[src|alt|width|height]';
	$allowed[] = 'ol[id|class]';
	$allowed[] = 'ul[id|class]';
	$allowed[] = 'div[id|class]';
	
	$config['html_purifier_settings'][] = array('HTML', 'Allowed', implode(',', $allowed));
	$config['html_purifier_settings'][] = array('Attr', 'EnableID', true);
	$config['html_purifier_settings'][] = array('Core', 'EscapeNonASCIICharacters', true);

	
	// load additional classes
	$config['load_add_core_class'][] = array('classname' => 'Xml', 'folder' => 'formats');
	//$config['load_add_core_class'][] = array('classname' => 'Json', 'folder' => 'formats');
	
	$config['load_add_core_class'][] = array('classname' => 'Thumbnail', 'autoload' => false);
	$config['load_add_core_class'][] = array('classname' => 'Director');
	$config['load_add_core_class'][] = array('classname' => 'Search');
	$config['load_add_core_class'][] = array('classname' => 'Cms_lib');
	$config['load_add_core_class'][] = array('classname' => 'Pages_lib');
	$config['load_add_core_class'][] = array('classname' => 'Display');

?>