<?php

/**
 * simpleDataRestDispatcher.php
 * 
 * A simple REST Services dispatcher (package)
 * 
 * @package 	Comodojo Spare Parts
 * @author		comodojo.org
 * @copyright 	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version 	__CURRENT_VERSION__
 *
 * @tutorial please see README file
 * @example  please see files in "services" directory
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

ob_start();

@(include('../configs/main-config.php')) OR die ("system error");

class simpleDataRestDispatcher {
	
	/********************** PRIVATE VARS **********************/
	/**
	 * Local pointer to database handler
	 */
	private $dbh = false;

	/**
	 * Local pointer to returned data (out of output buffer)
	 */
	private $toReturn = false;
	
	/**
	 * Comma separated service implemented methods (or SUPPORTED_METHODS if
	 * global logic)
	 */
	private $serviceImplementedMethods = false;
	/********************** PRIVATE VARS **********************/
	
	/********************* PROTECTED VARS *********************/
	/**
	 * Enable or disable service
	 * @var		bool
	 * @default	false
	 */
	protected $isServiceActive = false;
	
	/**
	 * Single service debug enable
	 * @var		bool
	 */
	protected $isDebug = GLOBAL_DEBUG_ENABLED;
	
	/**
	 * Single service tracing enable
	 * @var		bool
	 */
	protected $isTrace = GLOBAL_TRANSACTION_TRACING_ENABLED;
	
	/**
	 * Trace transaction to file (single service)
	 * @var		string (resource pointer)
	 */
	protected $logFile = DEFAULT_LOG_FILE;
	
	/**
	 * Metadata transport method
	 * @var		string
	 */
	protected $transport = DEFAULT_TRANSPORT;
	
	/**
	 * Client cache ttl
	 * @var		INT
	 */
	protected $ttl = DEFAULT_TTL;

	/**
	 * Allow origin header; see main-config for more information.
	 * @var		STRING
	 */	
	protected $accessControlAllowOrigin = DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN;
	
	/**
	 * Service name (for stats only)
	 * @var		string
	 */
	protected $service = DEFAULT_SERVICE_NAME;
	
	/**
	 * Define here required parameter that will be checked in each service call
	 * @var		Array
	 */
	protected $requiredParameters = Array();
	/********************* PROTECTED VARS *********************/
	
	/********************** PUBLIC VARS ***********************/
	/**
	 * Select if result is success or failure
	 * @var		bool
	 * @default	false
	 */
	public $success = false;
	
	/**
	 * Fill return data.
	 *
	 * PLEASE NOTE: a NULL result will throw a 204 (OK - no_content) status
	 * code, unless different one is explicitly defined.
	 * 
	 * @var		NULL/bool/string/array
	 * @default	NULL
	 */
	public $result = NULL;
	
	/**
	 * Status code, computed automatically unless explicitly defined.
	 * 
	 * @var		INT
	 * @default	false	will automatically select response status code.
	 */
	public $statusCode = false;
	
	/**
	 * Location to redirect client to, in case of 201,301,302,303,307
	 * responses
	 * 
	 * @var		STRING
	 * @default	false
	 */
	public $statusCode_location = false;
	
	/**
	 * Resource "last modified" date, in unix time, in case of 304 response
	 * to a GET request.
	 *
	 * PLEASE NOTE: in case of NOT MODIFIED RESPONSE, nothing will be sent
	 * to client except HTTP header!
	 * 
	 * @var		STRING
	 * @default	false
	 */
	public $statusCode_resourceLastModified = false;

	/********************** PUBLIC VARS ***********************/
	
	/******************** PPRIVATE METHODS ********************/

	

	
	
	
	
	/**
	 * Eval if client send right parameters to server
	 * 
	 * @return	bool	Eval status
	 */
	private function evalRequiredParameters($attributes) {
		$toReturn = true;
		if ($this->requiredParameters != false AND sizeof($this->requiredParameters) != 0) {
			foreach ($this->requiredParameters as $parameter) {
				if (isset($attributes[$parameter])) {
					continue;
				}
				else {
					$this->debug("cannot find parameter: " . $parameter);
					$toReturn = false;
				}
			}
		}
		return $toReturn;
	}
        
	
        
	/**
	 * Set transport according to request and default transport
	 */
	private function setTransport($attributes) {
		if (isset($attributes['transport'])) {
			$this->transport = in_array(strtoupper($attributes['transport']),Array("XML","JSON","YAML")) ? strtolower($attributes['transport']) : strtolower($this->transport);
		}
	}
	
	
	
	/**
	 * Match supported methods and currently implented methods.
	 *
	 * In case of global logic, return supported methods.
	 */
	private function getServiceImplementedMethods() {
		if (method_exists($this, 'logic')) {
			$this->serviceImplementedMethods = SUPPORTED_METHODS;
			$_supportedMethods = explode(',',SUPPORTED_METHODS);
		}
		else {
			$supportedMethods = explode(',',strtoupper(SUPPORTED_METHODS));
			$_supportedMethods = Array();
			foreach ($supportedMethods as $method) {
				if (method_exists($this, strtolower($method))) array_push($_supportedMethods,$method);
			}
			$this->serviceImplementedMethods = implode(',',$_supportedMethods);
		}
		return $_supportedMethods;
	}
	
	/**
	 * Takes attributes (or parameters) passed wia [METHOD] and return an
	 * array of them.
	 *
	 * Computed attributes may be used in single method implementation but
	 * also to keep $this::logic($arguments) methods-independent.
	 */
	private function getAttributes() {
		switch($_SERVER['REQUEST_METHOD']) {
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
		return $attributes;
	}
	/******************** PPRIVATE METHODS ********************/
        
	/******************* PROTECTED METHODS ********************/
	/**
	 * Transform stdObject string into array 
	 * 
	 * @param	string/stdObject		$string			The string to decode
	 * @return	array					$toReturn		The decoded array
	 */
	protected function stdObj2array($stdObj) {
		if(is_object($stdObj) OR is_array($stdObj)) {
			$array = array();
			foreach($stdObj as $key=>$val){
				$array[$key] = $this->stdObj2array($val);
			}
			return $array;
		}
		else {
			 return $stdObj;
		}
	}
	
	/**
	 * Transform an array into json string
	 * 
	 * @param	array		$array			The array to encode
	 * @return	string/json	$toReturn		The encoded string
	 */
	protected function array2json($array, $numeric_check=true) {
		if (!function_exists("json_encode")) {
			require("JSON.php");
			$json = new Services_JSON();
			$string = $json->encode($array);
		}
		else if ($numeric_check) {
			$string = json_encode($array, JSON_NUMERIC_CHECK);
		}
		else {
			$string = json_encode($array);
		}
		return $string;
	}

	/**
	 * Transform json string into array 
	 * 
	 * @param	string/json		$string			The string to decode
	 * @return	array			$toReturn		The decoded array
	 */
	protected function json2array($string, $raw = false) {
		if (!function_exists("json_decode")) {
			require("JSON.php");
			$json = new Services_JSON();
			$array = $json->decode($string);
			$return = $raw ? $array : $this->stdObj2array($array);
		}
		else {
			$return = json_decode($string, !$raw);
		}
		return $return;
	}

	/**
	 * Transform an array into xml string 
	 * 
	 * @param	array		$array		The array to encode
	 * @return	string/xml	$toReturn	The encoded string
	 */
	protected function array2xml($array) {
		require("XML.php");
		$xmlEngine = new XML();
		$xmlEngine->sourceArray = $array;
		return $xmlEngine->encode();
	}
	
	/**
	 * Transform XML string into an array 
	 * 
	 * @param	string/json		$dataString		The string to decode
	 * @return	array			$toReturn		The decoded array
	 */
	protected function xml2Array($dataString) {
		require("XML.php");
		$xmlEngine = new XML();
		$xmlEngine->$sourceString = $dataString;
		return $xmlEngine->decode();
	}
	
	/**
	 * Transform an array into YAML string 
	 * 
	 * @param	array		$array		The array to encode
	 * @return	string/YAML				The encoded string
	 */
	protected function array2yaml($array) {
		
		require("Spyc.php");
		return Spyc::YAMLDump($array);
		
	}

	/**
	 * Transform YAML string into an array 
	 * 
	 * @param	string/json		$dataString		The string to decode
	 * @return	array							The decoded array
	 */
	function yaml2array($dataString) {
		
		require("Spyc.php");
		return Spyc::YAMLLoadString($dataString);
		
	}

	/**
	 * Generate random alphanumerical string
	 * 
	 * @param	int		$length	The random string length
	 * @return	string	$locale	Serverside active locale
	 */
	protected function random($length) {
		
		if ($length <= 128) {
			$randNum = substr(md5(uniqid(rand(), true)), 0, $length);
		}
		else {
			$numString = (int)($length/128) + 1;
			$randNum = "";
			for ($i = 0; $i < $numString; $i++) {
				$randNum .=  md5(uniqid(rand(), true));
			}
			$randNum = substr($randNum, 0, $length);
		}
		return $randNum;
		
	}
	
	

	
        
	
        
	/**
	 * Return data according to selected transport (or fallback to JSON if none specified)
	 *
	 * @param       BOOL                        $success    The service exit status
	 * @param       STRING|ARRAY|NUMERIC|BOOL   $result     The service result
	 * @return	bool	Push status
	 */
	protected function returnData($success, $result) {
		
		$return = Array("success"=>$success, "result"=>$result);

		switch (strtoupper($this->transport)) {

			case 'JSON':
			$toReturn = $this->array2json($return);
			break;

			case 'XML':
			$toReturn = $this->array2xml($return);
			break;

			case 'YAML':
			$toReturn = $this->array2yaml($return);
			break;

			default:
			$toReturn = $this->array2json($return);
			break;
		}

		return $toReturn;

	}
	
	/**
	 * Add required value to required values' check 
	 *
	 * @param       STRING  $require    The required value
	 * @return	BOOL	Push status
	 */
	protected function addRequire($require) {

		if (is_string($require)) {
			array_push($this->requiredParameters, $require);
			return true;
		}
		else {
			$this->debug("------Skipping invalid required value------");
			$this->debug($require);
			$this->debug("-------------------------------------------");
			return false;
		}

	}
	/******************* PROTECTED METHODS ********************/
	
	/************** HTTP METHODS IMPLEMENTATIONS **************/
	
	/**
	 * Uncomment and fill this method if your service should support
	 * HTTP-GET requests
	 */
	//public function get($attributes) {}
	
	/**
	 * Uncomment and fill this method if your service should support
	 * HTTP-POST requests
	 */
	// public function post($attributes) {}
	
	/**
	 * Uncomment and fill this method if your service should support
	 * HTTP-PUT requests
	 */
	// public function put($attributes) {}
	
	/**
	 * Uncomment and fill this method if your service should support
	 * HTTP-DELETE requests
	 */
	// public function delete($attributes) {}
	
	/**
	 * Uncomment and fill this method if your service should support
	 * any HTTP requests (it's quite a wildcard, please be careful...)
	 */
	// public function logic($attributes) {}
	
	/************** HTTP METHODS IMPLEMENTATIONS **************/
	
	/********************* PUBLIC METHODS *********************/
		
	/**
	 * Constructor and dispatcher (new in 2.0).
	 *
	 * PLEASE NOTE: REMEMBER TO SET $service_config and
	 * $service_required_parameters in your service or in your service's
	 * configuration file!
	 */
	public final function __construct() {
		
		/****** SERVICE CONFIGURATION ******/
		global $service_config, $service_required_parameters;
		//set service name
		if (isset($service_config["serviceName"])) $this->service = $service_config["serviceName"];
		//put service online/offline
		if (isset($service_config["serviceActive"])) $this->isServiceActive = $service_config["serviceActive"];
		//set debug/trace
		if (isset($service_config["isDebug"])) $this->isDebug = $service_config["isDebug"];
		if (isset($service_config["isTrace"])) $this->isTrace = $service_config["isTrace"];
		if (isset($service_config["logFile"])) $this->logFile = $service_config["logFile"];
		//set cache ttl
		if (isset($service_config["ttl"])) $this->ttl = $service_config["ttl"];
		//set allow origin header
		if (isset($service_config["accessControlAllowOrigin"])) $this->accessControlAllowOrigin = $service_config["accessControlAllowOrigin"];
		//add required parameters
		foreach ($service_required_parameters as $parameter) {
			$this->addRequire($parameter);
		}
		
		/****** DIRECT DISPATCHING ******/
		$methods = $this->getServiceImplementedMethods();
		$attributes = $this->getAttributes();
		$this->setTransport($attributes);
		
		//eval if service is active or closed
		if (!$this->isServiceActive) {
			$this->statusCode = 200;
			$this->toReturn = $this->returnData(false,"service closed");
		}
		//eval if service is limited to some origind AND client send header orign information
		elseif (($this->accessControlAllowOrigin != "*" AND $this->accessControlAllowOrigin != false) AND !in_array(@$_SERVER['HTTP_ORIGIN'],explode(',',$this->accessControlAllowOrigin))) {
			$this->statusCode = 403;
			$this->toReturn = $this->returnData(false,"Origin not allowed");
		}
		//eval if service request method is allowed from framework
		elseif (!in_array($_SERVER['REQUEST_METHOD'], explode(',',SUPPORTED_METHODS))) {
			$this->statusCode = 405;
			$this->toReturn = NULL;
		}
		//eval if service request method match one of service implemented methods
		elseif (!in_array($_SERVER['REQUEST_METHOD'], $methods)) {
			$this->statusCode = 501;
			$this->toReturn = NULL;
		}
		//eval required parameters and, in case, process request
		elseif (!$this->evalRequiredParameters($attributes)) {
			$this->statusCode = 400;
			$this->toReturn = $this->returnData(false,"conversation error");
		}
		else {
			$exec = strtolower($_SERVER['REQUEST_METHOD']);
			if (method_exists($this, $exec)) $this->$exec($attributes);
			else $this->logic($attributes,$_SERVER['REQUEST_METHOD']);
			if ($this->accessControlAllowOrigin != '*' AND $this->accessControlAllowOrigin != false) header('Access-Control-Allow-Origin: './*$this->accessControlAllowOrigin*/@$_SERVER['HTTP_ORIGIN']);
			if (is_null($this->result)) {
				$this->statusCode = !$this->statusCode ? 204 : $this->statusCode;
				$this->toReturn = NULL;
			}
			else {
				$this->statusCode = !$this->statusCode ? 200 : $this->statusCode;
				$this->toReturn = $this->returnData($this->success, $this->result);
			}
		}
		
		$this->trace();
		$this->recordStat();
		
		$this->setHeader($this->statusCode, strlen($this->toReturn));
		
		ob_end_clean();

		die($this->toReturn);

	}
	/********************* PUBLIC METHODS *********************/

}

?>