<fieldset class="events-display small-frm">
	<div class="legend">
		<a id="editor_<?php print $next_id ?>" class="toggle-section open" href="#">Hide</a>
		<strong><?php print isset($section['content']['title']) ? (empty($section['content']['title']) ? 'Untitled Event' : $section['content']['title']) : 'New Event Display' ?></strong>
		<span>Events Display</span>
	</div>
	<div id="editor_<?php print $next_id ?>_form" class="editable">
		<input type="hidden" name="page_sections[<?php print $next_id ?>][section_type]" value="events_display" />
		<input type="hidden" name="page_sections[<?php print $next_id ?>][called_in_template]" value="<?php print isset($section['meta']['called_in_template']) ? $section['meta']['called_in_template'] : 0 ?>" />
		<ol>
			<li>
				<label for="events_<?php print $next_id ?>_title">Sub-Title:</label>
				<input id="events_<?php print $next_id ?>_title" name="page_sections[<?php print $next_id ?>][title]" type="text" value="<?php print isset($section['content']['title']) ? $section['content']['title'] : '' ?>" />
				<a class="help" href="<?php print router()->getModuleUrl('Events_Admin') ?>/help/section-events-subtitle.htm" title="Sub-Title">Help</a>
			</li>
			<li>
				<label for="events_<?php print $next_id ?>_show_title">Show Sub-Title:</label>
				<input id="events_<?php print $next_id ?>_show_title" name="page_sections[<?php print $next_id ?>][show_title]" type="checkbox" value="1" <?php print isset($section['content']['show_title']) && $section['content']['show_title'] ? ' checked="checked"' : '' ?> />
				<a class="help" href="<?php print router()->getModuleUrl('Events_Admin') ?>/help/section-events-show_subtitle.htm" title="Show Sub-Title">Help</a>
			</li>
			<li class="auto">
				<label for="events_<?php print $next_id ?>_placement_group">Placement Group:</label>
				<select id="events_<?php print $next_id ?>_placement_group" name="page_sections[<?php print $next_id ?>][placement_group]">
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
				<a class="help" href="<?php print router()->getModuleUrl('Events_Admin') ?>/help/section-events-placement_group.htm" title="Placement Group">Help</a>
			</li>

			<li>
				<label for="events_<?php print $next_id ?>_show_recurring">Show Recurring:</label>
				<input id="events_<?php print $next_id ?>_show_recurring" name="page_sections[<?php print $next_id ?>][show_recurring]" type="checkbox" value="1" <?php print isset($section['content']['show_recurring']) && $section['content']['show_recurring'] ? ' checked="checked"' : '' ?> />
				<a class="help" href="<?php print router()->getModuleUrl('Events_Admin') ?>/help/section-events-show_recurring.htm" title="Hide Expired">Help</a>
			</li>
			<li>
				<label for="events_<?php print $next_id ?>_show_nonrecurring">Show Upcoming:</label>
				<input id="events_<?php print $next_id ?>_show_nonrecurring" name="page_sections[<?php print $next_id ?>][show_nonrecurring]" type="checkbox" value="1" <?php print isset($section['content']['show_nonrecurring']) && $section['content']['show_nonrecurring'] ? ' checked="checked"' : '' ?> />
				<a class="help" href="<?php print router()->getModuleUrl('Events_Admin') ?>/help/section-events-show_nonrecurring.htm" title="Hide Expired">Help</a>
			</li>

			<li class="auto">
				<label for="events_<?php print $next_id ?>_display_num">Show recent events:</label>
				<input id="events_<?php print $next_id ?>_display_num" name="page_sections[<?php print $next_id ?>][display_num]" type="text" size="3" value="<?php print isset($section['content']['display_num']) ? $section['content']['display_num'] : 5 ?>" />
				<a class="help" href="<?php print router()->getModuleUrl('Events_Admin') ?>/help/section-events-show_recent_events.htm" title="Show Recent Events">Help</a>
			</li>
			<li>
				<label for="events_<?php print $next_id ?>_hide_expired">Hide Expired:</label>
				<input id="events_<?php print $next_id ?>_hide_expired" name="page_sections[<?php print $next_id ?>][hide_expired]" type="checkbox" value="1" <?php print isset($section['content']['hide_expired']) && $section['content']['hide_expired'] ? ' checked="checked"' : '' ?> />
				<a class="help" href="<?php print router()->getModuleUrl('Events_Admin') ?>/help/section-events-hide_expired.htm" title="Hide Expired">Help</a>
			</li>
			<li>
				<label for="events_<?php print $next_id ?>_link_to_full_page">Link to Full Page:</label>
				<input id="events_<?php print $next_id ?>_link_to_full_page" name="page_sections[<?php print $next_id ?>][link_to_full_page]" type="checkbox" value="1" <?php print isset($section['content']['link_to_full_page']) && $section['content']['link_to_full_page'] ? ' checked="checked"' : '' ?> />
				<a class="help" href="<?php print router()->getModuleUrl('Events_Admin') ?>/help/section-events-link_to_full_page.htm" title="Link to Full Page">Help</a>
			</li>
			<li>
				<label for="events_<?php print $next_id ?>_show_description">Show Description:</label>
				<input id="events_<?php print $next_id ?>_show_description" name="page_sections[<?php print $next_id ?>][show_description]" type="checkbox" value="1" <?php print isset($section['content']['show_description']) && $section['content']['show_description'] ? ' checked="checked"' : '' ?> />
				<a class="help" href="<?php print router()->getModuleUrl('Events_Admin') ?>/help/section-events-show_description.htm" title="Show Description">Help</a>
			</li>
			<li class="auto">
				<label for="events_<?php print $next_id ?>_template">Template:</label>
				<select id="events_<?php print $next_id ?>_template" name="page_sections[<?php print $next_id ?>][template]">
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
				<a class="help" href="<?php print router()->getModuleUrl('Events_Admin') ?>/help/section-events-placement_group.htm" title="Placement Group">Help</a>
			</li>
			<li class="auto">
				<label for="events_<?php print $next_id ?>_detail_page_id">Detail Page:</label>
				<select id="events_<?php print $next_id ?>_detail_page_id" name="page_sections[<?php print $next_id ?>][detail_page_id]">
				<option value="">Self</option>
				<?php print app()->Pages_Admin->pageOptionGroups(false, false, $section['content']['detail_page_id'], $section['meta']['page_id']); ?>
				</select>
			</li>
			<li class="auto">
				<label for="events_<?php print $next_id ?>_group_id">Select Group:</label>
				<select id="events_<?php print $next_id ?>_group_id" name="page_sections[<?php print $next_id ?>][group_id]">
				<?php print template()->getSelectOptions( template()->grabSelectArray('event_groups', 'name', 'DISTINCT', 'name'), $section['content']['group_id'], true); ?>
				</select>
				<a class="help" href="<?php print router()->getModuleUrl('Events_Admin') ?>/help/section-group-select.htm" title="Select Form">Help</a>
			</li>
		</ol>
		<a class="dark-button delete-confirm" href="#" title="Are you sure you want to delete &#8220;<?php print isset($section['content']['title']) ? (empty($section['content']['title']) ? 'Untitled Event' : $section['content']['title']) : 'New Event Display' ?>&#8221; and all it&#8217;s content?"><span>Delete Section</span></a>
	</div>
</fieldset>