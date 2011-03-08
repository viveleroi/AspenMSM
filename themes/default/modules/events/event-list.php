<?php
/**
 * Template: Event List
 */
?>

<div class="events">

	<?php if(!empty($content['title']) && $content['show_title']){ ?>
	<h3><?php print htmlentities($content['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
	<?php } ?>

<ul class="event-list">

	<?php
	if(is_array($content['events'])){
		foreach($content['events'] as $event){
	?>

	<li class="month-<?php print strtolower(date("F", strtotime($event['start_date']))); ?>">

		<?php
		if($content['link_to_full_page'] && $page['page_id']){
			if(!empty($event['content'])){
				$detail_page = $content['detail_page_id'] ? $content['detail_page_id'] : $page['page_id'];
				$link = app()->url($detail_page, false, array('id' => $event['id']));
		?>
		<h5><a href="<?php print $link; ?>" title="Click to view full event details"><?php print app()->html->purify($event['title']); ?></a></h5>
		<?php
			}
		} else {
		?>
		<h5><?php print $event['title']; ?></h5>
		<?php } ?>

		<div class="date-time">
		<?php if(!$event['recurring']){ ?>
			<span class="event-dates"><span class="start-date"><?php print date("D. M jS", strtotime($event['start_date'])); ?></span><?php print ($event['end_date'] != '0000-00-00' ? ' &#8211; <span class="end-date">' .  date("D. M jS", strtotime($event['end_date'])) . '</span>' : '') . ', <span>' . date("Y", strtotime($event['start_date'])); ?></span>
		<?php } else { ?>
			<span class="event-recurring"><?php print app()->html->purify($event['recur_description']); ?></span>
		<?php } ?>
			
			<span class="event-times"><?php ($event['start_time'] != '00:00:00' ? '<span class="start-time">'.date("g:i a", strtotime($event['start_time'])).'</span>' : '') . ($event['end_time'] != '00:00:00' ? ' &#8211; <span class="end-time">'.date("g:i a", strtotime($event['end_time'])).'</span>' : ''); ?></span>
		</div>
		
		<?php if($content['show_description']){ ?>
		<div class="event-description"><?php print $event['description']; ?></div>
		<?php } ?>

	</li>

<?php
	}
} else {
?>
		<li class="empty">Nothing listed at this time.</li>
<?php } ?>
	</ul>
</div>