<?php
/**
 * Template: Event Detail
 */

 if($page['page_id'] && isset($bits[0]) && is_array($content['events'])){
	$event = $content['events'][ $bits[0] ];

?>

<?php if(!empty($content['title']) && $content['show_title']){ ?>
<h3><?php print htmlentities($content['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
<?php } ?>

<div class="article">

	<h4><?php print app()->html->purify($event['title']); ?></h4>

	<?php if(!$event['recurring']){ ?>
		<div class="event-dates">
			<span class="start-date"><?php print date("D. M jS", strtotime($event['start_date'])); ?></span>
			<?php print ($event['end_date'] != '0000-00-00' ? ' &#8211; <span class="end-date">' .  date("D. M jS", strtotime($event['end_date'])) . '</span>' : '') . ', <span>' . date("Y", strtotime($event['start_date'])) ?></span></div>
		<div class="event-times"><?php print ($event['start_time'] != '00:00:00' ? '<span class="start-time">'.date("g:i a", strtotime($event['start_time'])).'</span>' : '') . ($event['end_time'] != '00:00:00' ? ' &#8211; <span class="end-time">'.date("g:i a", strtotime($event['end_time'])).'</span>' : ''); ?></div>
	<?php } else { ?>
		<div class="event-recurring"><?php print app()->html->purify($event['recur_description']); ?></div>';
	<?php } ?>

	<?php print $event['content']; ?>
			
</div>

<?php } ?>