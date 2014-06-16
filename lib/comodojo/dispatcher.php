<?php namespace comodojo;

class dispatcher {

	private 

	private $current_time = NULL;

	private $service_uri = NULL;

	private $service_requested = NULL;

	private $service_attributes = NULL;

	private $service_parameters = NULL;

	private $request_method = NULL;

	private $cacher = NULL;

	private $header = NULL;

	private $events = NULL;

	public final function __construct() {

		require("../configs/dispatcher-config.php");
		require("../configs/routing-table.php");		

		require("exceptions.php");

		require("database.php");
		require("debug.php");
		require("trace.php");
		require("statistic.php");

		require("cache.php");
		require("header.php");

		require("events.php");

		require("serialization.php");
		require("deserialization.php");

		require("http.php");
		require("random.php");
		
		require("service.php");

		$this->current_time = time();

		$this->request_method = $_SERVER['REQUEST_METHOD'];

		$this->service_uri = $this->url_uri();

		$this->cacher = new cache($this->current_time);

		$this->header = new header($this->current_time);

		$this->events = new events();

	}

	public final function dispatch() {

	}

	public final function set($param, $value) {

	}

	public final function get($param, $value) {

	}

	public final function add($type, $param, $value) {

		$type = strtoupper($type);

		switch ($type) {

			case 'ROUTE':
				


				break;
			
			case 'HOOK':

				$this->events->add($param, $value);

				break;

			default:
				# code...
				break;

		}

	}

	public final function remove($type, $param, $value=NULL) {

		$type = strtoupper($type);

		switch ($type) {

			case 'ROUTE':
				# code...
				break;
			
			case 'HOOK':
				
				$this->events->remove($param, $value);

				break;

			default:
				# code...
				break;

		}

	}

	private function url_interpreter($workingMode) {

		if ($workingMode == "REWRITE") {

			$uri = explode(‘/’, $_SERVER[‘REQUEST_URI’]);
			$scr = explode(‘/’, $_SERVER[‘SCRIPT_NAME’]);

			for($i= 0;$i < sizeof($scriptName);$i++) {
				if ($uri[$i] == $scr[$i]) unset($uri[$i]);
			}

			$service_matrix = array_values($requestURI);

			if (isset($service_matrix[0])) {

				$this->service_requested = $service_matrix[0];
				$this->service_attributes = array_slice($service_matrix,1);

			}

		}
		else {
			
			$service_matrix = $_GET;

			if (isset($service_matrix["service"])) {

				$this->service_requested = $service_matrix["service"];
				unset($service_matrix["service"]);

			}

			$this->service_attributes = $service_matrix;

		}

	}

	private function url_uri() {

		return $_SERVER[‘REQUEST_URI’];

	}

	/**
	 * Dispatch response composing also return header
	 *
	 * It process service specific header and then append also ones defined in routing table (if any)
	 *
	 * @param 	INT		$code				HTTP status code
	 * @param 	INT		$contentLenght		Response content length
	 * @param 	ARRAY	$route_headers		Headers defined in routing table for service
	 * @param 	ARRAY	$service_headers	Service specific headers
	 */
	private function route($service, $code, $content, $route_headers, $service_headers) {

		$content = $this->events->fire("DISPATCHER_PREROUTE", Array(
				"service"	=>	$service,
				"code"		=>	$code,
				"content"	=>	$content
			)
		);

		foreach ($service_headers as $header => $value) {
			
			$this->header->set($header, $value);

		}

		foreach ($route_headers as $header => $value) {
			
			$this->header->set($header, $value);

		}

		$this->header->compose($code, strlen($content));

		$this->events->fire("DISPATCHER_POSTROUTE", Array(
				"service"	=>	$service,
				"code"		=>	$code,
				"content"	=>	$content
			)
		);

		return $content;

	}

	private function redirect($service, $code, $location) {

		$location = $this->events->fire("DISPATCHER_PREREDIRECT", Array(
				"service"	=>	$service,
				"code"		=>	$code,
				"location"	=>	$location
			)
		);

		$this->header->free();

		$this->header->compose($code, 0, $location);

		$this->events->fire("DISPATCHER_POSTREDIRECT", Array(
				"service"	=>	$service,
				"code"		=>	$code,
				"location"	=>	$location
			)
		);

		return NULL;

	}

