<?= sml()->printMessage(); ?>

	<h2>Welcome <?php print session()->getRaw('nice_name') ?>, what would you like to do?</h2>
	<h3>Your Account</h3>
	<ul>
		<li><a href="<?php print $this->xhtmlUrl('my_account', false, 'Users') ?>" title="Click Here to Manage Your Settings">User Settings</a></li>
	</ul>
	<h3>Manage Pages</h3>
	<ul>
		<li><a href="<?php print $this->xhtmlUrl('view', false, 'Pages'); ?>" title="Click Here to Edit Your Pages">Edit A Page</a></li>
		<li><a href="<?php print $this->xhtmlUrl('add', false, 'Pages'); ?>" title="Click Here to Add A Page">Add A Page</a></li>
	</ul>
	<h3>Manage Users</h3>
	<ul>
		<li><a href="<?php print $this->xhtmlUrl('view', false, 'Users'); ?>" title="Click Here to Edit Your Users">Edit A User</a></li>
		<li><a href="<?php print $this->xhtmlUrl('add', false, 'Users'); ?>" title="Click Here to Add A User">Add A User</a></li>
	</ul>
	<h3>Get Help</h3>
	<ul>
		<li><a href="mailto:support@pointcreative.net" title="">Ask A Question</a></li>
	</ul>

	<div id="notes">
		<div>
			<p>In <a href="<?php print $this->xhtmlUrl('view', false, 'Pages'); ?>" title="Click Here to Manage Your Pages">&#8220;Manage Pages&#8221;</a> you can organize your site pages with a simple drag and drop, edit your content and images, and add new pages to your site.</p>
		</div>
		<div>
			<p>In <a href="<?php print $this->xhtmlUrl('view', false, 'Users'); ?>" title="Click Here to Manage Your Users">&#8220;Manage Users&#8221;</a> you can manage all of your site&#8217;s users, change passwords, delete users, and add new users.</p>
		</div>
		<div>
			<p>Don&#8217;t understand something? Need a little extra help with something on your site? Just <a href="mailto:support@pointcreative.net" title="">&#8220;Ask A Question&#8221;</a> by clicking above and we&#8217;ll respond as soon as possible.</p>
		</div>
	</div>
