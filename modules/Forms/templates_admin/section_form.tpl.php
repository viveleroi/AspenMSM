<fieldset class="form-display small-frm">
	<div class="legend">
		<a id="editor_<?php print $next_id ?>" class="toggle-section open" href="#">Hide</a> 
		<strong><?php print isset($section['content']['title']) ? (empty($section['content']['title']) ? 'Untitled Form Display' : $section['content']['title']) : 'New Form Display' ?></strong> 
		<span>Form Display</span>
	</div>
	<div id="editor_<?php print $next_id ?>_form" class="editable">
		<input type="hidden" name="page_sections[<?php print $next_id ?>][section_type]" value="form_display" />
		<input type="hidden" name="page_sections[<?php print $next_id ?>][called_in_template]" value="<?php print isset($section['meta']['called_in_template']) ? $section['meta']['called_in_template'] : 0 ?>" />
		<ol>
			<li>
				<label for="form_<?php print $next_id ?>_title">Sub-Title:</label>
				<input id="form_<?php print $next_id ?>_title" name="page_sections[<?php print $next_id ?>][title]" type="text" value="<?php print isset($section['content']['title']) ? $section['content']['title'] : '' ?>" />
				<a class="help" href="<?php print router()->getModuleUrl('Forms_Admin') ?>/help/section-form-subtitle.htm" title="Sub-Title">Help</a>
			</li>
			<li class="auto">
				<label for="form_<?php print $next_id ?>_show_title">Show Sub-Title:</label>
				<input id="form_<?php print $next_id ?>_show_title" name="page_sections[<?php print $next_id ?>][show_title]" type="checkbox" value="1" <?php print isset($section['content']['show_title']) && $section['content']['show_title'] ? ' checked="checked"' : '' ?> />
				<a class="help" href="<?php print router()->getModuleUrl('Forms_Admin') ?>/help/section-form-show_subtitle.htm" title="Show Sub-Title">Help</a>
			</li>
			<li class="auto">
				<label for="form_<?php print $next_id ?>_placement_group">Placement Group:</label>
				<select id="form_<?php print $next_id ?>_placement_group" name="page_sections[<?php print $next_id ?>][placement_group]">
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
				<a class="help" href="<?php print router()->getModuleUrl('Forms_Admin') ?>/help/section-form-placement_group.htm" title="Placement Group">Help</a>
			</li>
			<li class="auto">
				<label for="form_<?php print $next_id ?>_form_id">Select Form:</label>
				<select id="form_<?php print $next_id ?>_form_id" name="page_sections[<?php print $next_id ?>][form_id]">
				<?php print template()->getSelectOptions( template()->grabSelectArray('forms', 'title', 'DISTINCT', 'title'), $section['content']['form_id']); ?>
				</select>
				<a class="help" href="<?php print router()->getModuleUrl('Forms_Admin') ?>/help/section-form-select.htm" title="Select Form">Help</a>
			</li>
		</ol>
		<a class="dark-button delete-confirm" href="#" title="Are you sure you want to delete &#8220;<?php print isset($section['content']['title']) ? (empty($section['content']['title']) ? 'Untitled Form Display' : $section['content']['title']) : 'New Form Display' ?>&#8221; and all it&#8217;s content?"><span>Delete Section</span></a>
	</div>
</fieldset>
