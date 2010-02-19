<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract Manages urls and relation to our application
 * @package Aspen_Framework
 */
class Router {

	/**
	 * @var string $_selected_module Lists the currently selected module
	 * @access private
	 */
	private $_selected_module = false;

	/**
	 * @var string $_selected_method Lists the currently selected method
	 * @access private
	 */
	private $_selected_method = false;

	/**
	 * @var array $_selected_arguments An array of arguments to pass to a function
	 * @access private
	 */
	private $_selected_arguments = array();
	
	/**
	 * @var array $map Holds our array of mapped URL routes
	 * @access private
	 */
	private $map;

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;


	/**
	 * @abstract Constructor
	 * @return Router
	 * @access private
	 */
	public function __construct(){
		
		// get instance of our application
		$this->APP = get_instance();

		// force use of ssl if required
		if($this->APP->config('force_https')){
			header("Location: " . str_replace(array("https", "http"), "https", $this->getFullUrl()));
			exit;
		}

		// map the url elements and then identify the module/method to load
		$this->mapRequest();
		$this->identifyModuleForLoad();
		$this->identifyMethodForLoad();
		
	}
	
	
	/**
	 * @abstract Maps URI elements to an internal array - either GET or clean urls
	 * @access private
	 */
	private function mapRequest(){
		
		$bits = array();
		
		// if mod_rewrite enabled, and request doesn't look like a non-rewrite request
		if($this->APP->config('enable_mod_rewrite') && strpos($this->APP->params->server->getRaw('REQUEST_URI'), '.php?') === false){

			// we force the entire url as replacement because
			// an interface app name may be the same as a module
			// and we don't want to remove both, just one
			$replace = array($this->getApplicationUrl() .'/'.LS);
			if($this->getPath() != '/'){
				$replace[] = $this->getPath();
			}
			$uri = str_replace($replace, '', $this->getDomainUrl() . $this->APP->params->server->getRaw('REQUEST_URI'));
			$uri = explode('/', $uri);
		
			$this->map['module'] = isset($uri[1]) ? $uri[1] : false;
			$this->map['method'] = isset($uri[2]) ? $uri[2] : false;
			
			// loop additional bits to pass to our arguments
			for($i = 3; $i < count($uri); $i++){
				$bits[] = $uri[$i];
			}
		} else {
			
			$this->map['module'] = $this->APP->params->get->getRaw('module');
			$this->map['method'] = $this->APP->params->get->getRaw('method');
			
			// loop additional bits to pass to our arguments
			$get = $this->APP->params->getRawSource('get');

			if(is_array($get)){
				foreach($get as $key => $value){
					if($key != 'module' && $key != 'method'){
						$bits[$key] = $this->stripQuery($this->APP->params->get->getRaw($key));
					}
				}
			}
		}
		
		// we need to remove any left over query string from malformed mod_rewrite query
		$this->map['method'] = $this->stripQuery($this->map['method']);
		
		// append any function arguments
		$this->map['bits'] = $bits;
		
		// appen interface to module
		if(!empty($this->map['module'])){
			$this->map['module'] .= (LOADING_SECTION != '' ? '_'.LOADING_SECTION : '');
		}
	}


