
	<h2><span>Currently Editing Page:</span> <?php print $values['page_title'] ?></h2>
	<a href="<?php print $this->APP->cms_lib->url($values['page_id']); ?>">View Page</a>
	<?= $form->printErrors(); ?>
	<?= sml()->printMessage(); ?>
	<form id="page-edit"  class="cms-frm" method="post" action="<?php print $this->action(); ?>" enctype="multipart/form-data">
		<input type="hidden" value="<?php print $values['page_id'] ?>" name="page_id" id="page_id" />
		<div class="frame">
			<h3 class="show-hide"><a id="general" class="toggle-frame" href="#" title="Click to Open/Close this Section">Hide</a> Page Settings</h3>
			<div id="general-area" class="loadfirst clearfix">
				<fieldset>
					<ol>
						<li>
							<label for="page_is_live">Page Live Status:</label>
							<input id="page_is_live" name="page_is_live" type="checkbox" value="1"<?php print $values['page_is_live'] ? ' checked="checked"' : '' ?> />
							<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-show_page.htm" title="Page Live Status">Help</a>
						</li>
						<li>
							<label for="login_required">Private:</label>
							<input id="login_required" name="login_required" type="checkbox" value="1"<?php print $values['login_required'] ? ' checked="checked"' : '' ?> />
							<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-password_protected.htm" title="Private">Help</a>
						</li>
					</ol>

					<ol>
						<li>
							<label for="page_title">Page Title:</label>
							<input id="page_title" name="page_title" type="text" class="inputtext2" value="<?php print $values['page_title'] ?>" />
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
							<label for="show_in_menu">Show in Menu:</label>
							<input id="show_in_menu" name="show_in_menu" type="checkbox" value="1"<?php print $values['show_in_menu'] ? ' checked="checked"' : '' ?> />
							<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-show_in_menu.htm" title="Show In Menu">Help</a>
						</li>
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
								<?php print $this->APP->Pages_Admin->pageOptionGroups(false, false, $values['parent_id'], $values['page_id']); ?>
							</select>
							<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-choose_parent.htm" title="Choose Parent">Help</a>
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
					<?php if(IS_ADMIN){ ?>
					<ol>
						
						<li class="full">
							<label for="meta_keywords">META Keywords:</label>
							<input id="meta_keywords" name="meta_keywords" type="text" value="<?php print $values['meta_keywords'] ?>" />
							<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-meta_keywords.htm" title="Meta Keywords">Help</a>
						</li>
						<li class="full">
							<label for="meta_description">META Description:</label>
							<input id="meta_description" name="meta_description" type="text" value="<?php print $values['meta_description'] ?>" />
							<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-meta_description.htm" title="Meta Description">Help</a>
						</li>
						<?php if($values['parent_id']){ ?>
						<li>
							<label for="is_parent_default">Parent Default:</label>
							<input id="is_parent_default" name="is_parent_default" type="checkbox" value="1"<?php print $values['is_parent_default'] ? ' checked="checked"' : '' ?> />
							<a class="help" href="<?php print router()->getModuleUrl() ?>/help/settings-parent_default.htm" title="Parent Default">Help</a>
						</li>
						<?php } ?>
					</ol>
					<?php } ?>
				</fieldset>
			</div>
		</div>
		<div class="frame">
			<h3 class="show-hide"><a id="sections" class="toggle-frame open" href="#" title="Click to Open/Close this Section">Hide</a> Page Sections</h3>
			<div id="sections-area" class="loadfirst clearfix">
				<fieldset id="sections-fieldset">
					<ul id="page-sections">
					<?php
					if(isset($sections) && is_array($sections) && count($sections)){
						foreach($sections as $section){
							
							print '<li id="editor_'.$section['meta']['id'].'_sort" class="list">' . "\n";
							$this->APP->director->loadPageSection($section['meta']['section_type'], false, $section);
							print '</li>';
		
						}
					} else {
						print '<li id="none" class="empty">No page sections available. Select from the options below.</li>';
					}
					?>
					</ul>
					<div class="toolbox clearfix">
						<select name="add-section" id="add-section">
							<option value="0">Add a section&#8230;</option>
											
							<?php
							if(count($available_sections)){
								foreach($available_sections as $opt){
							?>
							<option value="<?php print $opt['option_value'] ?>"><?php print $opt['option_text'] ?></option>
							<?php
								}
							}
							?>
						</select>
						<a id="sort-toggle" href="#" title="Click to Enable/Disable Sorting">Enable Sorting</a>
					</div>
				</fieldset>
			</div>
		</div>
		<fieldset class="action">
			<button class="right" type="submit" name="submit"><span><em>Save</em></span></button>
			<a class="button left" href="<?php print $this->xhtmlUrl('view', false, 'Pages'); ?>" title="Click to Cancel Editing This Page"><span>Cancel</span></a>
		</fieldset>
	</form>