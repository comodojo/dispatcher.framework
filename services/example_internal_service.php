<?php

//include base classess and configs
include getcwd()."/../global/simpleDataRestDispatcher.php";
include getcwd()."/../configs/example_internal_service-config.php";

class example_internal_service extends simpleDataRestDispatcher {
	
	public function logic() {
		$internal_data = @file_get_contents(getcwd()."/../others/text_".intval($_GET['text']));
		$this->success = !$internal_data ? false : true;
		$this->result = !$internal_data ? 'File not found!' : $internal_data;
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
$rest = new example_internal_service();
//dispatch request
$rest->dispatch();

?>