	/**
	 * Removes any remaining query string from manual URI processing.
	 * @param string $str
	 * @return string
	 */
	private function stripQuery($str){
		return preg_replace('/\?(.*)/', '', $str);
	}
	
	
	/**
	 * @abstract Identifies which module needs to be loaded
	 * @return string
	 * @access public
	 */
	public function identifyModuleForLoad(){

		if(strtolower(get_class($this->APP)) == "app"){

			if($this->APP->isInstalled() && $this->map['method'] != 'success' && $this->map['method'] != 'account'){
		
				// if user must be logged in
				if($this->APP->requireLogin()){

					// do a quick check to see if the user is logged in or not
					// we need to create our own auth check, as the user module is not loaded at this point
					if($this->APP->params->session->getAlnum('domain_key') == sha1($this->getApplicationUrl())){
	
						$default = $this->map['module'] ? $this->map['module'] : $this->APP->config('default_module');
	
					} else {
	
						$default = 'Users' . (LOADING_SECTION ? '_' . LOADING_SECTION : false);
	
					}
				} else {
					
					$default = $this->map['module'] ? $this->map['module'] : $this->APP->config('default_module');
					
				}
			} else {

				$default = $this->APP->config('default_module_no_config');

			}
		} else {

			$default = get_class($this->APP);

		}

		$this->_selected_module['NAME'] = ucfirst($default);

	}
	
	
	/**
	 * @abstract Identifies which method needs to be loaded
	 * @param string $default
	 * @return string
	 * @access private
	 */
	private function identifyMethodForLoad(){
		
		// check if a login is required
		if($this->APP->requireLogin()){

			// do a basic login check as user module is not loaded at this point
			if($this->APP->params->session->getAlnum('domain_key') == sha1($this->getApplicationUrl())){
	
				$default = $this->map['method'];
				$default = $default ? $default : $this->APP->config('default_method');
	
			}
			elseif($this->map['method'] == 'authenticate' || $this->map['method'] == 'forgot'){
	
				$default = $this->map['method'];
	
			} else {
	
				if($this->APP->isInstalled() && $this->map['method'] != 'success' && $this->map['method'] != 'account'){
					$default = 'login';
				} else {
					if($this->getSelectedModule() == "Install_Admin"){
						$default = $this->map['method'] ? $this->map['method'] : 'view';
					}
				}
			}
		} else {
			
			$default = $this->map['method'];
			$default = $default ? $default : $this->APP->config('default_method');
			
		}

		$this->_selected_method = $default;

	}
	
	
	/**
	 * @abstract Returns the currently selected module
	 * @return string
	 * @access public
	 */
	public function getSelectedModule(){
		return $this->_selected_module['NAME'];
	}

	
	/**
	 * @abstract Returns the currently selected method
	 * @return string
	 * @access public
	 */
	public function getSelectedMethod(){
		return $this->_selected_method;
	}
	
	
	/**
	 * @abstract Returns all current method arguments
	 * @return string
	 * @access private
	 */
	public function getSelectedArguments(){
		return $this->_arg;
	}

	
	/**
	 * @abstract Determines the parent of the current module, mainly for navigation purposes.
	 * @return string
	 * @access public
	 */
	public function getParentModule(){

		$module = $this->getSelectedModule();

		if(method_exists($this->APP->{$this->getSelectedModule()}, 'whosYourDaddy')){
			$daddy = $this->APP->{$this->getSelectedModule()}->whosYourDaddy();
			$module = empty($daddy) ? $module : $daddy;
		}

		return $module;
	}


	/**
	 * @abstract Loads the lanuage file for the current module
	 * @access private
	 */
	private function loadModuleLanguage($module = false, $interface = false){
	
		$lang 			= array();
		$lang_setting 	= $this->APP->config('language');
		
		// load the interface language library
		$lang_path		= INTERFACE_PATH . DS . 'language' . DS . $lang_setting . '.php';
		define('LANG_PATH', $lang_path);
		
		$this->APP->log->write('Seeking interface language library ' . LANG_PATH);
		if(file_exists(LANG_PATH)){
			include(LANG_PATH);
			$this->APP->log->write('Including interface language library ' . LANG_PATH);
		}

		// load the module-specific language library
		$module_lang_path = $this->getModulePath($module, $interface).DS. 'language' . DS . $lang_setting.'.php';
		define('MODULE_LANG_PATH', $module_lang_path);
		
		$this->APP->log->write('Seeking module language library ' . MODULE_LANG_PATH);
		if(file_exists(MODULE_LANG_PATH)){
			include(MODULE_LANG_PATH);
			$this->APP->log->write('Including module language library ' . MODULE_LANG_PATH);
		}
		
		$this->APP->template->loadLanguageTerms($lang);
		
	}


