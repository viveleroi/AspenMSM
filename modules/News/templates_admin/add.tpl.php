	<h2>Add News Item</h2>
	<?php print $this->APP->form->printErrors(); ?>
	<?php print $this->APP->sml->printMessage(); ?>
	<form action="<?php print $this->createFormAction(); ?>" method="post" enctype="multipart/form-data">
		<div class="frame">
			<h3>News Details</h3>
			<fieldset>
				<ol>
					<li>
						<label for="title">Article Title:</label>
						<input id="title" name="title" type="text" value="<?php print $values['title']; ?>" />
						<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/news-article_title.htm" title="Article Title">Help</a>
					</li>
					<li>
						<label for="timestamp">Date:</label>
						<input id="timestamp" name="timestamp" type="text" value="<?php print date("Y-m-d", strtotime($values['timestamp'])); ?>" class="dateformat-Y-ds-m-ds-d" />
						<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/news-article_title.htm" title="Article Date">Help</a>
					</li>
				</ol>
				<ol>
					<li>
						<label for="summary">Summary:</label>
						<textarea id="summary" name="summary" rows="5" cols="60"><?php print htmlentities($values['summary'], ENT_QUOTES, 'UTF-8'); ?></textarea>
						<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/news-summary.htm" title="Summary">Help</a>
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
						<a class="help" href="<?php print $this->APP->router->getModuleUrl() ?>/help/news-pdf_attachment.htm" title="PDF Attachment">Help</a>
					</li>
				</ol>
			</fieldset>
		</div>
		<fieldset class="action">
			<button class="right" type="submit" name="submit"><span><em>Save</em></span></button>
			<a class="button left" href="<?php print $this->createXhtmlValidUrl('view'); ?>" title="Click to Cancel"><span>Cancel</span></a>
		</fieldset>
	</form>