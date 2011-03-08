<?php
/**
 * Template: Basic Text
 */
?>

<div class="text-section">
<?php if(!empty($content['title']) && $content['show_title']){ ?>
<h3><?php print app()->html->purify($content['title']); ?></h3>
<?php } ?>
<?php print $content['content']; ?>
</div>