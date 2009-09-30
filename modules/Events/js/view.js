$('html').addClass('js');

$(document).ready(function(){

	$('html').removeClass('js');
	
	$('#past').addClass('closed').html('Show');
	$('#past-area').hide();
	$('.toggle-event').removeClass('open').addClass('closed').html('Show');
	$('.event-info').hide();
	
	$("a.toggle-event").live('click', function() {
		toggleListItem(this);
		return false;
	});
	
	$('.vis_toggle').click(function(){
		elem_id = $(this).attr('id');
		elem_id = elem_id.replace(/vis_toggle_/, '');
		$('#vis_toggle_' + elem_id).addClass('loading');
		$.ajax({
			type: "GET",
			url: INTERFACE_URL+"/index.php",
			data: 'module=Events&method=ajax_toggleDisplay&id='+elem_id,
			success: function(xml){
				viewStatus(elem_id, xml);
			}
		});
		return false;
	});
});