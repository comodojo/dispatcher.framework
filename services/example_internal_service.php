<?php

/**
 * This example service tests internal file retrieval.
 *
 * Text argument will select a "text_" file in others directory.
 *
 * In case of file not fount, will return a 404 not found status code.
 *
 * Only GET method supported (PUT,POST,DELETE shoudl return 501 Not implemented
 * status code).
 */

include getcwd()."/../global/simpleDataRestDispatcher.php";
include getcwd()."/../configs/example_internal_service-config.php";

class service extends simpleDataRestDispatcher {
	
	public function get($attributes) {
		$internal_data = @file_get_contents(getcwd()."/../others/text_".intval($attributes['text']));
		$this->success = !$internal_data ? false : true;
		$this->statusCode = !$internal_data ? 404 : 200;
		$this->result = !$internal_data ? 'File not found!' : $internal_data;
	}
	
}

//create new service object
$rest = new service();

?>