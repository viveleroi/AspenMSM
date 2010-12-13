<fieldset class="text-content small-frm">
	<div class="legend"><a id="editor_<?php print $next_id ?>" class="toggle-section open" href="#">Hide</a> <strong><?php print isset($section['content']['title']) ? (empty($section['content']['title']) ? 'Untitled Text with Image' : $section['content']['title']) : 'New Page Section' ?></strong> <span>Text with Image Content</span></div>
	<div id="editor_<?php print $next_id ?>_form" class="editable">
		<input type="hidden" name="page_sections[<?php print $next_id ?>][next_id]" value="<?php print $next_id ?>" />
		<input type="hidden" name="page_sections[<?php print $next_id ?>][section_type]" value="imagetext_editor" />
		<input type="hidden" name="page_sections[<?php print $next_id ?>][called_in_template]" value="<?php print isset($section['meta']['called_in_template']) ? $section['meta']['called_in_template'] : 0 ?>" />
		<input type="hidden" name="page_sections[<?php print $next_id ?>][image_filename]" value="<?php print $section['content']['image_filename']; ?>" />
		<input type="hidden" name="page_sections[<?php print $next_id ?>][image_thumbname]" value="<?php print $section['content']['image_thumbname']; ?>" />
		<ol>
			<li>
				<label for="imagetext_<?php print $next_id ?>_title">Sub-Title:</label>
				<input id="imagetext_<?php print $next_id ?>_title" name="page_sections[<?php print $next_id ?>][title]" type="text" value="<?php print isset($section['content']['title']) ? htmlentities($section['content']['title'], ENT_QUOTES, 'UTF-8') : '' ?>" />
				<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/section-subtitle.htm" title="Sub-Title">Help</a>
			</li>
			<li>
				<label for="imagetext_<?php print $next_id ?>_show_title">Show Sub-Title:</label>
				<input id="imagetext_<?php print $next_id ?>_show_title" name="page_sections[<?php print $next_id ?>][show_title]" type="checkbox" value="1" <?php print isset($section['content']['show_title']) && $section['content']['show_title'] ? ' checked="checked"' : '' ?> />
				<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/section-show_subtitle.htm" title="Show Sub-Title">Help</a>
			</li>
			<li class="auto">
				<label for="imagetext_<?php print $next_id ?>_placement_group">Placement Group:</label>
				<select id="imagetext_<?php print $next_id ?>_placement_group" name="page_sections[<?php print $next_id ?>][placement_group]">
					<option value="0">--</option>
					<?php
					$placement_group = isset($section['meta']['placement_group']) ? $section['meta']['placement_group'] : 0;
					if($placement_groups['RECORDS']){
						foreach($placement_groups['RECORDS'] as $option){
							print '<option value="'.$option['id'].'"'.($placement_group == $option['id'] ? ' selected="selected"' : '').'>' . $option['group_name'] . '</option>';
						}
					}
					?>
				</select>
				<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/section-placement_group.htm" title="Placement Group">Help</a>
			</li>
			<li class="auto">
				<label for="basic_<?php print $next_id ?>_template">Template:</label>
				<select id="basic_<?php print $next_id ?>_template" name="page_sections[<?php print $next_id ?>][template]">
				<option value="0">--</option>
				<?php
				$template = isset($section['content']['template']) ? $section['content']['template'] : 0;
				if(is_array($templates)){
					foreach($templates as $option){
						print '<option value="'.$option['FILENAME'].'"'.($template == $option['FILENAME'] ? ' selected="selected"' : '').'>' . $option['NAME'] . '</option>';
					}
				}
				?>
				</select>
				<a class="help" href="<?php print $this->APP->router->getModuleUrl('Pages_Admin') ?>/help/section-basic-placement_group.htm" title="Placement Group">Help</a>
			</li>
			<li class="tmce">
				<label for="imagetext_<?php print $next_id ?>_content">Content:</label>
				<textarea id="imagetext_<?php print $next_id ?>_content" name="page_sections[<?php print $next_id ?>][content]" class="mce-editor content-area" cols="40" rows="5"><?php print isset($section['content']['content']) ? htmlentities($section['content']['content'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
			</li>
			<li>
				<label for="imagetext_<?php print $next_id ?>_image">Image:</label>
				<input id="imagetext_<?php print $next_id ?>_image" name="image_<?php print $next_id ?>" type="file" />
				<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/section-image.htm" title="Image">Help</a>
			</li>
			<li>
				<label for="imagetext_<?php print $next_id ?>_image_alt">Image Alt:</label>
				<input id="imagetext_<?php print $next_id ?>_image_alt" name="page_sections[<?php print $next_id ?>][image_alt]" type="text" value="<?php print isset($section['content']['image_alt']) ? $section['content']['image_alt'] : '' ?>" />
				<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/section-image_alt.htm" title="Image Alt">Help</a>
			</li>
			<li class="thumbs">
				<span class="false-label">Current Image:</span>
				<img src="<?php print $this->APP->router->getUploadsUrl() .DS. $section['content']['image_thumbname'];?>" />
				<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/section-current_image.htm" title="Current Image">Help</a>
			</li>
		</ol>
		<a class="dark-button delete-confirm" href="#"  title="Are you sure you want to delete &#8220;<?php print isset($section['content']['title']) ? (empty($section['content']['title']) ? 'Untitled Text' : $section['content']['title']) : 'New Page Section' ?>&#8221; and all it&#8217;s content?"><span>Delete Section</span></a>
	</div>
</fieldset>