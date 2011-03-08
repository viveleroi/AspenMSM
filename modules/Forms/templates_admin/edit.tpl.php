	<h2><span>Currently Editing:</span> <?php print $values['title']; ?></h2>
	<?php print $this->APP->form->printErrors(); ?>
	<?php print $this->APP->sml->printMessage(); ?>
	<form action="<?php print $this->createFormAction(); ?>" method="post">
		<div class="frame">
			<h3>Form Information</h3>
			<input type="hidden" name="id" id="id" value="<?php print $values['id']; ?>" />
			<fieldset>
				<ol>
					<li>
						<label for="title">Title:</label>
						<input id="title" name="title" type="text" value="<?php print $values['title']; ?>" />
						<a class="help" href="<?php print router()->getModuleUrl() ?>/help/formbuilder-settings-title.htm" title="Title">Help</a>
					</li>
					<li>
						<label for="email">E-mail Form to:</label>
						<input id="email" name="email" type="text" value="<?php print $values['email']; ?>" />
						<a class="help" href="<?php print router()->getModuleUrl() ?>/help/formbuilder-settings-email.htm" title="Email Form To">Help</a>
					</li>
					<li>
						<label for="email_to_user">E-mail User:</label>
						<input id="email_to_user" name="email_to_user" type="checkbox" value="1"<?php print $values['email_to_user'] ? ' checked="checked"' : ''; ?> />
						<a class="help" href="<?php print router()->getModuleUrl() ?>/help/formbuilder-settings-email_user.htm" title="E-mail User">Help</a>
					</li>
					<li>
						<label for="email_to_user_text">E-mail Message:</label>
						<textarea id="email_to_user_text" name="email_to_user_text"><?php print $values['email_to_user_text']; ?></textarea>
						<a class="help" href="<?php print router()->getModuleUrl() ?>/help/formbuilder-settings-email_message.htm" title="E-mail Message">Help</a>
					</li>
					<li>
						<label for="email_form_to_user">Include Form Data:</label>
						<input id="email_form_to_user" name="email_form_to_user" type="checkbox" value="1"<?php print $values['email_form_to_user'] ? ' checked="checked"' : ''; ?> />
						<a class="help" href="<?php print router()->getModuleUrl() ?>/help/formbuilder-settings-include_data.htm" title="Include Form Data">Help</a>
					</li>
					<li>
						<label for="return_page">Return Page:</label>
						<select id="return_page" name="return_page">
							<option value="0">--</option>
							<?php
								$options = $this->grabSelectArray('pages', 'page_title', 'distinct', 'page_title', 'page_id');
								foreach($options as $option){
								  	print '<option value="'.$option['page_id'].'"'.($values['return_page'] == $option['page_id'] ? ' selected="selected"' : '').'>' . $option['page_title'] . '</option>';
								}
							 ?>
						</select>
						<a class="help" href="<?php print router()->getModuleUrl() ?>/help/formbuilder-settings-return.htm" title="Return Page:">Help</a>
					</li>
				</ol>
			</fieldset>
		</div>
		<div class="frame">
			<h3 class="show-hide"><a id="form" class="toggle-frame" href="#" title="Click to Open/Close this Section">Hide</a> Form Builder</h3>
			<div id="form-area">
				<fieldset id="form-fieldset">
					<ul id="form-builder" class="loadfirst">
					
					</ul>
					<div class="toolbox clearfix">
						
						<a id="sort-toggle" href="#" title="Click to Enable/Disable Sorting">Enable Sorting</a>
					</div>
				</fieldset>
			</div>
		</div>
		<fieldset class="action">
			<button class="right" id="save-form" type="submit" name="submit"><span><em>Save</em></span></button>
			<a class="button left" href="<?php print $this->createXhtmlValidUrl('view'); ?>" title="Click to Cancel"><span>Cancel</span></a>
		</fieldset>
	</form>