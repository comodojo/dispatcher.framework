<?php namespace comodojo;

class event {

	private $time		= false;
	private $service	= false;
	private $remote		= false;
	private $user_agent = false;

	public function __construct($time, $service, $remote, $user_agent) {

		$this->time = empty($time) ? strtotime("now") : $time;
		$this->service = empty($service) ? "UNKNOWN" : $service;
		$this->remote = empty($remote) ? "UNKNOWN" : $remote;
		$this->user_agent = empty($user_agent) ? "UNKNOWN" : $user_agent;

		if (!defined('EVENTS_DB_TABLE') OR 
			!defined('EVENTS_DB_MODEL') OR 
			!defined('EVENTS_DB_HOST') OR 
			!defined('EVENTS_DB_PORT') OR 
			!defined('EVENTS_DB_NAME') OR 
			!defined('EVENTS_DB_USER') OR 
			!defined('EVENTS_DB_PASS')
		) {
			debug("Cannot record event, database constants not defined", 'ERROR', "event");
		}
		else {
			$this->record_event();
		}

	}

	private function record_event() {

		$query = "INSERT INTO `".EVENTS_DB_TABLE."` (timestamp,service,remote,useragent) VALUES (".$this->time.",'".$this->service."','".$this->remote."','".$this->user_agent."')";

		try {
			
			$db = new comodojo\database(EVENTS_DB_MODEL,EVENTS_DB_HOST,EVENTS_DB_PORT,EVENTS_DB_NAME,EVENTS_DB_USER,EVENTS_DB_PASS);
			$db->query($query, true);

		}
		catch (comodojo\exception $e) {
			//event's error should fail silently
			debug("Error recording event: ".$e->getMessage, 'ERROR', "event");
		}

	}

}

?>