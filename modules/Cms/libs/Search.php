<?php

/**
 * Enter description here...
 *
 */
class Search {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;

	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	public $paginator_info;


	/**
	 * @abstract Constructor, initializes the module
	 * @access public
	 */
	public function __construct(){ $this->APP = get_instance(); }


	/**
	 * Enter description here...
	 *
	 */
	public function load_content(){

		// clear existing index
		$model->query('TRUNCATE search_content_index');

		// load pages
		$pages = app()->cms_lib->pages();

		// loop pages
		if(is_array($pages)){
			foreach($pages as $page){

				// get content for the page
				app()->cms_lib->content($page['page_id']);
				$contents = app()->cms_lib->getContent();

				if(is_array($contents) && !empty($contents)){
					foreach($contents as $content){

						// pass to the module for handling it's searchable content output
						$to_index = director()->loadIndexer($content['type'], $content);

						// for the content, store in the search index table
						if(is_array($to_index)){
							foreach($to_index as $content_chunk){
								$model->executeInsert('search_content_index', $content_chunk);
							}
						}
					}
				}
			}
		}

		// reset page content for cms
		app()->cms_lib->content();

	}


	/**
	 * @abstract Performs a keyword search on the content index
	 * @return mixed
	 */
	public function search($add_params = false){

		$type 		= app()->params->get->getAlnum('inmodule');
		$keyword 	= app()->params->get->getRaw('keyword');
		$results 	= false;

		// check if page needs to be rerouted for a special module search
		if($type && array_key_exists($type, app()->config('search_pages'))){
			$pages = app()->config('search_pages');
			$url = app()->cms_lib->url($pages[$type]);
			if($url && app()->cms_lib->getPage('page_id') != $pages[$type]){
				$get = app()->params->getRawSource('get');
				unset($get['redirected']);
				header("Location: " . $url . '?' . http_build_query($get));
				exit;
			}
		}

		// @todo this may need to be moved to a cron job
		app()->search->load_content();

		// call module override if it exists
		if($type){
			$results = director()->moduleSearch($type, $keyword, $add_params);
		}

		// if no results, run normal
		if(!$results && $keyword){
			$model->enablePagination();
			$model = model()->open('search_content_index');
			$model->match($keyword);
			$model->paginate(app()->params->get->getRaw('page'), app()->config('search_results_per_page'));
			$results = $model->results();

			if($results){
				foreach($results as $key => $result){
					$results[$key]['source_url'] = app()->cms_lib->url($result['source_page_id']);
				}
			}
		}

		$this->paginator_info['records'] 	= $results['TOTAL_RECORDS_FOUND'];
		$this->paginator_info['current'] 	= $results['CURRENT_PAGE'];
		$this->paginator_info['per_page'] 	= $results['RESULTS_PER_PAGE'];
		$this->paginator_info['pages'] 		= $results['TOTAL_PAGE_COUNT'];

		return $results ? $results  : false;

	}


	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function pagination(){

		$url = app()->cms_lib->url();
		$url .= '?keyword=' . app()->params->get->getRaw('keyword') . '&amp;';
		$url .= 'inmodule=' . app()->params->get->getRaw('inmodule') . '&amp;';

		// build the html list item
		$html = '';
		$html .= sprintf('<li class="pag-count">Page %s of %s</li>'."\n", $this->paginator_info['current'], $this->paginator_info['pages']);

		if($this->paginator_info['pages'] > 1){

            $link_limit = 10;
            $limit_balance = ceil(($link_limit / 2));

			 $p      = 2; // current page number inside loops
			 $limit  = 1; // numbers of pages from $p to show

			// previous icon
			if($this->paginator_info['current'] > 1){
				$html .= sprintf('<li class="pag-prev"><a href="%spage=%d">Prev</a></li>'."\n", $url, ($this->paginator_info['current']-1));
			}

            // add in the first page
            $selected = $this->paginator_info['current'] == 1 ? ' class="at"' : '';
			$html .= sprintf('<li%s><a href="%spage=%d">%d</a></li>'."\n", $selected, $url, 1, 1);


            // if more than <limit> results, show <limit> page numbers closest to our current page


            if($this->paginator_info['pages'] > $link_limit){

                $p = $this->paginator_info['current'];

                // start loop at <$link_limit> pages prior to current, if possible
                if($p > $limit_balance){
                    $tmp_start = $p - $limit_balance;
                    if($tmp_start > 0){
                        $p = $tmp_start;
                    }

                    if($this->paginator_info['current'] >= ($this->paginator_info['pages'] - $limit_balance)){
                        $p = $this->paginator_info['pages'] - $link_limit;
                    }
                } else {
                    $p = 2;
                }

                $p      = $p == 1 ? 2 : $p;
                $limit  = $p + $link_limit;
            } else {
				$limit = $this->paginator_info['pages'];
			}

            // add elipse if > <$link_limit> pages
            if($this->paginator_info['pages'] > $link_limit && $this->paginator_info['current'] > ($limit_balance+2)){
                $html .= '<li class="pag-sep">&#8230;</li>'."\n";
            }

			// add in the numeric links
			while($p < $limit){
				$selected = $this->paginator_info['current'] == $p ? ' class="at"' : '';
				$html .= sprintf('<li%s><a href="%spage=%d">%d</a></li>'."\n", $selected, $url, $p, $p);
                $p++;
			}

            // add elipse if > <$link_limit> pages
            if($this->paginator_info['pages'] > $link_limit && $this->paginator_info['current'] < ($this->paginator_info['pages'] - $limit_balance)){
                $html .= '<li class="pag-sep">&#8230;</li>'."\n";
            }

            // add in the last page
            $p = $this->paginator_info['pages'];
            $selected = $this->paginator_info['current'] == $p ? ' class="at"' : '';
			$html .= sprintf('<li%s><a href="%spage=%d">%d</a></li>'."\n", $selected, $url, $p, $p);

			// next icon
			if($this->paginator_info['current'] < $this->paginator_info['pages']){
				$html .= sprintf('<li class="pag-next"><a href="%spage=%d">Next</a></li>'."\n", $url, ($this->paginator_info['current']+1));
			}

		}

		return $html;

	}
}
?>