<h2>Manage Courses</h2>
<?= sml()->printMessage(); ?>
	<?php if($course_groups['RECORDS']){
		foreach($course_groups['RECORDS'] as $group){ ?>
		<div class="frame">
			<h3 class="show-hide"><a id="course-<?php print $group['id'] ?>" class="main-course toggle-frame open" href="#" title="Click to Open/Close this Product">Hide</a> <?php print $group['name'] ?></h3>
			
			<div id="course-<?php print $group['id'] ?>-area" class="course-info loadfirst clearfix">
				<ul class="list-display">
				<?php if($group['courses']['RECORDS']){
					foreach($group['courses']['RECORDS'] as $course){ ?>
					<li id="item-<?php print $course['id']; ?>">
						<div class="legend">
							<strong><?php print $course['title']; ?></strong>
							<span class="icons">
								<a class="edit" href="<?php print $this->xhtmlUrl('edit', array('id' => $course['id'])) ?>" title="Edit this Case Study">Edit</a>
								<a class="delete confirm" href="" title="Are you sure you want to delete this case study">Delete</a>
								<a href="#" id="vis_toggle_<?php print $course['id'] ?>" class="vis_toggle case <?php print $course['public'] ? 'live' : 'private' ?>">Hide</a>
							</span>
						</div>
					</li>
					<?php }
				} else { ?>
					<li class="empty">There are currently no courses.</li>
				<?php } ?>
				</ul>
			</div>
		</div>
		<?php }
	} else { ?>
		<div class="frame">
			<h3>There are currently no products listed.</h3>
		</div>
	<?php } ?>
	<div class="action">
		<a class="button right" href="<?php print $this->xhtmlUrl('add'); ?>" title="Click to add a new course"><span>Add A Course</span></a>
	</div>