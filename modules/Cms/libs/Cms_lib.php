<?php

class Cms_lib {

	/**
	 * @var boolean Toggle the display of sublist on parent at
	 * @access public
	 */
	public $nav_show_children_on_at = false;
		
	/**
	 * @var boolean Nest navigation lists
	 * @access public
	 */
	public $nav_nest_children = true;
	
	/**
	 * @var boolean Nest navigation limit
	 * @access public
	 */
	public $nav_max_nesting_level = 25;
	
	/**
	 * @var integer Holds the id of the parent if "at"
	 * @access private
	 */
	private $at_parent_id;
	
	/**
	 * @var array Holds an array of non-page name values from the url
	 * @access private
	 */
	private $bits;
	
	/**
	 * @var array Holds an array of content for display
	 * @access private
	 */
	private $content;
	
	/**
	 * @var boolean Whether or not a display error was encountered
	 */
	private $error = false;
	
	/**
	 * @var array Holds the original page data if it's being masked
	 * @access private
	 */
	private $original_page = false;
	
	/**
	 * @var array Holds an array of info we pulled about the current page
	 * @access private
	 */
	private $page = false;
	
	/**
	 * @var array
	 * @access private
	 */
	private $pages;
	
	/**
	 * @var array
	 * @access private
	 */
	private $pages_private;
	
	/**
	 * @var boolean
	 * @access private
	 */
	private $private_page_found = false;
	
	/**
	 * @var array Holds an array of placement groups on this page
	 * @access private
	 */
	private $placements_this_page = array();
	
