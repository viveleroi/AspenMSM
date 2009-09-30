<?php
/**
 * Template: Login
 */
$this->process_login();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php print $this->page_title(); ?></title>
		<link href="<?php print $this->getThemeUrl() ?>/style.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
	
		<div id="container">
			<div id="header">
			
				<h1><?php print $this->website_title(); ?></h1>
			
			</div>
			
			<?php print $this->navigation(); ?>

			<div id="content">
			  	<?php $this->display_content(); ?>
			  	
				<?php print $this->form->printErrors(); ?>
				<?php print $this->sml->printMessage(); ?>
				<form method="post" action="<?php print $this->url(); ?>">
				<p>Username: <input type="text" name="user" size="20" /> </p>
				<p>Password:  <input type="password" name="pass" size="20" /></p>
				<br /><br />
				<p><input type="submit" name="submit" value="Login" /></p>
				</form>
			</div>
			  
			<div id="footer">
				<p></p>
			</div>
		</div>
	</body>
</html>