	private function error($service, $code, $message, $extra=false) {

		$message = $this->events->fire("DISPATCHER_PREERROR", Array(
				"service"	=>	$service,
				"code"		=>	$code,
				"message"	=>	$message
			)
		);

		$this->header->free();

		$this->header->compose($code, strlen($message), $extra);

		$this->events->fire("DISPATCHER_POSTERROR", Array(
				"service"	=>	$service,
				"code"		=>	$code,
				"message"	=>	$message
			)
		);		

		return $message;

	}

	private function service_is_in_routing_table($service) {

		global $routingtable;

		return isset($routingtable[$service]) ? true : false;

	}

	private function service_is_routable($service_file) {

		return is_readable($service_file) ? true : false;

	}

	private function service_is_compliant() {

	}

	private function get_service_route() {

		global $routingtable;

		return $routingtable[$service];

	}

	private function get_service_class($service_file, $service_class) {

		$comodojo_classes = Array(
			"Spyc",
			"XML",
			"cache",
			"database",
			"serialization",
			"deserialization",
			"event",
			"Exception",
			"header",
			"http",
			"trace",
			"service"
		);

		foreach( get_declared_classes() as $class ) {

			if ( in_array($class, $comodojo_classes) ) continue;

			if( $class instanceof service ) return $class;

		}

		throw new Exception("Invalid service class");
		
	}

	private function get_working_mode() {

		return DISPATCHER_USE_REWRITE ? "REWRITE" : "STANDARD";

	}

	private function deserialize_parameters($method, $raw=false) {

		$parameters = Array();

		switch($method) {

			case 'POST':

				$parameters = $raw ? file_get_contents('php://input') : $_POST;

				break;

			case 'PUT':
			case 'DELETE':
				
				if ($raw) $parameters = file_get_contents('php://input');
				else parse_str(file_get_contents('php://input'), $parameters);
				
				break;

		}

		return $parameters;

	}

	private function set_client_cache() {



	}

	private function set_server_cache() {

		return $this->cacher->set($uri, $content);

	}

	private function get_server_cache($uri, $ttl) {

		return $this->cacher->get($uri, $ttl);

	}

}




/**
 * The router.
 *
 * It receive requests and redirect them to services; it don't use Apache
 * .htaccess expressions in order to maintain better compatibility and keep
 * routing table clean.
 */
class router {
    
	/**
	 * Current URL (auto)
	 */
	private $currentUrl = false;

	/**
	 * Current PATH (auto)
	 */
	private $currentPath = false;

	/**
	 * Cache lifetime (default in the pas to disable browser cache)
	 */
	private $bestBefore = 'Mon, 26 Jul 1997 05:00:00 GMT';

	/**
	 * Cache max age
	 */
	private $maxAge = false;

	/**
	 * Content type (default or populated with 'transport' var)
	 */
	private $contentType = false;

	/**
	 * CURL channel (if any - in CLOAKED requests)
	 */
	private $ch = false;

	/**
	 * Original request method (auto)
	 */
	private $originalRequestMethod = false;

	/**
	 * Original request service (auto)
	 */
	private $originalRequestService = false;

	/**
	 * Original request attributes (auto)
	 */
	private $originalRequestAttributes = false;

	/**
	 * CURL request method (same of $originalRequestMethod, but may be different
	 * if 'forceMethod' specified in routing table for a service)
	 */
	private $requestMethod = false;

	/**
	 * Status received from service
	 */
	private $responseStatus = false;

	/**
	 * Header received from service
	 */
	private $responseHeader = Array();

	/**
	 * Custom header to return to client (specified in routing table)
	 */
	private $headersToThrow = Array();

	/**
	 * Redirect status code, used only in routed requests
	 */
	private $redirectStatusCode = 302;
    
    
	/**
	 * Default error patterns, to speedup error response
	 */
	private $error_patterns = Array(
		'JSON'	=>	"{success: false, result: '%s'}",
		'XML'	=>	"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<content><success></success><result>%s</result></content>",
		'YAML'	=>	"success: false\nresult: %s"
		);
    
	/**
	 * Route request with a 302 message back.
	 *
	 * @param	STRING	$location	The location to route request to
	 */
	private function go_route($location) {
	//header("Location: ".$location.(!sizeof($this->originalRequestAttributes) ? '' : '?'.http_build_query($this->originalRequestAttributes)),true,301);
		if (SERIALIZE_PARAMETERS_IN_ROUTING AND sizeof($this->originalRequestAttributes) != 0) {
			header("Location: ".$location.'?'.http_build_query($this->originalRequestAttributes),true,$this->redirectStatusCode);
		}
		else {
			header("Location: ".$location,true,$this->redirectStatusCode);
		}
	}

