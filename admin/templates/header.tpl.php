<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php print $this->APP->settings->getConfig('cms_title'); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="robots" content="all,follow" />

	<style type="text/css" media="screen">@import "<?php print $this->APP->router->getInterfaceUrl() ?>/css/screen.css";</style>
	<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="<?php print $this->APP->router->getInterfaceUrl() ?>/css/ie.css" media="screen"><![endif]-->

	<script type="text/javascript">
		var INTERFACE_URL = '<?php print $this->APP->router->getInterfaceUrl() ?>';
		var APPLICATION_URL = '<?php print $this->APP->router->getApplicationUrl() ?>/';
		var MODULE_URL = '<?php print $this->APP->router->getModuleUrl() ?>';
		var THEME_URL = '<?php print $this->APP->router->getApplicationUrl() . '/themes/' . $this->APP->settings->getConfig('active_theme') ?>';
		var IS_ADMIN = '<?php print IS_ADMIN ?>';
	</script>

	<script type="text/javascript" src="<?php print $this->APP->router->getInterfaceUrl() ?>/js/jquery.js"></script>
	<script type="text/javascript" src="<?php print $this->APP->router->getInterfaceUrl() ?>/js/jquery.ui.js"></script>
	<script type="text/javascript" src="<?php print $this->APP->router->getInterfaceUrl() ?>/js/jquery.plugins.js"></script>
	<script type="text/javascript" src="<?php print $this->APP->router->getInterfaceUrl() ?>/js/cms.js"></script>
	
	<script type="text/javascript" src="<?php print $this->APP->router->getInterfaceUrl() ?>/js/tinymce/tiny_mce_gzip.js"></script>
	<script type="text/javascript">
		tinyMCE_GZ.init({
			plugins : 'safari,inlinepopups,imagemanager,paste,table',
			themes : 'advanced',
			languages : 'en',
			disk_cache : true,
			debug : false
		});
	</script>
	
	<?php $this->loadModuleHeader(); ?>


</head>
<body id="<?php print strtolower($this->APP->router->getSelectedModule()) ?>_<?php print strtolower($this->APP->router->getSelectedMethod()) ?>">
	<?php if($this->APP->user->isLoggedIn()){ // if logged in ?>
	<div id="status">
		<ul>
			<li class="user">User: <a href="<?php print $this->createXhtmlValidUrl('my_account', false, 'Users') ?>" accesskey="u"><?php print $this->APP->params->session->getRaw('nice_name'); ?></a></li>
			<li class="logout"><?php print $this->createLink('Logout', 'logout', false, 'Users'); ?></li>
		</ul>
	</div>
	<?php } ?>
	<div id="header">
		<h1><?php print $this->APP->settings->getConfig('cms_title'); ?></h1>
	</div>
	<?php if($this->APP->user->isLoggedIn()){ // if logged in ?>
	<div id="nav">
		<ul class="clearfix">
			<!-- <li<?php print $this->APP->router->here('Index_Admin') ? ' class="at"' : '' ?>><a href="<?php print $this->createXhtmlValidUrl('view', false, 'Index') ?>" accesskey="i">Dashboard</a></li> -->
			<?php if($this->APP->user->userHasAccess('Pages')){ ?>
			<li<?php print $this->APP->router->here('Pages_Admin') ? ' class="at"' : '' ?>><a href="<?php print $this->createXhtmlValidUrl('view', false, 'Pages') ?>" accesskey="p">Site Pages</a></li>
			<?php } ?>
			<?php print $this->APP->generateNonBaseModuleLinks(); ?>
			
			<?php if(IS_ADMIN){ ?>
			<li<?php print $this->APP->router->here('Users_Admin') ? ' class="at"' : '' ?>><a href="<?php print $this->createXhtmlValidUrl('view', false, 'Users') ?>" accesskey="u">Manage Users</a></li>
			<li class="admin<?php print $this->APP->router->here('Admin_Admin') ? ' at' : '' ?>"><a href="<?php print $this->createXhtmlValidUrl('view', false, 'Admin') ?>" accesskey="s">Admin Settings</a></li>
			<?php } ?>
		</ul>
	</div>
	<?php } //end is loggedin?>
	<div id="content">
		<div class="container">
