<?php namespace comodojo;

use \comodojo\debug;
use \comodojo\Exception\DispatcherException;
use \comodojo\Exception\IOException;
use \comodojo\ObjectRequest\ObjectRequest;
use \comodojo\ObjectRoutingTable\ObjectRoutingTable;
use \comodojo\ObjectRoute\ObjectRoute;
use \comodojo\ObjectResult\ObjectSuccess;
use \comodojo\ObjectResult\ObjectError;
use \comodojo\ObjectResult\ObjectRedirect;

class dispatcher {

	private $enabled = DISPATCHER_ENABLED;

	private $current_time = NULL;

	private $working_mode = 'STANDARD';

	private $service_uri = NULL;

	private $service_url = NULL;

	private $request_method = NULL;

	private $routingtable = NULL;

	private $request = NULL;

	private $serviceroute = NULL;

	private $cacher = NULL;

	private $header = NULL;

	private $events = NULL;

	private $result_comes_from_cache = false;

	public final function __construct() {

		ob_start();

		// Now start to build dispatcher instance

		$this->current_time = time();

		$this->working_mode = $this->get_working_mode();

		$this->service_uri = $this->url_uri();

		$this->service_url = $this->url_url();

		$this->request_method = $_SERVER['REQUEST_METHOD'];

		$this->routingtable = new ObjectRoutingTable();

		debug('-----------------------------------------------------------','INFO','dispatcher');
		debug(' *** Starting dispatcher ***','INFO','dispatcher');
		debug('-----------------------------------------------------------','INFO','dispatcher');
		debug(' * Current time: '.$this->current_time,'INFO','dispatcher');
		debug(' * Working mode: '.$this->working_mode,'INFO','dispatcher');
		debug(' * Request URI: '.$this->service_uri,'INFO','dispatcher');
		debug('-----------------------------------------------------------','INFO','dispatcher');
		debug(' *** Loading modules...','INFO','dispatcher');
		
		$this->cacher = new cache($this->current_time);

		$this->header = new header($this->current_time);

		$this->events = new events();

		debug('-----------------------------------------------------------','INFO','dispatcher');

		// Starts composing request object (ObjectRequest)

		list($request_service,$request_attributes) = $this->url_interpreter($this->working_mode);

		list($request_parameters, $request_raw_parameters) = $this->deserialize_parameters($this->request_method);

		$request_headers = $this->header->get_request_headers();

		$this->request = new ObjectRequest();

		$this->request
			->setCurrentTime($this->current_time)
			->setService($request_service)
			->setMethod($this->request_method)
			->setAttributes($request_attributes)
			->setParameters($request_parameters)
			->setRawParameters($request_raw_parameters)
			->setHeaders($request_headers);

		debug(' * Requested service: '.$request_service,'INFO','dispatcher');
		debug(' * Request HTTP method: '.$this->request_method,'INFO','dispatcher');
		debug('-----------------------------------------------------------','INFO','dispatcher');

	}

