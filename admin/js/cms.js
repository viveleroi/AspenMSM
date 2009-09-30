$('html').addClass('js');

$(document).ready(function() {
	
	$('html').removeClass('js');
	
	$('.list-display li').css({backgroundColor: '#383838', marginBottom: 5});
	
   $('.success').css({backgroundColor: '#8c7f53'});
   $('.success').animate({backgroundColor: '#454134'}, 2000);
   $('.warning').css({backgroundColor: '#7e3535'});
   $('.warning').animate({backgroundColor: '#3e2b2b'}, 2000);
   setTimeout(function() { 
        $('.alert').animate({opacity: 'hide', height: 'hide', marginBottom: '0px', marginTop: '0px'}, 'slow');
    }, 10000);
   
   $("a.toggle-frame").click(function() {
		toggleFrame(this);
		return false;
	});

	$('a.confirm').live("click", function () {
		var loc = $(this).attr('href');
		confirm( $(this).attr('title'), function () {
			window.location.href = loc;
		});
		return false;
	});
	
	$('a.help').cluetip();
	
	$.preloadImages("/admin/img/modal.png");

});

function toggleFrame(elem){
	var target = $(elem).attr("id");
	if($(elem).html() == 'Hide'){
		$(elem).removeClass('open').addClass('closed').html('Show');
		$('#' + target + '-area').animate({opacity: 'hide', height: 'hide'}, 'slow');
		return false;
	}
	if($(elem).html() == 'Show'){
		$(elem).removeClass('closed').addClass('open').html('Hide');
		$('#' + target + '-area').animate({opacity: 'show', height: 'show'}, 'slow');
		return false;
	}
}

function toggleListItem(elem){
	var target = $(elem).attr("id");
	if($(elem).html() == 'Hide'){
		$(elem).removeClass('open').addClass('closed').html('Show');
		$('#' + target + '_details').animate({opacity: 'hide', height: 'hide'}, 'slow');
		$('#' + target + '_listing').animate({backgroundColor: '#383838', marginBottom: 5});
		return false;
	}
	if($(elem).html() == 'Show'){
		$(elem).removeClass('closed').addClass('open').html('Hide');
		$('#' + target + '_details').animate({opacity: 'show', height: 'show'}, 'slow');
		$('#' + target + '_listing').animate({backgroundColor: '#181818', marginBottom: 35});
		return false;
	}
}

function viewStatus(elem_id, xml){
	if($("direction", xml).text() == 0){
		$('#vis_toggle_' + elem_id).removeClass('live').addClass('private');
	} else {
		$('#vis_toggle_' + elem_id).removeClass('private').addClass('live');
	}
	setTimeout(function() { 
		$('#vis_toggle_' + elem_id).removeClass('loading');
	}, 1000);
}

function confirm(message, callback) {
	$.modal('<div class="modal">\
		<div class="content">\
			<div class="t"></div>\
			<div class="holder">\
				<h3>Please Confirm!</h3>\
				<p class="message"></p>\
				<a id="no" class="dark-button simplemodal-close" href="#"  title="No"><span>No</span></a>\
				<a id="yes" class="dark-button" href="#" title="Yes"><span>Yes</span></a>\
			</div>\
		</div>\
		<div class="b"><div></div></div>\
	</div>', {
		onOpen: modalOpen,
		close:false,
		overlayId:'modal-overlay',
		containerId:'modal-container',
		position: [100,],
		containerCss: {
			width: '300px'
		},
		opacity: 80,
		onShow: function (dialog) {
			dialog.data.find('.message').append(message);
			dialog.data.find('#yes').click(function () {
				if ($.isFunction(callback)) {
					callback.apply();
				}
				$.modal.close();
				return false;
			});
		},
		onClose: modalClose
	});
}

function modalOpen (dialog) {
	dialog.overlay.fadeIn(200, function () {
		dialog.data.show();
		dialog.container.fadeIn(200, function () {});
	});
}

function modalClose (dialog) {
	dialog.container.fadeOut(200, function () {});
	dialog.overlay.fadeOut(200, function () {
		$.modal.close();
	});
}


function status() {
	$.modal('<div class="status"></div>', {
		onOpen: statusOpen,
		close:false,
		overlayId:'modal-overlay',
		opacity: 80,
		onClose: statusClose
	});
}

function statusOpen(throbber) {
	throbber.overlay.fadeIn(200, function () {
		throbber.data.show();
		throbber.container.fadeIn(200, function () {});
	});
}

function statusClose(throbber) {
	throbber.overlay.fadeOut(200, function () {
		throbber.container.fadeOut(200, function () {});
		$.modal.close();
	});
}

function initTinyMCE() {
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		editor_selector : "mce-editor",
		plugins: "safari,inlinepopups,imagemanager,paste,table",
		browsers : "msie,gecko,opera,safari",
		width : "100%",
		height: '250px',
		remove_script_host: false,
		relative_urls: false,
		dialog_type : "modal",
		theme_advanced_buttons1 : "pastetext,pasteword,|,bold,italic,sub,sup,|,bullist,numlist,|,image,link,unlink,anchor,|,charmap",
		theme_advanced_buttons2 : "styleselect,formatselect",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_path_location : "bottom",
		theme_advanced_path : false,
		theme_advanced_resizing : true,
		theme_advanced_blockformats : "p,h3,h4,h5,h6",
		content_css : THEME_URL+"/css/tmce-styles.css",
		theme_advanced_resize_horizontal : false,
		extended_valid_elements : "strong/b,em/i,strike,u,-sub,-sup,p[class],a[id|class|name|href|target|title],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],span[class|align|style],object[width|height],param[name|value],embed[src|allowfullscreen|allowscriptaccess|width|height]",
		document_base_url : APPLICATION_URL,
		remove_script_host : true
	});
}

function textEditor(new_id){
	var view = $('<a href="#" class="view-source" title="Click to View Source Code">View Source</a>');
	$( (new_id ? '#'+new_id : '.mce-editor') ).before(view);
	view_src = new_id ? '#' + $('#'+new_id).parents('div').attr('id') + ' .view-source'  : '.view-source';
	$(view_src).click(function() {
		var id = $(this).next('textarea').attr('id');
		if($(this).html() == 'View Source'){
			$(this).html('View Editor');
			$(this).attr({title: "Click to View Text Editor"});
			tinyMCE.execCommand('mceRemoveControl', false, id);
		} else {
			$(this).html('View Source');
			$(this).attr({title: "Click to View Source Code"});
			tinyMCE.execCommand('mceAddControl', false, id);
		}
		return false;
	});
}