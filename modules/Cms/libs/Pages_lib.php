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

		$this->APP->form->loadTable('pages');

		// process the form if submitted
		if($this->APP->form->isSubmitted()){
			
			$this->APP->form->setCurrentValue('page_sort_order', ($model->quickValue('SELECT MAX(page_sort_order) FROM pages', 'MAX(page_sort_order)') + 1));

			// form field validation
			if(!$this->APP->form->isFilled('page_title')){
				$this->APP->form->addError('page_title', 'You must enter a page title.');
			}

			// if we have no errors, save the record
			if(!$this->APP->form->error()){
				
				// set the link text field to the page title if blank
				if(!$this->APP->form->isFilled('page_link_text')){
					$this->APP->form->setCurrentValue('page_link_text', $this->APP->form->cv('page_title'));
				}
				
				return $this->APP->form->save();
				
			}
		}
		
		return false;
		
	}
	
}
?>