	<h2>Add a New Event</h2>
	<?php print $this->APP->form->printErrors(); ?>
	<?php print $this->APP->sml->printMessage(); ?>
	<form action="<?php print $this->createFormAction() ?>" method="post">
		<div class="frame">
			<h3>Event Details</h3>
			<fieldset>
				<input type="hidden" name="id" id="event_id" value="0" />
				<ol>
					<li class="full">
						<label for="title">Event Title:</label>
						<input id="title" name="title" type="text" value="<?php print $values['title'] ?>" />
						<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/events-event_title.htm" title="Event Title">Help</a>
					</li>
					<li class="full">
						<label for="description">Brief Description:</label>
						<textarea id="description" name="description" rows="5" cols="30"><?php print $values['description'] ?></textarea>
					</li>
				</ol>
				<ol>
					<li class="full">
						<label for="recurring">Recurring Event</label>
						<input id="recurring" name="recurring" type="checkbox" value="1"<?php print $values['recurring'] ? ' checked="checked"' : '' ?> />
					</li>
					<li class="full">
						<label for="recur_description">Recurring Details</label>
						<input id="recur_description" name="recur_description" type="text" value="<?php print $values['recur_description'] ?>" />
					</li>
				</ol>
				<ol>
					<li class="auto">
						<label for="start_date">Start Date:</label>
						<input id="start_date" name="start_date" type="text" value="<?php print $values['start_date'] ?>" size="10" maxlength="10" class="dateformat-Y-ds-m-ds-d" />
						<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/events-start_date.htm" title="Start Date">Help</a>
					</li>
					<li class="third auto clear">
						<label for="end_date">Expiration Date:</label>
						<input id="end_date" name="end_date" type="text" value="<?php print $values['end_date'] ?>" size="10" maxlength="10" class="dateformat-Y-ds-m-ds-d" />
						<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/events-expiration_date.htm" title="Expiration Date">Help</a>
					</li>
				</ol>
				<ol>
					<li class="auto">
						<label for="start_hour">Start Time:</label>
						<select id="start_hour" name="start_hour">
							<option value="">--</option>
							<?php
							for($h = 1; $h <= 12; $h++){
							?>
								<option<?php print ($values['start_hour'] == $h ? ' selected' : '') ?>><?php print $h ?></option>
							<?php
							}
							?>
						</select>
						<span class="time-sep">:</span>
						<select id="start_minute" name="start_minute">
							<option value="">--</option>
							<option<?php print ($values['start_minute'] == '00' ? ' selected' : '') ?>>00</option>
							<option<?php print ($values['start_minute'] == '15' ? ' selected' : '') ?>>15</option>
							<option<?php print ($values['start_minute'] == '30' ? ' selected' : '') ?>>30</option>
							<option<?php print ($values['start_minute'] == '45' ? ' selected' : '') ?>>45</option>
						</select>
						<select id="start_ampm" name="start_ampm">
							<option value="">--</option>
							<option<?php print ($values['start_ampm'] == 'am' ? ' selected' : '') ?>>am</option>
							<option<?php print ($values['start_ampm'] == 'pm' ? ' selected' : '') ?>>pm</option>
						</select>
						<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/events-start_time.htm" title="Start Time">Help</a>
					</li>
					<li class="half auto">
						<label for="end_hour">Expiration Time:</label>
						<select id="end_hour" name="end_hour">
							<option value="">--</option>
						<?php
						for($h = 1; $h <= 12; $h++){
						?>
							<option<?php print ($values['end_hour'] == $h ? ' selected' : '') ?>><?php print $h ?></option>
						<?php
						}
						?>
						</select>
						<span class="time-sep">:</span>
						<select id="end_minute" name="end_minute">
							<option value="">--</option>
							<option<?php print ($values['end_minute'] == '00' ? ' selected' : '') ?>>00</option>
							<option<?php print ($values['end_minute'] == '15' ? ' selected' : '') ?>>15</option>
							<option<?php print ($values['end_minute'] == '30' ? ' selected' : '') ?>>30</option>
							<option<?php print ($values['end_minute'] == '45' ? ' selected' : '') ?>>45</option>
						</select>
						<select id="end_ampm" name="end_ampm" >
							<option value="">--</option>
							<option<?php print ($values['end_ampm'] == 'am' ? ' selected' : '') ?>>am</option>
							<option<?php print ($values['end_ampm'] == 'pm' ? ' selected' : '') ?>>pm</option>
						</select>
						<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/events-expiration_time.htm" title="Expiration Time">Help</a>
					</li>
				</ol>
				<ol>
					<li class="full">
						<label for="title">Event Group:</label>
						<select name="groups[]" id="groups" multiple="multiple" size="4">
						</select> <a href="#" id="manage-groups">Manage Groups</a>
						<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/events-event_title.htm" title="Event Title">Help</a>
					</li>
				</ol>
				<ol>
					<li class="tmce">
						<label for="body">Content:</label>
						<textarea id="body" name="content" class="mce-editor content-area" rows="25" cols="30"><?php print htmlentities($values['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
					</li>
				</ol>
			</fieldset>
		</div>
		<fieldset class="action">
			<button class="right" type="submit" name="submit"><span><em>Save</em></span></button>
			<a class="button left" href="<?php print $this->createXhtmlValidUrl('view', false, 'Events'); ?>" title="Click to Cancel Adding An Event"><span>Cancel</span></a>
		</fieldset>
	</form>
	
