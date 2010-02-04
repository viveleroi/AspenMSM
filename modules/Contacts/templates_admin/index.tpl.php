<h2>Contact Directory</h2>
<?php print $this->APP->sml->printMessage(); ?>
<div id="directory-list-nav" class="clearfix"></div>
<div class="pointer"></div>
<div class="frame">
	<div id="directory-names" >
		<h3>Contact List</h3>
		<div id="list-holder" class="clearfix">
			<div id="scroll-list" class="scroll-pane">
				<ul id="directory-list">
					<?php
					
					if($directory_list['RECORDS']){
						foreach($directory_list['RECORDS'] as $directory){
					?>
					
					<li id="contact-<?php print $directory['id']; ?>">
						
					 	<a href="<?php print $this->createXhtmlValidUrl('edit', array('id' => $directory['id'])) ?>" title="Edit this Contact"><?php print $directory['last_name']; ?>, <?php print $directory['first_name']; ?></a>
					 	<?php print empty($directory['company']) ? '' : ' - '.$directory['company']; ?>
					 	<a href="" class="drag">Drag</a>
					</li>
				
					<?php
						}
					} else {
					?>
					<il class="empty">There are currently no staff listings.</li>
					<?php } ?>
				</ul>
			</div>
			<a class="add" href="<?php print $this->createXhtmlValidUrl('add'); ?>" title="Add New Listing">Add</a>
		</div>
	</div>
	<div id="directory-groups">
		<h3>Contact Groups</h3>
		<!-- TEMPLATE, is used by js to add/display groups. -->
		<div id="groupholder-0" class="group">
			<div class="legend">
				<a id="group_0" class="toggle-group open" href="#">Show</a>
				<strong></strong>
			</div>
			<div id="group_0_details" class="group-info clearfix">
				<ul>
					<li class="empty">There are no contacts in this group.</li>
				</ul>
				<a class="delete" href="#" title="Are you sure you wish to delete this group?">Remove</a>
			</div>
		</div>
		<!-- TEMPLATE END -->
		<div id="add-group-area" class="clearfix">
			<form id="add-group" action="index.php" method="post">
				<input class="add-fld" type="text" name="add-group" />
				<input class="add-btn" type="submit" name="Add" />
			</form>
		</div>
		<?php
		if($group_list['RECORDS']){
			foreach($group_list['RECORDS'] as $group){
		?>
		<div id="groupholder-<?php print $group['id']; ?>" class="group">
			<div class="legend">
				<a id="group_<?php print $group['id']; ?>" class="toggle-group open" href="#">Show</a>
				<strong><?php print $this->truncateString($group['name'], 25); ?></strong>
			</div>
			<div id="group_<?php print $group['id']; ?>_details" class="group-info clearfix">
				<ul class="group-list" id="group_<?php print $group['id']; ?>_list">
					<?php
					if($group['contacts']['RECORDS']){
						foreach($group['contacts']['RECORDS'] as $contact){
					?>
					<li class="contact-<?php print $contact['id']; ?> clearfix" id="contact-<?php print $contact['id']; ?>"><span class="drag">Drag</span><span class="listed"><?php print $contact['last_name']; ?>, <?php print $contact['first_name']; ?></span> <a href="#" class="remove" title="Are you sure you wish to remove this listing?">Remove</a></li>
					<?php
						}
					} else {
					?>
					<li class="empty">There are no contacts in this group.</li>
					<?php } ?>
				</ul>
				<a class="sort-toggle" id="group_<?php print $group['id']; ?>_trigger" href="#" title="Click to Enable/Disable Sorting">Enable Sorting</a>
				<a class="delete" href="#" title="Are you sure you wish to delete this group?">Remove</a>
			</div>
		</div>
		<?php
				}
			} else {
			?>
		<div class="group">
			<p class="empty">There are currently no groups.</p>
		</div>
			<?php } ?>
		
	</div>
	
</div>