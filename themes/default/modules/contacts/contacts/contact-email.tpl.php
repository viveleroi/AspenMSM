<?php
/**
 * Template: Contact - Title, Tel, Email
 */
?>

	<div class="contacts contact-single">
	<?php print empty($content['title']) ? '' : '<h3>'.$content['title'].'</h3>'; ?>
	<?php foreach($content['results'] as $contact){ ?>
		<h5><?php print $contact['first_name'].' '; ?><?php print empty($contact['middle_name']) ? '' : $contact['middle_name'].' '; ?><?php print $contact['last_name'] ?></h5>
		<?php print empty($contact['job_title']) ? '' : '<div class="c-title">'.$contact['job_title'].'</div>'; ?>
		<?php print empty($contact['telephone']) ? '' : '<div class="c-tel">'.$contact['telephone'].'</div>'; ?>
		<?php print empty($contact['email']) ? '' : '<a class="c-email" href="mailto:'.$contact['email'].'" title="Email '.$contact['first_name'].'">'.$contact['email'].'</a>'; ?>
	<?php } ?>
	</div>