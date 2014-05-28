<?php

	/**
	 * Trace request/response in the specified log file
	 */
	private function trace() {

		if ($this->isTrace || GLOBAL_TRANSACTION_TRACING_ENABLED) {

			$myMessage = "****** REQUEST FROM " . $_SERVER["REMOTE_ADDR"] . " AT " . date("d-m-Y (D) H:i:s",time()) . " ******\n";
			$myMessage .= "- Client request's method: ".$_SERVER['REQUEST_METHOD']."\n";
			$myMessage .= "- Client sent: \n";
			foreach ($_GET as $parameter=>$value) {
				if (in_array($parameter,$this->requiredParameters)) $myMessage .= "[".$parameter."]* => ".$value."\n"; 
				else $myMessage .= "[".$parameter."] => ".$value."\n";
			}
			$myMessage .= "- Server reply with status code: ".$this->statusCode."\n";
			$myMessage .= "- Server returns (".$this->transport."): \n";
			$myMessage .= $this->toReturn;
			$myMessage .= "\n****** REQUEST END ******\n";

			try {
				if (!$fh = fopen(getcwd()."/../".TRANSACTION_TRACES_PATH.$this->logFile, 'a')) {
					throw new Exception('Could not open log file!');
				}
				if (!$fw = fwrite($fh, $myMessage)) {
					throw new Exception('Could not write log file!');
				}
				fclose($fh);
			}
			catch (Exception $e) {
				$this->debug($e);
			}

		}

	}

?>