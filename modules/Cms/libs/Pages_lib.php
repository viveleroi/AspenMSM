<?php

class Pages_lib {
	
	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;

	
	/**
	 * @abstract Constructor, initializes the module
	 * @access public
	 */
	public function __construct(){ $this->APP = get_instance(); }
	
	
	/**
	 * @abstract Add a new page
	 * @access public
	 */
	public function add(){

		$form = new Form('pages');

		// process the form if submitted
		if($form->isSubmitted()){
			
			$form->setCurrentValue('page_sort_order', ($model->quickValue('SELECT MAX(page_sort_order) FROM pages', 'MAX(page_sort_order)') + 1));

			// form field validation
			if(!$form->isFilled('page_title')){
				$form->addError('page_title', 'You must enter a page title.');
			}

			// if we have no errors, save the record
			if(!$form->error()){
				
				// set the link text field to the page title if blank
				if(!$form->isFilled('page_link_text')){
					$form->setCurrentValue('page_link_text', $form->cv('page_title'));
				}
				
				return $form->save();
				
			}
		}
		
		return false;
		
	}
	
}
?>