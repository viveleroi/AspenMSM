	<h2>Add, Edit and Organize Your Website Pages</h2>
	<?= sml()->printMessage(); ?>
	<div class="col-head">
		<span class="title">Page Title</span>
		<div class="head-right">
			<span class="head-edit">Edit</span>
			<span class="head-delete">Delete</span>
			<span class="head-view">View</span>
		</div>
	</div>
	<?php print $this->APP->Pages_Admin->displayPage($pages); ?>
	<div class="action">
		<a class="button right" href="<?php print $this->xhtmlUrl('add', false, false); ?>" title="Click to Add A Page"><span>Add A Page</span></a>
	</div>
	