	/**
	 * @abstract Calls the module/method with arguments from the url
	 * @access public
	 */
	public function loadFromUrl(){
		
		// redirect if upgrade pending
		if($this->APP->user->isLoggedIn() && $this->getSelectedModule() != 'Install_Admin'){
			if($this->APP->awaitingUpgrade()){ $this->redirect('upgrade', false, 'Install'); }
		}

		if($this->getSelectedMethod() && $this->APP->user->userHasAccess()){
			
			// load the module language file
			if($this->APP->config('enable_languages')){
				$this->loadModuleLanguage();
			}
	
			// set the function arguments for the method
			$i = 1;
			foreach($this->map['bits'] as $bit){
				$this->_selected_arguments[$i] = $bit;
				$i++;
			}
			
			$this->APP->log->write('Looking for Module: ' . $this->getSelectedModule() . '->' . $this->getSelectedMethod());

			/* this sucks balls, but I don't think you can call a function with an array as separate variables, like
			 * like imploding an array into separate function arguments  */
			if(isset($this->APP->{$this->getSelectedModule()})){
				if(method_exists($this->APP->{$this->getSelectedModule()}, $this->getSelectedMethod())){
					
					$this->APP->log->write('Running Module: ' . $this->getSelectedModule() . '->' . $this->getSelectedMethod());
					
					$this->APP->{$this->getSelectedModule()}->{$this->getSelectedMethod()}(
														$this->arg(1),
														$this->arg(2),
														$this->arg(3),
														$this->arg(4),
														$this->arg(5),
														$this->arg(6),
														$this->arg(7),
														$this->arg(8),
														$this->arg(9),
														$this->arg(10),
														$this->arg(11),
														$this->arg(12),
														$this->arg(13),
														$this->arg(14),
														$this->arg(15),
														$this->arg(16),
														$this->arg(17),
														$this->arg(18),
														$this->arg(19),
														$this->arg(20));
														
				} else { // method not found within module
					if($this->APP->config('log_error_on_404')){
						$this->APP->error->raise(2, 'Method ' . $this->getSelectedMethod() . ' does not exist in '
													 . $this->getSelectedModule(), __FILE__, __LINE__);
					}
					
					header("HTTP/1.0 404 Not Found");
					$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
					$this->APP->template->addView($this->APP->template->getTemplateDir().DS . '404.tpl.php');
					$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
					$this->APP->template->display();
					exit;
				}
			} else { // no module found
				if($this->APP->config('log_error_on_404')){
					$this->APP->error->raise(2, 'Module ' . $this->getSelectedModule() . ' does not exist.', __FILE__, __LINE__);
				}
				
				header("HTTP/1.0 404 Not Found");
				$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
				$this->APP->template->addView($this->APP->template->getTemplateDir().DS . '404.tpl.php');
				$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
				$this->APP->template->display();
				exit;
			}
		} else { // not authorized
			$this->loadModuleLanguage('Users_Admin', 'Admin');
			$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
			$this->APP->template->addView($this->APP->template->getModuleTemplateDir('Users_Admin', 'Admin').DS . 'denied.tpl.php');
			$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
			$this->APP->template->display();
			exit;
		}
	}


	/**
	 * @abstract Check for an argument index
	 * @param integer $index
	 * @return mixed
	 * @access private
	 */
	public function arg($index){
		return isset($this->_selected_arguments[$index]) ? $this->_selected_arguments[$index] : false;
	}
	
	
	/**
	 * @abstract Return an array of current arguments
	 * @return array
	 * @access public
	 */
	public function getMappedArguments(){
		return $this->map['bits'];
	}


	/**
	 * @abstract Returns a full URL path for the current page, query string and all
	 * @return string
	 * @access public
	 */
	public function getFullUrl(){
		
		$url = $this->getDomainUrl();
		$url .= $this->APP->params->server->getRaw('REQUEST_URI');

		return $url;

	}


