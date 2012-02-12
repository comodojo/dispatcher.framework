<?php

/**
 * simpleDataRestDispatcher.php
 * 
 * A simple REST Services dispatcher (package)
 * 
 * @package	Comodojo Spare Parts
 * @author	comodojo.org
 * @copyright	2012 comodojo.org (info@comodojo.org)
 * @version	1.0
 * 
 * @example		Following usage example:
 * 
 *  include "[my_syte_path]/global/simpleDataRestDispatcher.php";
 *
 *  class myService extends simpleDataRestDispatcher {
 *  
 *  	// logic() method
 *  	//
 *  	// *** PUT HERE THE SERVICE LOGIC
 * 		// SHOULD END RETURNING:
 *  	// $this->success	[Request success or failure]
 *  	// $this->result	[Result content/body returned by dispatcher]
 *  	// *** --------------------------
 *  	public function logic() {
 *  
 *  		$this->success = true;
 *  		$this->result = "this is a test";
 *  
 *  	}
 *  
 *  	//__construct() method
 *  	//
 *  	// *** PUT HERE THE SERVICE INITS
 *  	// *** --------------------------
 *  	public function __construct() {
 *  		$this->isDebug = true;
 *  		$this->isTrace = true;
 *  		$this->logFile = "myService.log";
 *  		$this->DB_HOST = "localhost";
 *  		$this->DB_NAME = "myService_services";
 *  		$this->DB_USER = "root";
 *  		$this->DB_PASSWORD = "root";
 *  		$this->DB_PREFIX = "myService_";
 *  	}
 *   }
 *   
 *   // Setup a new service
 *   $rest = new myService();
 *   
 *   // Dispatch request
 *   $rest->dispatch();
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

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
if (isset($_GET['transport'])) {
	if (strtoupper($_GET['transport']) == "XML") {
		header('Content-type: application/xml');
	}
	else {
		header('Content-type: application/json');
	}
}
else {
	header('Content-type: application/json');
}

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
	 * @default	false
	 */
	protected $isDebug = false;
	
	/**
	 * Single service tracing enable
	 * @var		bool
	 * @default	false
	 */
	protected $isTrace = false;
	
	/**
	 * Trace transaction to file (single service)
	 * @var		string (resource pointer)
	 * @default	"log/global.log"
	 */
	protected $logFile = "global.log";
	
	/**
	 * Metadata transport method
	 * @var		string
	 * @default	"JSON"
	 */
	protected $transport = "JSON";
	
	/**
	 * Service name (for stats only)
	 * @var		string
	 * @default	"undefined"
	 */
	protected $service = "undefined";
	
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
	 * Fill return data
	 * @var		bool/string/array
	 * @default	false
	 */
	public $result = false;
	/********************** PUBLIC VARS ***********************/
	
        /******************** PPRIVATE METHODS ********************/
        
        /**
	 * Debug someting into php error.log
	 * 
	 * @param	ARRAY|STRING|NUMERIC|BOOL   $message	Message to debug
	 */
	private function debug($message) {
		if ($this->isDebug || GLOBAL_DEBUG_ENABLED) {
			if (is_array($message)) {
				foreach ($message as $key->$value) {
					if (is_array($value)) {
						error_log("(DEBUG): ".$key." = Array(");
						$this->debug($value);
						error_log(")");
					}
					else error_log("(DEBUG): ".$key." = ".$value);
				}
			}
			elseif (is_object($message)) {
				$this->debug($this->stdObj2array($message));
			}
			elseif(is_string($message) || is_bool($message) || is_numeric($message)) {
				error_log("(DEBUG): ".$message);
			}
			else {
				error_log("(DEBUG): invalid value type for debug.");
			}
		}
	}
        
        /**
	 * Generate random alphanumerical string
	 * 
	 * @param	int		$length	The random string length
	 * @return	string	$locale	Serverside active locale
	 */
	private function trace() {
		if ($this->isTrace || GLOBAL_TRANSACTION_TRACING_ENABLED) {
			$myMessage = "****** REQUEST FROM " . $_SERVER["REMOTE_ADDR"] . " AT " . date("d-m-Y (D) H:i:s",time()) . " ******\n";
			$myMessage .= "- Client sent: \n";
			$transport = isset($_GET['transport']) ? strtoupper($_GET['transport']) : $this->transport;
			foreach ($_GET as $parameter=>$value) {
				if (in_array($parameter,$this->requiredParameters)) $myMessage .= "[".$parameter."]* => ".$value."\n"; 
				else $myMessage .= "[".$parameter."] => ".$value."\n";
			}
			$myMessage .= "- Server returns (".$transport."): \n";
			switch ($transport) {
				case "JSON":
					$toReturn = $this->array2json(Array("success"=>$this->success, "result"=>$this->result));
				break;
				case "XML":
					$toReturn = $this->array2xml(Array("success"=>$this->success, "result"=>$this->result));
				break;
				//fallback to json...
				default:
					$toReturn = $this->array2json(Array("success"=>$this->success, "result"=>$this->result));
				break;
			}
			$myMessage .= $toReturn;
			$myMessage .= "\n****** REQUEST END ******\n";
			try {
				//if (!$fh = fopen(getcwd()."/../log/".$this->logFile, 'a')) {
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
	private function evalRequiredParameters() {
		$toReturn = true;
		if ($this->requiredParameters != false AND sizeof($this->requiredParameters) != 0) {
			foreach ($this->requiredParameters as $parameter) {
				if (isset($_GET[$parameter])) {
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
                }
                
                return Array($connectionStatus,$localDbHandler);
                
        }
        
        /**
	 * Build result set from database query
	 * 
	 * @return	ARRAY	Result (ASSOC) plus result length (i.e. sizeof)
	 */
	private function _buildResultSet($data) {
		$this->debug("INFO (dbLayer) - Building result set...");
		if (is_resource($data) AND @get_resource_type($data) == "mysql result") {
			$i = 0;
			$myResult = array();
			$myResultLength = mysql_num_rows($data);
			while($i < mysql_num_rows($data)) {
				$myResult[$i] = mysql_fetch_array($data, MYSQL_ASSOC);
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
			"resultLength"	=>	$myResultLength
		);
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
                    return null;
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
	 *
	 * @return	ARRAY|NULL  Query result (in FETCH_ASSOC mode) or null (will throw an Exception)
	 */
	protected function query($dbHandler, $query, $dbDataModel=false) {
                if (!$dbDataModel) $dbDataModel = DEFAULT_DB_DATA_MODEL;
                // logging and tracing...
                $this->debug($query);

                if ($dbDataModel == "MYSQL") {
                        $response = @mysql_query($query, $dbHandler);
                        if (!$response) {
                                $_error = mysql_errno()." - ".mysql_error();
                                $this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
                                throw new Exception($_error);
                                return null;
                        }
                        //$this->returnedId = $this->returnId ? mysql_insert_id($this->dbHandler) : false;
                        $toReturn = $this->_buildResultSet($response);
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
                                return null;
                        }
                        //$this->returnedId = $this->returnId ? $this->dbHandler->lastInsertId() : false;
                        $toReturn = $this->_buildResultSet($response);
                }
                elseif ($dbDataModel == "DB2") {
                        $response = db2_exec($dbHandler,$query);
                        if (!$response) {
                                $_error = db2_stmt_error();
                                $this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
                                throw new Exception($_error);
                                return null;
                        }
                        $_response = Array();
                        while ($row = db2_fetch_assoc($response)) {
                                array_push($_response, $row);
                                //$_response[$row[0]] = $row[1];
                        }
                        $toReturn = $this->_buildResultSet($_response);
                }
                else {
                        $_error = "Unknown dbDataModel";
                        $this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
                        throw new Exception($_error);
                        return null;
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
		$transport = isset($_GET['transport']) ? strtoupper($_GET['transport']) : $this->transport;
		switch ($transport) {
			case "JSON":
				$toReturn = $this->array2json(Array("success"=>$success, "result"=>$result));
			break;
			case "XML":
				$toReturn = $this->array2xml(Array("success"=>$success, "result"=>$result));
			break;
			//fallback to json...
			default:
				$toReturn = $this->array2json(Array("success"=>$success, "result"=>$result));
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
	
	/********************* PUBLIC METHODS *********************/
	public function logic() {
		return false;
	}
		
	public function dispatch() {
		
		//eval if service is active or closed
		if (!$this->isServiceActive) {
			$this->toReturn = $this->returnData(false,"service closed");
		}
		//eval required parameters and, in case, process request (logic)
		elseif (!$this->evalRequiredParameters()) {
			$this->toReturn = $this->returnData(false,"conversation error");
		}
		else {
			$this->logic();
			$this->toReturn = $this->returnData($this->success, $this->result);
		}
		
		$this->trace();
		$this->recordStat();
		
                ob_end_clean();
                
		die($this->toReturn);
	}
	/********************* PUBLIC METHODS *********************/
        
}

?>