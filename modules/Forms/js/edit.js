$(document).ready(function(){
	
	$('#form-builder').formbuilder({
		'save_url': INTERFACE_URL+'/index.php?module=forms&method=ajax_saveForm',
		'load_url': INTERFACE_URL+'/index.php?module=forms&method=ajax_loadForm'
	});
		
	$("a.toggle-form").live('click', function() {
		toggleForm(this);
		return false;
	});
	
	$('#sort-toggle').click(function() {
		enableSorting();
		return false;
	});
	
	$("#form-builder").sortable({
		placeholder: "target",
		axis: 'y',
		handle: ".drag",
		scroll: true,
		opacity: 0.5
	});
	
});

function toggleForm(elem){
	var target = $(elem).attr("id");
	if($(elem).html() == 'Hide'){
		$(elem).removeClass('open').addClass('closed').html('Show');
		$('#' + target + '-fld').animate({opacity: 'hide', height: 'hide'}, 'slow');
		$('#' + target + '-item').animate({backgroundColor: '#383838', marginBottom: 5});
		return false;
	}
	if($(elem).html() == 'Show'){
		$(elem).removeClass('closed').addClass('open').html('Hide');
		$('#' + target + '-fld').animate({opacity: 'show', height: 'show'}, 'slow');
		$('#' + target + '-item').animate({backgroundColor: '#181818', marginBottom: 35});
		return false;
	}
}

function enableSorting(){
	if($('#sort-toggle').html() == 'Enable Sorting'){
		$('#form-builder li').animate({backgroundColor: '#181818', marginBottom: 5}); 
		$('.frm-holder:visible').animate({opacity: 'hide', height: 'hide'}, 'slow');
		$('.toggle-form').removeClass('open').addClass('drag').html('Drag');
		$('#sort-toggle').html('Disable Sorting');
		return false;
	}
	if($('#sort-toggle').html() == 'Disable Sorting'){
		$('#form-builder li').animate({backgroundColor: '#383838'});
		$('.toggle-form').removeClass('drag').addClass('closed').html('Show');
		$('#sort-toggle').html('Enable Sorting');
		return false;
	}
}

