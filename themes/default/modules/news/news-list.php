<?php
/**
 * Template: News List
 */
?>

<?php if(!empty($content['title']) && $content['show_title']){ ?>
<h3><?php print htmlentities($content['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
<?php } ?>

<?php
if(is_array($content['news'])){
	if($content['link_to_full_page']){
?>
<ul class="news-list">
<?php
foreach($content['news'] as $news){
	$date = template()->niceDate($news['timestamp'], "n/j/y", '-', true);
	$detail_page = $content['detail_page_id'] ? $content['detail_page_id'] : $page['page_id'];
	$link = app()->url($detail_page, false, array('id' => $news['id']));
?>
	<li>
		<h5><span class="date"><?php print $date ?></span>
		<a href="<?php print $link ?>" title="Click to read full article"><?php print app()->html->purify($news['title']) ?></a></h5>
		
		<?php if(!empty($news['summary']) && $content['show_description']){ ?>
		<p><?php print app()->html->purify($news['summary']); ?></p>
		<?php } ?>
	</li>
<?php } ?>
</ul>

<?php } else { ?>

<div class="news-article">
<?php
foreach($content['news'] as $news){
	$date = template()->niceDate($news['timestamp'], "n/j/y", '-', true);
	$link = app()->url(app()->config('news_page_id'), false, array('id' => $news['id']));
	$title = sprintf('<h4><span class="date">%s</span> %s</h4>', $date, $news['title']);
?>
	<?php print $title; ?>
	<?php print $news['body']; ?>
	
	<?php if(!empty($news['pdf_filename'])){ ?>
	<a href="/files/<?php print $news['pdf_filename']; ?>" title="Click here to download this articles PDF">Article PDF</a>
	<?php } ?>
<?php } ?>
</div>
<?php
	}
}
?>