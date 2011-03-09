	<h2><span>Currently Editing:</span> <?php print $form->cv('title'); ?></h2>
	<?= $form->printErrors(); ?>
	<?= sml()->printMessage(); ?>
	<form action="<?php print $this->action(); ?>" method="post" enctype="multipart/form-data">
		<div class="frame">
			<h3>News Details</h3>
			<fieldset>
				<ol>
					<li>
						<label for="title">Article Title:</label>
						<input id="title" name="title" type="text" value="<?php print htmlentities($form->cv('title'), ENT_QUOTES, 'UTF-8'); ?>" />
						<a class="help" href="<?php print router()->moduleUrl() ?>/help/news-article_title.htm" title="Article Title">Help</a>
					</li>
					<li>
						<label for="timestamp">Date:</label>
						<input id="timestamp" name="timestamp" type="text" value="<?php print date("Y-m-d", strtotime($form->cv('timestamp'))); ?>" class="dateformat-Y-ds-m-ds-d" />
						<a class="help" href="<?php print router()->moduleUrl() ?>/help/news-article_title.htm" title="Article Date">Help</a>
					</li>
				</ol>
				<ol>
					<li>
						<label for="summary">Summary:</label>
						<textarea id="summary" name="summary" rows="5" cols="60"><?php print htmlentities($form->cv('summary'), ENT_QUOTES, 'UTF-8'); ?></textarea>
						<a class="help" href="<?php print router()->moduleUrl() ?>/help/news-summary.htm" title="Summary">Help</a>
					</li>
					<li class="tmce">
						<label for="body">Body:</label>
						<textarea id="body" name="body" class="mce-editor content-area" rows="10" cols="60"><?php print htmlentities($form->cv('body'), ENT_QUOTES, 'UTF-8'); ?></textarea>
					</li>
				</ol>
				<ol>
					<li>
						<label for="pdf_filename">PDF Attachment:</label>
						<div class="attachment"><?php print $this->truncateFilename($form->cv('pdf_filename'), 35); ?></div>
					</li>
					<li>
						<label>Replace File:</label>
						<input type="file" name="pdf_filename" id="pdf_filename" />
						<a class="help" href="<?php print router()->moduleUrl() ?>/help/news-pdf_attachment.htm" title="PDF Attachment">Help</a>
					</li>
				</ol>
			</fieldset>
			<a class="dark-button confirm" href="<?php print $this->xhtmlUrl('delete', array('id' => $form->cv('news_id'))); ?>" title="Delete "><span>Delete</span></a>
		</div>
		<fieldset class="action">
			<button class="right" type="submit" name="submit"><span><em>Save</em></span></button>
			<a class="button left" href="<?php print $this->xhtmlUrl('view', false, 'News'); ?>" title="Click to Cancel"><span>Cancel</span></a>
		</fieldset>
	</form>