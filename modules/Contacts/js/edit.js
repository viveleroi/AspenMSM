initTinyMCE();

$(document).ready(function(){
	textEditor();

	// Set up languages
	$('#manage-langs').click(function(){
		manageLangs();
		return false;
	});
	refreshLangs();

	// Set up specialties
	$('#manage-specialties').click(function(){
		manageSpecialties();
		return false;
	});
	refreshSpecialties();
	
	// delete a specific image
	$('.del-img').click(function(){
		elem_id = this.id;
		elem_id = elem_id.replace(/del-/, '');
		$.ajax({
			type: 'GET',
			url: INTERFACE_URL+'/index.php',
			data: 'module=contacts&method=ajax_deleteImage&id=' + elem_id,
			success: function(xml){
			}
		});
		$(this).parent().remove();
		return false;
	});
});

/**
 *
 */
function refreshLangs(){
    $('#languages option').remove();
    $.getJSON(INTERFACE_URL+'/index.php?module=Contacts&method=ajax_listLanguages&id=' + $('#contact_id').val(),
        function(json){
            $.each(json.langs, function(i,lang){
                selected = typeof(lang['selected']) == 'string' && lang['selected'] == "1";
                $('#languages').append( '<option value="'+lang['id']+'"'+(selected ? ' selected="selected"' : '')+'>'+lang['language']+'</option>' );
            });
        }
    );
}


/**
 *
 */
function refreshSpecialties(){
    $('#specialties option').remove();
    $.getJSON(INTERFACE_URL+'/index.php?module=Contacts&method=ajax_listSpecialties&id=' + $('#contact_id').val(),
        function(json){
            $.each(json.specialties, function(i,specialties){
                selected = typeof(specialties['selected']) == 'string' && specialties['selected'] == "1";
                $('#specialties').append( '<option value="'+specialties['id']+'"'+(selected ? ' selected="selected"' : '')+'>'+specialties['specialty']+'</option>' );
            });
        }
    );
}


/**
 * 
 */
function manageLangs(title, message) {
	$.modal('<div class="modal">\
		<div class="content">\
			<div class="t"></div>\
			<div class="holder">\
				<h3>Manage Languages</h3>\
				<form id="add-lang" action="index.php" method="post">\
					<ul><li id="lang-0"style="display: none;"><span class="name"></span><a href="#" class="delete"></a></li></ul>\
					<input class="add-fld" type="text" name="add-lang" />\
					<input class="add-btn" type="submit" name="Add" value="Add" />\
				</form>\
				<a id="yes" class="dark-button" href="#" title="Close"><span>Close</span></a>\
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
			width: '300px',
		},
		opacity: 80,
		onShow: function (dialog) {

			// load in existing langs
			$.getJSON(INTERFACE_URL+'/index.php?module=Contacts&method=ajax_listLanguages',
				function(json){
					$.each(json.langs, function(i,lang){
						var new_grp = dialog.data.find('#lang-0').clone(false);
						new_grp.attr('id', 'lang-'+lang['id']).find('.name').html(lang['language']);
						new_grp.show();
						dialog.data.find('#add-lang ul').append( new_grp );
					});
				}
			);
			
			
			dialog.data.find('h3').append(title);
			dialog.data.find('.message').append(message);
			dialog.data.find('#yes').click(function () {
				
				// update select box in edit form with new values
				refreshLangs();
				
				$.modal.close();
				return false;
			});
			dialog.data.find('#add-lang').submit(function(){
				
				$.ajax({
					type: 'GET',
					url: INTERFACE_URL+'/index.php',
					data: 'module=Contacts&method=ajax_addLanguage&name=' + escape(dialog.data.find('#add-lang input[type=text]').val()),
					success: function(json){
						var res = $.evalJSON(json);
						if(res.success){
							dialog.data.find('#add-lang input[type=text]').val('');

							// prepare new element
							var new_grp = dialog.data.find('#lang-0').clone(false);
							new_grp.attr('id', 'lang-'+res.id).find('.name').html(res.name)
							new_grp.show();
							dialog.data.find('#add-lang ul').append( new_grp );
			
						}
					}
				});
				return false;
				
			});
			
			dialog.data.find('li .delete').live('click', function(){
				
				var par_id = $(this).parent().attr('id');
				par_id = par_id.replace(/lang-/, '');
				
				$.ajax({
					type: 'GET',
					url: INTERFACE_URL+'/index.php',
					data: 'module=Contacts&method=ajax_deleteLanguage&id=' + par_id,
					success: function(json){
						var res = $.evalJSON(json);
						if(res.success){
							$('#lang-'+res.id).remove();
							return false;
						}
					}
				});
				return false;
				
			});
		},
		onClose: modalClose
	});
}


/**
 * 
 */
function manageSpecialties(title, message) {
	$.modal('<div class="modal">\
		<div class="content">\
			<div class="t"></div>\
			<div class="holder">\
				<h3>Manage Contact Specialties</h3>\
				<form id="add-specialty" action="index.php" method="post">\
					<ul><li id="specialty-0"style="display: none;"><span class="name"></span><a href="#" class="delete"></a></li></ul>\
					<input class="add-fld" type="text" name="add-specialty" />\
					<input class="add-btn" type="submit" name="Add" value="Add" />\
				</form>\
				<a id="yes" class="dark-button" href="#" title="Close"><span>Close</span></a>\
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
			width: '300px',
		},
		opacity: 80,
		onShow: function (dialog) {

			// load in existing specialties
			$.getJSON(INTERFACE_URL+'/index.php?module=Contacts&method=ajax_listSpecialties',
				function(json){
					$.each(json.specialties, function(i,specialties){
						var new_grp = dialog.data.find('#specialty-0').clone(false);
						new_grp.attr('id', 'specialty-'+specialties['id']).find('.name').html(specialties['specialty']);
						new_grp.show();
						dialog.data.find('#add-specialty ul').append( new_grp );
					});
				}
			);

			dialog.data.find('h3').append(title);
			dialog.data.find('.message').append(message);
			dialog.data.find('#yes').click(function () {

				// update select box in edit form with new values
				refreshSpecialties();

				$.modal.close();
				return false;
			});
			dialog.data.find('#add-specialty').submit(function(){

				$.ajax({
					type: 'GET',
					url: INTERFACE_URL+'/index.php',
					data: 'module=Contacts&method=ajax_addSpecialty&name=' + escape(dialog.data.find('#add-specialty input[type=text]').val()),
					success: function(json){
						var res = $.evalJSON(json);
						if(res.success){
							dialog.data.find('#add-specialty input[type=text]').val('');

							// prepare new element
							var new_grp = dialog.data.find('#specialty-0').clone(false);
							new_grp.attr('id', 'specialty-'+res.id).find('.name').html(res.name)
							new_grp.show();
							dialog.data.find('#add-specialty ul').append( new_grp );

						}
					}
				});
				return false;

			});

			dialog.data.find('li .delete').live('click', function(){

				var par_id = $(this).parent().attr('id');
				par_id = par_id.replace(/specialty-/, '');

				$.ajax({
					type: 'GET',
					url: INTERFACE_URL+'/index.php',
					data: 'module=Contacts&method=ajax_deleteSpecialty&id=' + par_id,
					success: function(json){
						var res = $.evalJSON(json);
						if(res.success){
							$('#specialty-'+res.id).remove();
							return false;
						}
					}
				});
				return false;

			});
		},
		onClose: modalClose
	});
}