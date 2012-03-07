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
	
}

//create new service object
$rest = new example_internal_service();
//dispatch request
$rest->dispatch();

?>