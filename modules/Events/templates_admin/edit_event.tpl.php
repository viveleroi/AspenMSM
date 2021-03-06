	<h2><span>Currently Editing:</span> <?php print $form->cv('title') ?></h2>
	<?= $form->printErrors(); ?>
	<?= sml()->printMessage(); ?>
	<form action="<?php print $this->action() ?>" method="post">
		<div class="frame">
			<h3>Event Details</h3>
			<fieldset>
				<input type="hidden" name="id" id="event_id" value="<?php print $form->cv('id') ?>" />
				<ol>
					<li class="full">
						<label for="title">Event Title:</label>
						<input id="title" name="title" type="text" value="<?php print $form->cv('title') ?>" />
						<a class="help" href="<?php print router()->moduleUrl() ?>/help/events-event_title.htm" title="Event Title">Help</a>
					</li>
					<li class="full">
						<label for="description">Brief Description:</label>
						<textarea id="description" name="description" rows="5" cols="30"><?php print $form->cv('description') ?></textarea>
					</li>
				</ol>
				<ol>
					<li class="full">
						<label for="recurring">Recurring Event</label>
						<input id="recurring" name="recurring" type="checkbox" value="1"<?php print $form->cv('recurring') ? ' checked="checked"' : '' ?> />
					</li>
					<li class="full">
						<label for="recur_description">Recurring Details</label>
						<input id="recur_description" name="recur_description" type="text" value="<?php print $form->cv('recur_description') ?>" />
					</li>
				</ol>
				<ol>
					<li class="third auto">
						<label for="start_date">Start Date:</label>
						<input id="start_date" name="start_date" type="text" value="<?php print $form->cv('start_date') ?>"  size="10" maxlength="10" class="dateformat-Y-ds-m-ds-d" />
						<a class="help" href="<?php print router()->moduleUrl() ?>/help/events-start_date.htm" title="Start Date">Help</a>
					</li>
					<li class="third auto clear">
						<label for="end_date">Expiration Date:</label>
						<input id="end_date" name="end_date" type="text" value="<?php print $form->cv('end_date') ?>" size="10" maxlength="10"  class="dateformat-Y-ds-m-ds-d" />
						<a class="help" href="<?php print router()->moduleUrl() ?>/help/events-expiration_date.htm" title="Expiration Date">Help</a>
					</li>
				</ol>
				<ol>
					<li class="half auto">
						<label for="start_hour">Start Time:</label>
						<select id="start_hour" name="start_hour">
							<option value="">--</option>
						<?php
						for($h = 1; $h <= 12; $h++){
						?>
							<option<?php print ($form->cv('start_hour') == $h ? ' selected="selected"' : '') ?>><?php print $h ?></option>
						<?php
						}
						?>
						</select>
						<span class="time-sep">:</span>
						<select id="start_minute" name="start_minute" class="tight">
							<option value="">--</option>
							<option<?php print ($form->cv('start_minute') == '00' ? ' selected="selected"' : '') ?>>00</option>
							<option<?php print ($form->cv('start_minute') == '15' ? ' selected="selected"' : '') ?>>15</option>
							<option<?php print ($form->cv('start_minute') == '30' ? ' selected="selected"' : '') ?>>30</option>
							<option<?php print ($form->cv('start_minute') == '45' ? ' selected="selected"' : '') ?>>45</option>
						</select>
						<select id="start_ampm" name="start_ampm">
							<option value="">--</option>
							<option<?php print ($form->cv('start_ampm') == 'am' ? ' selected="selected"' : '') ?>>am</option>
							<option<?php print ($form->cv('start_ampm') == 'pm' ? ' selected="selected"' : '') ?>>pm</option>
						</select>
						<a class="help" href="<?php print router()->moduleUrl() ?>/help/events-start_time.htm" title="Start Time">Help</a>
					</li>
					<li class="half auto">
						<label>End Time:</label>
						<select id="end_hour" name="end_hour">
							<option value="">--</option>
						<?php
						for($h = 1; $h <= 12; $h++){
						?>
							<option<?php print ($form->cv('end_hour') == $h ? ' selected="selected"' : '') ?>><?php print $h ?></option>
						<?php
						}
						?>
						</select>
						<span class="time-sep">:</span>
						<select id="end_minute" name="end_minute" class="tight">
							<option value="">--</option>
							<option<?php print ($form->cv('end_minute') == '00' ? ' selected="selected"' : '') ?>>00</option>
							<option<?php print ($form->cv('end_minute') == '15' ? ' selected="selected"' : '') ?>>15</option>
							<option<?php print ($form->cv('end_minute') == '30' ? ' selected="selected"' : '') ?>>30</option>
							<option<?php print ($form->cv('end_minute') == '45' ? ' selected="selected"' : '') ?>>45</option>
						</select>
						<select id="end_ampm" name="end_ampm">
							<option value="">--</option>
							<option<?php print ($form->cv('end_ampm') == 'am' ? ' selected="selected"' : '') ?>>am</option>
							<option<?php print ($form->cv('end_ampm') == 'pm' ? ' selected="selected"' : '') ?>>pm</option>
						</select>
						<a class="help" href="<?php print router()->moduleUrl() ?>/help/events-expiration_time.htm" title="Expiration Time">Help</a>
					</li>
				</ol>
				<ol>
					<li class="full">
						<label for="title">Event Group:</label>
						<select name="Event_groups[]" id="groups" multiple="multiple" size="4">
						</select> <a href="#" id="manage-groups">Manage Groups</a>
						<a class="help" href="<?php print router()->moduleUrl() ?>/help/events-event_title.htm" title="Event Title">Help</a>
					</li>
				</ol>
				<ol>
					<li class="tmce">
						<label for="body">Content:</label>
						<textarea id="body" name="content" class="mce-editor content-area" rows="25" cols="30"><?php print htmlentities($form->cv('content'), ENT_QUOTES, 'UTF-8'); ?></textarea>
					</li>
				</ol>
			</fieldset>
		</div>
		<fieldset class="action">
			<button class="right" type="submit" name="submit"><span><em>Save</em></span></button>
			<a class="button left" href="<?php print $this->xhtmlUrl('view', false, 'Events'); ?>" title="Click to Cancel Editing This Event"><span>Cancel</span></a>
		</fieldset>
	</form>
	