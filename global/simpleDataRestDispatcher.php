<?php

/**
 * simpleDataRestDispatcher.php
 * 
 * A simple REST Services dispatcher (package)
 * 
 * @package	Comodojo Spare Parts
 * @author	comodojo.org
 * @copyright	2012 comodojo.org (info@comodojo.org)
 * @version	__CURRENT_VERSION__
 *
 * @tutorial	please see README file
 * @example	please see files in "services" directory
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
 *
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
	 * Debug someting into php error log
	 * 
	 * @param	ARRAY|STRING|NUMERIC|BOOL   $message	Message to debug
	 */
	private function debug($message) {
		if ($this->isDebug || GLOBAL_DEBUG_ENABLED) {
			if (is_array($message)) {
				error_log("(DEBUG): ".$key." = Array(");
				$this->debug_helper($message);
				error_log(")");
			}
			elseif (is_object($message)) {
				$this->debug($this->stdObj2array($message));
			}
			elseif(is_scalar($message)) {
				error_log("(DEBUG): ".$message);
			}
			else {
				error_log("(DEBUG): invalid value type for debug.");
			}
		}
	}
        
	
	private function debug_helper($value, $margin='') {
		foreach ($value as $key => $value) {
			if (is_array($value)) {
				error_log($margin.$key." = Array(");
				$this->debug_helper($value, $margin+='   ');
				error_log($margin.")");
			}
			else {
				error_log($margin.$key." = ".$value.",");
			}
		}
	}

        /**
	 * Trace request/response in the specified log file
	 */
	private function trace() {
		if ($this->isTrace || GLOBAL_TRANSACTION_TRACING_ENABLED) {
			$myMessage = "****** REQUEST FROM " . $_SERVER["REMOTE_ADDR"] . " AT " . date("d-m-Y (D) H:i:s",time()) . " ******\n";
			$myMessage .= "- Client request's method: ".$_SERVER['REQUEST_METHOD']."\n";
			$myMessage .= "- Client sent: \n";
			foreach ($_GET as $parameter=>$value) {
				if (in_array($parameter,$this->requiredParameters)) $myMessage .= "[".$parameter."]* => ".$value."\n"; 
				else $myMessage .= "[".$parameter."] => ".$value."\n";
			}
			$myMessage .= "- Server reply with status code: ".$this->statusCode."\n";
			$myMessage .= "- Server returns (".$this->transport."): \n";
			if (strtoupper($this->transport) == "XML") $toReturn = $this->array2xml(Array("success"=>$this->success, "result"=>$this->result));
			else $toReturn = $this->array2json(Array("success"=>$this->success, "result"=>$this->result));
			$myMessage .= $toReturn;
			$myMessage .= "\n****** REQUEST END ******\n";
			try {
				if (!$fh = fopen(getcwd()."/../".TRANSACTION_TRACES_PATH.$this->logFile, 'a')) {
					throw new Exception('Could not open log file!');
				}
				if (!$fw = fwrite($fh, $myMessage)) {
					throw new Exception('Could not write log file!');
				}
                                fclose($fh);
			}
			catch (Exception $e) {
				$this->debug($e);
			}
		}		
	}
	
	/**
	 * Record some statistic info on database
	 * 
	 * @return	bool	Record status
	 */
	private function recordStat() {
		if (GLOBAL_STATISTICS_ENABLED) {
                        try {
                                $dbh = $this->createDatabaseHandler(STATISTICS_DB_DATA_MODEL, STATISTICS_DB_HOST, STATISTICS_DB_PORT, STATISTICS_DB_NAME, STATISTICS_DB_USER, STATISTICS_DB_PASSWORD);
                                $example_result = $this->query($dbh, "INSERT INTO `comodojo_statistics` (id,timestamp,service,address,userAgent) VALUES (0,".strtotime('now').",'".$this->service."','".$_SERVER["REMOTE_ADDR"]."','".$_SERVER["HTTP_USER_AGENT"]."')", STATISTICS_DB_DATA_MODEL);
                        }
                        catch (Exception $e) {
                                $this->debug("Statistics error: " . $e->getMessage());
                                return false;
                        }
                        $this->closeDatabaseHandler($dbh, STATISTICS_DB_DATA_MODEL);
                        return true;
		}
	}
	
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
	 * Connect to database according to db data model (database type)
	 * 
	 * @return	ARRAY(BOOL,HANDLER|STRING)	Database connection status (bool), handler or error message
	 */
	private function _connectToDatabase($dbDataModel, $dbHost, $dbPort, $dbName, $dbUser, $dbPass) {
                $localDbHandler = false;
                $connectionStatus = true;
                switch ($dbDataModel) {
                        case ("MYSQL"):
                                $localDbHandler = @mysql_connect($dbHost.":".$dbPort, $dbUser, $dbPass);
                                if (!$localDbHandler) {
                                        $error = mysql_errno()." - ".mysql_error();
                                        $this->debug("ERROR (dbLayer) - Cannot connect to database: ".$error);
                                        $connectionStatus = false;
                                        $localDbHandler = $error;
                                }
                                else {
                                    $dbSelect = @mysql_select_db($dbName, $localDbHandler);
                                    if (!$dbSelect) {
                                            $error = mysql_errno()." - ".mysql_error();
                                            $this->debug("ERROR (dbLayer) - Cannot connect to database: ".$error);
                                            $connectionStatus = false;
                                            $localDbHandler = $error;
                                    }
                                }
                        break;
                        case ("MYSQL_PDO"):
                                $dsn="mysql:host=".$dbHost.";port=".$dbPort .";dbname=".$dbName;
                                try {
                                        $localDbHandler = new PDO($dsn,$dbUser,$dbPass);
                                }
                                catch (PDOException $e) {
                                        $error = $e->getCode()." - ".$e->getMessage();
                                        $this->debug("ERROR (dbLayer) - Cannot connect to database: ".$error);
                                        $connectionStatus = false;
                                        $localDbHandler = $error;
                                }
                                break;
                        case ("ORACLE_PDO"):
                                $dsn="oci:dbname=".$dbHost.":".$dbPort."/".$dbName;
                                try {
                                        $localDbHandler = new PDO($dsn,$dbUser,$dbPass);
                                }
                                catch (PDOException $e) {
                                        $error = $e->getCode()." - ".$e->getMessage();
                                        $this->debug("ERROR (dbLayer) - Cannot connect to database: ".$error);
                                        $connectionStatus = false;
                                        $localDbHandler = $error;
                                }
                                break;

                        case ("SQLITE_PDO"):
                                $dsn="sqlite:".$dbName;
                                try {
                                        $localDbHandler = new PDO($dsn);
                                }
                                catch (PDOException $e) {
                                        $error = $e->getCode()." - ".$e->getMessage();
                                        $this->debug("ERROR (dbLayer) - Cannot connect to database: ".$error);
                                        $connectionStatus = false;
                                        $localDbHandler = $error;
                                }
                                break;

                        case ("DB2"):
                                $dsn="ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=".$dbName.";HOSTNAME=".$dbHost.";PORT=".$dbPort.";PROTOCOL=TCPIP;UID=".$dbUser.";PWD=".$dbPass.";";
                                $localDbHandler = db2_pconnect($dsn,$dbUser,$dbPass);
                                if (!$localDbHandler){
                                        $error = db2_conn_errormsg();
                                        $this->debug("ERROR (dbLayer) - Cannot connect to database: ".$error);
                                        $connectionStatus = false;
                                        $localDbHandler = $error;
                                }
                                break;

                        case ("DBLIB_PDO"):
                                $dsn = "dblib:host=".$dbHost.":".$dbPort.";dbname=".$dbName;
                                try {
                                        $localDbHandler = new PDO($dsn,$dbUser,$dbPass);
                                }
                                catch (PDOException $e) {
                                        $error = $e->getCode()." - ".$e->getMessage();
                                        $this->debug("ERROR (dbLayer) - Cannot connect to database: ".$error);
                                        $connectionStatus = false;
                                        $localDbHandler = $error;
                                }
                                break;
			
			case ("POSTGRESQL"):
				$dsn = "host=".$dbHost." port=".$dbPort." dbname=".$dbName." user=".$dbUser." password=".$dbPass;
				$localDbHandler = @pg_connect($dsn);
                                if (!$localDbHandler) {
                                        $error = pg_last_error();
                                        $this->debug("ERROR (dbLayer) - Cannot connect to database: ".$error);
                                        $connectionStatus = false;
                                        $localDbHandler = $error;
                                }
                                break;
                }
                
                return Array($connectionStatus,$localDbHandler);
                
        }
        
        /**
	 * Build result set from database query
	 * 
	 * @return	ARRAY	Result (ASSOC) plus result length (i.e. sizeof)
	 */
	private function _buildResultSet($data,$id=false) {
		$this->debug("INFO (dbLayer) - Building result set...");
		if (is_resource($data) AND @get_resource_type($data) == "mysql result") {
			$i = 0;
			$myResult = array();
			$myResultLength = mysql_num_rows($data);
			while($i < $myResultLength) {
				$myResult[$i] = mysql_fetch_array($data, MYSQL_ASSOC);
				$i++;
			}			
		}
		elseif (is_resource($data) AND @get_resource_type($data) == "pgsql result") {
			$i = 0;
			$myResult = array();
			$myResultLength = pg_num_rows($data);
			while($i < $myResultLength) {
				$myResult[$i] = pg_fetch_assoc($data);
				$i++;
			}			
		}
		elseif(is_object($data)) {
			$myResult = array();
			foreach($data as $key=>$val){
				$myResult[$key] = $val;
			}
			$myResultLength = sizeof($myResult);
		}
		else {
			$myResult = $data;
			$myResultLength = false;
		}
		return Array(
			"result"	=>	$myResult,	
			"resultLength"	=>	$myResultLength,
			"returnedId"	=>	$id
		);
        }
        
	/**
	 * Set transport according to request and default transport
	 */
	private function setTransport($attributes) {
		if (isset($attributes['transport']) AND (@strtoupper($attributes['transport']) == "XML" OR @strtoupper($attributes['transport']) == "JSON") ) $this->transport = strtolower($attributes['transport']);
	}
	
	/**
	 * Set header content type and cache control
	 */
	private function setHeader ($statusCode, $contentLength) {
		
		//not strictly needed but may cause problems if omitted in some XHR request
		if ($this->accessControlAllowOrigin == '*') header('Access-Control-Allow-Origin: *');
		
		switch ($statusCode) {
			case 200: //OK
				if ($this->ttl > 0) {
					header('Content-type: application/'.strtolower($this->transport));
					header('Cache-Control: max-age='.$this->ttl.', must-revalidate');
					header('Expires: '.gmdate("D, d M Y H:i:s", time() + $this->ttl)." GMT");
				}
				elseif ($this->ttl == 0) {
					header('Content-type: application/'.strtolower($this->transport),true);
					header('Cache-Control: no-cache, must-revalidate');
					header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
				}
				else {
					header('Content-type: application/'.strtolower($this->transport),true);
				}
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
			case 307: //Temporary Redirect
				header("Location: ".$this->statusCodeLocation,true,$statusCode);
				header('Content-Length: '.$contentLength,true); //is it needed?
			break;
			case 304: //Not Modified
				if (!$this->statusCode_resourceLastModified) header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');
				else header('Last-Modified: '.gmdate('D, d M Y H:i:s', $statusCode_resourceLastModified).' GMT', true, 304);
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
				header('Allow: ' . SUPPORTED_METHODS, true, 405); //Not allowed
			break;
			case 501:
				header('Allow: ' . $this->serviceImplementedMethods, true, 501);
			break;
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
	protected function array2json($array) {
		if (!function_exists("json_encode")) {
			if (!function_exists('loadHelper_JSON')) require("JSON.php");
			$json = new Services_JSON();
			$string = $json->encode($array);
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
	protected function json2array($string) {
		if (!function_exists("json_decode")) {
			if (!function_exists('loadHelper_JSON')) require("JSON.php");
			$json = new Services_JSON();
			$array = $json->decode($string);
		}
		else {
			$array = json_decode($string);
		}
		$array = $this->stdObj2array($array);
		return $array;
	}
	
	/**
	 * Transform an array into xml string 
	 * 
	 * @param	array		$array		The array to encode
	 * @return	string/xml	$toReturn	The encoded string
	 */
	protected function array2xml($array) {
		if (!function_exists('loadHelper_XML')) require("XML.php");
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
		if (!function_exists('loadHelper_XML')) require("XML.php");
		$xmlEngine = new XML();
		$xmlEngine->$sourceString = $dataString;
		return $xmlEngine->decode();
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
	 * Create a database handler according to parameters passed
	 * 
	 * @return	HANDLER     Database handler or null (will throw an Exception)
	 */
	protected function createDatabaseHandler($dbDataModel = false, $dbHost = false, $dbPort = false, $dbName = false, $dbUser = false, $dbPass = false) {
                $this->debug("Creating DB Handler. Datamodel:".$dbDataModel.", host:".$dbHost.", port:".$dbPort.", name:".$dbName);
                
                $_dbDataModel = !$dbDataModel ? DEFAULT_DB_DATA_MODEL : $dbDataModel;
                $_dbHost = !$dbHost ? DEFAULT_DB_HOST : $dbHost;
                $_dbPort = !$dbPort ? DEFAULT_DB_PORT : $dbPort;
                $_dbName = !$dbName ? DEFAULT_DB_NAME : $dbName;
                $_dbUser = !$dbUser ? DEFAULT_DB_USER : $dbUser;
                $_dbPass = !$dbPass ? DEFAULT_DB_PASSWORD : $dbPass;
                
                $connection = $this->_connectToDatabase($_dbDataModel, $_dbHost, $_dbPort, $_dbName, $_dbUser, $_dbPass);
                
                if (!$connection[0]) {
                    throw new Exception($connection[1]);
                }
                else {
                    return $connection[1];
                }
        }

        /**
	 * Shot a query to database
	 *
	 * @param       HANDLER     $dbHandler      The database handler
	 * @param       STRING      $dbDataModel    The database data model (db type)
	 * @param       STRING      $query          The composed query
	 * @param       BOOL	    $returnId       If true, return db last insert id
	 *
	 * @return	ARRAY|NULL  Query result (in FETCH_ASSOC mode) or null (will throw an Exception)
	 */
	protected function query($dbHandler, $query, $dbDataModel=false, $returnId=false) {
                if (!$dbDataModel) $dbDataModel = DEFAULT_DB_DATA_MODEL;
                // logging and tracing...
                $this->debug($query);

                if ($dbDataModel == "MYSQL") {
                        $response = @mysql_query($query, $dbHandler);
                        if (!$response) {
                                $_error = mysql_errno()." - ".mysql_error();
                                $this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
                                throw new Exception($_error);
                        }
                        $toReturn = $this->_buildResultSet($response,!$returnId ? false : mysql_insert_id($dbHandler));
                }
                elseif ($dbDataModel == "MYSQL_PDO" OR $dbDataModel == "ORACLE_PDO" OR $dbDataModel == "SQLITE_PDO" OR $dbDataModel == "DBLIB_PDO") {
                        try {
                                $response = $dbHandler->query($query,PDO::FETCH_ASSOC);
                        }
                        catch (PDOException $e) {
                                $_errorInfo = $dbHandler->errorInfo();
                                $_error = $_errorInfo[1]." - ".$_errorInfo[2];
                                $this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
                                throw new Exception($_error);
                        }
                        $toReturn = $this->_buildResultSet($response,!$returnId ? false : $dbHandler->lastInsertId());
                }
                elseif ($dbDataModel == "DB2") {
                        $response = db2_exec($dbHandler,$query);
                        if (!$response) {
                                $_error = db2_stmt_error();
                                $this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
                                throw new Exception($_error);
                        }
                        $_response = Array();
                        while ($row = db2_fetch_assoc($response)) {
                                array_push($_response, $row);
                        }
                        $toReturn = $this->_buildResultSet($_response,!$returnId ? false : db2_last_insert_id($dbHandler));
                }
		elseif ($dbDataModel == "POSTGRESQL") {
                        $response = pg_query($dbHandler,$query);
                        if (!$response) {
                                $_error = pg_last_error();
                                $this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
                                throw new Exception($_error);
                        }
                        $_response = Array();
                        while ($row = db2_fetch_assoc($response)) {
                                array_push($_response, $row);
                        }
                        $toReturn = $this->_buildResultSet($_response,!$returnId ? false : db2_last_insert_id($dbHandler));
                }
                else {
                        $_error = "Unknown dbDataModel";
                        $this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
                        throw new Exception($_error);
                }
                return $toReturn;
        }

        /**
	 * Close a database handler according to parameters passed
	 *
	 * @param       STRING      $dbDataModel    The database data model (db type)
	 * @param       HANDLER     $dbHandler      The database handler
	 * 
	 * @return	BOOL        Operation status
	 */
	protected function closeDatabaseHandler($dbHandler, $dbDataModel=false) {
                if (!$dbDataModel) $dbDataModel = DEFAULT_DB_DATA_MODEL;
                $this->debug("Closing database handler. Datamodel:".$dbDataModel);
                switch($dbDataModel) {
                        case ("MYSQL"):
                                mysql_close($dbHandler);
                                break;
                        case ("MYSQL_PDO"):
                                $dbHandler=null;
                                break;
                        case ("ORACLE_PDO"):
                                $dbHandler=null;
                                break;
                        case ("SQLITE_PDO"):
                                $dbHandler=null;
                                break;
                        case ("DB2"):
                                db2_close($dbHandler);
                                break;
                        case ("DBLIB_PDO"):
                                unset($dbHandler);
                                break;
			case ("POSTGRESQL"):
                                pg_close($dbHandler);
                                break;
                        default:
                                return false;
                                break;
                }
                return true;
        }
        
        /**
	 * Try to get file from url via (not authenticated) http GET.
	 * Will use CURL (if available) or fsocks (fallback)
	 *
	 * @param       STRING      $address        The URL address
	 * @param       NUMERIC     $port           The URL port
	 * @param       ARRAY       $getdata        Array of data to include into query string
	 * 
	 * @return	STRING      Grabbed data
	 */
	protected function httpGet($address, $port, $getdata=Array()) {
                $channel = false;
                $received = false;
                if (function_exists("curl_init") AND !count($getdata)) {
                        $channel = curl_init();
                        if (!$channel) return false;
                        curl_setopt($channel, CURLOPT_URL, $address);
                        curl_setopt($channel, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($channel, CURLOPT_TIMEOUT, 30);
                        curl_setopt($channel, CURLOPT_PORT, $port);
                        $received = curl_exec($channel);
                        curl_close($channel);
                }
                else {
                        $getdata_str = count($getdata) ? '?' : '';
                        foreach ($getdata as $k => $v) $getdata_str .= urlencode($k) .'='. urlencode($v) . '&';
                        $_url = parse_url($address);
                        $crlf = "\r\n";

                        $req = 'GET '. $_url['path'] . $getdata_str .' HTTP/1.1' . $crlf;
                        $req .= 'Host: '. $_url['host'] . $crlf;
                        $req .= 'User-Agent: COMODOJO_SERVICES' . $crlf;
                        $req .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . $crlf;
                        $req .= 'Accept-Language: en-us,en;q=0.5' . $crlf;
                        $req .= 'Accept-Encoding: deflate' . $crlf;
                        $req .= 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . $crlf . $crlf;

                        $channel = fsockopen($_url['host'], $port, $errno, $errstr, 30);
                        if (!$channel) return false;
                        fputs($channel, $req);
                        $received = '';
                        while ($line = fgets($channel)) $received .= $line;
                        fclose($channel);
                        $received = substr($received, strpos($received, "\r\n\r\n") + 4);
                        
                }
                return $received;
        }        
        
	/**
	 * Return data according to selected transport (or fallback to JSON if none specified)
	 *
	 * @param       BOOL                        $success    The service exit status
	 * @param       STRING|ARRAY|NUMERIC|BOOL   $result     The service result
	 * @return	bool	Push status
	 */
	protected function returnData($success, $result) {
		if (strtoupper($this->transport) == "XML") return $this->array2xml(Array("success"=>$success, "result"=>$result));
		else return $this->array2json(Array("success"=>$success, "result"=>$result));
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