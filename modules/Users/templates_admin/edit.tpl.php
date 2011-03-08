	<h2><span>Editing User:</span> <strong><?php print $values['nice_name'] ?></strong></h2>
	<?= $form->printErrors(); ?>
	<?php print $this->APP->sml->printMessage(); ?>
	<form id="adminform" method="post" action="<?php print $this->createFormAction() ?>">
		<div class="frame">
			<h3>Account Information</h3>
			<fieldset>
				<input type="hidden" name="id" value="<?php print $values['id'] ?>" />
				<ol>
					<li>
						<label for="nice_name">Name:</label>
						<input id="nice_name" name="nice_name" type="text" value="<?php print $values['nice_name'] ?>" />
					</li>
					<li>
						<label for="username">User Name:</label>
						<input id="username" name="username" type="text" value="<?php print $values['username'] ?>" />
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
						<input id="allow_login" name="allow_login" type="checkbox"<?php print $values['allow_login'] ? ' checked="checked"' : '' ?> value="1" />
					</li>
					<li>
						<div class="false-label">Access Groups:</div>
						<div class="multi">
							<?php $groups = user()->groupList(); ?>
							<?php foreach($groups as $group){ ?>
							<input id="group_<?php print $group['id'] ?>" name="group[]" type="checkbox" value="<?php print $group['id'] ?>"<?php print (in_array($group['id'],$values['group'])? ' checked="checked"' : '') ?> />
							<label for="group_<?php print $group['id'] ?>"><?php print $group['name'] ?></label>
							<?php } ?>
						</div>
					</li>
				</ol>
			</fieldset>
		</div>
		<fieldset class="action">
			<button class="right" type="submit" name="submit"><span><em>Save</em></span></button>
			<a class="button" href="<?php print $this->createXhtmlValidUrl('view'); ?>" title="Cancel Adding A New User"><span>Cancel</span></a>
		</fieldset>
	</form>
