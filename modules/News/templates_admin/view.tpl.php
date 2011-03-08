	<h2>Manage Your Site&#8217;s News</h2>
	<?= sml()->printMessage(); ?>
	<div class="frame">
		<h3 class="show-hide"><a id="news" class="toggle-frame open" href="#" title="Click to Open/Close this Section">Hide</a> Recent News</h3>
		<div id="news-area" class="loadfirst clearfix">
			<ul id="news-list" class="list-display">
				<?php
					if($cur_news){
						foreach($cur_news as $record){
				?>
				<li id="item_<?php print $record['news_id'] ?>_listing">
					<div class="legend">
						<a id="item_<?php print $record['news_id'] ?>" class="toggle-news open" href="#">Hide</a>
						<strong><?php print $this->truncateText($record['title'],5) ?></strong>
						<span class="icons">
							<a class="edit" href="<?php print $this->xhtmlUrl('edit', array('id' => $record['news_id'])) ?>" title="Edit this News Item">Edit</a>
							<a class="delete confirm" href="<?php print $this->xhtmlUrl('delete', array('id' => $record['news_id'])) ?>" title="Are you sure you want to delete this news article?">Delete</a>
							<a href="#" id="vis_toggle_<?php print $record['news_id'] ?>" class="vis_toggle <?php print $record['public'] ? 'live' : 'private' ?>" title="Click to toggle visibility on live site">Hide</a>
						</span>
					</div>
					<div id="item_<?php print $record['news_id'] ?>_details" class="news-info">
						<div class="brief">
							<p class="dates"><?php print date("D M jS Y", strtotime($record['timestamp'])) ?></p>
							<?php print $record['summary'] ?>
						</div>
					</div>
				</li>
				<?php
						}
					} else {
				?>
				<li class="empty">No news entries available.</li>
				<?php
					}
				?>
			</ul>
		</div>
	</div>
	<div class="frame">
		<h3 class="show-hide"><a id="past" class="toggle-frame" href="#" title="Click to Open/Close this Section">Hide</a> Older News</h3>
		<div id="past-area" class="loadfirst clearfix">
			<ul id="past-list" class="list-display">
				<?php
					if($past_news){
						foreach($past_news as $record){
				?>
				<li id="item_<?php print $record['news_id'] ?>_listing">
					<div class="legend">
						<a id="item_<?php print $record['news_id'] ?>" class="toggle-news open" href="#">Hide</a>
						<strong><?php print $this->truncateText($record['title'],5) ?></strong>
						<span class="icons">
							<a class="edit" href="<?php print $this->xhtmlUrl('edit', array('id' => $record['news_id'])) ?>" title="Edit this News Item">Edit</a>
							<a class="delete confirm" href="<?php print $this->xhtmlUrl('delete', array('id' => $record['news_id'])) ?>" title="Are you sure you want to delete this news article?">Delete</a>
							<a href="#" id="vis_toggle_<?php print $record['news_id'] ?>" class="vis_toggle <?php print $record['public'] ? 'live' : 'private' ?>" title="Click to toggle visibility on live site">Hide</a>
						</span>
					</div>
					<div id="item_<?php print $record['news_id'] ?>_details" class="news-info">
						<div class="brief">
							<p class="dates"><?php print date("D M jS Y", strtotime($record['timestamp'])) ?></p>
							<?php print $record['summary'] ?>
						</div>
					</div>
				</li>
				<?php
						}
					} else {
				?>
				<li class="empty">No news entries available.</li>
				<?php
					}
				?>
			</ul>
		</div>
	</div>
	<div class="action">
		<a class="button right" href="<?php print $this->xhtmlUrl('add'); ?>" title="Click to Add A Page"><span>Add News</span></a>
	</div>