	public final function dispatch() {

		// Before building dispatcher instance, fire THE level1 event "dispatcher"
		// This is the only way (out of dispatcher-config) to disable dispatcher

		$fork = $this->events->fire("dispatcher", "DISPATCHER", $this->enabled);

		if ( is_bool($fork)  ) $this->enabled = $fork;

		// After building dispatcher instance, fire THE level2 event "dispatcher.request"
		// This default hook will expose current request (ObjectRequest) to callbacks

		$fork = $this->events->fire("dispatcher.request", "REQUEST", $this->request);

		if ( $fork instanceof \comodojo\ObjectRequest\ObjectRequest ) $this->request = $fork;

		// Fire level3 event "dispatcher.request.[method]"
		
		$fork = $this->events->fire("dispatcher.request.".$this->request_method, "REQUEST", $this->request);

		if ( $fork instanceof \comodojo\ObjectRequest\ObjectRequest ) $this->request = $fork;

		// Fire level3 event "dispatcher.request.[service]"
		
		$fork = $this->events->fire("dispatcher.request.".$this->request->getService(), "REQUEST", $this->request);

		if ( $fork instanceof \comodojo\ObjectRequest\ObjectRequest ) $this->request = $fork;

		// Check if dispatcher is enabled

		if ( $this->enabled === false ) {

			$route = new ObjectError();
			$route->setStatusCode(503);

			$return = $this->route($route);

			ob_end_clean();

			exit($return);

		}

		// Before calculating service route, expose the routing table via level2 event "dispatcher.routingtable"

		$fork = $this->events->fire("dispatcher.routingtable", "TABLE", $this->routingtable);

		if ( $fork instanceof \comodojo\ObjectRoutingTable\ObjectRoutingTable ) $this->routingtable = $fork;

		// Retrieve current route from routing table

		$service = $this->request->getService();

		$preroute = $this->routingtable->getRoute($service);

		$this->serviceroute = new ObjectRoute();
		$this->serviceroute->setService($service)
			->setType($preroute["type"])
			->setTarget($preroute["target"]);

		if ( isset($preroute["parameters"]["class"]) ) {
			$this->serviceroute->setClass($preroute["parameters"]["class"]);
		} else {
			$t = pathinfo($preroute["target"]);
			$this->serviceroute->setClass(preg_replace('/\\.[^.\\s]{3,4}$/', '', $t["filename"]));
		}
		
		if ( isset($preroute["parameters"]["redirectCode"]) ) {
			$this->serviceroute->setRedirectCode($preroute["parameters"]["redirectCode"]);
			unset($preroute["parameters"]["redirectCode"]);
		}
		if ( isset($preroute["parameters"]["errorCode"]) ) {
			$this->serviceroute->setErrorCode($preroute["parameters"]["errorCode"]);
			unset($preroute["parameters"]["errorCode"]);
		}
		if ( isset($preroute["parameters"]["cache"]) ) {
			$this->serviceroute->setCache($preroute["parameters"]["cache"]);
			unset($preroute["parameters"]["cache"]);
		}
		if ( isset($preroute["parameters"]["ttl"]) ) {
			$this->serviceroute->setTtl($preroute["parameters"]["ttl"]);
			unset($preroute["parameters"]["ttl"]);
		}
		if ( isset($preroute["parameters"]["headers"]) ) {
			$this->serviceroute->setHeaders($preroute["parameters"]["headers"]);
			unset($preroute["parameters"]["headers"]);
		}
		if ( isset($preroute["parameters"]["accessControl"]) ) {
			$this->serviceroute->setRedirectCode($preroute["parameters"]["accessControl"]);
			unset($preroute["parameters"]["accessControl"]);
		}

		foreach ($preroute["parameters"] as $parameter => $value) {
			$this->serviceroute->setParameter($parameter, $value);
		}

		// Now that we have a route, fire the level2 event "dispatcher.serviceroute"
		// and level3 events:
		// - "dispatcher.serviceroute.[type]"
		// - "dispatcher.serviceroute.[service]"

		$fork = $this->events->fire("dispatcher.serviceroute", "ROUTE", $this->serviceroute);

		if ( $fork instanceof \comodojo\ObjectRoute\ObjectRoute ) $this->serviceroute = $fork;

		$fork = $this->events->fire("dispatcher.serviceroute.".$this->serviceroute->getType(), "ROUTE", $this->serviceroute);

		if ( $fork instanceof \comodojo\ObjectRoute\ObjectRoute ) $this->serviceroute = $fork;

		$fork = $this->events->fire("dispatcher.serviceroute.".$this->serviceroute->getService(), "ROUTE", $this->serviceroute);

		if ( $fork instanceof \comodojo\ObjectRoute\ObjectRoute ) $this->serviceroute = $fork;

		// Before using route to handle request, check if access control should block instance

		$accesscontrol = preg_replace('/\s+/', '', $this->serviceroute->getAccessControl());

		if ( $accesscontrol != NULL AND $accesscontrol != "*" ) {

			$origins = explode(",", $accesscontrol);

			if ( !in_array(@$_SERVER['HTTP_ORIGIN'], $origins) ) {

				$route = new ObjectError();
				$route->setStatusCode(403)->setContent("Origin not allowed");

				$return = $this->route($route);

				ob_end_clean();

				exit($return);

			}

		}

		switch($this->serviceroute->getType()) {

			case "ERROR":

				$route = new ObjectError();
				$route->setService($this->serviceroute->getService())
					  ->setStatusCode($this->serviceroute->getErrorCode())
					  ->setContent($this->serviceroute->getTarget())
					  ->setHeaders($this->serviceroute->getHeaders());

				break;

			case "REDIRECT":

				$route = new ObjectRedirect();
				$route->setService($this->serviceroute->getService())
					  ->setStatusCode($this->serviceroute->getRedirectCode())
					  ->setLocation($this->serviceroute->getTarget())
					  ->setHeaders($this->serviceroute->getHeaders());

				break;

			case "ROUTE":

				try {
					
					$route = $this->run_service($this->request, $this->serviceroute);

				} catch (DispatcherException $de) {
				
					$route = new ObjectError();
					$route->setService($this->serviceroute->getService())
						  ->setStatusCode($de->getCode())
						  ->setContent($de->getMessage());

				} catch (Exception $e) {

					$route = new ObjectError();
					$route->setService($this->serviceroute->getService())
						  ->setStatusCode(500)
						  ->setContent($e->getMessage());

				}

				break;

		}

		$return = $this->route($route);

		ob_end_clean();

		exit($return);

	}

