<fieldset class="contactgroup-display small-frm">
	<div class="legend">
		<a id="editor_<?php print $next_id ?>" class="toggle-section open" href="#">Hide</a>
		<strong><?php print isset($section['content']['title']) ? (empty($section['content']['title']) ? 'Untitled Contact Display' : $section['content']['title']) : 'New Contact Display' ?></strong>
		<span>Contact Display</span>
	</div>
	<div id="editor_<?php print $next_id ?>_form" class="editable">
		<input type="hidden" name="page_sections[<?php print $next_id ?>][section_type]" value="contacts_display" />
		<input type="hidden" name="page_sections[<?php print $next_id ?>][called_in_template]" value="<?php print isset($section['meta']['called_in_template']) ? $section['meta']['called_in_template'] : 0 ?>" />
		<ol>
			<li
				<label for="contactgroup_<?php print $next_id ?>_title">Sub-Title:</label>
				<input id="contactgroup_<?php print $next_id ?>_title" name="page_sections[<?php print $next_id ?>][title]" type="text" value="<?php print isset($section['content']['title']) ? $section['content']['title'] : '' ?>" />
				<a class="help" href="<?php print router()->getModuleUrl('Contacts_Admin') ?>/help/section-contactgroup-subtitle.htm" title="Sub-Title">Help</a>
			</li>
			<li>
				<label for="contactgroup_<?php print $next_id ?>_show_title">Show Sub-Title:</label>
				<input id="contactgroup_<?php print $next_id ?>_show_title" name="page_sections[<?php print $next_id ?>][show_title]" type="checkbox" value="1" <?php print isset($section['content']['show_title']) && $section['content']['show_title'] ? ' checked="checked"' : '' ?> />
				<a class="help" href="<?php print router()->getModuleUrl('Contacts_Admin') ?>/help/section-contactgroup-show_subtitle.htm" title="Show Sub-Title">Help</a>
			</li>
			<li class="auto">
				<label for="contactgroup_<?php print $next_id ?>_placement_group">Placement Group:</label>
				<select id="contactgroup_<?php print $next_id ?>_placement_group" name="page_sections[<?php print $next_id ?>][placement_group]">
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
				<a class="help" href="<?php print router()->getModuleUrl('Contacts_Admin') ?>/help/section-contactgroup-placement_group.htm" title="Placement Group">Help</a>
			</li>
			<li class="auto">
				<label for="contactgroup_<?php print $next_id ?>_template">Template:</label>
				<select id="contactgroup_<?php print $next_id ?>_template" name="page_sections[<?php print $next_id ?>][template]">
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
				<a class="help" href="<?php print router()->getModuleUrl('Contacts_Admin') ?>/help/section-contactgroup-placement_group.htm" title="Placement Group">Help</a>
			</li>
			<li class="auto">
				<label for="contactgroup_<?php print $next_id ?>_contact_id">Select Contact:</label>
				<select id="contactgroup_<?php print $next_id ?>_contact_id" name="page_sections[<?php print $next_id ?>][contact_id]">
				<?php print $this->APP->template->getSelectOptions( $this->APP->template->grabSelectArray('contacts', 'last_name', 'DISTINCT', 'last_name'), $section['content']['contact_id'], true); ?>
				</select>
				<a class="help" href="<?php print router()->getModuleUrl('Contacts_Admin') ?>/help/section-group-select.htm" title="Select Form">Help</a>
			</li>
			
			
			<li>
				<label for="contactgroup_<?php print $next_id ?>_link_to_full_page">Link to Full Page:</label>
				<input id="contactgroup_<?php print $next_id ?>_link_to_full_page" name="page_sections[<?php print $next_id ?>][link_to_full_page]" type="checkbox" value="1" <?php print isset($section['content']['link_to_full_page']) && $section['content']['link_to_full_page'] ? ' checked="checked"' : '' ?> />
			</li>
			<li class="auto">
				<label for="contactgroup_<?php print $next_id ?>_detail_page_id">Detail Page:</label>
				<select id="contactgroup_<?php print $next_id ?>_detail_page_id" name="page_sections[<?php print $next_id ?>][detail_page_id]">
				<option value="">Self</option>
				<?php print $this->APP->Pages_Admin->pageOptionGroups(false, false, $section['content']['detail_page_id'], $section['meta']['page_id']); ?>
				</select>
			</li>
			
			
			
		</ol>
		<a class="dark-button delete-confirm" href="#" title="Are you sure you want to delete &#8220;<?php print isset($section['content']['title']) ? (empty($section['content']['title']) ? 'Untitled Contact Display' : $section['content']['title']) : 'New Contact Display' ?>&#8221; and all it&#8217;s content?"><span>Delete Section</span></a>
	</div>
</fieldset>