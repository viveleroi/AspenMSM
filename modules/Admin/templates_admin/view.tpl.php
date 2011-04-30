	<h2>Admin Settings</h2>
	<?= sml()->printMessage(); ?>
	<!--
	<div class="frame">
		<h3 class="show-hide"><a id="adminModules" class="toggle-frame" href="#" title="Click to Open/Close this Section">Hide</a> Site Modules</h3>
		<div id="adminModules-area" class="loadfirst clearfix">
			<ul>
				<?php
				/*
				if(count($mods)){
					foreach($mods as $mod){
						
						if($mod['install']){
							$action = '<a title="Install Module" href="' . $this->createUrl('install_module', array('guid' => $mod['guid']), 'Install').'">Install</a>';
						} else {
							$action = '<a title="Uninstall Module" href="' . $this->createUrl('uninstall_module', array('guid' => $mod['guid']), 'Install').'" title="Are you sure you want to uninstall this module?" class="confirm">Uninstall</a>';
						}
				?>
				  	<li class="clearfix">
						<?php print $mod['name'] ?>
						<?php print $action ?>
					</li>
			
				<?php
					}
				}
				 */
				?>
			</ul>
		</div>
	</div>-->
	<div class="frame">
		<h3 class="show-hide"><a id="adminTheme" class="toggle-frame" href="#" title="Click to Open/Close this Section">Hide</a> Site Theme</h3>
		<div id="adminTheme-area" class="loadfirst clearfix">
			<div id="theme-ch">
			<?php
			if(isset($themes) && is_array($themes)){
				if(count($themes) > 1){
					foreach($themes as $theme){
						
						$theme_path = str_replace("modules/Admin/templates_admin", 'themes', dirname(__FILE__));
						$theme_path .= DS . $theme . DS . 'screen.jpg';
		
						if(file_exists($theme_path)){
							$image_path = str_replace('admin', 'themes/' . $theme . '/', router()->interfaceUrl()) . 'screen.jpg';
						} else {
							$image_path = router()->interfaceUrl() . '/img/noImageAvailable.jpg';
						}
			?>
			<div class="theme_box" id="<?php print $theme ?>">
				<h4>Theme: <?php print $theme ?></h4>
				<img src="<?php print $image_path ?>" alt="No Image" />
				<a href="#" onclick="enableTheme('<?php print $theme ?>'); return false;" class="<?php print $live == $theme ? 'live' : 'private' ?>">Activate</a>
			</div>
			<?php
					}
				} else {
			?>
				<p>Only one theme is available for this site.</p>
			<?php
				}
			}
			?>
			</div>
		</div>
	</div>
	<form class="whack-form" action="<?php print $this->action(); ?>" method="post">
		<div class="frame">
			<h3 class="show-hide"><a id="adminConfig" class="toggle-frame" href="#" title="Click to Open/Close this Section">Hide</a> Site Configuration</h3>
			<div id="adminConfig-area"  class="loadfirst clearfix">
				<fieldset>
					<ol>
						<li>
							<label for="website_title">Website Title:</label>
							<input type="text" name="website_title" id="website_title" value="<?php print $form->cv('website_title') ?>" />
							<a class="help" href="<?php print router()->moduleUrl() ?>/help/admin-website_title.htm" title="Website Title">Help</a>
						</li>
						<li>
							<label for="cms_title">CMS Title:</label>
							<input type="text" name="cms_title" id="cms_title" value="<?php print $form->cv('cms_title') ?>" />
							<a class="help" href="<?php print router()->moduleUrl() ?>/help/admin-cms_title.htm" title="CMS Title">Help</a>
						</li>
						<li class="auto">
							<label for="home_page">Home Page:</label>
							<select name="home_page" id="home_page">
							<?php
							if($pages){
								foreach($pages as $page){
							?>
								<option value="<?php print $page['page_id'] ?>"<?php print $form->cv('home_page') == $page['page_id'] ? ' selected="selected"' : false ?>><?php print $page['page_title'] ?></option>
							<?php
								}
							}
							?>
							</select>
							<a class="help" href="<?php print router()->moduleUrl() ?>/help/admin-home_page.htm" title="Home Page">Help</a>
						</li>
					</ol>
					<ol>
						<li>
							<label for="meta_keywords">Meta Keywords:</label>
							<textarea name="meta_keywords" id="meta_keywords"  rows="5" cols="50"><?php print $form->cv('meta_keywords') ?></textarea>
							<a class="help" href="<?php print router()->moduleUrl() ?>/help/admin-meta_keywords.htm" title="Meta Keywords">Help</a>
						</li>
						<li>
							<label for="meta_description">Meta Description:</label>
							<textarea name="meta_description" id="meta_description"  rows="5" cols="50"><?php print $form->cv('meta_description') ?></textarea>
							<a class="help" href="<?php print router()->moduleUrl() ?>/help/admin-meta_description.htm" title="Meta Description">Help</a>
						</li>
					</ol>
				</fieldset>
			</div>
		</div>
		<fieldset class="action">
			<button class="right" type="submit" name="submit"><span><em>Save</em></span></button>
		</fieldset>
	</form>
	