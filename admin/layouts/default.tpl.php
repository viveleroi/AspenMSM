<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php print settings()->getConfig('cms_title'); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="robots" content="all,follow" />

	<style type="text/css" media="screen">@import "<?php print router()->interfaceUrl() ?>/css/screen.css";</style>
	<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="<?php print router()->interfaceUrl() ?>/css/ie.css" media="screen"><![endif]-->

	<script type="text/javascript">
		var INTERFACE_URL = '<?php print router()->interfaceUrl() ?>';
		var APPLICATION_URL = '<?php print router()->appUrl() ?>/';
		var MODULE_URL = '<?php print router()->moduleUrl() ?>';
		var THEME_URL = '<?php print router()->appUrl() . '/themes/' . settings()->getConfig('active_theme') ?>';
		var IS_ADMIN = '<?php print IS_ADMIN ?>';
	</script>

	<script type="text/javascript" src="<?php print router()->interfaceUrl() ?>/js/jquery.js"></script>
	<script type="text/javascript" src="<?php print router()->interfaceUrl() ?>/js/jquery.ui.js"></script>
	<script type="text/javascript" src="<?php print router()->interfaceUrl() ?>/js/jquery.plugins.js"></script>
	<script type="text/javascript" src="<?php print router()->interfaceUrl() ?>/js/cms.js"></script>
	
	<script type="text/javascript" src="<?php print router()->interfaceUrl() ?>/js/tinymce/tiny_mce_gzip.js"></script>
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
<body id="<?php print strtolower(router()->module()) ?>_<?php print strtolower(router()->method()) ?>">
	<?php if(user()->isLoggedIn()){ // if logged in ?>
	<div id="status">
		<ul>
			<li class="user">User: <a href="<?php print $this->xhtmlUrl('users/my_account') ?>" accesskey="u"><?php print session()->combine('%s %s', array('first_name','last_name'), 'getName'); ?></a></li>
			<li class="logout"><?php print $this->link('Logout', 'users/logout'); ?></li>
		</ul>
	</div>
	<?php } ?>
	<div id="header">
		<h1><?php print settings()->getConfig('cms_title'); ?></h1>
	</div>
	<?php if(user()->isLoggedIn()){ // if logged in ?>
	<div id="nav">
		<ul class="clearfix">
			<?php if(user()->userHasAccess('Pages')){ ?>
			<li<?php print router()->here('admin/pages') ? ' class="at"' : '' ?>><a href="<?php print $this->xhtmlUrl('pages/view') ?>" accesskey="p">Site Pages</a></li>
			<?php } ?>
			<?php //print app()->generateNonBaseModuleLinks(); ?>
			
			<?php if(IS_ADMIN){ ?>
			<li<?php print router()->here('admin/users') ? ' class="at"' : '' ?>><a href="<?php print $this->xhtmlUrl('users/view') ?>" accesskey="u">Manage Users</a></li>
			<li class="admin<?php print router()->here('admin/admin') ? ' at' : '' ?>"><a href="<?php print $this->xhtmlUrl('admin/view') ?>" accesskey="s">Admin Settings</a></li>
			<?php } ?>
		</ul>
	</div>
	<?php } //end is loggedin?>
	<div id="content">
		<div class="container">
<?php $this->page(); ?>
		</div>
	</div>
	<?php if(user()->isLoggedIn()){ // if logged in ?>
	<div id="footer">
		<p><strong>Questions?</strong> Contact Trellis Development <a href="mailto:botsko@gmail.com?subject=Bug Report: <?php print app()->config('application_name') ?> v<?php print VERSION ?> @ <?php print app()->params->server->getRaw('SERVER_NAME') ?>" title="Click to contact Trellis Development">support@trellisdev.com</a></p>
	</div>
	<?php } ?>
	<?php $this->loadModuleFooter(); ?>
</body>
<?= $this->htmlHide(VERSION_COMPLETE); ?>
</html>