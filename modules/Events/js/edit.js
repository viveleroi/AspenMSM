initTinyMCE();

$(document).ready(function(){
	textEditor();
	
	$('#manage-groups').click(function(){
		manageGroups();
		return false;
	});
	
	// load existing groups
	refreshGroups();
});

function refreshGroups(){
    $('#groups option').remove();
    $.getJSON(INTERFACE_URL+'/index.php?module=Events&method=ajax_listGroups&id=' + $('#event_id').val(),
        function(json){
            $.each(json.groups, function(i,group){
                selected = typeof(group['selected']) == 'string' && group['selected'] == "1";
                $('#groups').append( '<option value="'+group['id']+'"'+(selected ? ' selected="selected"' : '')+'>'+group['name']+'</option>' );
            });
        }
    );
}


function manageGroups(title, message) {
	$.modal('<div class="modal">\
		<div class="content">\
			<div class="t"></div>\
			<div class="holder">\
				<h3>Manage Event Groups</h3>\
				<p class="message">Manage event groups for all events.</p>\
				<form id="add-group" action="index.php" method="post">\
					<ul id="modal-list">\
						<li id="group-0"style="display: none;"><span class="name"></span><a href="#" class="delete"></a></li>\
					</ul>\
					<input class="add-fld" type="text" name="add-group" />\
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

			// load in existing groups
			$.getJSON(INTERFACE_URL+'/index.php?module=Events&method=ajax_listGroups',
				function(json){
					$.each(json.groups, function(i,group){

						var new_grp = dialog.data.find('#group-0').clone(false);
						new_grp.attr('id', 'group-'+group['id']).find('.name').html(group['name']);
						new_grp.show();
						dialog.data.find('#add-group ul').append( new_grp );
					});
				}
			);
			
			
			dialog.data.find('h3').append(title);
			dialog.data.find('.message').append(message);
			dialog.data.find('#yes').click(function () {
				
				// update select box in edit form with new values
				refreshGroups();
				
				$.modal.close();
				return false;
			});
			dialog.data.find('#add-group').submit(function(){
				
				$.ajax({
					type: 'GET',
					url: INTERFACE_URL+'/index.php',
					data: 'module=Events&method=ajax_addGroup&name=' + escape(dialog.data.find('#add-group input[type=text]').val()),
					success: function(json){
						var res = $.evalJSON(json);
						if(res.success){
							dialog.data.find('#add-group input[type=text]').val('');

							// prepare new element
							var new_grp = dialog.data.find('#group-0').clone(false);
							new_grp.attr('id', 'group-'+res.id).find('.name').html(res.name)
							new_grp.show();
							dialog.data.find('#add-group ul').append( new_grp );
			
						}
					}
				});
				return false;
				
			});
			
			dialog.data.find('li .delete').live('click', function(){
				
				var par_id = $(this).parent().attr('id');
				par_id = par_id.replace(/group-/, '');
				
				$.ajax({
					type: 'GET',
					url: INTERFACE_URL+'/index.php',
					data: 'module=Events&method=ajax_deleteGroup&id=' + par_id,
					success: function(json){
						var res = $.evalJSON(json);
						if(res.success){
							$('#group-'+res.id).remove();
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