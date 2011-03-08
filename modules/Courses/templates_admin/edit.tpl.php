<h2><?php print $values['id'] ? 'Edit' : 'Add'; ?> Course</h2>

	<?php app()->form->printErrors(); ?>
	<form action="<?php print $this->action(); ?>" method="post">
		<div class="frame">
			<h3>Course Details</h3>
			<input type="hidden" name="id" id="course_id" value="<?php print $values['id'] ?>" />
			<fieldset>
				<ol>
					<li>
						<label for="groups">Course Group:</label>
						<select id="groups" name="groups[]">
							<option></option>
							<?php
							$options = $this->grabSelectArray('course_groups', 'name', 'DISTINCT', 'name');
							foreach($options as $option){
								print '<option value="'.$option['id'].'"'.(in_array($option['id'], $values['groups']) ? ' selected="selected"' : '').'>' . $option['name'] . '</option>';
							}
						?>
						</select>
					</li>
					<li>
						<label for="title">Course Title:</label>
						<input type="text" name="title" id="title" value="<?php print htmlentities($values['title'], ENT_QUOTES, 'UTF-8'); ?>" />
					</li>
					<li class="short">
						<label for="code">Course Code:</label>
						<input type="text" name="code" id="code" value="<?php print $values['code']; ?>" />
					</li>
					<li>
						<label for="duration">Duration:</label>
						<input type="text" name="duration" id="duration" value="<?php print $values['duration']; ?>" />
					</li>
					<li>
						<span class="false-label">Pricing:</span>
						<span class="multi cost">
							<label for="pricing_single">Single <input type="text" name="pricing_single" id="pricing_single" value="<?php print $values['pricing_single']; ?>" /></label>
							<label for="pricing_few">2-5 <input type="text" name="pricing_few" id="pricing_few" value="<?php print $values['pricing_few']; ?>" /></label>
							<label for="pricing_many">6+<input type="text" name="pricing_many" id="pricing_many" value="<?php print $values['pricing_many']; ?>" /></label>
						</span>
					</li>
				</ol>
				<ol>
					<li id="schedule">
						<span class="false-label">Schedule:</span>
						<div id="classes">
							<div class="class none">No classes scheduled at this time.</div>
<!--
						<span class="multi">
							<span class="false-date-label">Date:</span>
							<span class="false-location-label">Location:</span>
							<span class="false-seating-label">Seating:</span>
							<div class="individual">
								<input class="sch-date" type="text" name="date" id="date" value="<?php print $values['date'] ?>" />
								<input class="sch-location" type="text" name="location" id="location" value="<?php print htmlentities($values['location'], ENT_QUOTES, 'UTF-8'); ?>" />
								<input class="sch-seating" type="text" name="seating" id="seating" value="<?php print $values['seating'] ?>" />
							</div>
						</span>
-->
							<div class="class">
								<a class="edit" href="#" title="Edit this class">Edit</a>
								<div class="c-date"><strong>Date</strong> 10/25/71</div>
								<div class="c-seat"><strong>Seating</strong> 20</div>
								<div class="c-loc"><strong>Location</strong> At Jay's House</div>
							</div>
						</div>
						
						<a class="add" href="<?php print $this->xhtmlUrl('add'); ?>" title="Add New Listing">Add</a>
					</li>
				</ol>
				<ol>
					<li>
						<label for="summary">Brief Description:</label>
						<textarea id="summary" name="summary" rows="5" cols="60"><?php print htmlentities($values['summary'], ENT_QUOTES, 'UTF-8'); ?></textarea>
					</li>
					<li class="tmce">
						<label for="body">Body:</label>
						<textarea id="body" name="body" class="mce-editor content-area" rows="10" cols="60"><?php print htmlentities($values['body'], ENT_QUOTES, 'UTF-8'); ?></textarea>
					</li>
				</ol>
				<ol>
					<li>
						<label>Add File:</label>
						<input id="pdf_filename" name="pdf_filename" type="file" />
					</li>
				</ol>
			</fieldset>
		</div>
		<fieldset class="action">
			<button class="right" type="submit" name="submit"><span><em>Save</em></span></button>
			<a class="button left" href="<?php print $this->xhtmlUrl('view', false, 'Corses_Admin'); ?>" title="Click to Cancel"><span>Cancel</span></a>
		</fieldset>
	</form>