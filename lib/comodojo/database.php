<?php namespace comodojo;

/**
 * standard spare parts database handler
 * 
 * @package 	Comodojo Spare Parts
 * @author		comodojo.org
 * @copyright 	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version 	__CURRENT_VERSION__
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

class database {

	private $model = COMODOJO_DEFAULT_DB_DATA_MODEL;

	private $host = COMODOJO_DEFAULT_DB_HOST;

	private $port = COMODOJO_DEFAULT_DB_PORT;

	private $name = COMODOJO_DEFAULT_DB_NAME;

	private $user = COMODOJO_DEFAULT_DB_USER;

	private $pass = COMODOJO_DEFAULT_DB_PASSWORD;

	private $dbh = false;

	private $id = false;

	private $fetch = "ASSOC";

	private $rows = false;

	private $supported_models = Array("MYSQLI","MYSQL_PDO","ORACLE_PDO","SQLITE_PDO","DBLIB_PDO","DB2","POSTGRESQL");

	public function __construct($model=false, $host=false, $port=false, $name=false, $user=false, $pass=false, ) {

		if (in_array(strtoupper($model), $this->supported_models)) $this->model = strtoupper($model);
		if (!empty($host)) $this->host = $host;
		if (!empty($port)) $this->port = filter_var($port, FILTER_VALIDATE_INT);
		if (!empty($name)) $this->name = $name;
		if (!empty($user)) $this->user = $user;
		if (!empty($pass)) $this->pass = $pass;

		debug("Creating database handler (".$this->model.") - ".$this->name."@".$this->host.":".$this->port, "INFO", "database");

		try {
			$this->connect();
		} catch (comodojo\exception $ce) {
			debug("Error creating database handler (".$this->model.") - ".$ce->getMessage, "ERROR", "database");
			throw $ce;
		}

	}

	public function __destruct() {

		$this->disconnect();

	}

	public function fetch($mode) {

		if ( in_array(strtoupper($fetch), Array('ASSOC','NUM','BOTH')) ) {
			$this->fetch = strtoupper($fetch);
		}
		else {
			throw new comodojo\exception('Invalid data fetch method');
		}

		return $this;

	}

	public function id($enabled=true) {

		$this->id = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
		return $this;

	}

	public function query($query, $return_raw=false) {

		debug("Ready to perform query: ".$query, "INFO", "database");

		switch ($this->model) {

			case ("MYSQLI"):
				
				$response = $this->dbh->query($query);
				if (!$response) {
					debug("Cannot perform query: ".$this->dbh->error, "ERROR", "database");
					throw new comodojo\exception($this->dbh->error, $this->dbh->errno);
				}

			break;

			case ("MYSQL_PDO"):
			case ("ORACLE_PDO"):
			case ("SQLITE_PDO"):
			case ("DBLIB_PDO"):

				try {
					$response = $this->dbh->prepare($query);
					//$response->setFetchMode($fetch);
					$response->execute();
				}
				catch (PDOException $e) {
					$error = $dbHandler->errorInfo();
					debug("Cannot perform query: ".$error[2], "ERROR", "database");
					throw new comodojo\exception($error[1], $error[2]);
				}

			break;

			case ("DB2"):

				$response = db2_exec($this->dbh,$query);
				if (!$response) {
					debug("Cannot perform query: ".db2_stmt_error(), "ERROR", "database");
					throw new comodojo\exception(db2_stmt_error());
				}
				
			}
			break;

			case ("POSTGRESQL"):

				$response = pg_query($this->dbh,$query);
				if (!$response) {
					$_error = pg_last_error();
					debug("Cannot perform query: ".pg_last_error(), "ERROR", "database");
					throw new comodojo\exception(pg_last_error());
				}

			break;

		}

		if (!$return_raw) {

			try {
				$this->results_to_array($response);
			} catch (comodojo\exception $e) {
				throw $e;
			}

		}
		else {
			$return = $response;
		}

		return $return;

	}

	private function connect() {

		if ( empty($this->model) ) throw new comodojo\exception('Invalid database data model');

		switch ($this->model) {

			case ("MYSQLI"):
				
				if ( empty($this->host) OR empty($this->port) OR empty($this->name) OR empty($this->user) OR empty($this->pass) ) {
					throw new comodojo\exception('Invalid database parameters');
				}

				$this->dbh = new mysqli($this->host, $this->user, $this->pass, $this->name, $this->port);

				if ($this->dbh->connect_error) {
					throw new comodojo\exception($this->dbh->connect_error, $this->dbh->connect_errno);
				}

			break;

			case ("MYSQL_PDO"):

				if ( empty($this->host) OR empty($this->port) OR empty($this->name) OR empty($this->user) OR empty($this->pass) ) {
					throw new comodojo\exception('Invalid database parameters');
				}

				$dsn="mysql:host=".$this->host.";port=".$this->port .";dbname=".$this->name;
				
				try {
					$this->dbh = new PDO($dsn,$this->user,$this->pass);
				}
				catch (PDOException $e) {
					throw new comodojo\exception($e->getMessage(), $e->getCode());
				}

			break;

			case ("ORACLE_PDO"):

				if ( empty($this->host) OR empty($this->port) OR empty($this->name) OR empty($this->user) OR empty($this->pass) ) {
					throw new comodojo\exception('Invalid database parameters');
				}

				$dsn="oci:dbname=".$this->host.":".$this->port."/".$this->name;
				
				try {
					$this->dbh = new PDO($dsn,$this->user,$this->pass);
				}
				catch (PDOException $e) {
					throw new comodojo\exception($e->getMessage(), $e->getCode());
				}

			break;

			case ("SQLITE_PDO"):
			
				if ( empty($this->name) ) {
					throw new comodojo\exception('Invalid database parameters');
				}

				$dsn="sqlite:".$this->name;

				try {
					$this->dbh = new PDO($dsn);
				}
				catch (PDOException $e) {
					throw new comodojo\exception($e->getMessage(), $e->getCode());
				}

			break;

			case ("DB2"):

				if ( empty($this->host) OR empty($this->port) OR empty($this->name) OR empty($this->user) OR empty($this->pass) ) {
					throw new comodojo\exception('Invalid database parameters');
				}

				$dsn="ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=".$this->name.";HOSTNAME=".$this->host.";PORT=".$this->port.";PROTOCOL=TCPIP;UID=".$this->user.";PWD=".$this->pass.";";

				$this->dbh = db2_pconnect($dsn,$this->user,$this->pass);
				if (!$this->dbh){
					throw new comodojo\exception(db2_conn_errormsg());
				}

			}
			break;

			case ("DBLIB_PDO"):

				if ( empty($this->host) OR empty($this->port) OR empty($this->name) OR empty($this->user) OR empty($this->pass) ) {
					throw new comodojo\exception('Invalid database parameters');
				}

				$dsn = "dblib:host=".$this->host.":".$this->port.";dbname=".$this->name;
			
				try {
					$this->dbh = new PDO($dsn,$this->user,$this->pass);
				}
				catch (PDOException $e) {
					throw new comodojo\exception($e->getMessage(), $e->getCode());
				}

			break;

			case ("POSTGRESQL"):

				if ( empty($this->host) OR empty($this->port) OR empty($this->name) OR empty($this->user) OR empty($this->pass) ) {
					throw new comodojo\exception('Invalid database parameters');
				}

				$dsn = "host=".$this->host." port=".$this->port." dbname=".$this->name." user=".$this->user." password=".$this->pass;

				$this->dbh = @pg_connect($dsn);
				if (!$this->dbh) {
					throw new comodojo\exception(pg_last_error());
				}

			break;

		}

	}

	private function disconnect() {

		debug("Closing database handler (".$this->model.")", "INFO", "database");

		switch($this->model) {
			
			case ("MYSQLI"):
				if ($this->dbh !== false) $this->dbh->close();
			break;
			
			case ("MYSQL_PDO"):
			case ("ORACLE_PDO"):
			case ("SQLITE_PDO"):
			case ("DBLIB_PDO"):
				$this->dbh = null;
			break;
			
			case ("DB2"):
				if ($this->dbh !== false) db2_close($this->dbh);
			break;
			
			case ("POSTGRESQL"):
				if ($this->dbh !== false) pg_close($this->dbh);
				$this->dbh = null;
			break;
			
			default:
				debug("Unknown database model (".$this->model.")", "WARNING", "database");
			break;
		}

	}

	private function results_to_array($data) {

		debug("Building result set (".$this->model.")", "INFO", "database");

		if ( empty($this->model) ) throw new comodojo\exception('Invalid database data model');

		$result	= Array();
		$id		= false;
		$length = 0;
		$rows	= 0;

		$iterator = 0;

		switch ($this->model) {

			case ("MYSQLI"):
				
				if ( !is_object($data) OR !is_a($data, 'mysqli_result') ) throw new comodojo\exception('Invalid result data for model '.$this->model);

				switch ($this->fetch) {
					case 'NUM':		$fetch = MYSQLI_NUM;	break;
					case 'ASSOC':	$fetch = MYSQLI_ASSOC;	break;
					default:		$fetch = MYSQLI_BOTH;	break;
				}
				
									$length = $data->num_rows;
				if ($this->id)		$id 	= $this->dbh->insert_id;
				if ($this->rows)	$rows 	= $this->dbh->affected_rows;

				while($iterator < $length) {
					$result[$iterator] = $data->fetch_array($fetch);
					$iterator++;
				}
				$data->free();

			break;

			case ("MYSQL_PDO"):
			case ("ORACLE_PDO"):
			case ("SQLITE_PDO"):
			case ("DBLIB_PDO"):

				if ( !is_object($data) ) throw new comodojo\exception('Invalid result data for model '.$this->model);

				switch ($this->fetch) {
					case 'NUM':		$fetch = PDO::FETCH_NUM;	break;
					case 'ASSOC':	$fetch = PDO::FETCH_ASSOC;	break;
					default:		$fetch = PDO::FETCH_BOTH;	break;
				}

				$result = $sth->fetchAll($fetch);

									$length = sizeof($result);
				if ($this->id)		$id 	= $this->dbh->lastInsertId();
				if ($this->rows)	$rows 	= $data->rowCount();

			break;

			case ("DB2"):

				if ( !is_resource($data) OR @get_resource_type($data) != "DB2 Statement" ) throw new comodojo\exception('Invalid result data for model '.$this->model);

									$length = db2_num_fields($data);
				if ($this->id)		$id 	= db2_last_insert_id($this->dbh);
				if ($this->rows)	$rows 	= db2_num_rows($data);

				switch ($this->fetch) {
					case 'NUM': 	while ($row = db2_fetch_row($data)) array_push($result, $row);		break;
					case 'ASSOC': 	while ($row = db2_fetch_assoc($data)) array_push($result, $row);	break;
					default: 		while ($row = db2_fetch_both($data)) array_push($result, $row);		break;
				}

			}
			break;

			case ("POSTGRESQL"):

				if ( !is_resource($data) OR @get_resource_type($data) != "pgsql result" ) throw new comodojo\exception('Invalid result data for model '.$this->model);
				
									$length = pg_num_rows($data);
				if ($this->id)		$id 	= pg_last_oid($data);
				if ($this->rows)	$rows 	= pg_affected_rows($data);

				while($iterator < $length) {
					switch ($this->fetch) {
						case 'NUM': 	$result[$iterator] = pg_fetch_array($data);	break;
						case 'ASSOC': 	$result[$iterator] = pg_fetch_assoc($data);	break;
						default: 		$result[$iterator] = pg_fetch_all($data);	break;
					}
					$iterator++;
				}

			break;

		}

		return Array(
			"data"			=>	$result,
			"length"		=>	$length,
			"id"			=>	$id,
			"affected_rows"	=>	$rows
		);

	}

}

?>