	<h2>Secure Login</h2>
	<?php print $this->APP->form->printErrors(); ?>
	<?php print $this->APP->sml->printMessage(); ?>
	<form id="login-frm" action="<?php print $this->createFormAction('authenticate'); ?>" method="post">
		<fieldset>
			<ol>
				<li>
					<label for="user">Username:</label>
					<input id="user" name="user" type="text" />
				</li>
				<li>
					<label for="pass">Password:</label>
					<input id="pass" name="pass" type="password" />
				</li>
			</ol>
		</fieldset>
		<fieldset class="action">
			<a href="<?php print $this->createXhtmlValidUrl('forgot'); ?>" title="Click Here to Retrieve Your Password">Forgot your password?</a>
			<button class="right" type="submit" name="submit"><span><em>Login</em></span></button>
		</fieldset>
	</form>
	