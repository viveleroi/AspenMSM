<h2><?php print $values['id'] ? 'Edit' : 'Add'; ?> Contact</h2>
<?php $this->APP->form->printErrors(); ?>
<form action="<?php print $this->action() ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="id" id="contact_id" value="<?php print $values['id'] ?>" />
	<div class="frame">
		<h3>Contact Details</h3>
		<fieldset>
			<ol>
				<li class="half">
					<label for="title">Honorific Title:</label>
					<input type="text" name="title" id="title" value="<?php print $values['title'] ?>" />
				</li>
				<li>
					<label for="first_name">First name:</label>
					<input type="text" name="first_name" id="first_name" value="<?php print $values['first_name'] ?>" />
				</li>
				<li>
					<label for="middle_name">Middle name:</label>
					<input type="text" name="middle_name" id="middle_name" value="<?php print $values['middle_name'] ?>" />
				</li>
				<li>
					<label for="last_name">Last name:</label>
					<input type="text" name="last_name" id="last_name" value="<?php print $values['last_name'] ?>" />
				</li>
			</ol>
			<ol>
				<li>
					<label for="accreditation">Accreditation:</label>
					<input type="text" name="accreditation" id="accreditation" value="<?php print $values['accreditation'] ?>" />
				</li>
				<li>
					<label for="job_title">Job Title:</label>
					<input type="text" name="job_title" id="job_title" value="<?php print $values['job_title'] ?>" />
				</li>
				<li>
					<label for="company">Company/Practice:</label>
					<input type="text" name="company" id="company" value="<?php print $values['company'] ?>" />
				</li>
				<li>
					<label for="website">Website:</label>
					<input type="text" name="website" id="website" value="<?php print empty($values['website']) ? 'http://' : $values['website'] ?>" />
				</li>
			</ol>
			<ol>
				<li>
					<label for="address_1">Address:</label>
					<input type="text" name="address_1" id="address_1" value="<?php print $values['address_1'] ?>" />
				</li>
				<li>
					<label for="city">City:</label>
					<input type="text" name="city" id="city" value="<?php print $values['city'] ?>" />
				</li>
				<li>
					<label for="state">State:</label>
					<input type="text" name="state" id="state" value="<?php print $values['state'] ?>" />
				</li>
				<li>
					<label for="postal">Zip Code:</label>
					<input type="text" name="postal" id="postal" value="<?php print $values['postal'] ?>" />
				</li>
				<li>
					<label for="email">E-mail:</label>
					<input type="text" name="email" id="email" value="<?php print $values['email']; ?>" />
				</li>
				<li>
					<label for="telephone">Telephone:</label>
					<input type="text" name="telephone" id="telephone" value="<?php print $values['telephone'] ?>" />
				</li>
				<li>
					<label for="telephone_2">Second Telephone:</label>
					<input type="text" name="telephone_2" id="telephone_2" value="<?php print $values['telephone_2'] ?>" />
				</li>
				<li>
					<label for="fax">Fax:</label>
					<input type="text" name="fax" id="fax" value="<?php print $values['fax'] ?>" />
				</li>
			</ol>
			<ol>
				<li>
					<label for="brief_bio">Brief Bio:</label>
					<textarea name="brief_bio" id="brief_bio" rows="10" cols="60"><?php print $values['brief_bio'] ?></textarea>
				</li>
				<li class="tmce">
					<label for="bio">Full Bio:</label>
					<textarea name="bio" id="bio" class="mce-editor content-area" rows="10" cols="60"><?php print $values['bio'] ?></textarea>
				</li>
			</ol>
			<ol>
				<li>
					<label for="groups" class="required">Groups:</label>
					<select id="groups" name="groups[]" size="7" multiple >
						<option></option>
						<?php
							$options = $this->grabSelectArray('contact_groups', 'name', 'DISTINCT', 'name');
							foreach($options as $option){
								print '<option value="'.$option['id'].'"'.(in_array($option['id'], $values['groups']) ? ' selected="selected"' : '').'>' . $option['name'] . '</option>';
							}
						?>
					</select>
				</li>
				<li>
					<label for="languages" class="required">Languages:</label>
					<select id="languages" name="languages[]" size="7" multiple>
					</select>
					<a href="#" id="manage-langs">Manage Languages</a>
				</li>
				<li>
					<label for="specialties" class="required">Specialties:</label>
					<select id="specialties" name="specialties[]"  size="7" multiple>
					</select>
					<a href="#" id="manage-specialties">Manage Specialties</a>
				</li>
				<li>
					<label for="file_path">Picture:</label>
					
					<?php
				if($images['RECORDS']){
					foreach($images['RECORDS'] as $image){
				?>
				<div>
					<img src="<?php print router()->getUploadsUrl() . '/contacts/' . $values['id'] . '/' . $image['filename_thumb']; ?>" width="<?php print $image['width_thumb']; ?>" height="<?php print $image['height_thumb']; ?>" alt="Contact Profile Picture" />
					<a href="#" class="delete del-img" id="del-<?php print $image['id']; ?>">Delete</a>
				</div>
				<?php
					}
				} else { ?>
					<img src="/admin/img/no-contact-photo.png" width="100" height="100" alt="No Photo Available" />
					<input type="file" name="file_path" id="file_path" />
				<?php } ?>
				</li>
			</ol>
		</fieldset>
		<a class="dark-button confirm" href="<?php print $this->xhtmlUrl('delete', array('id' => $values['id'])); ?>" title="Are you sure you want to delete this contact?"><span>Delete</span></a>
	</div>
	<fieldset class="action">
		<button class="right" type="submit" name="submit"><span><em>Save</em></span></button>
		<a class="button left" href="<?php print $this->xhtmlUrl('view', false, 'Contacts_Admin'); ?>" title="Click to Cancel"><span>Cancel</span></a>
	</fieldset>
</form>