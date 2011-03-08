<?php
/**
 * @abstract Events Admin class - Allows an admin user to manage an events list
 * @package Aspen Framework
 * @author Michael Botsko, Botsko.net LLC
 * @uses Admin
 */
class Forms {

	/**
	 * @var object Holds our original application
	 * @access private
	 */
	private $APP;

	/**
	 *
	 * @var <type> 
	 */
	private $validation_errors = false;

	/**
	 * @abstract Constructor, initializes the module
	 * @return Install_Admin
	 * @access public
	 */
	public function __construct(){
		$this->APP = get_instance();
		director()->registerCmsSection(__CLASS__, 'form_display');
		$this->processSubmission();
	}
	
	
	/**
	 * Enter description here...
	 *
	 */
	private function processSubmission(){
	
		$results = false;
		$error = '';

		if($this->APP->params->post->getInt('form_id')){
			
			if($form_db = $model->quickSelectSingle('forms', $this->APP->params->post->getInt('form_id'))){
		
				if(sha1($form_db['structure']) == $form_db['hash']){
					
					$results 	= array();
					$form 		= unserialize($form_db['structure']);

					// Put together an array of all expected indices
					if(is_array($form)){
						foreach($form as $field){
							
							$field['required'] = $field['required'] == 'true' ? true : false;

							if($field['class'] == 'input_text' || $field['class'] == 'textarea'){
								
								$val = $this->APP->params->post->getRaw( $this->elemId($field['values']));
								
								if($field['required'] && empty($val)){
									$error .= '<li>Please complete the ' . $field['values'] . ' field.</li>' . "\n";
								} else {
									$results[ $this->elemId($field['values']) ] = $val;
								}
								
							}
							elseif($field['class'] == 'radio' || $field['class'] == 'select'){

								$val = $this->APP->params->post->getRaw( $this->elemId($field['title']));
								
								if($field['required'] && empty($val)){
									$error .= '<li>Please complete the ' . $field['title'] . ' field.</li>' . "\n";
								} else {
									$results[ $this->elemId($field['title']) ] = $val;
								}
								
							}
							elseif($field['class'] == 'checkbox'){
								if(is_array($field['values'])){
									
									$at_least_one_checked = false;
									
									foreach($field['values'] as $item){

										$elem_id = $this->elemId($item['value'], $field['title']);
										
										$val = $this->APP->params->post->getRaw($elem_id);
		
										if(!empty($val)){
											$at_least_one_checked = true;
										}
										
										$results[ $this->elemId($item['value']) ] = $this->APP->params->post->getRaw($elem_id);
									}
									
									if(!$at_least_one_checked && $field['required']){
										$error .= '<li>Please check at least one ' . $field['title'] . ' choice.</li>' . "\n";
									}
								}
							} else { }
						}
					}
					
					// if results is array, send email
					if(!$error && is_array($results) && count($results)){
						$this->emailFormResults_staff($results, $form_db);
						
						if($form_db['email_to_user']){
							$this->emailFormResults_user($results, $form_db);
						}
						
						$return = $this->APP->cms_lib->url($form_db['return_page']);
						if($return){
							header("Location: " . $return);
							exit;
						}
					} else {
						
						$this->validation_errors = $error;
						
					}
				}
			}
		}
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $results
	 * @param unknown_type $form
	 */
	private function emailFormResults_staff($results, $form){
		
		// SEND THE EMAIL TO THE WEBSITE STAFF
		$this->APP->mail->AddAddress($form['email']);
		$this->APP->mail->From      	= $this->APP->config('email_sender');
		$this->APP->mail->FromName  	= $this->APP->config('email_sender_name');
		$this->APP->mail->Mailer    	= "mail";
		$this->APP->mail->ContentType 	= 'text/html';
		$this->APP->mail->Subject   	= $form['title'] . " - Form Submission";
		
		$body = '<table>';
		$body .= '<thead><tr><th colspan="2" style="text-align: left;">Online Form - ' . $form['title'] . '</th></tr></thead>';
		foreach($results as $key => $value){
			$body .= sprintf('<tr><th style="text-align: left;">%s:</th><td>%s</td></tr>', ucwords( str_replace('_', ' ', $key) ), $value);
		}
		$body .= '</table>';
		
		$this->APP->mail->Body 			= 'Hello,<br /><br />Below are the results from the latest form submission on your web site.<br /><br />' . $body;
						
		if(!$this->APP->mail->Send()){
		}
		$this->APP->mail->ClearAddresses();
		
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $results
	 * @param unknown_type $form
	 */
	private function emailFormResults_user($results, $form){
		
		if($results['email']){
		
			// SEND THE EMAIL TO THE USER
			$this->APP->mail->AddAddress($results['email']);
			$this->APP->mail->From      	= $this->APP->config('email_sender');
			$this->APP->mail->FromName  	= $this->APP->config('email_sender_name');
			$this->APP->mail->Mailer    	= "mail";
			$this->APP->mail->ContentType 	= 'text/html';
			$this->APP->mail->Subject   	= "Thank You for Your " . $form['title'] . " Submission";
			
			$body = '';
			
			if($form['email_form_to_user']){
				$body = '<table>';
				$body .= '<thead><tr><th colspan="2" style="text-align: left;">' . $form['title'] . '</th></tr></thead>';
				foreach($results as $key => $value){
					$body .= sprintf('<tr><th style="text-align: left;">%s:</th><td>%s</td></tr>', ucwords( str_replace('_', ' ', $key) ), $value);
				}
				$body .= '</table>';
			}
			
			if(empty($form['email_to_user_text'])){
				$this->APP->mail->Body = 'Hello,<br /><br />Thank you for your form submission.<br /><br />' . $body;
			} else {
				$this->APP->mail->Body = $form['email_to_user_text'].'<br /><br />' . $body;
			}
							
			if(!$this->APP->mail->Send()){
			}
			
			$this->APP->mail->ClearAddresses();
			
		}
	}
	
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $section_data
	 * @return unknown
	 */
	public function readSection($section_data){
		
		$data = array();
		
		// pull the section for the database
		$section_results = $model->query(sprintf('SELECT * FROM section_form_display WHERE id = "%s"', $section_data['section_id']));
		
		if($section_results->RecordCount()){
			while($section_content = $section_results->FetchRow()){
				
				$section_content['type'] = $section_data['section_type'];
				$section_content['placement_group'] = $section_data['group_name'];

				$section_content['form'] = $model->quickSelectSingle('forms', $section_content['form_id']);
				$data['section'] = $section_content;
			
				if(!$section_data['called_in_template']){
					$data['content'] = $section_content;
				}
			}
		}
		
		return $data;

	}
	
	
	/**
	 * @abstract Displays the default content for this module
	 * @param array $section
	 * @access public
	 */
	public function displaySection($section){
		
		if(!empty($section['title']) && $section['show_title']){
			print "\n" . '<h3>' . htmlentities($section['title'], ENT_QUOTES, 'UTF-8') . '</h3>' . "\n";
		}
		
		if(is_array($section['form'])){
			if(sha1($section['form']['structure']) == $section['form']['hash']){
				
				if($this->validation_errors){
					print '<div class="frm-warning">' . "\n";
					print '<ol>' . "\n";
					print $this->validation_errors;
					print '</ol>' . "\n";
					print '</div>' . "\n";
				}
				
				print '<form class="frm-bldr" method="post" action="'.$this->APP->cms_lib->url().'">' . "\n";
				printf('<input type="hidden" name="form_id" id="form_%s" value="%1$s" />'."\n", $section['form']['id']);
				printf('<ol id="%s">'."\n", router()->encodeForRewriteUrl(strtolower($section['form']['title'])));
				
				$form = unserialize($section['form']['structure']);
				if(is_array($form)){
					foreach($form as $field){
						
						print $this->loadField($field);
						
					}
				}
				
				printf('<li class="btn-submit"><input type="submit" name="submit" value="%s" /></li>' . "\n", 'Submit');
				print '</ol>' . "\n";
				print '</form>' . "\n";
				
			}
		}
	}
	
	
	/**
	 * @abstract Loads a new field based on its type
	 * @param array $field
	 * @return string
	 * @access private
	 */
	private function loadField($field){

		if(is_array($field) && isset($field['class'])){

			switch($field['class']){
				
				case 'input_text':
					return $this->loadInputText($field);
					break;
				case 'textarea':
					return $this->loadTextarea($field);
					break;
				case 'checkbox':
					return $this->loadCheckboxGroup($field);
					break;
				case 'radio':
					return $this->loadRadioGroup($field);
					break;
				case 'select':
					return $this->loadSelectBox($field);
					break;
			}
		}
		
		return false;
	
	}
	

	/**
	 *
	 * @param <type> $key
	 * @return <type> 
	 */
	private function getPostValue($key){
		return $this->APP->params->post->getRaw($key);
	}
	
	
	/**
	 * @abstract Returns html for a textarea
	 * @param array $field Field values from database
	 * @return string
	 * @access private
	 */
	private function loadTextarea($field){
		
		$field['required'] = $field['required'] == 'true' ? ' required' : false;
		
		$html = '';
		$html .= sprintf('<li class="%s%s" id="fld-%s">' . "\n", $this->elemId($field['class']), $field['required'], $this->elemId($field['values']));
		$html .= sprintf('<label for="%s">%s</label>' . "\n", $this->elemId($field['values']), $field['values']);
		$html .= sprintf('<textarea id="%s" name="%s" rows="5" cols="50">%s</textarea>' . "\n",
								$this->elemId($field['values']),
								$this->elemId($field['values']),
								$this->getPostValue($this->elemId($field['values'])));
		$html .= '</li>' . "\n";
		
		return $html;
		
	}
	
	
	/**
	 * @abstract Returns html for an input type="text"
	 * @param array $field Field values from database
	 * @return string
	 * @access private
	 */
	private function loadInputText($field){
	
		$field['required'] = $field['required'] == 'true' ? ' required' : false;
		
		$html = '';
		$html .= sprintf('<li class="%s%s" id="fld-%s">' . "\n", $this->elemId($field['class']), $field['required'], $this->elemId($field['values']));
		$html .= sprintf('<label for="%s">%s</label>' . "\n", $this->elemId($field['values']), $field['values']);
		$html .= sprintf('<input type="text" id="%s" name="%s" value="%s" />' . "\n",
								$this->elemId($field['values']),
								$this->elemId($field['values']),
								$this->getPostValue($this->elemId($field['values'])));
		$html .= '</li>' . "\n";
		
		return $html;
		
	}
	
	
	/**
	 * @abstract Returns html for an input type="text"
	 * @param array $field Field values from database
	 * @return string
	 * @access private
	 */
	private function loadCheckboxGroup($field){
	
		$field['required'] = $field['required'] == 'true' ? ' required' : false;
		
		$html = '';
		$html .= sprintf('<li class="%s%s" id="fld-%s">' . "\n", $this->elemId($field['class']), $field['required'], $this->elemId($field['title']));
		
		if(isset($field['title']) && !empty($field['title'])){
			$html .= sprintf('<span class="false_label">%s</span>' . "\n", $field['title']);
		}
		
		if(isset($field['values']) && is_array($field['values'])){
			$html .= sprintf('<span class="multi-row clearfix">') . "\n";
			foreach($field['values'] as $item){
				
				// set the default checked value
				$checked = $item['default'] == 'true' ? true : false;
				
				// load post value
				$val = $this->getPostValue($this->elemId($item['value']));
				$checked = !empty($val);
				
				// if checked, set html
				$checked = $checked ? ' checked="checked"' : '';
				
				$checkbox 	= '<span class="row clearfix"><input type="checkbox" id="%s-%s" name="%s-%s" value="%s"%s /><label for="%s-%s">%s</label></span>' . "\n";
				$html .= sprintf($checkbox, $this->elemId($field['title']), $this->elemId($item['value']), $this->elemId($field['title']), $this->elemId($item['value']), $item['value'], $checked, $this->elemId($field['title']), $this->elemId($item['value']), $item['value']);
			}
			$html .= sprintf('</span>') . "\n";
		}
		
		$html .= '</li>' . "\n";

		return $html;
		
	}
	
	
	/**
	 * @abstract Returns html for an input type="text"
	 * @param array $field Field values from database
	 * @return string
	 * @access private
	 */
	private function loadRadioGroup($field){
	
		$field['required'] = $field['required'] == 'true' ? ' required' : false;
		
		$html = '';
		
		$html .= sprintf('<li class="%s%s" id="fld-%s">' . "\n", $this->elemId($field['class']), $field['required'], $this->elemId($field['title']));
		
		if(isset($field['title']) && !empty($field['title'])){
			$html .= sprintf('<span class="false_label">%s</span>' . "\n", $field['title']);
		}
		
		if(isset($field['values']) && is_array($field['values'])){
			$html .= sprintf('<span class="multi-row">') . "\n";
			foreach($field['values'] as $item){
				
				// set the default checked value
				$checked = $item['default'] == 'true' ? true : false;
				
				// load post value
				$val = $this->getPostValue($this->elemId($field['title']));
				$checked = !empty($val);
				
				// if checked, set html
				$checked = $checked ? ' checked="checked"' : '';
				
				$radio 		= '<span class="row clearfix"><input type="radio" id="%s-%s" name="%1$s" value="%s"%s /><label for="%1$s-%2$s">%3$s</label></span>' . "\n";
				$html .= sprintf($radio, 
										$this->elemId($field['title']),
										$this->elemId($item['value']),
										$item['value'],
										$checked);
			}
			$html .= sprintf('</span>') . "\n";
		}
		
		$html .= '</li>' . "\n";
		
		return $html;
		
	}
	
	
	/**
	 * @abstract Returns html for an input type="text"
	 * @param array $field Field values from database
	 * @return string
	 * @access private
	 */
	private function loadSelectBox($field){
	
		$field['required'] = $field['required'] == 'true' ? ' required' : false;
		
		$html = '';
		
		$html .= sprintf('<li class="%s%s" id="fld-%s">' . "\n", $this->elemId($field['class']), $field['required'], $this->elemId($field['title']));
		
		if(isset($field['title']) && !empty($field['title'])){
			$html .= sprintf('<label for="%s">%s</label>' . "\n", $this->elemId($field['title']), $field['title']);
		}
		
		if(isset($field['values']) && is_array($field['values'])){
			$multiple = $field['multiple'] == "true" ? ' multiple="multiple"' : '';
			$html .= sprintf('<select name="%s" id="%s"%s>' . "\n", $this->elemId($field['title']), $this->elemId($field['title']), $multiple);
			
			foreach($field['values'] as $item){
				
				// set the default checked value
				$checked = $item['default'] == 'true' ? true : false;
				
				// load post value
				$val = $this->getPostValue($this->elemId($field['title']));
				$checked = !empty($val);
				
				// if checked, set html
				$checked = $checked ? ' checked="checked"' : '';
				
				$option 	= '<option value="%s"%s>%s</option>' . "\n";
				$html .= sprintf($option, $item['value'], $checked, $item['value']);
			}
			
			$html .= '</select>' . "\n";
			$html .= '</li>' . "\n";
			
		}

		return $html;
		
	}
	
	
	/**
	 * @abstract Generates an html-safe element id using it's label
	 * @param string $label
	 * @return string
	 * @access private
	 */
	private function elemId($label, $prepend = false){
		if(is_string($label)){
			$prepend = is_string($prepend) ? $this->elemId($prepend).'-' : false;
			return $prepend.strtolower( preg_replace("/[^A-Za-z0-9_]/", "", str_replace(" ", "_", $label) ) );
		}
		return false;
	}
}
?>