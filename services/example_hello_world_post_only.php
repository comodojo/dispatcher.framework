<?php

/**
 * This example service tests single method definition.
 *
 * Requests via GET, PUT, DELETE should return a 501 Not Implemented response.
 */

include getcwd()."/../global/simpleDataRestDispatcher.php";
include getcwd()."/../configs/example_hello_world_post_only-config.php";

class service extends simpleDataRestDispatcher {
	
        public function post($attributes) {
	    $this->result = $this->say_hello_to(isset($attributes['to']) ? $attributes['to'] : 'comodojo','POST');
	}
        
	private function say_hello_to($to,$method) {
            $this->success = true;
            return "(HTTP Method: ".$method.") Hello " . $to . "!";
        }
        
}

$rest = new service();

?>