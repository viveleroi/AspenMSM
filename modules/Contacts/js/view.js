$('html').addClass('js');

$(document).ready(function(){

	$('html').removeClass('js');
	
	$('#scroll-list').jScrollPane();
	
	$('#directory-list').listnav({
		includeNums: false,
		onClick: function(){ $('#scroll-list').jScrollPane(); } 
	});
	
	$('.toggle-group').removeClass('open').addClass('closed').html('Show');
	$('.group-info').hide();
	
	$("a.toggle-group").live('click', function() {
		toggleListItem(this);
		return false;
	});
	
	
	
	/**
	 * Group Management
	 */
	// handle add form
	$('#add-group').submit(function(){
		status();
		$.ajax({
			type: "GET",
			url: "index.php",
			data: 'module=Contacts&method=ajax_addGroup&name=' + escape($('#add-group-area input[type=text]').val()),
			success: function(json){
				var res = $.evalJSON(json);
				if(res.success){
					$('#add-group-area input[type=text]').val('');
					setTimeout(function() {
						
						// prepare new element
						var new_grp = $('#groupholder-0').clone(false);
						new_grp.attr('id', 'groupholder-'+res.id);
						new_grp.find('.group-info').attr('id', 'group_'+res.id+'_details');
						new_grp.find('.group-info li').remove();
						new_grp.find('.group-info ul').append( '<li class="empty">There are no contacts in this group.</li>' );
						new_grp.find('.legend a').attr('id', 'group_'+res.id);
						new_grp.find('.legend strong').html(res.name);
						$('#directory-groups').append( new_grp );

				        $.modal.close();
				    }, 500);
				} else {
				
					$.modal.close();
					
				}
			}
		});
		return false;
	});
	
	// used to avoid function scope issues
	var global_id = 0;
	
	// delete group link
	$('#directory-groups a.delete').click(function(){
		
		var par_id = $(this).parent().attr('id');
		par_id = par_id.replace(/group_/, '');
		par_id = par_id.replace(/_details/, '');
		global_id = par_id;
		
		confirm('Are you sure you want to delete this group?', function(){
			
			par_id = global_id;
			global_id = 0;
			
			status();
			$.ajax({
				type: "GET",
				url: "index.php",
				data: 'module=Contacts&method=ajax_deleteGroup&id=' + par_id,
				success: function(json){
					var res = $.evalJSON(json);
					if(res.success){
						setTimeout(function() { 
							$('#groupholder-'+res.id).remove();
					        $.modal.close();
					    }, 500);
						return false;
					}
				}
			});
		});	
			
		return false;
	});
	
	// make names draggable
	$('#directory-list li').draggable( {
		handle: '.drag',
		helper: 'clone',
		appendTo: 'body',
		revert: true
	} );
	
	// make groups droppable
	$('#directory-groups .group').live('mouseover', function(){
		
		$('#directory-groups .group').droppable({
			hoverClass: 'menu-target',
			drop: function(event, ui){
	
				var group = $(this).attr('id');
				group = group.replace(/groupholder-/, '');
				
				var is_open = $('#group_'+group+'_details').css('display') == 'block' ? true : false;
				
				var contact = ui.draggable.attr('id');
				contact = contact.replace(/contact-/, '');
				
				status();
				$.ajax({
					type: "GET",
					url: "index.php",
					data: 'module=Contacts&method=ajax_dropContact&group='+group+'&id=' + contact,
					success: function(json){
						var res = $.evalJSON(json);
						
						if(!is_open){
							toggleListItem( $('#groupholder-'+group+' a.toggle-group') );
						}
						
						// if success, append the name
						if(res.success){
							if(res.contact.first_name){

								$('#group_'+group+'_details ul li').each(function(){
									if($(this).hasClass('empty')){
										$(this).remove();
									}
								})
								
								$('#group_'+group+'_details ul').append( $('<li class="contact-'+res.id+'">'+res.contact.last_name+', '+res.contact.first_name+'<a class="remove" title="Are you sure you wish to remove this listing?" href="#">Remove</a></li>') );
							}
						} else {
							
							if(res.exists){
								 // person already exists
							} else {
								alert('There was an error adding this group to the contact.'); 
							}
						}
						
						$.modal.close();
						
					}
				});
			}
		});
	});
	
	// delete member from group link
	$('#directory-groups li a.remove').live('click', function(){
		
		var group_id = $(this).parents('div').attr('id');
		group_id = group_id.replace(/group_/, '');
		group_id = group_id.replace(/_details/, '');
		
		var contact_id = $(this).parent().attr('class');
		contact_id = contact_id.replace(/contact-/, '');
		
		status();
		$.ajax({
			type: "GET",
			url: "index.php",
			data: 'module=Contacts&method=ajax_removeContactFromGroup&id=' + contact_id +'&group_id='+ group_id,
			success: function(json){
				var res = $.evalJSON(json);
				if(res.success){
					setTimeout(function() {
						$('#group_'+group_id+'_details li.contact-'+contact_id).remove();
						
						if($('#group_'+group_id+'_details li').length == 0){
							$('#group_'+group_id+'_details ul').append( '<li class="empty">There are no contacts in this group.</li>' );
						}
						
				        $.modal.close();
				    }, 500);
				}
			}
		});
		
		return false;
	});
});