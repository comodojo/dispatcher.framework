<?php

/**
 * This example service tests database interaction using simple methods inlcuded
 * in simpleDataRestDispatcher and custom HTTP response codes
 *
 * Requests via GET should return a 200 OK response with readed content.
 * Requests via PUT or POST should retund a 202 Accepted response.
 * Request via DELETE should return a 204 No Content response.
 */

include getcwd()."/../global/simpleDataRestDispatcher.php";
include getcwd()."/../configs/example_database_based_service-config.php";

class service extends simpleDataRestDispatcher {
	
	public function get($attributes) {
		
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
        
        public function put($attributes) {
		if (isset($attributes['top']) AND isset($attributes['middle']) AND isset($attributes['bottom'])) {
			$query = "INSERT INTO comodojo_example VALUES (NULL,".strtotime('now').",".$attributes['top'].",".$attributes['middle'].",".$attributes['bottom'].");";
			try {
				//creating database handler with no values will return default database handler as specified in main-config.php
				$dbh = $this->createDatabaseHandler(/*$dbDataModel, $dbHost, $dbPort, $dbName, $dbUser, $dbPass*/);
				//alternatively complete declaration should be something like:
				//$dbh = $this->createDatabaseHandler('MYSQL', 'localhost',  3306, 'comodojo_services', 'comodojo', 'password');
				$example_result = $this->query($dbh, $query, /*use default dbDataModel=>*/false, /*return last insert id=>*/true);
			}
			catch (Exception $e) {
				$this->success = false;
				$this->result = $e->getMessage();
				return;
			}
			
			$this->closeDatabaseHandler($dbh /*,$dbDataModel*/);
			
			$this->success = true;
			$this->statusCode = 202;
			$this->result = $example_result['returnedId'];
		}
		else {
			$this->success = false;
			$this->statusCode = 400;
			$this->result = "Invalid parameters";
		}
	}
        
        public function post($attributes) {
		if (isset($attributes['top']) AND isset($attributes['middle']) AND isset($attributes['bottom'])) {
			$query = "INSERT INTO comodojo_example VALUES (NULL,".strtotime('now').",".$attributes['top'].",".$attributes['middle'].",".$attributes['bottom'].");";
			try {
				//creating database handler with no values will return default database handler as specified in main-config.php
				$dbh = $this->createDatabaseHandler(/*$dbDataModel, $dbHost, $dbPort, $dbName, $dbUser, $dbPass*/);
				//alternatively complete declaration should be something like:
				//$dbh = $this->createDatabaseHandler('MYSQL', 'localhost',  3306, 'comodojo_services', 'comodojo', 'password');
				$example_result = $this->query($dbh, $query, /*use default dbDataModel=>*/false, /*return last insert id=>*/true);
			}
			catch (Exception $e) {
				$this->success = false;
				$this->result = $e->getMessage();
				return;
			}
			
			$this->closeDatabaseHandler($dbh /*,$dbDataModel*/);
			
			$this->success = true;
			$this->statusCode = 202;
			$this->result = $example_result['returnedId'];
		}
		else {
			$this->success = false;
			$this->statusCode = 400;
			$this->result = "Invalid parameters";
		}
	}
        
	public function delete($attributes) {
		$query = "DELETE FROM comodojo_example WHERE id>14;";
		try {
			//creating database handler with no values will return default database handler as specified in main-config.php
			$dbh = $this->createDatabaseHandler(/*$dbDataModel, $dbHost, $dbPort, $dbName, $dbUser, $dbPass*/);
			//alternatively complete declaration should be something like:
			//$dbh = $this->createDatabaseHandler('MYSQL', 'localhost',  3306, 'comodojo_services', 'comodojo', 'password');
			$example_result = $this->query($dbh, $query);
		}
		catch (Exception $e) {
			$this->success = false;
			$this->result = $e->getMessage();
			return;
		}
		
		$this->closeDatabaseHandler($dbh /*,$dbDataModel*/);
		
		$this->success = true;
		$this->statusCode = 204;
	}
	
}

//create new service object
$rest = new service();

?>