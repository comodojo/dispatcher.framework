<?php

/**
 * This example service tests external url http retrieval.
 *
 * "p" argument will ask "http://loripsum.net" to generate "p" paragraphs of
 * lorem ipsum content.
 *
 * Only GET,PUT,POST methods supported (DELETE shoudl return 501 Not implemented
 * status code).
 */
include getcwd()."/../global/simpleDataRestDispatcher.php";
include getcwd()."/../configs/example_external_service-config.php";

class service extends simpleDataRestDispatcher {
	
	public function get($attributes) {
		$external_data = $this->httpGet('http://loripsum.net/generate.php?p='.intval($attributes['p']).'&l=medium',80);
		$this->success = !$external_data ? false : true;
		$this->result = $external_data;
	}
	
	public function put($attributes) {
		$external_data = $this->httpGet('http://loripsum.net/generate.php?p='.intval($attributes['p']).'&l=medium',80);
		$this->success = !$external_data ? false : true;
		$this->result = $external_data;
	}
	
	public function post($attributes) {
		$external_data = $this->httpGet('http://loripsum.net/generate.php?p='.intval($attributes['p']).'&l=medium',80);
		$this->success = !$external_data ? false : true;
		$this->result = $external_data;
	}

}

//create new service object
$rest = new service();

?>