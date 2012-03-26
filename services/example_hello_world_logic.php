<?php

/**
 * This example service tests all supported HTTP methods (GET,PUT,POST,DELETE)
 * defining single global method (logic) for all of them.
 *
 * For example to single methods please see "example_hello_world.php" service.
 */

include getcwd()."/../global/simpleDataRestDispatcher.php";
include getcwd()."/../configs/example_hello_world_logic-config.php";

class service extends simpleDataRestDispatcher {
	
        public function logic($attributes,$method) {
		$this->success = true;
		$this->result = "(HTTP Method: ".$method.") Hello " . (isset($attributes['to']) ? $attributes['to'] : 'comodojo') . "!";
	}
        
}

$rest = new service();

?>