<link rel="stylesheet" type="text/css" href="<?php print router()->getModuleUrl() ?>/css/style.css" />

<?php if(router()->method() == "edit"){ ?>
<script type="text/javascript" src="<?php print router()->interfaceUrl() ?>/js/datepicker.js"></script>
<script type="text/javascript" src="<?php print router()->getModuleUrl() ?>/js/edit.js"></script>
<?php } ?>

<?php if(router()->method() == "view"){ ?>
<script type="text/javascript" src="<?php print router()->interfaceUrl() ?>/js/jtree.js"></script>
<script type="text/javascript" src="<?php print router()->getModuleUrl() ?>/js/view.js"></script>
<?php } ?>

<?php if(router()->method() != "view"){ ?>
<script type="text/javascript">
	var last_id = '<?php print $this->APP->Pages_Admin->section_count ?>';
</script>
<?php } ?>