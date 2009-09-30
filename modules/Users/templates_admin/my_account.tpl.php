	<h2>My Account</h2>
	<?php print $this->APP->form->printErrors(); ?>
	<?php print $this->APP->sml->printMessage(); ?>
	<form action="<?php print $this->createUrl() ?>" method="post">
		<div class="frame">
			<h3>Manage Your Password</h3>
			<fieldset>
				<ol>
					<li>
						<label for="password_1">Password:</label>
						<input id="password_1" name="password_1" type="password" />
					</li>
					<li>
						<label for="password_2">Confirm:</label>
						<input id="password_2" name="password_2" type="password" />
						<span class="note">Enter your new password and confirm to change.</span>
					</li>
				</ol>
			</fieldset>
		</div>
		<fieldset class="action">
			<button class="right" type="submit" name="submit"><span><em>Save</em></span></button>
			<a class="button" href="<?php print $this->createXhtmlValidUrl('view', false, 'Pages'); ?>" title="Click to Cancel Adding A New Page"><span>Cancel</span></a>
		</fieldset>
	</form>