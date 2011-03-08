<link rel="stylesheet" type="text/css" href="<?php print router()->getModuleUrl() ?>/css/style.css" />

<?php if(router()->getSelectedMethod() == "view"){ ?>
<script type="text/javascript" src="<?php print router()->getModuleUrl() ?>/js/view.js"></script>
<?php } ?>

<?php if(router()->getSelectedMethod() == "edit"){ ?>
<script type="text/javascript" src="<?php print router()->getModuleUrl() ?>/js/edit.js"></script>
<?php } ?>