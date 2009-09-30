<?php


$sql[] = "

CREATE TABLE IF NOT EXISTS `authentication` (
  `id` int(11) NOT NULL auto_increment,
  `username` text NOT NULL,
  `nice_name` varchar(155) NOT NULL default '',
  `password` text NOT NULL,
  `latest_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `allow_login` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

";


$sql[] = "

CREATE TABLE IF NOT EXISTS `config` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `config_key` varchar(155) NOT NULL default '',
  `default_value` text NOT NULL,
  `current_value` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

";
	
	
$sql[] = "

CREATE TABLE IF NOT EXISTS `error_log` (
  `id` int(11) NOT NULL auto_increment,
  `application` text NOT NULL,
  `version` text NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `visitor_ip` text NOT NULL,
  `referer_url` text NOT NULL,
  `request_uri` text NOT NULL,
  `user_agent` text NOT NULL,
  `error_type` text NOT NULL,
  `error_file` text NOT NULL,
  `error_line` text NOT NULL,
  `error_message` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";
		

$sql[] = "

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(155) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

";

		
$sql[] = "

CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `guid` varchar(50) NOT NULL default '',
  `is_base_module` tinyint(1) NOT NULL,
  `autoload_with` varchar(155) NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

";


$sql[] = "

CREATE TABLE `pages` (
  `page_id` int(11) unsigned NOT NULL auto_increment,
  `show_in_menu` tinyint(1) NOT NULL default '1',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `is_parent_default` tinyint(1) NOT NULL default '0',
  `page_template` varchar(155) NOT NULL default '',
  `page_title` varchar(255) NOT NULL default '',
  `page_link_text` varchar(155) NOT NULL default '',
  `page_link_hover` varchar(255) NOT NULL default '',
  `page_window_title` text NOT NULL,
  `page_body_id` varchar(155) NOT NULL,
  `page_is_live` tinyint(1) NOT NULL default '0',
  `page_sort_order` int(11) NOT NULL default '0',
  `meta_keywords` text NOT NULL,
  `meta_description` text NOT NULL,
  `login_required` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";

		
$sql[] = "

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  `interface` varchar(155) NOT NULL,
  `module` varchar(155) NOT NULL default '',
  `method` varchar(155) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

";


$sql[] = "

CREATE TABLE IF NOT EXISTS `preferences_sorts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `location` varchar(155) NOT NULL default '',
  `sort_by` varchar(155) NOT NULL default '',
  `direction` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";


$sql[] = "

CREATE TABLE `search_content_index` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `source_type` varchar(155) NOT NULL,
  `source_page_id` int(10) unsigned NOT NULL,
  `source_id` int(10) unsigned NOT NULL,
  `source_title` mediumtext NOT NULL,
  `source_content` longtext NOT NULL,
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `FULLTEXT` (`source_title`,`source_content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

";


$sql[] = "

CREATE TABLE IF NOT EXISTS `section_basic_editor` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `page_id` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `content` text NOT NULL,
  `show_title` tinyint(1) NOT NULL default '1',
  `template` varchar(155) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

";
 

$sql[] = "

CREATE TABLE IF NOT EXISTS `section_imagetext_editor` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `page_id` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `content` text NOT NULL,
  `show_title` tinyint(1) NOT NULL default '1',
  `image_filename` varchar(255) NOT NULL,
  `image_thumbname` varchar(255) NOT NULL,
  `image_alt` varchar(255) NOT NULL,
  `template` varchar(155) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";

	
$sql[] = "

CREATE TABLE IF NOT EXISTS `section_list` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `page_id` int(10) unsigned NOT NULL default '0',
  `section_type` varchar(155) NOT NULL default '',
  `section_id` int(10) unsigned NOT NULL default '0',
  `sort_order` int(11) NOT NULL default '0',
  `called_in_template` tinyint(1) NOT NULL default '0',
  `placement_group` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

";


$sql[] = "

CREATE TABLE IF NOT EXISTS `template_placement_group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `template` varchar(255) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";
	
$sql[] = "

CREATE TABLE IF NOT EXISTS `upgrade_history` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `current_build` varchar(155) NOT NULL default '',
  `upgrade_completed` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

";
	
$sql[] = "

CREATE TABLE IF NOT EXISTS `user_group_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

";


$sql[] = "

INSERT INTO `config` (`id`, `config_key`, `default_value`, `current_value`) VALUES
(1, 'active_theme', 'default', ''),
(2, 'home_page', '1', '1'),
(3, 'cms_title', 'CMS', ''),
(4, 'website_title', 'Your Website', ''),
(5, 'meta_keywords', '', ''),
(6, 'meta_description', '', '');
	
";


$sql[] = "

INSERT INTO `groups` (`id`, `name`) VALUES
(1, 'Administrator'),
(2, 'User'),
(3, 'Website User');

";


$sql[] = "

INSERT INTO `modules` (`id`, `guid`, `is_base_module`, `autoload_with`, `sort_order`) VALUES
(1, '652d519c-b7f3-11dc-8314-0800200c9a66', 1, '', 0),
(2, 'f801e330-c7ba-11dc-95ff-0800200c9a66', 1, '', 0),
(3, 'eee1d8c0-d50a-11dc-95ff-0800200c9a66', 1, '', 0),
(4, 'c3f28790-269f-11dd-bd0b-0800200c9a66', 1, '', 0),
(5, '9e385c30-3e5c-11dd-ae16-0800200c9a66', 1, '', 0),
(6, '2f406120-3f1e-11dd-ae16-0800200c9a66', 1, '', 0),
(7, '007b300a-fe0c-4f7b-b36f-ef458c32753a', 1, '', 0);

";

	
$sql[] = "

INSERT INTO `pages` (`page_id`, `parent_id`, `is_parent_default`, `page_template`, `page_title`, `page_link_text`, `page_link_hover`, `page_window_title`, `page_is_live`, `page_sort_order`, `meta_keywords`, `meta_description`, `login_required`) VALUES
(1, 0, 0, 'index.php', 'Home', 'Home', 'AspenMSM Home', 'Home', 1, 1, '', '', 0),
(2, 0, 0, 'login.php', 'Login', 'Login', 'Login', 'Login', 1, 2, '', '', 0),
(3, 0, 0, 'logout.php', 'Logout', 'Logout', 'Logout', 'Logout', 1, 20, '', '', 1),
(4, 0, 0, 'register.php', 'Register', 'Register', 'Register', 'Register', 1, 3, '', '', 0),
(5, 4, 0, 'index.php', 'Thank You', '', '', 'Thank You', 1, 4, '', '', 0),
(6, 4, 0, 'forgot.php', 'Password Reset', 'Password Reset', 'Password Reset', 'Password Reset', 1, 4, '', '', 0);

	
";


$sql[] = "

INSERT INTO `permissions` (`id`, `user_id`, `group_id`, `interface`, `module`, `method`) VALUES
(1, 0, 1, '*', '*', '*'),
(2, 0, 2, 'Admin', 'Index', '*'),
(3, 0, 2, 'Admin', 'Pages', '*'),
(5, 0, 2, 'Admin', 'Users', 'my_account'),
(6, 0, 3, '', 'Cms', '*');

";


$sql[] = "

INSERT INTO `section_basic_editor` (`id`, `page_id`, `title`, `date_created`, `content`, `show_title`) VALUES
(2, 1, 'Welcome!', '2009-02-17 10:48:06', '<p>Welcome to your new CMS!</p>', 1),
(3, 5, 'Thank You!', '2009-02-17 10:49:06', '<p>Thank you for registering. Please check your email.</p>', 1);


";


$sql[] = "

INSERT INTO `section_list` (`id`, `page_id`, `section_type`, `section_id`, `sort_order`, `called_in_template`, `placement_group`) VALUES
(2, 1, 'basic_editor', 2, 0, 0, 0),
(3, 5, 'basic_editor', 3, 0, 0, 0);

";


$sql[] = "

INSERT INTO `user_group_link` (`id`, `user_id`, `group_id`) VALUES
(1, 1, 1),
(2, 2, 3);

";

?>