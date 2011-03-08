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

		app()->form->loadTable('pages');

		// process the form if submitted
		if(app()->form->isSubmitted()){
			
			app()->form->setCurrentValue('page_sort_order', ($model->quickValue('SELECT MAX(page_sort_order) FROM pages', 'MAX(page_sort_order)') + 1));

			// form field validation
			if(!app()->form->isFilled('page_title')){
				app()->form->addError('page_title', 'You must enter a page title.');
			}

			// if we have no errors, save the record
			if(!app()->form->error()){
				
				// set the link text field to the page title if blank
				if(!app()->form->isFilled('page_link_text')){
					app()->form->setCurrentValue('page_link_text', app()->form->cv('page_title'));
				}
				
				return app()->form->save();
				
			}
		}
		
		return false;
		
	}
	
}
?>