	/**
	 * @abstract Returns the absolute url to the aspen installation path.
	 * @access public
	 * @return string
	 */
	public function getApplicationUrl(){

		$url = $this->getDomainUrl();
		$url .= $this->APP->config('application_url') ? $this->APP->config('application_url') : $this->getPath();
		return $url;

	}
	
	
	/**
	 * @abstract Returns the current port number
	 * @return integer
	 * @access public
	 */
	public function getPort(){
		return $this->APP->params->server->getInt('SERVER_PORT');
	}


	/**
	 * @abstract Returns the protocol / domain name only
	 * @access public
	 * @return string
	 */
	public function getDomainUrl(){
	
		$url = $this->getPort() == 443 ? 'https://' : 'http://';
		$url .= $this->APP->params->server->getRaw('SERVER_NAME');
		
		if($this->getPort() != 80 && $this->getPort() != 443){
			$url .= ':'.$this->getPort();
		}

		return $url;

	}
	

    /**
     * @abstract Attempts to return the post-domain name path to our application (minus interface)
     * @return string
     * @access public
     */
    public function getPath(){

        $adjusted_app_path = str_replace('\\', '/', APPLICATION_PATH);
        $adjusted_doc_root = str_replace('\\', '/', $this->APP->params->server->getRaw('DOCUMENT_ROOT'));

        $doc_strlen = strlen($adjusted_doc_root);
        if(substr($adjusted_doc_root, $doc_strlen - 1, $doc_strlen) == '/'){
            $adjusted_doc_root = substr($adjusted_doc_root, 0, $doc_strlen - 1);
        }

        // compare the first characters of both path vars,
        // if they don't match then we can't trust as true path
        $ar = substr($adjusted_app_path, 0, 7);
        $dr = substr($adjusted_doc_root, 0, 7);

        // attempt to determine path replacing doc root with domain
        if($adjusted_doc_root && $ar == $dr){
            $uri = str_replace($adjusted_doc_root, '', $adjusted_app_path);
        } else {
            // if no mod_rewrite, we need to handle the paths appropriately
            if($this->APP->config('enable_mod_rewrite') && strpos($this->APP->params->server->getRaw('REQUEST_URI'), '.php?') === false){

                $redirected = stripslashes($this->APP->params->get->getRaw('redirected'));
                $interface = LS ? LS : '';

                $replace = array();
                if(!empty($interface)){
                    $replace[] = '/'.$interface;
                }
                if(!empty($redirected)){
                    $replace[] = '/'.$redirected;
                }

                $uri = str_replace($replace, '', urldecode($this->APP->params->server->getRaw('REQUEST_URI')));
				$uri = $this->stripQuery($uri);

            } else {

                $no_qs_url = str_replace('?' . $this->APP->params->server->getRaw('QUERY_STRING'), '', $this->APP->params->server->getRaw('REQUEST_URI'));
                $interface = LS ? LS : '';
                $uri = str_replace(array("index.php", '/'.$interface), "", $no_qs_url);

            }
        }

        $uri = $uri == '/' ? false : $uri;
        return $uri;

    }


	/**
	 * @abstract Returns an absolute url to the current interface application.
	 * @param string $interface
	 * @return string
	 * @access public
	 */
	public function getInterfaceUrl($interface = false){
        $interface = $interface ? $interface : LS;
        return $this->getApplicationUrl() . '/' . $interface;
    }

    
	/**
	 * @abstract Returns the url to the file upoads directory
	 * @return string
	 * @access public
	 */
	public function getUploadsUrl(){
		$browser_url = $this->APP->config('upload_browser_path');
		if(!$browser_url){
			$browser_url = str_replace(APPLICATION_PATH, $this->getApplicationUrl(), $this->APP->config('upload_server_path'));
		}
		return $browser_url;
    }

    
	/**
	 * @abstract Returns the static content path if set in config, otherwise just interfaceUrl
	 * @return string
	 * @access public
	 */
	public function getStaticContentUrl(){
		return ($this->APP->config('static_content_path') ? $this->APP->config('static_content_path') :
																					$this->getInterfaceUrl());
	}


