
initTinyMCE();
var remove_id = 0;

$(document).ready(function() {

	textEditor();

	$('#general').addClass('closed').html('Show');
	$('#general-area').hide();
	$('#sections-head a').addClass('open').html('Hide');
	$('#page-sections fieldset').css({backgroundColor: 'transparent'});
	$('#page-sections li.list').css({backgroundColor: '#383838', marginBottom: 5});
	$('.toggle-section').removeClass('open').addClass('closed').html('Show');
	$('.editable').hide();
	
	$("#page-sections").sortable({
		placeholder: "target",
		axis: 'y',
		handle: ".drag",
		scroll: true,
		opacity: 0.5
	});
	
	$('a.delete-confirm').live("click", function () {
		$(this).parents().each(function(){
			if(this.tagName == 'LI'){
				remove_id = this.id;
			}
		});
		confirm( $(this).attr('title'), function () {
			removeSection();
		});
		return false;
	});
	
	$("a.toggle-section").live('click', function() {
		toggleSection(this);
		return false;
	});
	
	$("#add-section").live('change', function() {
		addsection();
	});
	
	$('#sort-toggle').click(function() {
		enableSorting();
		return false;
	});
	
});

function enableSorting(){
	if($('#sort-toggle').html() == 'Enable Sorting'){
		
		$('textarea').each(function(){
			tinyMCE.execCommand('mceRemoveControl', false, this.id);
		});
		
		$('#page-sections li.list').stop().animate({backgroundColor: '#181818', marginBottom: 5}); 
		$('.editable').stop().animate({opacity: 'hide', height: 'hide'}, 'slow');
		$('.toggle-section').addClass('drag').html('Drag');
		$('#sort-toggle').html('Disable Sorting');
		return false;
	}
	if($('#sort-toggle').html() == 'Disable Sorting'){
		initTinyMCE();
		$('.toggle-section').removeClass('drag').addClass('closed').html('Show');
		$('#page-sections li.list').stop().animate({backgroundColor: '#383838'});
		$('#sort-toggle').html('Enable Sorting');
		return false;
	}
}

function toggleSection(elem){
	var target = $(elem).attr("id");
	if($(elem).html() == 'Hide'){
		$(elem).removeClass('open').addClass('closed').html('Show');
		$('#' + target + '_form').animate({opacity: 'hide', height: 'hide'}, 'slow');
		$('#' + target + '_sort').animate({backgroundColor: '#383838', marginBottom: 5});
		return false;
	}
	if($(elem).html() == 'Show'){
		$(elem).removeClass('closed').addClass('open').html('Hide');
		$('#' + target + '_form').animate({opacity: 'show', height: 'show'}, 'slow');
		$('#' + target + '_sort').animate({backgroundColor: '#181818', marginBottom: 35});
		return false;
	}
}


// handle the image-based checkbox
function checkPageIsLive(){
	var checked = $("#page_is_live").attr("checked");
	$("#page_is_live").attr("checked", !checked);
	if(!checked){
		$('#page_is_live_toggle').removeClass('private').addClass('live');
	} else {
		$('#page_is_live_toggle').removeClass('live').addClass('private');
	}
}

//add a new sextion
function addsection(){

	var selection = $('#add-section').val();
	last_id = last_id + 1;

	status();
	$.ajax({
		type: "GET",
		url: INTERFACE_URL+"/index.php",
		data: 'module=Pages&method=ajax_loadBlankSection&section='+selection+'&next_id=' + last_id + '&page_id=' + $('#page_id').val() + '&template=' + $('#page_template').val(),
		success: function(html){
			var new_li = document.createElement('li');
			$(new_li).hide();
			new_li.innerHTML = html;
			new_li.id = 'editor_' + last_id + '_sort';
			new_li.className = 'new';	
			var new_id = selection.replace(/editor/, '');
			new_id += last_id + '_content';
			$('#page-sections').append(new_li);
			tinyMCE.execCommand('mceAddControl', false, new_id);
			textEditor(new_id);
			$('#page-sections fieldset').css({backgroundColor: 'transparent'});
			if ($('#none:visible').length){
				$('#none').animate({opacity: 'hide', height: 'hide'}, 'slow', function() {
					$(this).remove();
					$(new_li).animate({opacity: 'show', height: 'show'}, 'slow', function() {
						setTimeout(function() { 
							$.modal.close();
						}, 500);
					});
				});
			} else {
				$(new_li).animate({opacity: 'show', height: 'show'}, 'slow', function() {
					setTimeout(function() { 
						$.modal.close();
					}, 500);
				});
			}
			$('a.help').cluetip();
		}
	});
	$('#add-section').val(0).blur();
	return false;
}

// removes any type of section from dom
function removeSection(){
	
	section_id = remove_id.replace(/editor_/, '');
	section_id = section_id.replace(/_sort/, '');

	status();
	$.ajax({
		type: "GET",
		url: INTERFACE_URL+"/index.php",
		data: 'module=Pages&method=ajax_removeSection&section='+section_id,
		success: function(){
			$('#'+remove_id).animate({opacity: 'hide', height: 'hide'}, 'slow', function () {
				$(this).remove();
				if($('#page-sections li').length == 0) {
					var empty_li = document.createElement('li');
					$(empty_li).hide();
					empty_li.innerHTML = 'No page sections available. Select from the options below.';
					empty_li.id = 'none';
					empty_li.className = 'empty';
					$('#page-sections').append(empty_li);
					$(empty_li).animate({opacity: 'show', height: 'show'}, 'slow');
				}
				setTimeout(function() { 
					$.modal.close();
				}, 500);
			});
		}
	});
}