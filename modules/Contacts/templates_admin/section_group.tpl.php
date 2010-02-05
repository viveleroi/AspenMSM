<fieldset class="contactgroup-display small-frm">
	<div class="legend">
		<a id="editor_<?php print $next_id ?>" class="toggle-section open" href="#">Hide</a>
		<strong><?php print isset($section['content']['title']) ? (empty($section['content']['title']) ? 'Untitled Contact Group Display' : $section['content']['title']) : 'New Contact Group Display' ?></strong>
		<span>Contact Group Display</span>
	</div>
	<div id="editor_<?php print $next_id ?>_form" class="editable">
		<input type="hidden" name="page_sections[<?php print $next_id ?>][section_type]" value="contactgroup_display" />
		<input type="hidden" name="page_sections[<?php print $next_id ?>][called_in_template]" value="<?php print isset($section['meta']['called_in_template']) ? $section['meta']['called_in_template'] : 0 ?>" />
		<ol>
			<li
				<label for="contactgroup_<?php print $next_id ?>_title">Sub-Title:</label>
				<input id="contactgroup_<?php print $next_id ?>_title" name="page_sections[<?php print $next_id ?>][title]" type="text" value="<?php print isset($section['content']['title']) ? $section['content']['title'] : '' ?>" />
				<a class="help" href="<?php print $this->APP->router->getModuleUrl('Contacts_Admin') ?>/help/section-contactgroup-subtitle.htm" title="Sub-Title">Help</a>
			</li>
			<li>
				<label for="contactgroup_<?php print $next_id ?>_show_title">Show Sub-Title:</label>
				<input id="contactgroup_<?php print $next_id ?>_show_title" name="page_sections[<?php print $next_id ?>][show_title]" type="checkbox" value="1" <?php print isset($section['content']['show_title']) && $section['content']['show_title'] ? ' checked="checked"' : '' ?> />
				<a class="help" href="<?php print $this->APP->router->getModuleUrl('Contacts_Admin') ?>/help/section-contactgroup-show_subtitle.htm" title="Show Sub-Title">Help</a>
			</li>
			<li class="auto">
				<label for="contactgroup_<?php print $next_id ?>_placement_group">Placement Group:</label>
				<select id="contactgroup_<?php print $next_id ?>_placement_group" name="page_sections[<?php print $next_id ?>][placement_group]">
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
				<a class="help" href="<?php print $this->APP->router->getModuleUrl('Contacts_Admin') ?>/help/section-contactgroup-placement_group.htm" title="Placement Group">Help</a>
			</li>
			<li class="auto">
				<label for="contactgroup_<?php print $next_id ?>_template">Template:</label>
				<select id="contactgroup_<?php print $next_id ?>_template" name="page_sections[<?php print $next_id ?>][template]">
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
				<a class="help" href="<?php print $this->APP->router->getModuleUrl('Contacts_Admin') ?>/help/section-contactgroup-placement_group.htm" title="Placement Group">Help</a>
			</li>
			<li class="auto">
				<label for="contactgroup_<?php print $next_id ?>_group_id">Select Group:</label>
				<select id="contactgroup_<?php print $next_id ?>_group_id" name="page_sections[<?php print $next_id ?>][group_id]">
				<?php print $this->APP->template->getSelectOptions( $this->APP->template->grabSelectArray('contact_groups', 'name', 'DISTINCT', 'name'), $section['content']['group_id'], true); ?>
				</select>
				<a class="help" href="<?php print $this->APP->router->getModuleUrl('Contacts_Admin') ?>/help/section-group-select.htm" title="Select Form">Help</a>
			</li>
			<li class="auto">
				<label for="contactgroup_<?php print $next_id ?>_sort_method">Sort:</label>
				<select id="contactgroup_<?php print $next_id ?>_sort_method" name="page_sections[<?php print $next_id ?>][sort_method]">
					<option value="sort_order"<?php print $section['content']['sort_method'] == 'sort_order' ? ' selected="selected"' : ''; ?>>Sort Order</option>
					<option value="alpha"<?php print $section['content']['sort_method'] == 'alpha' ? ' selected="selected"' : ''; ?>>Alphabetically</option>
				</select>
				<a class="help" href="<?php print $this->APP->router->getModuleUrl('Contacts_Admin') ?>/help/section-group-select.htm" title="Select Form">Help</a>
			</li>
			
		</ol>
		<a class="dark-button delete-confirm" href="#" title="Are you sure you want to delete &#8220;<?php print isset($section['content']['title']) ? (empty($section['content']['title']) ? 'Untitled Contact Group Display' : $section['content']['title']) : 'New Contact Group Display' ?>&#8221; and all it&#8217;s content?"><span>Delete Section</span></a>
	</div>
</fieldset>