	<h2><span>Editing User:</span> <strong><?php print $form->cv('nice_name') ?></strong></h2>
	<?= $form->printErrors(); ?>
	<?= sml()->printMessage(); ?>
	<form id="adminform" method="post" action="<?php print $this->action() ?>">
		<div class="frame">
			<h3>Account Information</h3>
			<fieldset>
				<input type="hidden" name="id" value="<?php print $form->cv('id') ?>" />
				<ol>
					<li>
						<label for="first_name">First Name:</label>
						<input id="first_name" name="first_name" type="text" value="<?php print $form->cv('first_name') ?>" />
					</li>
					<li>
						<label for="last_name">Last Name:</label>
						<input id="last_name" name="last_name" type="text" value="<?php print $form->cv('last_name') ?>" />
					</li>
					<li>
						<label for="username">User Name:</label>
						<input id="username" name="username" type="text" value="<?php print $form->cv('username') ?>" />
						<span class="note">Your username will be your email</span>
					</li>
				</ol>
				<ol>
					<li>
						<label for="password">Password:</label>
						<input id="password" name="password" type="password" />
					</li>
					<li>
						<label for="password_confirm">Confirm Password:</label>
						<input id="password_confirm" name="password_confirm" type="password" />
						<span class="note">Please use more than 9 characters</span>
					</li>
				</ol>
				<ol>
					<li>
						<label for="allow_login">Allow Login:</label>
						<input id="allow_login" name="allow_login" type="checkbox"<?php print $form->cv('allow_login') ? ' checked="checked"' : '' ?> value="1" />
					</li>
					<li>
						<div class="false-label">Access Groups:</div>
						<div class="multi">
							<?php $groups = user()->groupList(); ?>
							<?php foreach($groups as $group){ ?>
							<input type="checkbox" name="Groups[]" value="<?php print $group['id'] ?>" id="group_<?php print $group['id'] ?>"<?php print $form->checked('Groups', $group['id']); ?> />
							<label for="group_<?php print $group['id'] ?>"><?php print $group['name'] ?></label>
							<?php } ?>
						</div>
					</li>
				</ol>
			</fieldset>
		</div>
		<fieldset class="action">
			<button class="right" type="submit" name="submit"><span><em>Save</em></span></button>
			<a class="button" href="<?php print $this->xhtmlUrl('view'); ?>" title="Cancel Adding A New User"><span>Cancel</span></a>
		</fieldset>
	</form>
