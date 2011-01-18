
$(document).ready(function(){

	$('html').removeClass('js');

	$('.main-course').removeClass('open').addClass('closed').html('Show');
	$('.course-info').hide();

	
	$("a.toggle-news").live('click', function() {
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
			data: 'module=Courses&method=ajax_toggleDisplay&id='+elem_id,
			success: function(xml){
				viewStatus(elem_id, xml);
			}
		});
		return false;
	});
});