(function($){
	$.fn.formbuilder = function(options) {
		/**
		 * jQuery Form Builder
		 * Copyright (c) 2009 Mike Botsko, Botsko.net LLC
		 * Licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
		 * Copyright notice and license must remain intact for legal use
		 * Version 1
		 */
		
		
		// Extend the configuration options with user-provided
		var defaults = {
			save_url: false,
			load_url: false
		};
		var opts = $.extend(defaults, options);
		
		
		// Begin the core plugin
		return this.each(function() {
			var ul_obj = this;
			
			var field = '';
			var field_type = '';
			var last_id = 1;
			
			
			/**
			 * LOAD ANY EXISTING XML DATA, GENERATE NEW FORM EDITOR FROM CONTENT
			 */
			if(opts.load_url){
				status();
				$.ajax({
					type: "GET",
					url: opts.load_url,
					data: 'id='+$('#id').val(),
					success: function(xml){
						setTimeout(function() { 
							$.modal.close();
						}, 1000);
						
						var values = '';
						var options = false;
						var required = false;
			
						$(xml).find('field').each(function(){
							// checkbox type
							if($(this).attr('type') == 'checkbox'){
								options = new Array;
								options[0] = $(this).attr('title');
								
								values = new Array;
								$(this).find('checkbox').each(function(a){
									values[a] = new Array(2);
									values[a][0] = $(this).text();
									values[a][1] = $(this).attr('checked');
								});
							}
							// radio type
							else if($(this).attr('type') == 'radio'){
								options = new Array;
								options[0] = $(this).attr('title');
								
								values = new Array;
								$(this).find('radio').each(function(a){
									values[a] = new Array(2);
									values[a][0] = $(this).text();
									values[a][1] = $(this).attr('checked');
								});
							}
							// select type
							else if($(this).attr('type') == 'select'){
								options = new Array;
								options[0] = $(this).attr('title');
								options[1] = $(this).attr('multiple');
								
								values = new Array;
								$(this).find('option').each(function(a){
									values[a] = new Array(2);
									values[a][0] = $(this).text();
									values[a][1] = $(this).attr('checked');
								});
							}
							else {
								values = $(this).text();
							}
							
							required = $(this).attr('required');

							appendNewField( $(this).attr('type'), values, options, required );
							
						});
					$('a.help').cluetip();
					}
				});
			}
			
			
			/**
			 * PREPARE THE DOM FOR THE FORM EDITOR
			 */
			// set the form save action
			$('#save-form').click(function(){
				save();
				return false;
			});
			
			// Create form control select box and add into the editor
			var select = '';
			select += '<select name="field_control" id="field_control">';
			select += '<option value="0">Add New Field...</option>';
			select += '<option value="input_text">Text</option>';
			select += '<option value="textarea">Paragraph</option>';
			select += '<option value="checkbox">Checkboxes</option>';
			select += '<option value="radio">Radio</option>';
			select += '<option value="select">Select List</option>';
			select += '</select>';
			$('.toolbox').append(select);
			$('#field_control').change(function(){
				appendNewField($(this).val());
				$('a.help').cluetip();
				$(this).val(0).blur();
			});
			
			// Register delete item actions
/*
			$('.remove').live('click', function(){
				
				
				confirm( $(this).attr('title'), function () {
					$(this).parent().animate({opacity: 'hide', height: 'hide'}, 'slow').remove();
				});
				return false;
			});
*/
			
			
			/**
			 * ADDING NEW FIELDS
			 */
			// Wrapper for adding a new field
			var appendNewField = function(type, values, options, required){
				
				field = '';
				field_type = type;
				
				if(typeof(values) == 'undefined'){
					values = '';
				}
				
				switch(type){
					case 'input_text':
						appendTextInput(values, required);
						break;
					case 'textarea':
						appendTextarea(values, required);
						break;
					case 'checkbox':
						appendCheckboxGroup(values, options, required);
						break;
					case 'radio':
						appendRadioGroup(values, options, required);
						break;
					case 'select':
						appendSelectList(values, options, required);
						break;
				}
			}
			
			// single line input type="text"
			var appendTextInput = function(values, required){
				
				field += '<label>Label:</label>';
				field += '<input class="fld-title" id="title-'+last_id+'" type="text" value="'+values+'" />';
				help = MODULE_URL + '/help/formbuilder-textfield.htm';
				
				appendFieldLi('Text Field', field, required, help);
				
			}
			
			// multi-line textarea
			var appendTextarea = function(values, required){
				
				field += '<label>Label:</label>';
				field += '<input type="text" value="'+values+'" />';
				help = MODULE_URL + '/help/formbuilder-paragraph.htm';
				
				appendFieldLi('Paragraph Field', field, required, help);
				
			}
			
			// adds a checkbox element
			var appendCheckboxGroup = function(values, options, required){
				
				var title = '';
				if(typeof(options) == 'object'){
					title = options[0];
				}

				field += '<div class="chk_group">';
				field += '<div class="frm-fld"><label>Title:</label>';
				field += '<input type="text" name="title" value="'+title+'" /></div>';
				field += '<div class="false-label">Select Options</div>';
				field += '<div class="fields">';
	
				if(typeof(values) == 'object'){
					for(c in values){
						field += checkboxFieldHtml(values[c]);
					}
				} else {
					field += checkboxFieldHtml('');
				}
				
				field += '<div class="add-area"><a href="#" class="add add_ck">Add</a></div>';
				field += '</div>';
				field += '</div>';
				
				help = MODULE_URL + '/help/formbuilder-checkbox.htm';
				appendFieldLi('Checkbox Group', field, required, help);
				
				$('.add_ck').live('click', function(){
					$(this).parent().before( checkboxFieldHtml() );
					return false;
				});
			}
			
			// Checkbox field html, since there may be multiple
			var checkboxFieldHtml = function(values){
				
				var checked = false;
				
				if(typeof(values) == 'object'){
					var value = values[0];
					checked = values[1] == 'false' ? false : true;
				} else {
					var value = '';
				}
				
				field = '';
				field += '<div>';
				field += '<input type="checkbox"'+(checked ? ' checked="checked"' : '')+' /><input type="text" value="'+value+'" />';
				field += '<a href="#" class="remove" title="Are you sure you want to remove this checkbox?">Remove</a>';
				field += '</div>';
				
				return field;
				
			}
			
			// adds a radio element
			var appendRadioGroup = function(values, options, required){
				
				var title = '';
				if(typeof(options) == 'object'){
					title = options[0];
				}

				field += '<div class="rd_group">';
				field += '<div class="frm-fld"><label>Title:</label>';
				field += '<input type="text" name="title" value="'+title+'" /></div>';
				field += '<div class="false-label">Select Options</div>';
				field += '<div class="fields">';
	
				if(typeof(values) == 'object'){
					for(c in values){
						field += radioFieldHtml(values[c], 'frm-'+last_id+'-fld');
					}
				} else {
					field += radioFieldHtml('', 'frm-'+last_id+'-fld');
				}
				
				field += '<div class="add-area"><a href="#" class="add add_rd">Add</a></div>';
				field += '</div>';
				field += '</div>';
				help = MODULE_URL + '/help/formbuilder-radio.htm';
				
				appendFieldLi('Radio Group', field, required, help);
				
				$('.add_rd').live('click', function(){
					$(this).parent().before( radioFieldHtml(false, $(this).parents('.frm-holder').attr('id')) );
					return false;
				});
			}

			// Radio field html, since there may be multiple
			var radioFieldHtml = function(values, name){
				
				var checked = false;
				
				if(typeof(values) == 'object'){
					var value = values[0];
					checked = values[1] == 'false' ? false : true;
				} else {
					var value = '';
				}
				
				field = '';
				field += '<div>';
				field += '<input type="radio"'+(checked ? ' checked="checked"' : '')+' name="radio_'+name+'" /><input type="text" value="'+value+'" />';
				field += '<a href="#" class="remove" title="Are you sure you want to remove this radio button option?">Remove</a>';
				field += '</div>';
				
				return field;
				
			}
			
			// adds a select/option element
			var appendSelectList = function(values, options, required){
				
				var multiple = false;
				var title = '';
				if(typeof(options) == 'object'){
					title = options[0];
					multiple = options[1] == 'true' ? true : false;
				}
				
				field += '<div class="opt_group">';
				field += '<div class="frm-fld"><label>Title:</label>';
				field += '<input type="text" name="title" value="'+title+'" /></div>';
				field += '';
				field += '<div class="false-label">Select Options</div>';
				field += '<div class="fields">';
				field += '<input type="checkbox" name="multiple"'+(multiple ? 'checked="checked"' : '')+'><label class="auto">Allow Multiple Selections</label>';
	
				if(typeof(values) == 'object'){
					for(c in values){
						field += selectFieldHtml(values[c], multiple);
					}
				} else {
					field += selectFieldHtml('', multiple);
				}
				
				field += '<div class="add-area"><a href="#" class="add add_opt">Add</a></div>';
				field += '</div>';
				field += '</div>';
				help = MODULE_URL + '/help/formbuilder-select-list.htm';
				
				appendFieldLi('Select List', field, required, help);
				
				$('.add_opt').live('click', function(){
					$(this).parent().before( selectFieldHtml('', multiple) );
					return false;
				});
			}

			// Select field html, since there may be multiple
			var selectFieldHtml = function(values, multiple){
				if(multiple){
					return checkboxFieldHtml(values);
				} else {
					return radioFieldHtml(values);
				}
			}
			
			
			// Appends the new field markup to the editor
			var appendFieldLi = function(title, field_html, required, help){
				
				if(required){
					required = required == 'true' ? true : false;
				}
							
				var li = '';
				li += '<li id="frm-'+last_id+'-item" class="'+field_type+'">';
				li += '<div class="legend"><a id="frm-'+last_id+'" class="toggle-form open" href="#">Hide</a> <strong id="txt-title-'+last_id+'">'+title+'</strong> <a class="help" href="'+help+'" title="'+title+'">Help</a></div>';
				li += '<div id="frm-'+last_id+'-fld" class="frm-holder">';
				li += '<div class="frm-elements">';
				li += '<div class="frm-fld"><label for="required-'+last_id+'">Required?</label><input class="required" type="checkbox" value="1" name="required-'+last_id+'" id="required-'+last_id+'"'+(required ? ' checked="checked"' : '')+' /></div>';
				li += field;
				li += '</div>';
				li += '<a id="del_'+last_id+'" class="dark-button delete-confirm" href="#"  title="Are you sure you want to delete this form section?"><span>Delete</span></a>'
				li += '</div>';
				li += '</li>';
				
				$(ul_obj).append(li);
				$('#frm-'+last_id+'-item').hide();
				$('#frm-'+last_id+'-item').animate({opacity: 'show', height: 'show', marginBottom: 35}, 'slow');
				
				last_id++;
			}
			
			$('.remove').live('click', function(){
				$(this).parent('div').animate({opacity: 'hide', height: 'hide', marginBottom: '0px'}, 'fast', function () {
					$(this).remove();
				});
				return false;
			});
			
			$('.delete-confirm').live('click', function() {
				var delete_id = $(this).attr("id").replace(/del_/, '');
				confirm( $(this).attr('title'), function () {
					$('#frm-'+delete_id+'-item').animate({opacity: 'hide', height: 'hide', marginBottom: '0px'}, 'slow', function () {
						$(this).remove();
					});
				});
				return false;
			});
			
			
			// saves the serialized data to the server 
			var save = function(){
				if(opts.save_url){
					if($('#title').val() != ''){
						status();
						
						var post_data = 'id='+$('#id').val();
						post_data += '&title='+$('#title').val();
						post_data += '&email='+$('#email').val();
						post_data += '&email_to_user='+$('#email_to_user').attr('checked');
						post_data += '&email_to_user_text='+$('#email_to_user_text').val();
						post_data += '&email_form_to_user='+$('#email_form_to_user').attr('checked');
						post_data += '&return_page='+$('#return_page').val();
						post_data += $(ul_obj).serializeFormList();
						
						$.ajax({
							type: "POST",
							url: opts.save_url,
							data: post_data,
							success: function(xml){
								setTimeout(function() {
									$.modal.close(); 
								}, 1000);
							}
						});
					} else {
						alert('You must enter a form title before saving.');
					}
				}
			}
			

		});
	};
})(jQuery);


