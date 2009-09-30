<?php
/**
 * Template: Search
 */

$results = $this->search();

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
			  	
			  	<?php
				if($results){
					foreach($results as $result){
				?>
						
				<a href="<?php print $result['source_url']; ?>"><?php print $result['source_title']; ?></a>
				<p><?php print $this->template->truncateText($result['source_content']); ?></p>
						
				<?php
					}
				}
				?>

				<?php if($results){ ?>
				<ul id="paginate">
					<?php print $this->search->pagination(); ?>
				</ul>
				<?php } ?>
				
			</div>
			  
			<div id="footer">
				<p></p>
			</div>
		</div>
	</body>
</html>