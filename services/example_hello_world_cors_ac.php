<?php

/**
 * This example service tests CORS access control header. Only GET method is
 * implemented and should return:
 * - 200 OK - "hello comodojo" or "hello _TO_" if requested from comodojo.org
 * - 403 Forbidden - "Origin not allowed" in other cases
 */

include getcwd()."/../global/simpleDataRestDispatcher.php";
include getcwd()."/../configs/example_hello_world_cors_ac-config.php";

class service extends simpleDataRestDispatcher {
	
        public function get($attributes) {
            $this->result = $this->say_hello_to(isset($attributes['to']) ? $attributes['to'] : 'comodojo','GET');
	}

        private function say_hello_to($to,$method) {
            $this->success = true;
            return "(HTTP Method: ".$method.") Hello " . $to . "!";
        }
        
}

$rest = new service();

?>