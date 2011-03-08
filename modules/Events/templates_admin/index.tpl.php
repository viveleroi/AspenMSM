	<h2>Manage Your Events</h2>

	<?= sml()->printMessage(); ?>
	<div class="frame">
		<h3 class="show-hide"><a id="current" class="toggle-frame open" href="#" title="Click to Open/Close this Section">Hide</a> Upcoming Events (<?php print $cur_events['TOTAL_RECORDS_FOUND']; ?>)</h3>
		<div id="current-area" class="loadfirst clearfix">
			<ul id="current-list" class="list-display">
				<?php
					if($cur_events){
						foreach($cur_events as $events_item){
				?>
				<li id="item_<?php print $events_item['id'] ?>_listing">
					<div class="legend">
						<a id="item_<?php print $events_item['id'] ?>" class="toggle-event open" href="#">Hide</a>
						<strong><?php print $this->truncateText($events_item['title'], 5); ?></strong>
						<span class="icons">
							<a class="edit" href="<?php print $this->xhtmlUrl('edit_event', array('id' => $events_item['id'])) ?>" title="Edit this Event">Edit</a>
							<a class="delete confirm" href="<?php print $this->xhtmlUrl('delete', array('id' => $events_item['id'])) ?>" title="Are you sure you want to delete this event?">Delete</a>
							<a href="#" id="vis_toggle_<?php print $events_item['id'] ?>" class="vis_toggle <?php print $events_item['public'] ? 'live' : 'private' ?>" title="Click to toggle visibility on live site">Hide</a>
						</span>
					</div>
					<div id="item_<?php print $events_item['id'] ?>_details" class="event-info">
						<div class="brief">
							<p class="dates"><?php print date("D M jS Y", strtotime($events_item['start_date'])) ?><?php print $events_item['end_date'] == '0000-00-00' ? '' : ' &#8211; ' . date("D M jS Y", strtotime($events_item['end_date'])) ?></p>
							<?php print $this->truncateText($events_item['description'], 25); ?>
						</div>
					</div>
					<p class="groups"><strong>Groups:</strong> <?php print isset($events_item['groups']) ? implode(', ', $events_item['groups']) : 'N/A'; ?></p>
				</li>
				<?php
						}
					} else {
				?>
				<li class="empty">No event entries available.</li>
				<?php
					}
				?>
			</ul>
		</div>
	</div>
		<div class="frame">
		<h3 class="show-hide"><a id="recurring" class="toggle-frame open" href="#" title="Click to Open/Close this Section">Hide</a> Recurring Events (<?php print $recurring_events['TOTAL_RECORDS_FOUND']; ?>)</h3>
		<div id="recurring-area" class="loadfirst clearfix">
			<ul id="recurring-list" class="list-display">
				<?php
					if($recurring_events){
						foreach($recurring_events as $events_item){
				?>
				<li id="item_<?php print $events_item['id'] ?>_listing">
					<div class="legend">
						<a id="item_<?php print $events_item['id'] ?>" class="toggle-event open" href="#">Hide</a>
						<strong><?php print $this->truncateText($events_item['title'], 5); ?></strong>
						<span class="icons">
							<a class="edit" href="<?php print $this->xhtmlUrl('edit_event', array('id' => $events_item['id'])) ?>" title="Edit this Event">Edit</a>
							<a class="delete confirm" href="<?php print $this->xhtmlUrl('delete', array('id' => $events_item['id'])) ?>" title="Are you sure you want to delete this event?">Delete</a>
							<a href="#" id="vis_toggle_<?php print $events_item['id'] ?>" class="vis_toggle <?php print $events_item['public'] ? 'live' : 'private' ?>" title="Click to toggle visibility on live site">Hide</a>
						</span>
					</div>
					<div id="item_<?php print $events_item['id'] ?>_details" class="event-info">
						<div class="brief">
							<?php print $this->truncateText($events_item['description'], 25); ?>
						</div>
					</div>
					<p class="groups"><strong>Groups:</strong> <?php print isset($events_item['groups']) ? implode(', ', $events_item['groups']) : 'N/A'; ?></p>
				</li>
				<?php
						}
					} else {
				?>
				<li class="empty">No event entries available.</li>
				<?php
					}
				?>
			</ul>
		</div>
	</div>
	<div class="frame">
		<h3 class="show-hide"><a id="past" class="toggle-frame" href="#" title="Click to Open/Close this Section">Hide</a> Past Events (<?php print $past_events['TOTAL_RECORDS_FOUND']; ?>)</h3>
		<div id="past-area" class="loadfirst clearfix">
			<ul id="past-list" class="list-display">
				<?php
					if($past_events){
						foreach($past_events as $events_item){
				?>
				<li id="item_<?php print $events_item['id'] ?>_listing">
					<div class="legend">
						<a id="item_<?php print $events_item['id'] ?>" class="toggle-event open" href="#">Hide</a>
						<strong><?php print $this->truncateText($events_item['title'], 5); ?></strong>
						<span class="icons">
							<a class="edit" href="<?php print $this->xhtmlUrl('edit_event', array('id' => $events_item['id'])) ?>" title="Edit this Event">Edit</a>
							<a class="delete confirm" href="<?php print $this->xhtmlUrl('delete', array('id' => $events_item['id'])) ?>" title="Are you sure you want to delete this event?">Delete</a>
							<a href="#" id="vis_toggle_<?php print $events_item['id'] ?>" class="vis_toggle <?php print $events_item['public'] ? 'live' : 'private' ?>" title="Click to toggle visibility on live site">Hide</a>
						</span>
					</div>
					<div id="item_<?php print $events_item['id'] ?>_details" class="event-info">
						<div class="brief">
							<p class="dates"><?php print date("D M jS Y", strtotime($events_item['start_date'])) ?><?php print $events_item['end_date'] == '0000-00-00' ? '' : ' &#8211; ' . date("D M jS Y", strtotime($events_item['end_date'])) ?></p>
							<?php print $this->truncateText($events_item['description'], 25); ?>
						</div>
					</div>
					<p class="groups"><strong>Groups:</strong> <?php print isset($events_item['groups']) ? implode(', ', $events_item['groups']) : 'N/A'; ?></p>
				</li>
				<?php
						}
					} else {
				?>
				<li class="empty">No event entries available.</li>
				<?php
					}
				?>
			</ul>
		</div>
	</div>

	<div class="action">
		<a class="button right" href="<?php print $this->xhtmlUrl('add_event', false); ?>" title="Click to Add an Event"><span>Add an Event</span></a>
	</div>