    /**
	 * @abstract Returns an absolute URL to the module folder
	 * @param string $module_name
	 * @return string
	 * @access public
	 */
	public function getModuleUrl($module_name = false){
		$module_name = $module_name ? $module_name : $this->getSelectedModule();
		$registry = $this->APP->moduleRegistry(false, $module_name);
		return isset($registry->folder) ? $this->getApplicationUrl() . '/modules/' . $registry->folder : false;
	}


	/**
	 * @abstract Encodes a string for use in the url
	 * @param string $var
	 * @access public
	 * @return string
	 */
	public function encodeForRewriteUrl($var){
		$var = str_replace("?", "-question-", $var);
		$var = str_replace("/", "-slash-", $var);
		$var = str_replace("&", "-and-", $var);
		$var = str_replace(" ", "_", $var);
		$var = urlencode($var);
		return $var;
	}


	/**
	 * @abstract Decodes a string for use in the url
	 * @param string $var
	 * @access public
	 * @return string
	 */
	public function decodeForRewriteUrl($var){
		$var = urldecode($var);
		$var = str_replace("-question-", "?", $var);
		$var = str_replace("-slash-", "/", $var);
		$var = str_replace("-and-", "&", $var);
		$var = str_replace("_", " ", $var);
		return $var;
	}
	
	
	/**
	 * @abstract Returns the server path to our module
	 * @return string
	 * @access public
	 */
	public function getModulePath($module = false, $interface = false){
		
		$module = $module ? $module : $this->getSelectedModule();
		$registry = $this->APP->moduleRegistry(false, $module, $interface);

		if(isset($registry->folder)){
			return MODULES_PATH . DS . $registry->folder;
		}
		return false;
	}


	/**
	 * @abstract Answers whether or not the user is in a specific location
	 * @param string $module
	 * @param string $method
	 * @return boolean
	 * @access public
	 */
	public function here($module = false, $method = false){

		$here = false;

		$selected_module = $this->getSelectedModule();

		if(isset($this->APP->{$this->getSelectedModule()})){
			if(method_exists($this->APP->{$this->getSelectedModule()}, 'whosYourDaddy')){
				$daddy = $this->APP->{$this->getSelectedModule()}->whosYourDaddy();
				$selected_module = empty($daddy) ? $selected_module : $daddy;
			}
		}

		if($module && $selected_module == $module){
			if($method){
				if($this->getSelectedMethod() == $method){
					$here = true;
				}
			} else {

				$here = true;

			}
		}

		return $here;

	}


	/**
	 * @abstract Sets the referring page for future reference
	 * @access public
	 */
	public function setReturnToReferrer(){
		if(strpos($this->getSelectedMethod(), 'ajax') === false){
			if($this->APP->params->server->getRaw('HTTP_REFERER')){
				$_SESSION['referring_page'] = $this->APP->params->server->getRaw('HTTP_REFERER');
			}
		}
	}


	/**
	 * @abstract Returns the user to the referring page when called
	 * @access public
	 */
	public function returnToReferrer(){
		$location = $this->APP->params->session->getRaw('referring_page', $this->APP->template->createUrl('view'));
		if(!empty($location)){
			header('Location: ' . $location);
			exit;
		}
	}
	
	
	/**
	 * @abstract Redirects user to an inner-application module/method address.
	 * @param string $method
	 * @param array $bits
	 * @param string $module
	 * @access public
	 */
	public function redirect($method = false, $bits = false, $module = false){
       
        header("Location: " . $this->APP->template->createUrl($method, $bits, $module));
        exit;
       
    }
}
?>