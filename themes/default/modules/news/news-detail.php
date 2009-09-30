<?php
/**
 * Template: News Detail
 */
 if($page['page_id'] && isset($bits[0]) && is_array($content['news'])){
	$news = $content['news'][ $bits[0] ];
?>

<div id="article">
	<?php if(!empty($news['pdf_filename'])){ ?>
	<p class="pdf-download">
		<a href="/files/news/<?php print $news['pdf_filename']; ?>" title="Click here to download this articles PDF">Downloadable PDF Available</a>
	</p>
	<?php } ?>

	<p class="date"><?php print $this->APP->template->niceDate($news['timestamp'], "l F j, Y", '-', true); ?></p>
	<h3><?php print $this->APP->html->purify($news['title']); ?></h3>
	<?php print $news['body']; ?>
</div>

<?php } ?>