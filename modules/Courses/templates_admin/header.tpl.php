<link rel="stylesheet" type="text/css" href="<?php print $this->APP->router->getModuleUrl() ?>/css/style.css" />

<?php if($this->APP->router->getSelectedMethod() != "view"){ ?>
<script type="text/javascript" src="<?php print $this->APP->router->getModuleUrl() ?>/js/edit.js"></script>
<?php } else {?>
<script type="text/javascript" src="<?php print $this->APP->router->getInterfaceUrl() ?>/js/jScrollPane.js"></script>
<script type="text/javascript" src="<?php print $this->APP->router->getModuleUrl() ?>/js/view.js"></script>
<?php } ?>


