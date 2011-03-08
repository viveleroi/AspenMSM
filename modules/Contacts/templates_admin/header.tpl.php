<link rel="stylesheet" type="text/css" href="<?php print router()->getModuleUrl() ?>/css/style.css" />

<?php if(router()->method() != "view"){ ?>
<script type="text/javascript" src="<?php print router()->getModuleUrl() ?>/js/edit.js"></script>
<?php } else {?>
<script type="text/javascript" src="<?php print router()->interfaceUrl() ?>/js/jquery.listnav.js"></script>
<script type="text/javascript" src="<?php print router()->interfaceUrl() ?>/js/jScrollPane.js"></script>
<script type="text/javascript" src="<?php print router()->getModuleUrl() ?>/js/view.js"></script>
<?php } ?>


