<?php

//include base classess and configs
include getcwd()."/../global/simpleDataRestDispatcher.php";
include getcwd()."/../configs/example_external_service-config.php";

class example_external_service extends simpleDataRestDispatcher {
	
	public function logic() {
		$external_data = $this->httpGet('http://loripsum.net/generate.php?p='.intval($_GET['p']).'&l=medium',80);
		$this->success = !$external_data ? false : true;
		$this->result = $external_data;
	}

}

//create new service object
$rest = new example_external_service();
//dispatch request
$rest->dispatch();

?>