	/**
	 * @var array Holds an array of sections
	 * @access private
	 */
	private $sections;

	
	/**
	 * @abstract Constructor, initializes the module
	 * @access public
	 */
	public function aspen_init(){
	
		if(app()->isInstalled()){
		
			// obtain a list of all live pages
			$this->knowAllLivePages();
			
			// obtain a list of all private pages
			$this->knowAllPrivatePages();
			
		}
	}
	
	
	/**
	 * Enter description here...
	 *
	 */
	private function reset(){
		
		$this->content = false;
		$this->bits = false;
		$this->sections = false;
		
	}
	
	
	/**
	 * @abstract Loads the page/cms information to prepare for display
	 * @access public
	 */
	public function load(){
		
		// set the theme URL
		$this->setThemeUrl();
		
		// identify which page visitor is looking for
		$this->identifyPage();

		// identify which theme and template we're using
		$this->loadTemplateFiles();
	}
	
	
	/**
	 * @abstract Retrieves an array of all live pages
	 * @access private
	 */
	private function knowAllLivePages(){
		$model = model()->open('pages');
		$model->where('page_is_live', 1);
		$model->orderBy('page_sort_order');
		$this->pages = $model->results();		
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function pages(){
		return $this->pages;
	}
	
	
	/**
	 * @abstract Retrieves an array of all private pages
	 * @access private
	 */
	private function knowAllPrivatePages(){
		$model = model()->open('pages');
		$model->where('page_is_live', 0);
		$model->orderBy('page_sort_order');
		$pages = $model->results();
		$this->pages_private = $pages;
	}
	
	
	/**
	 * @abstract Identifies the page we need to load using parents, children, and bits
	 * @access private
	 */
	private function identifyPage(){
		
		// if rewrites enabled, pull them from the url
		$uri = explode('/', stripslashes(get()->getRaw('redirected')));

		if(isset($uri[0]) && !empty($uri[0])){
			$final_page = $this->findPageFromHeirarchy($uri);
		} else {
			$final_page = array();
			$final_page['page'] = model()->open('pages')->quickSelectSingle(settings()->getConfig('home_page'), 'page_id');
			$final_page['bits'] = array();
		}
		
		// identify any children set as the default
		if(isset($final_page['page']['page_id'])) {
			$model = model()->open('pages');
			$model->where('parent_id', $final_page['page']['page_id']);
			$model->where('is_parent_default', 1);
			$pages = $model->results();
			
			if($pages){
				
				$this->original_page = $final_page['page'];
				
				foreach($pages as $page){
					$final_page['page'] = $page;
				}
			}
		}
		
		$this->page = $final_page['page'];
		$this->bits = $final_page['bits'];

		// set parent page
		if(isset($this->page['parent_id']) && $this->page['parent_id']){
			$this->at_parent_id = $this->page['parent_id'];
		}
		
		// if page doesn't exist or is invalid or private, error out
		$this->verifyPage();
	}
	
	
	/**
	 * @abstract Displays a 404 error if page not found
	 * @access private
	 */
	private function verifyPage(){
		
		$access = false;

		// if the page is private and user is logged in admin
		if($this->private_page_found && IS_ADMIN){
			$this->page = isset($this->pages_private[$this->private_page_found]) ? $this->pages_private[$this->private_page_found] : false;
			$this->page['page_window_title'] = (!empty($this->page['page_window_title']) ? $this->page['page_window_title'] : $this->page['page_title']) . ' - PREVIEW ONLY';
			$access = true;
		}
		
		// if the page is private and user is logged in admin
		if($this->page['login_required']){
			if(app()->user->isLoggedIn()){
				$access = true;
			} else {
				$_SESSION['cms_post_login_redirect'] = app()->params->server->getRaw('REQUEST_URI');
				$login = $this->url(app()->config('login_page_id'));
				header("Location: " . (empty($login) ? 'index.php' : $login) );
				exit;
			}
		}
	
		
		// determine if page is valid, and live
		if(is_array($this->page)){
			if(isset($this->page['page_is_live']) && $this->page['page_is_live']){
				$access = true;
			} else {
				if(IS_ADMIN){
					$access = true;
				}
			}
		}
			
		if(!$access){
			$this->error_404();
		}
	}
	
	
	/**
	 * @abstract Sends the visitor to a 404 error
	 * @access public
	 */
	public function error_404($redirect_only = false){
	
		$this->error = '404';
		
		// if there is a specific config page
		if(app()->config('404_page_id')){
			$error_404 = $this->url(app()->config('404_page_id'));
			if(!empty($error_404)){
				router()->redirectToUrl($error_404);
			}
		}
		
		// begin output of 404
		header("HTTP/1.0 404 Not Found");
		$this->reset();
		
		// attempt to load a 404 template from the theme
		$path = $this->getThemePath() . DS . '404.php';
		if(file_exists($path)){
			$this->reset();
			$this->page = array();
			$this->page['page_template'] = '404.php';
			$this->page['page_title'] = '404';
			$this->display_file = $path;
		} else {
			print 'The page you have requested was not found.';
			exit;
		}
	}
	
	
	/**
	 * @abstract Returns the current error status
	 * @return mixed
	 * @access public
	 */
	public function error(){
		return $this->error;
	}
	
	
	/**
	 * @abstract Finds the correct page from a hierarchy
	 * @param string $uri
	 * @param integer $parent_must_be
	 * @return array
	 */
	private function findPageFromHeirarchy($uri, $parent_must_be = 0){
		
		$request = array();
		$request['page'] = false;
		$request['bits'] = array();
		
		// loop the uri and identify each potential page, only if a child
		foreach($uri as $potent_page){

			// search pages array for this page name
			$found = false;
			foreach($this->pages as $page){
				if(strtolower($page['page_title']) == strtolower(router()->decodeForRewriteUrl($potent_page)) && $page['parent_id'] == $parent_must_be){
					$parent_must_be 	= $page['page_id'];
					$request['page'] 	= $page;
					$found 				= true;
				}
			}
			
			if(is_array($this->pages_private)){
				foreach($this->pages_private as $page){
					if(strtolower($page['page_title']) == strtolower(router()->decodeForRewriteUrl($potent_page)) && $page['parent_id'] == $parent_must_be){
						$this->private_page_found = $page['page_id'];
						$parent_must_be 	= $page['page_id'];
						$request['page'] 	= $page;
						$found 				= true;
					}
				}
			}
			
			// append bits that weren't found (only if they're integers, for details pages)
			if(!$found){
				$request['bits'][] = router()->decodeForRewriteUrl($potent_page);
			}
		}

		if(count($request['bits']) && !in_array($request['page']['page_id'], app()->config('pages_allowing_url_extensions'))){
			$this->error_404();
		}
		
		return $request;
		
	}


	/**
	 * @abstract Returns the current page array
	 * @return array
	 */
	public function getPage($key = false){
		if($key && isset($this->page[$key])){
			return $this->page[$key];
		}
		return $this->page;
	}

	
	/**
	 * @abstract Returns a specific URI element in the uri array /PageName/Val/Val = 1/2/3
	 * @param integer $key
	 * @return string
	 */
	public function getUriBit($key = 1){
		return isset($this->bits[($key - 1)]) ? $this->bits[($key - 1)] : false;
	}
	

	/**
	 * @abstract Returns an array of parent page IDs for the current page
	 * @param integer $id
	 * @return array
	 */
	public function myParents($id = false){
		
		$id = $id ? $id : (isset($this->page['parent_id']) ? $this->page['parent_id'] : false);
		
		$parents = array();
		if($id){
			if(isset($this->pages[$id])){
				$parents[] = $this->pages[$id]['page_id'];
				if($this->pages[$id]['parent_id']){
					$parents = array_merge($parents, $this->myParents($this->pages[$id]['parent_id']));
				}
			}
		}
		return $parents;
	}
	
	
	/**
	 * @abstract Returns base theme path
	 * @return string
	 * @access private
	 */
	public function getThemePath(){
		
		// determine the current website theme
		$template_name = settings()->getConfig('active_theme');
		
		// set the theme path
		$path = app()->config('site_theme_path') ? app()->config('site_theme_path') : APPLICATION_PATH . '/themes';
		$path .= '/' . $template_name;
		
		return $path;
		
	}
	
	
	/**
	 * @abstract Sets the theme url
	 * @access private
	 */
	private function setThemeUrl(){
		$template_name = settings()->getConfig('active_theme');
		$this->theme_url = (app()->config('site_theme_url') ? app()->config('site_theme_url') : router()->appUrl() . '/themes') . '/' . $template_name;
	}
	
	
	/**
	 * @abstract Loads in a new template file
	 * @access private
	 */
	private function loadTemplateFiles(){
		
		// get theme path
		$path = $this->getThemePath();

		// determine the specific page to load
		$file = isset($this->page['page_template']) ? $this->page['page_template'] : 'index.php';
		$file = empty($file) ? 'index.php' : $file;
		
		// if query string asks us to look for a replacement template inside a module
		$inmodule = get()->getAlnum('inmodule');
		
		if($inmodule){
			$replacement = $path . DS . 'modules' . DS . $inmodule . DS . $file;
			if(file_exists($replacement)){
				$this->display_file = $replacement;
			}
		}
		
		if(!isset($this->display_file) || !$this->display_file){
			$this->display_file = $path . '/' . $file;
		}
		
		// load content
		$this->content();
		
	}
	
	
	/**
	 * @abstract Returns the display file path
	 * @return string
	 * @access public
	 */
	public function getDisplayFile(){
		return $this->display_file;
	}
	
	
	/**
	 * @abstract Generates a url for a particular page
	 * @param string $page_id ID of page to generate link to
	 * @param boolean $force_no_rewrite Force no rewrite
	 * @param array $params Any additional parameters to pass
	 * @return string
	 * @access public
	 */
	public function url($page_id = false, $force_no_rewrite = false, $params = false){
	
		$page_id = $page_id ? $page_id : $this->page['page_id'];
		
		// determine of we should make this a cleanurl
		$enable_mod_rewrite = $force_no_rewrite ? false : app()->config('enable_mod_rewrite');

		$url_base = router()->appUrl();
		$url_base .= $enable_mod_rewrite ? '/' : '/index.php?redirected=';
		$url = $url_base . implode('/', array_reverse($this->getFullPagePath($page_id)));
		
		if(is_array($params)){
			foreach($params as $param){
				$url .= '/' . router()->encodeForRewriteUrl($param);
			}
		}
		
		return $url;
	}
	
	
	/**
	 * @abstract Generates a url for the current page with all params
	 * @param boolean $force_no_rewrite Force no rewrite
	 * @return string
	 * @access public
	 */
	public function selfUrl($force_no_rewrite = false){

		// determine of we should make this a cleanurl
		$enable_mod_rewrite = $force_no_rewrite ? false : app()->config('enable_mod_rewrite');

		$url_base = router()->appUrl();
		$url_base .= $enable_mod_rewrite ? '/' : '/index.php?redirected=';
		$url = $url_base . implode('/', array_reverse($this->getFullPagePath($this->page['page_id'])));
		
		if(is_array($this->bits)){
			foreach($this->bits as $param){
				$url .= '/' . router()->encodeForRewriteUrl($param);
			}
		}
		
		return $url;
	}
	
	
	/**
	 * @abstract Returns the complete path to a page, parents included
	 * @param integer $page_id
	 * @return array
	 */
	private function getFullPagePath($page_id){
		
		$parents = array();
		
		if(isset($this->pages[$page_id])){
			$parents = array(router()->encodeForRewriteUrl($this->pages[$page_id]['page_title']));
			if(isset($this->pages[$page_id]['parent_id'])){
				$parents = array_merge($parents, $this->getFullPagePath($this->pages[$page_id]['parent_id']));
			}
		}
		elseif(isset($this->pages_private[$page_id])){
			$parents = array(router()->encodeForRewriteUrl($this->pages_private[$page_id]['page_title']));
			if(isset($this->pages_private[$page_id]['parent_id'])){
				$parents = array_merge($parents, $this->getFullPagePath($this->pages_private[$page_id]['parent_id']));
			}
			elseif(isset($this->pages[$page_id]['parent_id'])){
				$parents = array_merge($parents, $this->getFullPagePath($this->pages[$page_id]['parent_id']));
			}
		}
		
		return $parents;
		
	}
	
	
	/**
	 * @abstract Determines if we're at a specific page
	 * @param string $page_title
	 * @param integer $page_id
	 * @return mixed
	 * @access public
	 */
	public function at($page_id, $allow_at_class_bubbling = true){
		
		$at = false;

		// if at current page
		if(isset($this->page['page_id'])){
			$at = $page_id == $this->page['page_id'] ? 'at' : false;
		}
		
		// if at any children
		if($allow_at_class_bubbling){
			$at = $at ? $at : $this->at_children($page_id);
		}
		
		// if this page is a mask, set 'at' if at original
		if(!$at && isset($this->original_page) && is_array($this->original_page)){
			if(
				$this->original_page['page_id'] == $this->pages[$page_id]['parent_id'] &&
				$this->page['page_id'] == $page_id
			){
				$at = $this->at($this->original_page['page_id']);
			}
		}
		
		return $at;
		
	}
	
	
	/**
	 * @abstract Returns boolean if at children of current page
	 * @param integer $id
	 * @return boolean
	 * @access public
	 */
	public function at_children($id = 0){
		
		$at = false;
	
		foreach($this->pages as $child_page){
			if($child_page['parent_id'] == $id){
				
				$at = $at ? $at : $this->at($child_page['page_id']);
				
				if(!$at){
					$at = $this->at_children($child_page['page_id']);
				}
			}
		}
		
		return $at;
		
	}
	
	
	/**
	 * @abstract Generates navigation in an UL format
	 * @param integer $parent_id
	 * @param string $ul_id
	 * @return string
	 * @access public
	 */
	public function navigation(
										$parent_id = 0, 
										$ul_id = 'menu', 
										$show_parent_link = true, 
										$show_parent_link_nest = false, 
										$show_ul = true,
										$nest_count = 1,
										$li_id_prepend = 'nav'){
		
		$html = '';
		
		if(is_array($this->pages)){
		
			$html = $show_ul ? sprintf('<ul%s>'."\n", ($ul_id ? ' id="'.$ul_id.'"' : '')) : '';
			
			// add parent page
			if(app()->config('include_parent_in_subnav') && $show_parent_link && $parent_id != 0 && isset($this->pages[$parent_id])){
				$html .= $this->navigation_li( $this->pages[$parent_id], $show_parent_link_nest, true, $nest_count, $li_id_prepend);
			}
			
			if(!$show_parent_link_nest){
				foreach($this->pages as $page){
					if($page['parent_id'] == $parent_id){

						// create a link
						$html .= $this->navigation_li($page, true, true, $nest_count, $li_id_prepend);
					
					}
				}
				
			}
			
			
			$html .= $show_ul ? "</ul>\n" : '';
			
		}
		
		if(strpos($html, 'li') === false){
			$html = '';
		}
		
		return $html;
		
	}
	
	
	/**
	 * @abstract Generates a single list item link for navigation
	 * @param array $page
	 * @return string
	 * @access public
	 */
	private function navigation_li($page, $show_children = true, $allow_at_class_bubbling = true, $nest_count = 1, $li_id_prepend = 'nav'){

		$html = '';
		
		// at this page?
		$at = $this->at($page['page_id'], $allow_at_class_bubbling);

		//print $page['page_id'] . ' = ' . $at . "<br>";
					
		// find any children
		$children = '';

		$nest_allowed = true;
		if($nest_count >= $this->nav_max_nesting_level){
			$nest_allowed = false;
		}

		if($this->nav_nest_children && $show_children && $nest_allowed){
			if($this->nav_show_children_on_at){
				$children = $at ? $this->navigation($page['page_id'], false, false, false, true, ($nest_count+1)) : false;
			} else {
				$children = $this->navigation($page['page_id'], false, false, false, true, ($nest_count+1));
			}
		}
		
		// determine classes
		$class  = empty($at) ? '' : $at;
		$class .= empty($children) ? '' : ' has_child';
		$class  = empty($class) ? '' : ' class="' . $class . '"';
		
		$link_id = false;
		if(!$page['parent_id']){
			$link_id = empty($page['page_body_id']) ? '' : ' id="'.$li_id_prepend.'-' . strtolower(router()->encodeForRewriteUrl($page['page_body_id'])) . '"';
		}
		$title = empty($page['page_link_hover']) ? '' : ' title="' . app()->html->purify($page['page_link_hover']) . '"';
		$text = empty($page['page_link_text']) ? $page['page_title'] : $page['page_link_text'];
		
		// create list item link
		if($page['show_in_menu']){
			$html .= $li = sprintf('<li%s%s><a href="%s"%s>%s</a>',
									$link_id,
									$class,
									$this->url($page['page_id']),
									$title,
									app()->html->purify($text));
			$html .= $children;
			$html .= '</li>' . "\n";
		}
		
		return $html;
	}
	
	
	/**
	 * @abstract Generates navigation for current parent, if at
	 * @param string $ul_id
	 * @return string
	 * @access public
	 */
	public function sub_navigation($ul_id = false, $show_parent_link = true, $li_id_prepend = 'sub'){
	
		if(!$this->at_parent_id){
			$this->at_parent_id = isset($this->page['page_id']) ? $this->page['page_id'] : false;
		}
		
		return $this->navigation($this->at_parent_id, $ul_id, $show_parent_link, $show_parent_link, true, 1, $li_id_prepend);
		
	}
	
	
	/**
	 * @abstract Loads all content for the current page
	 * @access public
	 */
	public function content($page = false){
		
		if(!$page){
			$page = isset($this->page['page_id']) ? $this->page['page_id'] : false;
		}
		
		$this->sections = array();
		$this->content = array();
		
		if($page){
				
			// pull all references to sections for this page
			$model = model()->open('section_list');
			$model->joins('template_placement_groups');
			$model->where('page_id', $page);
			$model->orderBy('sort_order');
			$sections = $model->results();

			if($sections){
				foreach($sections as $section){
					$section_data = director()->readPageSections($section);
					if(is_array($section_data) && isset($section_data['section'])){
						$this->sections[]  	= $section_data['section'];
						$this->content[]  	= isset($section_data['content']) ? $section_data['content'] : '';
					}
				}
			}
		}
	}
	
	
	/**
     * @abstract Shows an individual content section, outside of it's group
     * @param string $section_title Title of section to display
     * @access public
     */
	public function show_section($section_title = false){
		
		if($section_title && is_array($this->sections)){
			foreach($this->sections as $section){
				if($section['title'] == $section_title){
					
					model()->open('section_list')->query(sprintf('
						UPDATE section_list SET called_in_template = 1
						WHERE section_type = "%s" AND section_id = "%s"',
						$section['type'], $section['id']));
					
					print '<h2>' . $section['title'] . '</h2>' . "\n";
					print $section['content'] . "\n";
					
				}
			}
		}
	}
	
	
	/**
     * @abstract Returns an individual content section, outside of it's group
     * @param string $section_title Title of section to display
     * @return array
     * @access public
     */
	public function get_section($section_title = false){
		
		if($section_title && is_array($this->sections)){
			foreach($this->sections as $section){
				if($section['title'] == $section_title){
					
					model()->open('section_list')->query(sprintf('
						UPDATE section_list SET called_in_template = 1
						WHERE section_type = "%s" AND section_id = "%s"',
						$section['type'], $section['id']));
					
					return $section;
				}
			}
		}
		return false;
	}
	
	
	/**
     * @abstract Returns first content section for current section list
     * @return array
     * @access public
     */
	public function show_first_section(){
		
		if(is_array($this->sections) && isset($this->sections[0])){
				
			model()->open('section_list')->query(sprintf('
				UPDATE section_list SET called_in_template = 1
				WHERE section_type = "%s" AND section_id = "%s"',
				$this->sections[0]['type'], $this->sections[0]['id']));
				
			return $this->sections[0];
		}
		return false;
	}
	
	
	/**
     * @abstract Loads all content for a page or specific page group
     * @param string $placement_group
     * @return array
     * @access public
     */
	public function getContent($placement_group = false){

		$content = array();

		if(is_array($this->content) && count($this->content)){
			foreach($this->content as $section){
				if($placement_group){
					if(strtolower($placement_group) == strtolower($section['placement_group'])){
						$content[] = $section;
					}
				} else {
					if(!in_array(strtolower($section['placement_group']), $this->placements_this_page)){
						$content[] = $section;
					}
				}
			}
		}
			
		// save this section as one we've called already
		if($placement_group){
			$this->placements_this_page[] = strtolower($placement_group);
		}
	
		if(count($content) == 0){
			$content = false;
			// add the placement to the database because it doesn't exist
			if(!empty($placement_group) && isset($this->page['page_template'])){
				// if nothing exists already
				$model = model()->open('template_placement_groups');
				$model->where('template', $this->page['page_template']);
				$model->where('group_name', $placement_group);
				$exists = $model->results();
				
				if(!$exists){
					$model->query(sprintf('INSERT INTO template_placement_groups (template, group_name) VALUES ("%s", "%s")',
						strtolower($this->page['page_template']), $placement_group));
				}
			}
		}

		return $content;

	}
	
	
	/**
	 * @abstract Returns current page array
	 * @return array
	 * @access public
	 */
	public function getPageData(){
		return $this->page;
	}
	
	
	/**
     * @abstract Returns page title with website title appended if enabled
     * @return string
     * @access public
     */
	public function page_title($append_website_title = true){
		
		$append = $append_website_title ? $this->website_title() : '';

		if(isset($this->page['page_window_title']) && !empty($this->page['page_window_title'])){
			$title = $this->page['page_window_title'];
		} else {
			$title = (isset($this->page['page_title']) ? $this->page['page_title'] : false);
		}
		
		/* return $append . ($append_website_title && !empty($title) ? ' - ' : '') . app()->html->purify($title); */
		return app()->html->purify($title) . ($append_website_title && !empty($title) ? ' - ' : '') . $append;
	}
	
	
	/**
     * @abstract Returns css id attribute text (lowercased, spaces->underscores)
     * @return string
     * @access public
     */
	public function parent_classes($page_id = false){
		
		$page = strtolower(router()->encodeForRewriteUrl($this->page['page_title']));

		$parents = $this->parent_classes_recursive($page_id);
		if(is_array($parents) && count($parents)){
			$page = implode(' ', array_reverse($parents)) . ' ' . $page;
		}
		
		return $page;
		
	}
	
	
	/**
     * @abstract Returns css id attribute text (lowercased, spaces->underscores)
     * @return string
     * @access public
     */
	public function parent_classes_recursive($page_id = false){
		
		$page_id = $page_id ? $page_id : (isset($this->page['parent_id']) ? $this->page['parent_id'] : false);
		
		$classes = array();
		if($page_id && isset($this->pages[$page_id])) {
			$parent = $this->pages[$page_id]['page_title'];
			$classes[] = strtolower(router()->encodeForRewriteUrl($parent));
			if($this->pages[$page_id]['parent_id']){
				$classes = array_merge($classes, $this->parent_classes_recursive($this->pages[$page_id]['parent_id']));
			}
		}
		return $classes;
	}
	
	
	/**
     * @abstract Returns css id attribute text (lowercased, spaces->underscores)
     * @return string
     * @access public
     */
	public function page_id(){
		
		// if body if is set
		if(isset($this->page['page_body_id']) && !empty($this->page['page_body_id'])) {
			return strtolower(router()->encodeForRewriteUrl($this->page['page_body_id']));
		} else {
			
			// look for any parents with body_id set
			$parents = $this->myParents();
			
			foreach($parents as $parent){
				$parent = $this->pages[$parent];
				if(isset($parent['page_body_id']) && !empty($parent['page_body_id'])) {
					return strtolower(router()->encodeForRewriteUrl($parent['page_body_id']));
				}
			}
		}
		
		// nothing was found, so use the page title
		if(isset($this->page['page_title'])) {
			return strtolower(router()->encodeForRewriteUrl($this->page['page_title']));
		}
		
		return false;

	}
	
	
	/**
     * @abstract Returns page title for H1 elements
     * @return string
     * @access public
     */
	public function page_header(){
		if(isset($this->page['page_title'])) {
			return app()->html->purify($this->page['page_title']);
		}
		return 'Untitled';
	}
	
	
	/**
     * @abstract Returns absolute theme url
     * @return string
     * @access public
     */
	public function getThemeUrl(){
		return $this->theme_url;
	}
	
	
	/**
     * @abstract Returns meta keywords for current page
     * @return string
     * @access public
     */
	public function page_meta_keywords(){
		if(isset($this->page['meta_keywords']) && !empty($this->page['meta_keywords'])){
			return $this->page['meta_keywords'];
		} else {
			if(isset($this->page['parent_id']) && $this->page['parent_id'] && isset($this->pages[$this->page['parent_id']]['meta_keywords']) && !empty($this->pages[$this->page['parent_id']]['meta_keywords'])){
				return $this->pages[$this->page['parent_id']]['meta_keywords'];
			} else {
				return settings()->getConfig('meta_keywords');
			}
		}
	}
	
	
	/**
     * @abstract Returns meta description
     * @return string
     * @access public
     */
	public function page_meta_description(){
		if(isset($this->page['meta_description']) && !empty($this->page['meta_description'])){
			return $this->page['meta_description'];
		} else {
			if(isset($this->page['parent_id']) && $this->page['parent_id'] && isset($this->pages[$this->page['parent_id']]['meta_description']) && !empty($this->pages[$this->page['parent_id']]['meta_description'])){
				return $this->pages[$this->page['parent_id']]['meta_description'];
			} else {
				return settings()->getConfig('meta_description');
			}
		}
	}
	
	
	/**
     * @abstract Returns base website title
     * @return string
     * @access public
     */
	public function website_title(){
		return settings()->getConfig('website_title');
	}
	
	
	/**
     * @abstract Prints all content for the current page.
     * @return string
     * @access public
     */
	public function display_content($section = false){
		$content = $this->getContent($section);
		if($content){
			foreach($content as $section){
				director()->displayPageSections($section, $this->page, $this->bits);
			}
			return true;
		} else {
			return false;
		}
	}
}
?>