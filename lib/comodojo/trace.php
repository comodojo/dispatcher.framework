<?php namespace comodojo:

/**
 * Produce request trace on file.
 * 
 * @package		Comodojo Spare Parts
 * @author		comodojo.org
 * @copyright	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version		__CURRENT_VERSION__
 * @license		GPL Version 3
 */

class trace {

	private $trace_path = getcwd()."/../".TRANSACTION_TRACES_PATH;

	private $service = false;

	private $trace_content = '';

	public function __construct($service) {

		if ($this->trace_path[strlen($this->trace_path)-1] != "/") $this->trace_path = $this->trace_path . "/";

		$current_time = date("d-m-Y (D) H:i:s",time());

		$this->service = empty($service) ? "unknown" : $service;

		$this->trace_content .= "****** TRACE START ******\n";

		$this->trace_content .= " >> REQUEST FROM " . $_SERVER["REMOTE_ADDR"] . " AT " . $current_time . " <<\n";

	}

	public function __destruct() {

		$this->trace_content .= "****** TRACE END ******\n";

		try {
			$this->write_content();	
		} catch (Exception $e) {
			//debug something here!
		}

	}

	public function client($method, $parameters, $required) {

		$this->trace_content .= "- Client request's method: " . $method . "\n";

		$this->trace_content .= "- Client sent: \n";
			
		foreach ($parameters as $parameter=>$value) {
			
			if (in_array($parameter,$required)) {
				$this->trace_content .= "[".$parameter."]* => ".$value."\n"; 
			}
			else {
				$this->trace_content .= "[".$parameter."] => ".$value."\n";
			}

		}

	}

	public function server($status, $transport, $data) {

		$this->trace_content .= "+ Server reply with status code: ".$status."\n";
		$this->trace_content .= "+ Server returns (".$transport."): \n";
		$this->trace_content .= var_export($data, true) . "\n";

	}

	private function write_content() {

		try {
			if (!$fh = fopen($this->trace_path.$this->service.".trace", 'a')) {
				throw new comodojo\exception('Could not open log file!');
			}
			if (!$fw = fwrite($fh, $this->trace_content)) {
				throw new comodojo\exception('Could not write log file!');
			}
			fclose($fh);
		}
		catch (comodojo\exception $e) {
			throw $e;
		}
	}

}

?>