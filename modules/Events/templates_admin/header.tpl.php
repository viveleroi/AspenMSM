<link rel="stylesheet" type="text/css" href="<?php print router()->getModuleUrl() ?>/css/style.css" />

<script type="text/javascript" src="<?php print router()->getModuleUrl() ?>/js/view.js"></script>

<?php if(router()->getSelectedMethod() != "view"){ ?>
<link rel="stylesheet" type="text/css" href="<?php print router()->getInterfaceUrl() ?>/css/datepicker.css" />
<script type="text/javascript" src="<?php print router()->getInterfaceUrl() ?>/js/datepicker.js"></script>
<script type="text/javascript" src="<?php print router()->getModuleUrl() ?>/js/edit.js"></script>
<?php } ?>