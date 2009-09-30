<link rel="stylesheet" type="text/css" href="<?php print $this->APP->router->getModuleUrl() ?>/css/style.css" />

<?php if($this->APP->router->getSelectedMethod() == "edit"){ ?>
<script type="text/javascript" src="<?php print $this->APP->router->getInterfaceUrl() ?>/js/datepicker.js"></script>
<script type="text/javascript" src="<?php print $this->APP->router->getModuleUrl() ?>/js/edit.js"></script>
<?php } ?>

<?php if($this->APP->router->getSelectedMethod() == "view"){ ?>
<script type="text/javascript" src="<?php print $this->APP->router->getInterfaceUrl() ?>/js/jtree.js"></script>
<script type="text/javascript" src="<?php print $this->APP->router->getModuleUrl() ?>/js/view.js"></script>
<?php } ?>

<?php if($this->APP->router->getSelectedMethod() != "view"){ ?>
<script type="text/javascript">
	var last_id = '<?php print $this->APP->Pages_Admin->section_count ?>';
</script>
<?php } ?>