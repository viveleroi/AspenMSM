	<h2>Reset Your Password</h2>
	<?= $form->printErrors(); ?>
	<?= sml()->printMessage(); ?>
	<form id="login-frm" action="<?php print $this->action(); ?>" method="post">
		<fieldset>
			<ol>
				<li>
					<label for="user">Username:</label>
					<input id="user" name="user" type="text" />
				</li>
			</ol>
		</fieldset>
		<fieldset class="action">
			<button class="right" type="submit" name="submit"><span><em>Reset</em></span></button>
			<a class="button left" href="<?php print $this->xhtmlUrl('login'); ?>" title="Cancel Reset"><span>Cancel</span></a>
		</fieldset>
	</form>
	