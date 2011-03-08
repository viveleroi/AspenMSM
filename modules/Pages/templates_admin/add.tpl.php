	<h2>Add A New Page</h2>
	<?= $form->printErrors(); ?>
	<?php print $this->APP->sml->printMessage(); ?>
	<form method="post" action="<?php print $this->createFormAction('add'); ?>">
		<div class="frame">
			<h3>General Page Settings</h3>
			<fieldset>
				<ol>
					<li>
						<label for="page_title">Page Title:</label>
						<input id="page_title" name="page_title" type="text" value="<?php print $values['page_title'] ?>" />
						<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-page_title.htm" title="Page Title">Help</a>
					</li>
					<li>
						<label for="page_window_title">Window Title:</label>
						<input id="page_window_title" name="page_window_title" type="text" value="<?php print $values['page_window_title'] ?>" />
						<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-window_title.htm" title="Window Title">Help</a>
					</li>
					<?php if(IS_ADMIN){ ?>
					<li>
						<label for="page_body_id">Body ID:</label>
						<input id="page_body_id" name="page_body_id" type="text" value="<?php print $values['page_body_id'] ?>" />
						<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-body_id.htm" title="Body ID">Help</a>
					</li>
					<?php } ?>
					<li>
						<label for="page_link_text">Link Text:</label>
						<input id="page_link_text" name="page_link_text" type="text" class="inputtext3" value="<?php print $values['page_link_text'] ?>" />
						<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-link_text.htm" title="Link Title">Help</a>
					</li>
					<li>
						<label for="page_link_text">Link Hover Title:</label>
						<input id="page_link_hover" name="page_link_hover" type="text" class="inputtext3" value="<?php print $values['page_link_hover'] ?>" />
						<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-link_title.htm" title="Link Hover Title">Help</a>
					</li>
				<li class="auto">
					<label for="parent_id">Page Parent:</label>
					<select id="parent_id" name="parent_id">
						<option value="0">--</option>
						<?php print $this->APP->Pages_Admin->pageOptionGroups(false, false, $values['parent_id']); ?>
					</select>
					<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-choose_parent.htm" title="Page Parent">Help</a>
				</li>
				<li class="auto">
					<label for="page_template">Page Template:</label>
					<select id="page_template" name="page_template">
						<option value="index.php">Default</option>
						<?php
						if(isset($templates) && is_array($templates)){
							foreach($templates as $template){
						?>
						<option value="<?php print $template['FILENAME'] ?>"<?php print ($values['page_template'] == $template['FILENAME'] ? ' selected' : '')?>><?php print $template['NAME'] ?></option>
						<?php
							}
						}
						?>
					</select>
					<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-page_template.htm" title="Page Template">Help</a>
				</li>
			</ol>
		</fieldset>
		</div>
		<fieldset class="action">
			<button class="right" type="submit" name="submit"><span><em>Continue</em></span></button>
			<a class="button" href="<?php print $this->createXhtmlValidUrl('view', false, 'Pages'); ?>" title="Click to Cancel Adding A New Page"><span>Cancel</span></a>
		</fieldset>
	</form>
