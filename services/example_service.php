<?php

//include base classess and configs
include getcwd()."/../global/simpleDataRestDispatcher.php";
include getcwd()."/../configs/example_service-config.php";

class example_service extends simpleDataRestDispatcher {
	
	public function logic() {
		$this->success = true;
		$this->result = "Hello " . ($_GET["hello_to"] != "" ? $_GET["hello_to"] : "World") . "!";
	}
	
}

//create new service object
$rest = new example_service();

?>