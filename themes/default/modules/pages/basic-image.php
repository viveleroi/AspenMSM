<?php
/**
 * Template: Basic Image
 */
?>

<?php
// check for valid large image
$image_loc = $this->APP->router->getUploadsUrl().DS.$content['image_filename'];
$server_loc = $this->APP->config('upload_server_path').DS.$content['image_filename'];
if(empty($content['image_filename']) || !file_exists($server_loc)){
	$image_loc = $this->APP->router->getInterfaceUrl('admin') . '/img/noImageAvailable.jpg';
}

// check for valid thumbnail
$image_thm = $this->APP->router->getUploadsUrl().DS.$content['image_thumbname'];
$server_loc = $this->APP->config('upload_server_path').DS.$content['image_thumbname'];
if(empty($content['image_filename']) || !file_exists($server_loc)){
	$image_thm = $this->APP->router->getInterfaceUrl('admin') . '/img/noImageAvailable.jpg';
}

$image_alt = empty($content['image_alt']) ? $content['title'] : $content['image_alt'];
?>

<div class="text-image-section">
<a href="'.$image_loc.'" class="thumb-display" rel="images[display]" title="'.$image_alt.'">
	<img class="thumb" src="'.$image_thm.'" alt="'.$image_alt.'" /></a>
<?php if(!empty($content['title']) && $content['show_title']){ ?>
<h4><?php print htmlentities($content['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
<?php } ?>
<?php print $content['content']; ?>
</div>