	/**
	 * Cloak request, with or without cache
	 *
	 * @param	STRING	$location	The location to route request to
	 * @param	STRING	$cache		The cache type
	 * @param	INT	$ttl		The cache time to live
	 */
	private function go_cloak($location, $cache=AUTO_CACHE, $ttl=DEFAULT_TTL) {
		if (!$cache) {
			$result = $this->go_curl($location);
		}
		elseif (strtoupper($cache) == "BOTH") {
			$result = $this->get_cache($location,$ttl,true);
			if ($result === false) {
				$result = $this->go_curl($location);
				if ($this->set_cache($location,$result)) {
					$this->maxAge = $ttl;
					$this->bestBefore = gmdate("D, d M Y H:i:s", time() + $ttl) . " GMT";
				};
			}
		}
		elseif (strtoupper($cache) == "SERVER") {
			$result = $this->get_cache($location,$ttl,false);
			if ($result === false) {
				$result = $this->go_curl($location);
				$this->set_cache($location,$result);
			}
		}
		else {
			$this->maxAge = $ttl;
			$this->bestBefore = gmdate("D, d M Y H:i:s", time() + $ttl) . " GMT";
			$result = $this->go_curl($location);
		}
		return $result;
	}

	/**
	 * Cloak request using internal curl.
	 *
	 * @param	STRING	$location	The location to cloak
	 */
	private function go_curl($location) {
		$this->ch = curl_init();
		if (!$this->ch) die ($this->go_error('router error'));

		curl_setopt_array($this->ch,Array(
			CURLOPT_RETURNTRANSFER	=>	1,
			CURLOPT_HEADER 		=>	0,
			CURLOPT_HEADERFUNCTION	=>	array(&$this,'read_curl_header'),
			CURLOPT_HTTPHEADER		=>	array("Expect:"),
			CURLOPT_TIMEOUT		=>	30,
			CURLOPT_USERAGENT		=>	$_SERVER['HTTP_USER_AGENT'],
			CURLOPT_PORT		=>	$_SERVER['SERVER_PORT'],
			CURLOPT_CUSTOMREQUEST	=>	$this->requestMethod,
	 	   //CURLOPT_HTTPHEADER	=>	array('ORIGIN: _URL_'),
			CURLOPT_FOLLOWLOCATION	=>	1
			));

		switch ($this->requestMethod) {
			case 'PUT':
			case 'DELETE':
			curl_setopt($this->ch, CURLOPT_URL, $location);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->originalRequestAttributes));
			break;
			case 'POST':
			curl_setopt($this->ch, CURLOPT_URL, $location);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->originalRequestAttributes);
			break;
	 	   //FALLBACK to HTTP-GET
			default:
			curl_setopt($this->ch, CURLOPT_URL, $location.(!sizeof($this->originalRequestAttributes) ? '' : '?'.http_build_query($this->originalRequestAttributes)));
			break;
		}

		$toReturn = curl_exec($this->ch);

		$this->responseStatus = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

		return $toReturn;
	}

	/**
	 * Read and store header back from cloaked request
	 */
	private function read_curl_header($ch, $string) {
		$h = explode(':',$string,2);
		if (is_array($h) AND isset($h[1])) $this->responseHeader[$h[0]] = $h[1];
		$length = strlen($string);
		return $length;
	}

	/**
	 * Get error message according to current transport
	 */
	private function go_error($message) {
		return sprintf($this->error_patterns[strtoupper($this->contentType)],$message);
	}

	/**
	 * Get server cache and, if valid cache, compute client cache params
	 *
	 * @param	STRING	$request	The request to tag
	 * @param	INT	$ttl		The cache time to live
	 * @param	BOOL	$client		If true, compute client cache params
	 */
	private function get_cache($request, $ttl, $client) {
		$currentTime = strtotime('now');
		$last_time_limit = $currentTime-$ttl;
		$requestTag = md5($request);
		if (is_readable($this->currentPath."/cache/".$requestTag) AND @filemtime($this->currentPath."/cache/".$requestTag) >= $last_time_limit) {
			if ($client) {
				$cache_time = filemtime($this->currentPath."/cache/".$requestTag);
				$this->maxAge = $cache_time + $ttl - $currentTime;
				$this->bestBefore = gmdate("D, d M Y H:i:s", $cache_time + $ttl) . " GMT";
			}
			$this->responseStatus = 200;
			return file_get_contents($this->currentPath."/cache/".$requestTag);
		}
		else return false;
	}

	/**
	 * Set server cache
	 *
	 * @param	STRING	$request	The request to tag
	 * @param	STRING	$data		The data to return
	 */
	function set_cache($request, $data) {
		//if returned status code != 200 or null content OR method != GET, DO NOT CACHE
		if ($this->responseStatus != 200 OR strlen($data) == 0 OR $this->requestMethod != 'GET') return false;
		$requestTag = md5($request);
		$fh = fopen($this->currentPath."/cache/".$requestTag, 'w');
		if (!$fh) return false;
		if (!fwrite($fh, $data)) return false;
		fclose($fh);
		return true;
	}

	/**
	 * Set header content type and cache
	 */
	private function set_header($contentLength) {
		if (DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN == '*') header('Access-Control-Allow-Origin: *');
		switch ($this->contentType) {
			case 'xml':
			case 'json':
			$_transport = 'application/'.$this->contentType;
			break;
			case 'yaml':
			$_transport = 'application/x-yaml';
			break;
			default:
			$_transport = 'text/plain';
			break;
		}
		switch ($this->responseStatus) {
	    case 200: //OK
	    header('Cache-Control: ' . (!$this->maxAge ? 'no-cache, must-revalidate' : 'max-age='.$this->maxAge.', must-revalidate') );
	    header('Expires: '.$this->bestBefore);
	    header('Content-type: '.$_transport);
	    header('Content-Length: '.$contentLength,true);
	    break;
	    case 202: //Accepted
		//PLEASE NOTE: according to HTTP/1.1, 202 header SHOULD HAVE status description in body... just in case
	    header($_SERVER["SERVER_PROTOCOL"].' 202 Accepted');
	    header('Status: 202 Accepted');
	    header('Content-Length: '.$contentLength,true);
	    break;
	    case 204: //OK - No Content
	    header($_SERVER["SERVER_PROTOCOL"].' 204 No Content');
	    header('Status: 204 No Content');
	    header('Content-Length: 0',true);
	    break;
	    case 201: //Created
	    case 301: //Moved Permanent
	    case 302: //Found
	    case 303: //See Other
	    case 307: //Temporary Redirect - this should never happens in router
	    header("Location: ".$this->responseHeader['Location'],true,$this->responseStatus);
		header('Content-Length: '.$contentLength,true); //is it needed?
		break;
	    case 304: //Not Modified
	    if (!isset($this->responseHeader['Last-Modified'])) header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');
	    else header('Last-Modified: '.$this->responseHeader['Last-Modified'], true, 304);
	    header('Content-Length: '.$contentLength,true);
	    break;
	    case 400: //Bad Request
	    header($_SERVER["SERVER_PROTOCOL"].' 400 Bad Request', true, 400);
		    header('Content-Length: '.$contentLength,true); //is it needed?
		    break;
		    case 403:
		    header('Origin not allowed', true, 403); //Not originated from allowed source
		    break;
	    case 404: //Not Found
	    header($_SERVER["SERVER_PROTOCOL"].' 404 Not Found');
	    header('Status: 404 Not Found');
	    break;
	    case 405:
	    case 501:
	    header('Allow: ' . $this->responseHeader['Allow'], true, $this->responseStatus);
	    break;
	}
	foreach ($this->headersToThrow as $header) {
		if (isset($this->responseHeader[$header])) header($header.': '.$this->responseHeader[$header],true);
	}
	}

	/**
	 * Get request attributes according to HTTP method.
	 *
	 * Also, result will be splitted to separate service tag to other values.
	 */
	private function get_request_attributes() {
		//$attributes = Array();
		switch($this->originalRequestMethod) {
			case 'GET':
			case 'HEAD':
			$attributes = $_GET;
			break;
			case 'POST':
			$attributes = $_POST;
			break;
			case 'PUT':
			case 'DELETE':
			parse_str(file_get_contents('php://input'), $attributes);
			break;
		}
		if (isset($attributes['service'])) {
			$service = $attributes['service'];
			unset($attributes['service']);
		}
		else {
			$service = false;
		}
		return Array($service, $attributes);
	}

	/**
	 * Get current url (http/https)
	 */
	private function get_current_url() {
		$http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';
		$uri = $_SERVER['REQUEST_URI'];
		$uri = strpos($uri,'index.php') !== false ? preg_replace("/\/index.php(.*?)$/i","",$uri) : preg_replace("/\/\?.*/","",$uri);
		$currentUrl = $http . $_SERVER['HTTP_HOST'] . $uri;
		return str_replace('%20',' ',$currentUrl);
	}

	/**
	 * Constructor: setup basic variables then apply routing logic.
	 */
	public function __construct() {

		global $registered_services;

		//setup basic variables
		$this->currentUrl = $this->get_current_url();
		$this->currentPath = getcwd();
		$this->originalRequestMethod = $_SERVER['REQUEST_METHOD'];
		$this->requestMethod = $_SERVER['REQUEST_METHOD'];

		//get attributes from original request
		list($this->originalRequestService, $this->originalRequestAttributes) = $this->get_request_attributes();

		if (isset($this->originalRequestAttributes['transport']) AND in_array(@strtoupper($this->originalRequestAttributes['transport']), Array("XML","JSON","YAML"))) {
			$this->contentType = strtolower($this->originalRequestAttributes['transport']);
		}
		else {
			$this->contentType = strtolower(DEFAULT_TRANSPORT);
		}

		//default, return null
		$toReturn = NULL;

		/*********************/
		/*** ROUTING LOGIC ***/
		/*********************/

		//if GLOBALLY NOT AUTHORIZED, DIE IMMEDIATELY
		if (DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN != '*' AND DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN != false AND !in_array(@$_SERVER['HTTP_ORIGIN'],explode(',',DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN))) {
			$this->responseStatus = 403;
			$toReturn = $this->go_error("Origin not allowed");
		}

		//if no service, die immediately
		elseif (!$this->originalRequestService) {
			$this->responseStatus = 400;
			die ($this->go_error('unspecified service'));
		}

		//if service not in routing table and autoroute disabled, die immediately
		elseif (!isset($registered_services[$this->originalRequestService]) AND !AUTO_ROUTE) {
			$this->responseStatus = 400;
			$toReturn = $this->go_error("unknown service");
		}

		//if service not in routing table and autoroute enabled, process request with default params
		elseif (
			(!isset($registered_services[$this->originalRequestService]) AND AUTO_ROUTE)
			OR
			(isset($registered_services[$this->originalRequestService])
				AND (!isset($registered_services[$this->originalRequestService]["target"]) OR !isset($registered_services[$this->originalRequestService]["policy"]))
				)
			){
			if (is_readable("services/".$this->originalRequestService.".php")) {
				$location = $this->currentUrl."/services/".$this->originalRequestService.".php";
				$toReturn = strtoupper(DEFAULT_POLICY) == 'CLOAK' ? $this->go_cloak($location) : $this->go_route($location);
			}
			else {
				$this->responseStatus = 400;
				$toReturn = $this->go_error("unknown service");
			}
		}

		//service IS in routing table
		else{
			$location = $this->currentUrl.'/services/'.$registered_services[$this->originalRequestService]['target'];

			if (isset($registered_services[$this->originalRequestService]['customHeaders'])) $this->headersToThrow = $registered_services[$this->originalRequestService]['customHeaders'];

			$this->requestMethod = isset($registered_services[$this->originalRequestService]['forceMethod']) ? strtoupper($registered_services[$this->originalRequestService]['forceMethod']) : $this->originalRequestMethod;

			if (isset($registered_services[$this->originalRequestService]["accessControlAllowOrigin"]) AND
				@$registered_services[$this->originalRequestService]["accessControlAllowOrigin"] != '*'AND $registered_services[$this->originalRequestService]["accessControlAllowOrigin"] != false AND
				!in_array(@$_SERVER['HTTP_ORIGIN'],explode(',',$registered_services[$this->originalRequestService]["accessControlAllowOrigin"])))
			{
				$this->responseStatus = 403;
				$toReturn = $this->go_error("Origin not allowed");
			}
			elseif ($registered_services[$this->originalRequestService]["policy"] == 'CLOAK') {		
				$toReturn = $this->go_cloak(
					$location,
					isset($registered_services[$this->originalRequestService]['cache']) ? $registered_services[$this->originalRequestService]['cache'] : AUTO_CACHE,
					isset($registered_services[$this->originalRequestService]['ttl']) ? $registered_services[$this->originalRequestService]['ttl'] : DEFAULT_TTL
					);
			}
			else {
				if (isset($registered_services[$this->originalRequestService]['redirectStatusCode'])) $this->redirectStatusCode = $registered_services[$this->originalRequestService]['redirectStatusCode'];
				$this->go_route($location);
			}
		}
		$this->set_header(strlen($toReturn));
		echo $toReturn;
		exit;
	}

	/**
	 * Destructor; only kill curl channel if any
	 */
	public function __destruct() {
		if ($this->ch !== false) curl_close($this->ch);
	}

}

$router = new router();


?>