	public final function setRoute($service, $type, $target, $parameters=Array(), $relative=true) {

		if ( strtoupper($type) == "ROUTE" ) {

			if ( $relative ) $this->routingtable->setRoute($service, $type, DISPATCHER_SERVICES_FOLDER.$target, $parameters);

			else $this->routingtable->setRoute($service, $type, $target, $parameters);

		}

		else $this->routingtable->setRoute($service, $type, $target, $parameters);

	}

	public final function unsetRoute($service) {

		$this->routingtable->unsetRoute($service);

	}

	public final function addHook($event, $callback, $method=NULL) {

		$this->events->add($event, $callback, $method);

	}

	public final function removeHook($event, $callback=NULL) {

		$this->events->remove($event, $callback);

	}

	public final function loadPlugin($plugin, $folder=DISPATCHER_PLUGINS_FOLDER) {

		include $folder.$plugin.".php";

	}

	public final function provideTemplate($template, $target, $relative=true) {

	}

	public final function getCurrentTime() {

		return $this->current_time;

	}

	/**
	 * Url interpreter
	 *
	 * Starting from $workingMode (REWRITE|STANDARD) will acquire service route from request.
	 *
	 * In other words, will separate service and attributes and populate class parameters
	 * service_requested and service_attributes
	 *
	 * @param 	string 	$workingMode 	(REWRITE|STANDARD)
	 */
	private function url_interpreter($workingMode) {

		if ($workingMode == "REWRITE") {

			$uri = explode('/', $_SERVER['REQUEST_URI']);
			$scr = explode('/', $_SERVER['SCRIPT_NAME']);

			for($i= 0;$i < sizeof($scr);$i++) {
				if ($uri[$i] == $scr[$i]) unset($uri[$i]);
			}

			$service_matrix = array_values($uri);

			if (isset($service_matrix[0])) {

				$service_requested = $service_matrix[0];
				$service_attributes = array_slice($service_matrix,1);

			}
			else {

				$service_requested = "default";
				$service_attributes = Array();

			}

		}
		else {
			
			$service_matrix = $_GET;

			if (isset($service_matrix["service"])) {

				$service_requested = $service_matrix["service"];
				unset($service_matrix["service"]);
				$service_attributes = $service_matrix;

			}
			else {

				$service_requested = "default";
				$service_attributes = Array();

			}

		}

		return Array($service_requested, $service_attributes);

	}

