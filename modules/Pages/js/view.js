$(document).ready(function(){
		
	$("#page-list").jTree({
		showHelper: true,
		clickHandle: ".drag",
		hOpacity: 0.75,
		pHeight: "5px",
		snapBack: 300,
		callback: function(){
			status();
			$.ajax({
				type: "GET",
				url: INTERFACE_URL+"/index.php",
				data: 'module=Pages&method=ajax_nestPages'+serializeUl($('#page-list'),'&list'),
				success: function(html){
					setTimeout(function() { 
				        $.modal.close();
				    }, 2000);
				}
			});
		}
	});
	
	$(".vis_toggle").live('click', function() {
		elem_id = $(this).attr('id');
		elem_id = elem_id.replace(/vis_toggle_/, '');
		$('#vis_toggle_' + elem_id).addClass('loading');
		$.ajax({
			type: "GET",
			url: "index.php",
			data: 'module=Pages&method=ajax_toggleDisplay&id=' + elem_id,
			success: function(xml){
				viewStatus(elem_id, xml);
			}
		});
		return false;
	});
	
});



function serializeUl(elem, prepend){
	var serialStr 	= '';
	var li_count 	= 0;
	$(elem).children().each(function(){
		var parent_li = this.id;
		parent_li = parent_li.replace(/page-/, '');
		serialStr += prepend+'['+li_count+'][id]='+parent_li;
		var child_base = prepend+'['+li_count+'][children]';

		$(this).children().each(function(){
			if(this.tagName == 'UL'){
				serialStr += serializeUl(this, child_base);
			}
		});
		li_count++;
	});
	return serialStr;
}

