<link rel="stylesheet" type="text/css" href="<?php print router()->moduleUrl() ?>/css/style.css" />

<?php if(router()->method() == "view"){ ?>
<script type="text/javascript" src="<?php print router()->moduleUrl() ?>/js/view.js"></script>
<?php } ?>

<?php if(router()->method() != "view"){ ?>
<link rel="stylesheet" type="text/css" href="<?php print router()->interfaceUrl() ?>/css/datepicker.css" />
<script type="text/javascript" src="<?php print router()->interfaceUrl() ?>/js/datepicker.js"></script>
<script type="text/javascript" src="<?php print router()->moduleUrl() ?>/js/edit.js"></script>
<?php } ?>