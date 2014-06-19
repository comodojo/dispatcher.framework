<?php namespace comodojo;

define("DISPATCHER_REAL_PATH",realpath(dirname(__FILE__)));

require(DISPATCHER_REAL_PATH."/../../configs/dispatcher-config.php");
//require(DISPATCHER_REAL_PATH."/../../configs/routing-table.php");
require(DISPATCHER_REAL_PATH."/Exception/DispatcherException.php");
require(DISPATCHER_REAL_PATH."/Exception/DatabaseException.php");
require(DISPATCHER_REAL_PATH."/Exception/IOException.php");
require(DISPATCHER_REAL_PATH."/ObjectResult/ObjectResultInterface.php");
require(DISPATCHER_REAL_PATH."/ObjectResult/ObjectSuccess.php");
require(DISPATCHER_REAL_PATH."/ObjectResult/ObjectError.php");
require(DISPATCHER_REAL_PATH."/ObjectResult/ObjectRedirect.php");
require(DISPATCHER_REAL_PATH."/debug.php");
require(DISPATCHER_REAL_PATH."/cache.php");
require(DISPATCHER_REAL_PATH."/header.php");
require(DISPATCHER_REAL_PATH."/events.php");
require(DISPATCHER_REAL_PATH."/serialization.php");
require(DISPATCHER_REAL_PATH."/deserialization.php");
require(DISPATCHER_REAL_PATH."/service.php");

use \comodojo\Exception\DispatcherException;
use \comodojo\Exception\IOException;
use \comodojo\Exception\DatabaseException;
use \comodojo\ObjectResult\ObjectSuccess;
use \comodojo\ObjectResult\ObjectError;
use \comodojo\ObjectResult\ObjectRedirect;

class dispatcher {

	private $current_time = NULL;

	private $service_uri = NULL;

	private $service_requested = NULL;

	private $service_attributes = NULL;

	private $service_parameters = NULL;

	private $request_method = NULL;

	private $routingtable = Array();

	private $working_mode = 'STANDARD';

	private $cacher = NULL;

	private $header = NULL;

	private $events = NULL;

	public final function __construct() {

		ob_start();

		global $routingtable;

		if ( is_array($routingtable) ) $this->routingtable = $routingtable;

		//require("database.php");
		//require("trace.php");
		//require("statistic.php");
		//require("http.php");
		//require("random.php");
		
		$this->current_time = time();

		$this->working_mode = $this->get_working_mode();

		$this->request_method = $_SERVER['REQUEST_METHOD'];

		$this->service_uri = $this->url_uri();

		debug('-----------------------------------------------------------','INFO','dispatcher');
		debug(' *** Starting dispatcher ***','INFO','dispatcher');
		debug('-----------------------------------------------------------','INFO','dispatcher');
		debug(' * Current time: '.$this->current_time,'INFO','dispatcher');
		debug(' * Working mode: '.$this->working_mode,'INFO','dispatcher');
		debug(' * Request HTTP method: '.$this->request_method,'INFO','dispatcher');
		debug(' * Request URI: '.$this->service_uri,'INFO','dispatcher');
		debug('-----------------------------------------------------------','INFO','dispatcher');
		debug(' *** Loading modules...','INFO','dispatcher');
		
		$this->cacher = new cache($this->current_time);

		$this->header = new header($this->current_time);

		$this->events = new events();

		debug('-----------------------------------------------------------','INFO','dispatcher');

		$this->url_interpreter($this->working_mode);

		debug(' * Requested service: '.$this->service_requested,'INFO','dispatcher');
		debug(' * Raw attributes: ','INFO','dispatcher');
		debug($this->service_attributes,'INFO','dispatcher');

		debug('-----------------------------------------------------------','INFO','dispatcher');

	}

	public final function dispatch() {

		try {
			
			if ( empty($this->service_requested) ) throw new DispatcherException("Bad request", 400);
			
			if ( $this->service_is_in_routing_table($this->service_requested) ) {

			}
			else if ( DISPATCHER_AUTO_ROUTE AND $this->service_is_routable($this->service_requested) ) {

			}
			else {

				throw new DispatcherException("Not found", 404);
				
			}

		} catch (DispatcherException $de) {

			$route = new ObjectError();
			$route->setService($this->service_requested)
				  ->setStatusCode($de->getCode())
				  ->setContent($de->getMessage());

			$return = $this->route($route);

		} catch (Exception $e) {

			
		
		}

		ob_end_clean();

		exit($return);

	}

	public final function set($param, $value) {

	}

	public final function get($param, $value) {

	}

	public final function add($type, $param, $value) {

		$type = strtoupper($type);

		switch ($type) {

			case 'ROUTE':
				
				$this->routingtable[$param] = $value;

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
				
				if ( isset($this->routingtable[$param]) ) unset($this->routingtable[$param]);

				break;
			
			case 'HOOK':
				
				$this->events->remove($param, $value);

				break;

			default:
				# code...
				break;

		}

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

	/**
	 * Return current request uri
	 *
	 * @return uri 	The request uri
	 */
	private function url_uri() {

		return $_SERVER['REQUEST_URI'];

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

		// Fire first hook (level2), as specified above

		$fork = $this->events->fire($hook, $route);

		if ( $fork instanceof \comodojo\ObjectResult\ObjectResultInterface ) $route = $fork;

		// Now select and fire second hook (level3)
		// This means that event engine will fire something like "dispatcher.route.200"
		// or "dispatcher.error.500"

		$fork = $this->events->fire($hook.".".$route->getStatusCode(), $route);

		if ( $fork instanceof \comodojo\ObjectResult\ObjectResultInterface ) $route = $fork;		

		// After hooks, start composing header

		$this->header->free();

		foreach ($route->getHeaders() as $header => $value) {
			
			$this->header->set($header, $value);

		}

		$message = $route->getContent();

		$this->header->compose($route->getStatusCode(), strlen($message), $route->getLocation()); # ><!/\!°>

		// Return the content (stuff that will go on screen)

		return $message;

	}

	private function service_is_in_routing_table($service) {

		return isset($this->routingtable[$service]) ? true : false;

	}

	private function service_is_routable($service_file) {

		return is_readable($service_file) ? true : false;

	}

	private function service_is_compliant() {

	}

	private function get_service_route($service) {

		return $this->routingtable[$service];

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

?>