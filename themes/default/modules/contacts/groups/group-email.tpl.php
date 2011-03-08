<?php
/**
 * Template: Group - Title, Tel, Email
 */
?>

	<div class="contacts contact-group">
	<?php print empty($content['title']) ? '' : '<h3>'.$content['title'].'</h3>'; ?>
		<ul class="clearfix">
		<?php foreach($content['results'] as $results){ ?>
			<?php
				$i = 1;
				foreach($results['contacts'] as $contact){
			?>
			<li<?php print ($i % 2 ? ' class="row"' : '') ?>>
				<h5><?php print $contact['first_name'].' '; ?><?php print empty($contact['middle_name']) ? '' : $contact['middle_name'].' '; ?><?php print $contact['last_name'] ?><?php print empty($contact['accreditation']) ? '' : ', '.$contact['accreditation']; ?></h5>
				<?php print empty($contact['job_title']) ? '' : '<div class="c-title">'.$contact['job_title'].'</div>'; ?>
				<?php print empty($contact['telephone']) ? '' : '<div class="c-tel">'.$contact['telephone'].'</div>'; ?>
				<?php print empty($contact['email']) ? '' : '<a class="c-email" href="mailto:'.$contact['email'].'" title="Email '.$contact['first_name'].'">'.$contact['email'].'</a>'; ?>
			</li>
			<?php
				$i++;
			} ?>
		<?php } ?>
		</ul>
	</div>
	
	