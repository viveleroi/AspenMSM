	<h2>Manage Site Users</h2>
	<?= sml()->printMessage(); ?>
	<div class="frame">
		<h3>Current Users</h3>
			<ul id="user-list" class="list-display">
				 <?php
					if($users['RECORDS']){
						foreach($users['RECORDS'] as $user){
					?>
				<li id="item_<?php print $user['id'] ?>_listing">
					<div class="legend">
						<strong><?php print $user['nice_name'] ?></strong> 
						<span class="icons">
							<a class="edit" href="<?php print $this->xhtmlUrl('edit', array('id' => $user['id'])) ?>" title="Edit this User">Edit</a> 
							<a class="delete confirm" href="<?php print $this->xhtmlUrl('delete', array('id' => $user['id'])) ?>" title="Are you sure you want to delete this user account?">Delete</a>
						</span>
					</div>
				</li>
				<?php
					}
				}
				?>
			</ul>
	</div>
	<div class="action">
		<a class="button right" href="<?php print $this->xhtmlUrl('add', false); ?>" title="Click to Add an Event"><span>Add a User</span></a>
	</div>
