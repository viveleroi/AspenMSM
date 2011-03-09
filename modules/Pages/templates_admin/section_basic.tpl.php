<fieldset class="text-content small-frm">
	<div class="legend"><a id="editor_<?php print $next_id ?>" class="toggle-section open" href="#">Hide</a> <strong><?php print isset($section['content']['title']) ? (empty($section['content']['title']) ? 'Untitled Text' : $section['content']['title']) : 'New Page Section' ?></strong> <span>Text Content</span></div>
	<div id="editor_<?php print $next_id ?>_form" class="editable">
		<input type="hidden" name="page_sections[<?php print $next_id ?>][section_type]" value="basic_editor" />
		<input type="hidden" name="page_sections[<?php print $next_id ?>][called_in_template]" value="<?php print isset($section['meta']['called_in_template']) ? $section['meta']['called_in_template'] : 0 ?>" />
		<ol>
			<li>
				<label for="basic_<?php print $next_id ?>_title">Sub-Title:</label>
				<input type="text" name="page_sections[<?php print $next_id ?>][title]" id="basic_<?php print $next_id ?>_title" value="<?php print isset($section['content']['title']) ? htmlentities($section['content']['title'], ENT_QUOTES, 'UTF-8') : '' ?>" />
				<a class="help" href="<?php print router()->moduleUrl() ?>/help/section-subtitle.htm" title="Sub-Title">Help</a>
			</li>
			<li>
				<label for="basic_<?php print $next_id ?>_show_title">Show Sub-Title:</label>
				<input type="checkbox" name="page_sections[<?php print $next_id ?>][show_title]" id="basic_<?php print $next_id ?>_show_title" value="1" <?php print isset($section['content']['show_title']) && $section['content']['show_title'] ? ' checked="checked"' : '' ?> />
				<a class="help" href="<?php print router()->moduleUrl() ?>/help/section-show_subtitle.htm" title="Show Sub-Title">Help</a>
			</li>
			<li class="auto">
				<label for="basic_<?php print $next_id ?>_placement_group">Placement Group:</label>
				<select name="page_sections[<?php print $next_id ?>][placement_group]" id="basic_<?php print $next_id ?>_placement_group">
				<option value="0">--</option>
				<?php
				$placement_group = isset($section['meta']['placement_group']) ? $section['meta']['placement_group'] : 0;
				if($placement_groups){
					foreach($placement_groups as $option){
						print '<option value="'.$option['id'].'"'.($placement_group == $option['id'] ? ' selected="selected"' : '').'>' . $option['group_name'] . '</option>';
					}
				}
				?>
				</select>
				<a class="help" href="<?php print router()->moduleUrl() ?>/help/section-placement_group.htm" title="Placement Group">Help</a>
			</li>
			<li class="auto">
				<label for="basic_<?php print $next_id ?>_template">Template:</label>
				<select id="basic_<?php print $next_id ?>_template" name="page_sections[<?php print $next_id ?>][template]">
				<option value="0">--</option>
				<?php
				$template = isset($section['content']['template']) ? $section['content']['template'] : NULL;
				if(is_array($templates)){
					foreach($templates as $option){
						print '<option value="'.$option['FILENAME'].'"'.($template == $option['FILENAME'] ? ' selected="selected"' : '').'>' . $option['NAME'] . '</option>';
					}
				}
				?>
				</select>
				<a class="help" href="<?php print router()->moduleUrl('Pages_Admin') ?>/help/section-basic-placement_group.htm" title="Placement Group">Help</a>
			</li>
			<li class="tmce">
				<label for="basic_<?php print $next_id ?>_content">Content:</label>
				<textarea class="mce-editor content-area" name="page_sections[<?php print $next_id ?>][content]" id="basic_<?php print $next_id ?>_content" cols="40" rows="5"><?php print isset($section['content']['content']) ? htmlentities($section['content']['content'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
			</li>
		</ol>
		<a class="dark-button delete-confirm" href="#"  title="Are you sure you want to delete &#8220;<?php print isset($section['content']['title']) ? (empty($section['content']['title']) ? 'Untitled Text' : $section['content']['title']) : 'New Page Section' ?>&#8221; and all it&#8217;s content?"><span>Delete Section</span></a>
	</div>
</fieldset>