(function($){
	$.fn.serializeFormList = function(options) {
		/**
		 * Modified from the serialize list plugin
		 */
		// Extend the configuration options with user-provided
		var defaults = {
			prepend: 'ul',
			is_child: false,
			attributes: ['class']
		};
		var opts = $.extend(defaults, options);
		var serialStr 	= '';
		
		if(!opts.is_child){ opts.prepend = '&'+opts.prepend; }
		
		// Begin the core plugin
		this.each(function() {
			var ul_obj = this;

			var li_count 	= 0;
			$(this).children().each(function(){
				
				for(att in opts.attributes){
					serialStr += opts.prepend+'['+li_count+']['+opts.attributes[att]+']='+escape($(this).attr(opts.attributes[att]));
					
					// append the form field values
					if(opts.attributes[att] == 'class'){
						
						serialStr += opts.prepend+'['+li_count+'][required]='+escape($('#'+$(this).attr('id')+' input.required').attr('checked'));
					
						switch($(this).attr(opts.attributes[att])){
							case 'input_text':
								serialStr += opts.prepend+'['+li_count+'][values]='+escape($('#'+$(this).attr('id')+' input[type=text]').val());
								break;
							case 'textarea':
								serialStr += opts.prepend+'['+li_count+'][values]='+escape($('#'+$(this).attr('id')+' input[type=text]').val());
								break;
							case 'checkbox':
								var c = 1;
								$('#'+$(this).attr('id')+' input[type=text]').each(function(){
									
									if($(this).attr('name') == 'title'){
										serialStr += opts.prepend+'['+li_count+'][title]='+escape($(this).val());
									} else {
										serialStr += opts.prepend+'['+li_count+'][values]['+c+'][value]='+escape($(this).val());
										serialStr += opts.prepend+'['+li_count+'][values]['+c+'][default]='+$(this).prev().attr('checked');
									}
									c++;
								});
								break;
							case 'radio':
								var c = 1;
								$('#'+$(this).attr('id')+' input[type=text]').each(function(){
									if($(this).attr('name') == 'title'){
										serialStr += opts.prepend+'['+li_count+'][title]='+escape($(this).val());
									} else {
										serialStr += opts.prepend+'['+li_count+'][values]['+c+'][value]='+escape($(this).val());
										serialStr += opts.prepend+'['+li_count+'][values]['+c+'][default]='+$(this).prev().attr('checked');
									}
									c++;
								});
								break;
							case 'select':
								var c = 1;
								
								serialStr += opts.prepend+'['+li_count+'][multiple]='+$('#'+$(this).attr('id')+' input[name=multiple]').attr('checked');
								
								$('#'+$(this).attr('id')+' input[type=text]').each(function(){
									
									if($(this).attr('name') == 'title'){
										serialStr += opts.prepend+'['+li_count+'][title]='+escape($(this).val());
									} else {
										serialStr += opts.prepend+'['+li_count+'][values]['+c+'][value]='+escape($(this).val());
										serialStr += opts.prepend+'['+li_count+'][values]['+c+'][default]='+$(this).prev().attr('checked');
									}
									c++;
								});
								break;
						}
						
					}
				}
				li_count++;
			});
		});
		return(serialStr);
	};
})(jQuery);