	/**
	 * Return current request uri
	 *
	 * @return uri 	The request uri
	 */
	private function url_uri() {

		return $_SERVER['REQUEST_URI'];

	}

	private function url_url() {

		return $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

	}

	/**
	 * Route request handling ObjectResult hooks
	 *
	 * @param 	ObjectResult 	$route 	An implementation of ObjectResultInterface
	 * @return 	string 					Content (stuff that will go on screen)
	 */
	private function route(\comodojo\ObjectResult\ObjectResultInterface $route) {

		// Starting from the routing instance, select the relative level2 hook
		// This means event engine will fire a dispatcher.[routetype] event
		// In case of wrong instance, create an ObjectError (500, NULL) instance

		if ( $route instanceof ObjectSuccess ) $hook = "dispatcher.route";
		else if ( $route instanceof ObjectError ) $hook = "dispatcher.error";
		else if ( $route instanceof ObjectRedirect ) $hook = "dispatcher.redirect";
		else {

			$route = new ObjectError();

		}

		// Fire first hook, a generic "dispatcher.result", Object Type independent

		$fork = $this->events->fire("dispatcher.result", "RESULT", $route);

		if ( $fork instanceof \comodojo\ObjectResult\ObjectResultInterface ) $route = $fork;

		// Fire second hook (level2), as specified above

		$fork = $this->events->fire($hook, "RESULT", $route);

		if ( $fork instanceof \comodojo\ObjectResult\ObjectResultInterface ) $route = $fork;

		// Now select and fire last hook (level3)
		// This means that event engine will fire something like "dispatcher.route.200"
		// or "dispatcher.error.500"

		$fork = $this->events->fire($hook.".".$route->getStatusCode(), "RESULT", $route);

		if ( $fork instanceof \comodojo\ObjectResult\ObjectResultInterface ) $route = $fork;		

		// After hooks:
		// - store cache
		// - start composing header
		// - return result

		$cache = $this->serviceroute->getCache();

		if ( $this->request_method == "GET" AND 
			( $cache == "SERVER" OR $cache == "BOTH" ) AND
			$this->result_comes_from_cache == false AND 
			$fork instanceof \comodojo\ObjectResult\ObjectSuccess )
		{

			$this->cacher->set($this->service_url, $route);

		}


		$this->header->free();

		if ( $cache == "CLIENT" OR $cache == "BOTH" ) $this->header->setClientCache($this->serviceroute->getTtl());

		$this->header->setContentType($route->getContentType(), $route->getCharset());

		foreach ($route->getHeaders() as $header => $value) {
			
			$this->header->set($header, $value);

		}

		$message = $route->getContent();

		$this->header->compose($route->getStatusCode(), strlen($message), $route->getLocation()); # ><!/\!°>

		// Return the content (stuff that will go on screen)

		return $message;

	}

	private function get_working_mode() {

		return DISPATCHER_USE_REWRITE ? "REWRITE" : "STANDARD";

	}

	private function deserialize_parameters($method) {

		$parameters = Array();

		switch($method) {

			case 'POST':

				$parameters = $_POST;

				break;

			case 'PUT':
			case 'DELETE':
				
				parse_str(file_get_contents('php://input'), $parameters);
				
				break;

		}

		return Array($parameters, file_get_contents('php://input'));

	}

	private function attributes_match($provided, $expected, $liked) {

		if ( $this->working_mode == "STANDARD" ) return $this->parameters_match($provided, $expected, $liked);

		$attributes = Array();

		$psize = sizeof($provided);
		$esize = sizeof($expected);
		$lsize = sizeof($liked);

		if ( $psize < $esize ) throw new DispatcherException("Conversation error", 400);

		else if ( $psize == $esize ) {

			$attributes = array_combine($expected, $provided);

		}

		else {

			$pvalues = array_slice($provided, 0, $esize);

			$e_attributes = array_combine($expected, $pvalues);

			$lvalues = array_slice($provided, $esize);

			$lvaluessize = sizeof($lvalues);

			$l_attributes = Array();

			if ( $lvaluessize < $lsize ) {

				$l_attributes = array_combine(array_slice($liked, 0, $lvaluessize), $lvalues);

			}
			else if ( $lvaluessize == $lsize ) {

				$l_attributes = array_combine($liked, $lvalues);

			}
			else {

				$r_attributes = array_combine($liked, array_slice($lvalues, 0, $lsize));

				$remaining_parameters = array_slice($lvalues, $lsize);

				$l_attributes = array_merge($r_attributes, $remaining_parameters);

			}

			$attributes = array_merge($e_attributes, $l_attributes);

		}
		
		return $attributes;

	}

