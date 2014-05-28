<?php


/**
	 * Create a database handler according to parameters passed
	 * 
	 * @return	HANDLER     Database handler or null (will throw an Exception)
	 */
	protected function createDatabaseHandler($dbDataModel = false, $dbHost = false, $dbPort = false, $dbName = false, $dbUser = false, $dbPass = false) {

		$this->debug("Creating DB Handler. Datamodel:".$dbDataModel.", host:".$dbHost.", port:".$dbPort.", name:".$dbName);

		$_dbDataModel	= !$dbDataModel	? DEFAULT_DB_DATA_MODEL	: $dbDataModel;
		$_dbHost		= !$dbHost		? DEFAULT_DB_HOST		: $dbHost;
		$_dbPort		= !$dbPort		? DEFAULT_DB_PORT		: $dbPort;
		$_dbName		= !$dbName		? DEFAULT_DB_NAME		: $dbName;
		$_dbUser		= !$dbUser		? DEFAULT_DB_USER		: $dbUser;
		$_dbPass		= !$dbPass		? DEFAULT_DB_PASSWORD	: $dbPass;

		$connection = $this->_connectToDatabase($_dbDataModel, $_dbHost, $_dbPort, $_dbName, $_dbUser, $_dbPass);

		if (!$connection[0]) {
			throw new Exception($connection[1]);
		}
		else {
			return $connection[1];
		}

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

			case ("MYSQLI"):
			$localDbHandler = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
			if ($localDbHandler->connect_error) {
				$error = $localDbHandler->connect_error." - ".$localDbHandler->connect_errno;
				$this->debug("ERROR (dbLayer) - Cannot connect to database: ".$error);
				$connectionStatus = false;
				$localDbHandler = $error;
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
	* @return	ARRAY
	*/
	private function resource_to_array($data,$id=false,$affectedRows=false,$fetch=false) {

		$this->debug("INFO (dbLayer) - Building result set...");

		$_fetch = in_array(strtoupper($fetch), Array('ASSOC','NUM','BOTH')) ? $fetch : 'BOTH';

		if (is_resource($data) AND @get_resource_type($data) == "mysql result") {
			switch ($_fetch) {
				case 'NUM': $fetch = MYSQL_NUM; break;
				case 'ASSOC': $fetch = MYSQL_ASSOC; break;
				default: $fetch = MYSQL_BOTH; break;
			}
			$i = 0;
			$myResult = array();
			$myResultLength = mysql_num_rows($data);
			while($i < $myResultLength) {
				$myResult[$i] = mysql_fetch_array($data, $fetch);
				$i++;
			}			
		}
		elseif (is_resource($data) AND @get_resource_type($data) == "pgsql result") {
			$i = 0;
			$myResult = array();
			$myResultLength = pg_num_rows($data);
			while($i < $myResultLength) {
				switch ($_fetch) {
					case 'NUM': 	$myResult[$i] = pg_fetch_array($data);	break;
					case 'ASSOC': 	$myResult[$i] = pg_fetch_assoc($data);	break;
					default: 		$myResult[$i] = pg_fetch_all($data);	break;
				}
				$i++;
			}			
		}
		elseif (is_resource($data) AND @get_resource_type($data) == "DB2 Statement") {
			$myResult = array();
			$myResultLength = db2_num_fields($data);
			switch ($_fetch) {
				case 'NUM': 	while ($row = db2_fetch_row($data)) array_push($myResult, $row);	break;
				case 'ASSOC': 	while ($row = db2_fetch_assoc($data)) array_push($myResult, $row);	break;
				default: 		while ($row = db2_fetch_both($data)) array_push($myResult, $row);	break;
			}
		}
		else if (is_object($data) AND is_a($data, 'mysqli_result')) {
			switch ($_fetch) {
				case 'NUM': $fetch = MYSQLI_NUM; break;
				case 'ASSOC': $fetch = MYSQLI_ASSOC; break;
				default: $fetch = MYSQLI_BOTH; break;
			}
			$i = 0;
			$myResult = array();
			$myResultLength = $data->num_rows;
			while($i < $myResultLength) {
				$myResult[$i] = $data->fetch_array($fetch);
				$i++;
			}
			$data->free();
		}
		elseif(is_object($data)) {
			$myResult = array();
			foreach($data as $key=>$val) $myResult[$key] = $val;
			$myResultLength = sizeof($myResult);
		}
		else {
			$myResult = $data;
			$myResultLength = false;
		}

		foreach ($this->transform as $to) {
			if (isset($myResult[$to])) $myResult[$to] = json2array($myResult[$to]);
		}

		return Array(
			"result"		=>	$myResult,
			"resultLength"	=>	$myResultLength,
			"transactionId"	=>	$id,
			"affectedRows"	=>	$affectedRows
		);

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
	protected function query($dbHandler, $query, $dbDataModel=DEFAULT_DB_DATA_MODEL, $returnId=false, $fetch=DEFAULT_DB_FETCH) {

		$this->debug($query);

		switch ($dbDataModel) {

			case "MYSQL":
			$response = @mysql_query($query, $dbHandler);
			if (!$response) {
				$_error = mysql_errno()." - ".mysql_error();
				$this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
				throw new Exception($_error);
			}
			$toReturn = $this->resource_to_array($response,!$returnId ? false : mysql_insert_id($dbHandler), @mysql_affected_rows($dbHandler),$fetch);
			break;

			case "MYSQLI":
			$response = $dbHandler->query($query);
			if (!$response) {
				$_error = $dbHandler->errno." - ".$dbHandler->error;
				$this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
				throw new Exception($_error);
			}
			$toReturn = $this->resource_to_array($response,!$returnId ? false : $dbHandler->insert_id, $dbHandler->affected_rows,$fetch);
			break;

			case "MYSQL_PDO":
			case "ORACLE_PDO":
			case "SQLITE_PDO":
			case "DBLIB_PDO":
			switch (strtoupper($fetch)) {
				case 'NUM': $_fetch = PDO::FETCH_NUM; break;
				case 'ASSOC': $_fetch = PDO::FETCH_ASSOC; break;
				default: $_fetch = PDO::FETCH_BOTH; break;
			}
			try {
				$response = $dbHandler->query($query,$_fetch);
			}
			catch (PDOException $e) {
				$_errorInfo = $dbHandler->errorInfo();
				$_error = $_errorInfo[1]." - ".$_errorInfo[2];
				$this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
				throw new Exception($_error);
			}
			$toReturn = $this->resource_to_array($response,!$returnId ? false : $dbHandler->lastInsertId(),@$response->rowCount(),$fetch);
			break;

			case "DB2":
			$response = db2_exec($dbHandler,$query);
			if (!$response) {
				$_error = db2_stmt_error();
				$this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
				throw new Exception($_error);
			}
			$toReturn = $this->resource_to_array($response,!$returnId ? false : db2_last_insert_id($dbHandler),@db2_num_rows($data),$fetch);
			break;

			case "POSTGRESQL":
			$response = pg_query($dbHandler,$query);
			if (!$response) {
				$_error = pg_last_error();
				$this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
				throw new Exception($_error);
			}
			$toReturn = $this->resource_to_array($response,!$returnId ? false : pg_last_oid($response), @pg_affected_rows($response),$fetch);
			break;

			default:
			$_error = "Unknown dbDataModel";
			$this->debug("ERROR (dbLayer) - Cannot perform query: ".$_error);
			throw new Exception($_error);
			break;

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
			$dbHandler=null;
			break;
			
			case ("MYSQLI"):
			$dbHandler->close(); 
			break;
			
			case ("MYSQL_PDO"):
			case ("ORACLE_PDO"):
			case ("SQLITE_PDO"):
			case ("DBLIB_PDO"):
			$dbHandler=null;
			break;
			
			case ("DB2"):
			db2_close($dbHandler);
			break;
			
			case ("POSTGRESQL"):
			pg_close($dbHandler);
			$dbHandler=null;
			break;
			
			default:
			return false;
			break;
		}

		return true;

	}

?>