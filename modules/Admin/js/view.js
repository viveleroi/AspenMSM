$(document).ready(function() {

	$('#adminConfig').addClass('open').html('Hide');
	
	$('#adminModules').addClass('closed').html('Show');
	$('#adminModules-area').hide();
	
	$('#adminTheme').addClass('closed').html('Show');
	$('#adminTheme-area').hide();
	
/*
	$('a.help-new[title]').qtip({
	content: {
         text: false, // Use each elements title attribute
         url: $(this).attr('href'), // Use the rel attribute of each element for the url to load
      },
   show: 'mouseover',
   hide: 'mouseout'
*/
   
   $('a.help-new[title]').each(function() {
	   var self = this;
	   $(self).qtip({
			content: {
				url: $(self).attr('href'),
				title: {
					text: $(self).attr('title'), // Give the tooltip a title using each elements text
               button: 'Close' // Show a close link in the title
				}
			},
			position: {
            corner: {
               target: 'rightMiddle', // Position the tooltip above the link
               tooltip: 'leftMiddle'
            },
            adjust: {
               screen: true, // Keep the tooltip on-screen at all times
               scroll: true
            }
         },
			show: 'mouseover',
		   hide: 'unfocus',
		    style: {
            tip: true,
            border: {
               width: 0,
               radius: 4,
               color: 'rgba(0,0,0,0.75)'
            },
            name: 'light', // Use the default light style
            width: 200 // Set the tooltip width
         }
	   })
   });
   
   
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
});