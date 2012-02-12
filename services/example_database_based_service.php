<?php

//include base classess and configs
include getcwd()."/../global/simpleDataRestDispatcher.php";
include getcwd()."/../configs/example_database_based_service-config.php";

class example_database_based_service extends simpleDataRestDispatcher {
	
	public function logic() {
		
		$query = "SELECT * FROM comodojo_example";
		
		try {
			//creating database handler with no values will return default database handler as specified in main-config.php
			$dbh = $this->createDatabaseHandler(/*$dbDataModel, $dbHost, $dbPort, $dbName, $dbUser, $dbPass*/);
			//alternatively complete declaration should be something like:
			//$dbh = $this->createDatabaseHandler('MYSQL', 'localhost',  3306, 'comodojo_services', 'comodojo', 'password');
			$example_result = $this->query($dbh, $query /*,$dbDataModel*/);
		}
		catch (Exception $e) {
			$this->success = false;
			$this->result = $e->getMessage();
			return;
		}
		
		$this->closeDatabaseHandler($dbh /*,$dbDataModel*/);
		
		$this->success = true;
		$this->result = $example_result['result'];
	}
	
	public function __construct() {
		global $service_config, $service_required_parameters;
		//set service name
		$this->service = $service_config["serviceName"];
		//put service online/offline
		$this->isServiceActive = $service_config["serviceActive"];
		//set debug/trace
		$this->isDebug = $service_config["isDebug"];
		$this->isTrace = $service_config["isTrace"];
		$this->logFile = $service_config["logFile"];
		//add required parameters
		foreach ($service_required_parameters as $parameter) {
			$this->addRequire($parameter);
		}
	}

}

//create new service object
$rest = new example_database_based_service();
//dispatch request
$rest->dispatch();

?>