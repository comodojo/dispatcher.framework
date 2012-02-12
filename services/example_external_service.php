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
$rest = new example_external_service();
//dispatch request
$rest->dispatch();

?>