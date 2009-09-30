<link rel="stylesheet" type="text/css" href="<?php print $this->APP->router->getModuleUrl() ?>/css/style.css" />
<script type="text/javascript" src="<?php print $this->APP->router->getInterfaceUrl() ?>/js/jquery.qtip.js"></script>
<script type="text/javascript" src="<?php print $this->APP->router->getModuleUrl() ?>/js/view.js"></script>

<script type="text/javascript">
	// send an ajax toggle to make page live or private
	function enableTheme(theme){
		
		$.ajax({
		    type: "GET",
		    url: INTERFACE_URL+"/index.php",
		    data: 'module=Admin&method=ajax_enableTheme&theme=' + escape(theme),
			success: function(){
				
				// disable all other theme icons
				$('.theme_box a').removeClass('live');
				$('.theme_box a').addClass('private');
				
				$('#' + theme + ' a').removeClass('private');
				$('#' + theme + ' a').addClass('live');
	   		}
	   	});
	}
	
</script>