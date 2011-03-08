	<h2>Manage Your Site&#8217;s Forms</h2>
	<?= sml()->printMessage(); ?>
	<div class="frame">
		<h3>Current Forms</h3>
		<ul id="form-list" class="list-display">
			<?php
				if($forms){
					foreach($forms as $record){
			?>
			<li>
				<div class="legend">
					<strong><?php print $record['title'] ?></strong> 
					<span class="icons">
						<a class="edit" href="<?php print $this->xhtmlUrl('edit', array('id' => $record['id'])) ?>" title="Edit this Form">Edit</a> 
						<a class="delete confirm" href="<?php print $this->xhtmlUrl('delete', array('id' => $record['id'])) ?>" title="Are you sure you want to delete this form?">Delete</a>
					</span>
				</div>
			</li>
			<?php
					}
				} else {
			?>
			<li class="empty">No forms listed.</li>
			<?php
				}
			?>
		</ul>
	</div>
	<div class="action">
		<a class="button right" href="<?php print $this->xhtmlUrl('add'); ?>" title="Click to Add A Page"><span>Add Form</span></a>
	</div>