	private function parameters_match($provided, $expected, $liked) {

		foreach ($expected as $parameter) {
			
			if ( !isset($provided[$parameter]) ) throw new DispatcherException("Conversation error", 400);

		}

		return $provided;

	}

	private function run_service($request, $route) {

		$method = $request->getMethod();
		$service = $route->getService();
		$cache = $route->getCache();
		$ttl = $route->getTtl();
		$target = $route->getTarget();

		// First of all, check cache (in case of GET request)

		if ( $method == "GET" AND ( $cache == "SERVER" OR $cache == "BOTH" ) ) {

			$from_cache = $this->cacher->get($this->service_url, $ttl);

			if ( is_array($from_cache) ) {

				$maxage = $from_cache["maxage"];
				$bestbefore = $from_cache["bestbefore"];
				$result = $from_cache["object"];

				// Publish that result comes from cache (so will not be re-cached)

				$this->result_comes_from_cache = true;

				// $result = new ObjectSuccess();
				// $result->setService($from_cache["service"])
				// 	->setStatusCode($from_cache["statuscode"])
				// 	->setContent($from_cache["content"])
				// 	->setHeaders($from_cache["headers"])
				// 	->setContentType($from_cache["contenttype"]);

				return $result;

			}

		}

		// If there's no cache for this request, use routing information to find service

		if ( (include($target)) === false ) throw new DispatcherException("Cannot run service", 500);

		// Find a service implementation and try to init it

		$service_class = $route->getClass();

		if ( empty($service_class) ) throw new DispatcherException("Cannot run service", 500);
	
		$service_class = "\comodojo\\".$service_class;

		$theservice = new $service_class();

		// Setup service

		try {
		
			$theservice->setup();

		} catch (Exception $e) {
			
			throw $e;

		}

		// Check if service supports current HTTP method

		if ( !in_array($method, explode(",", $theservice->getSupportedMethods())) ) {

			throw new DispatcherException("Allow: ".$theservice->getSupportedMethods(), 405);

		}

		// Check if service implements current HTTP method

		if ( !in_array($method, $theservice->getImplementedMethods()) ) {

			throw new DispatcherException("Allow: ".implode(",",$theservice->getSupportedMethods()), 501);

		}

		// Match attributes and parameters

		list($expected_attributes, $expected_parameters) = $theservice->getExpected($method);

		list($liked_attributes, $liked_parameters) = $theservice->getLiked($method);

		try {
		
			$validated_attributes = $this->attributes_match($request->getAttributes(), $expected_attributes, $liked_attributes);

			$validated_parameters = $this->parameters_match($request->getParameters(), $expected_parameters, $liked_parameters);

		} catch (DispatcherException $de) {
			
			throw $de;

		}
		
		// Fill service with dispatcher pieces

		$theservice->attributes = $validated_attributes;
		$theservice->parameters = $validated_parameters;
		$theservice->raw_parameters = $request->getRawParameters();

		// Finally run service method and catch exceptions

		try {

			$result = $theservice->$method();

			$return = new ObjectSuccess();
			$return->setService($service)
				->setStatusCode($theservice->getStatusCode())
				->setContent($result)
				->setHeaders($theservice->getHeaders())
				->setContentType($theservice->getContentType())
				->setCharset($theservice->getCharset());
			
		} catch (DispatcherException $de) {

			throw $de;

		} catch (Exception $e) {

			throw $e;

		}

		return $return;

	}

}

?>