<?php

/**
 * http.php
 * 
 * Send a http request (optionally with basic authentication support) to 
 * remote host using GET or POST methods and,
 * 
 * Request will be sent using curl (if available) or fsock's (fallback)
 *
 * @package		Comodojo ServerSide Core Packages
 * @author		comodojo.org
 * @copyright	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version		__CURRENT_VERSION__
 * @license		GPL Version 3
 */
 
class http {

/********************** PRIVATE VARS *********************/
	/**
	 * Remote host address (complete url)
	 */
	private $address = null;
	
	/**
	 * Remote host port
	 */
	private $port = 80;
	
	/**
	 * Conversation method (GET or POST)
	 */
	private $method = 'GET';
	
	/**
	 * Data to send to host, in unidimensional array form or simple string
	 * 
	 * Example:
	 * 
	 * ::data = Array('dateFrom'=>'2010-10-10','dateTo'=>'2010-10-20')
	 * 
	 * AND GET method will result in:
	 * 
	 * URL->[YOUR_ADDRESS]?dateFrom=2010-10-10&dateTo=2010-10-20
	 * 
	 * @param	ARRAY
	 */
	//private $data = false;
	
	/**
	 * Timeout for request, in seconds.
	 * 
	 * @param	INT	seconds
	 * @default	30
	 */
	private $timeout = 20;
	
	/**
	 * HTTP Version (1.0/1.1)
	 * 
	 * @param	STRING	
	 * @default	false	auto
	 */
	private $httpVersion = "1.0";
	
	/**
	 * Auth method to use. It currently support only:
	 * - BASIC
	 * - NTLM (only if CURL is available)
	 */
	private $authenticationMethod = "NONE";
	
	/**
	 * Remote host auth username
	 */
	private $userName = null;
	
	/**
	 * Remote host auth password
	 */
	private $userPass = null;

	/**
	 * Encoding of request
	 * PLEASE NOTE: this should be the same used in xmlRpcEncoder!
	 */
	//private $encoding = COMODOJO_DEFAULT_ENCODING;
	
	/**
	 * Request user agent
	 * 
	 * @param	STRING
	 * @default	Comodojo-core_1.0-beta
	 */
	private $userAgent = 'comodojo-core___CURRENT_VERSION__';
	
	/**
	 * Content type
	 * 
	 * @param	STRING
	 * @default	text/xml
	 */
	private $contentType = 'application/x-www-form-urlencoded';

	/**
	 * Allowed HTTP methods?
	 */
	private $allowed_http_methods = Array("GET","POST","PUT","DELETE");

	private $header = Array(
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Language: en-us,en;q=0.5',
		'Accept-Encoding: deflate',
		'Accept-Charset: UTF-8;q=0.7,*;q=0.7'
	);

	private $proxy = null;

	private $proxy_auth = null;

	/**
	 * Are we using curl?
	 */
	private $curl = true;
	
	/**
	 * Remote host 
	 * @var string
	 */
	private $remoteHost = null;

	/**
	 * Remote host path
	 * @var string
	 */
	private $remotePath = null;
	
	/**
	 * Remote query string
	 * @var string
	 */
	private $remoteQuery = null;

	private $receivedHeader = null;

	/**
	 * Transfer channel
	 * @var resource
	 */
	private $ch = false;
/********************** PRIVATE VARS *********************/
	
/********************* PUBLIC METHODS ********************/
	
	public final function __construct($address, $curl=true) {

		if (!$address) throw new Exception("Invalid remote host address", 1502);
		
		$curl = filter_var($curl, FILTER_VALIDATE_BOOLEAN);

		if (!function_exists("curl_init") OR !$curl) {
			$this->curl = false;
			$this->address = $address;
			$url = parse_url($address);
			$this->remoteHost = isset($url['host']) ? $url['host'] : '';
			$this->remotePath = isset($url['path']) ? $url['path'] : '';
			$this->remoteQuery = isset($url['query']) ? $url['query'] : '';
			comodojo_debug("http will use fsock (compatibility mode)","DEBUG","http");
		}
		else {
			$this->curl = true;
			$this->address = $address;
			comodojo_debug("http will use curl","DEBUG","http");
		}

	}

	public final function authentication($method, $user, $pass) {
		$method = strtoupper($method);
		//if (!in_array($method, Array("BASIC","NTLM")) OR empty($user) OR empty($pass)) {
		//	comodojo_debug("Unsupported authentication mode: ".$method,"ERROR","http");
		//	throw new Exception("Unsupported authentication mode", 1506);
		//}
		//if (!$this->curl AND $method=="NTLM") {
		//	comodojo_debug("NTLM auth with FSOCKS not supported","ERROR","http");
		//	throw new Exception("NTLM auth with FSOCKS not supported", 1505);
		//}
		$this->authenticationMethod = $method;
		$this->userName = $user;
		$this->userPass = $pass;
		comodojo_debug("Using auth method: ".$method,"DEBUG","http");
		return $this;
	}

	//public final function encoding($enc) {
	//	$this->encoding = $enc;
	//	comodojo_debug("Using encoding: ".$enc,"DEBUG","http");
	//	return $this;
	//}

	public final function userAgent($ua) {
		$this->userAgent = $ua;
		comodojo_debug("Using user agent: ".$ua,"DEBUG","http");
		return $this;	
	}

	public final function timeout($sec) {
		$time = filter_var($sec, FILTER_VALIDATE_INT);
		$this->timeout = $time;
		comodojo_debug("Timeout: ".$time,"DEBUG","http");
		return $this;
	}

	public final function httpVersion($ver) {
		if (!in_array($ver, Array("1.0","1.1"))) {
			$version = "NONE";
		}
		else {
			$version = $ver;
		}
		$this->httpVersion = $version;
		comodojo_debug("Using http version: ".$version,"DEBUG","http");
		return $this;
	}

	public final function contentType($type) {
		$this->contentType = $type;
		comodojo_debug("Using content type: ".$type,"DEBUG","http");
		return $this;
	}

	public final function port($port) {
		$port = filter_var($port, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range" => 65535, "default" => 80 )));
		comodojo_debug("Using port: ".$port,"DEBUG","http");
		return $this;
	}

	public final function httpMethod($method) {
		$method = strtoupper($method);
		//if (!in_array($method, $this->allowed_http_methods)) {
		//	throw new Exception("Unsupported HTTP method", 1507);
		//}
		$this->method = $method;
		comodojo_debug("Using method: ".$method,"DEBUG","http");
		return $this;
	}

	public final function header($head) {
		$this->header = !is_array($head) ? Array($head) : $head;
		comodojo_debug("Using header: ".$head,"DEBUG","http");
		return $this;
	}

	public final function proxy($address, $user=null, $pass=null) {
		$this->proxy = filter_var($address, FILTER_VALIDATE_URL);
		if ($user !== null AND $pass !== null) {
			$this->proxy_auth = $user.':'.$pass;
			comodojo_debug("Using proxy: ".$user."@".$address,"DEBUG","http");
		}
		else comodojo_debug("Using proxy: ".$address,"DEBUG","http");
		return $this;
	}

	public final function get_header() {
		return $this->receivedHeader;
	}

	/**
	 * Init transport and send data to the remote host.
	 * 
	 * @return	string	Received Data
	 */
	public function send($data = null) {
			
		try {
			$tosend = $this->init_transport($data);
		}
		catch (Exception $e) {
			throw $e;
		}

		comodojo_debug("Sending data to ".$this->address,"DEBUG","http");
		
		if ($this->curl) {
			$body = curl_exec($this->ch);
			if ($body === false) {
				comodojo_debug("Cannot exec http request, curl error: ".curl_errno($this->ch)." - ".curl_error($this->ch),"ERROR","http");
				throw new Exception("Cannot exec http request", 1504);
			}
			$header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
			$this->receivedHeader = substr($body, 0, $header_size);
			$received = substr($body, $header_size);
		}
		else {
			$body = '';
			$receiver = fwrite($this->ch, $tosend, strlen($tosend));
			if ($receiver === false) {
				comodojo_debug("Cannot exec http request, fwrite error.","ERROR","http");
				throw new Exception("Cannot exec http request", 1504);
			}
			while(!feof($this->ch)) $body .= fgets($this->ch,4096); 
			//while ($line = fgets($this->ch)) $received .= $line;
			//$received = substr($received, strpos($received, "\r\n\r\n") + 4);
			list($this->receivedHeader, $received) = preg_split("/\R\R/", $body, 2);
		}
		
		$this->close_transport();
		
		comodojo_debug("-------- Received data --------- ","DEBUG","http");
		comodojo_debug($received,"DEBUG","http");
		comodojo_debug("---------- End of data --------- ","DEBUG","http");
		
		return $received;
	}
	
	/**
	 * Reset the data channel for new request
	 * 
	 */
	public function reset() {
		$this->address = null;
		$this->port = 80;
		$this->method = 'GET';
		$this->data = false;
		$this->timeout = 20;
		$this->httpVersion = "1.0";
		$this->authenticationMethod = null;
		$this->userName = null;
		$this->userPass = null;
		$this->userAgent = 'comodojo-core___CURRENT_VERSION__';
		$this->contentType = 'application/x-www-form-urlencoded';
		$this->allowed_http_methods = Array("GET","POST","PUT","DELETE");
		$this->header = Array(
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'Accept-Language: en-us,en;q=0.5',
			'Accept-Encoding: gzip, deflate',
			'Accept-Charset: UTF-8;q=0.7,*;q=0.7'
		);
		$this->curl = true;
		$this->remoteHost = null;
		$this->remotePath = null;
		$this->remoteQuery = null;
		$this->ch = false;
	}
/********************* PUBLIC METHODS ********************/

/********************* PRIVATE METHODS *******************/
	
	/**
	 * Initialize transport layer
	 */
	private function init_transport($data) {
		
		if (!in_array($this->authenticationMethod, Array("BASIC","NTLM","NONE"))) {
			comodojo_debug("Unsupported authentication mode: ".$this->authenticationMethod,"ERROR","http");
			throw new Exception("Unsupported authentication mode", 1506);
		}

		if (!in_array($this->method, $this->allowed_http_methods)) {
			comodojo_debug("Unsupported HTTP method: ".$this->method,"ERROR","http");
			throw new Exception("Unsupported HTTP method", 1507);
		}

		if ($this->curl) {

			$this->ch = curl_init();
			
			if (!$this->ch) {
				comodojo_debug("Cannot init data channel","ERROR","http");
				throw new Exception("Cannot init data channel", 1501);
			}

			switch ($this->httpVersion) {
				case '1.0':
					curl_setopt($this->ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
					break;
				case '1.1':
					curl_setopt($this->ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
					break;
				default:
					curl_setopt($this->ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_NONE);
					break;
			}

			switch ($this->authenticationMethod) {
				case 'BASIC':
					curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
					curl_setopt($this->ch, CURLOPT_USERPWD, $this->userName.":".$this->userPass); 
					break;
				case 'NTLM':
					curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
					curl_setopt($this->ch, CURLOPT_USERPWD, $this->userName.":".$this->userPass); 
					break;
			}

			if (!is_null($this->proxy)) {
				curl_setopt($this->ch, CURLOPT_PROXY, $this->proxy);
				if (!is_null($this->proxy_auth)) curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $this->proxy_auth);
			}

			switch ($this->method) {
				case 'GET':
					curl_setopt($this->ch, CURLOPT_URL, $this->address.'?'.http_build_query($data));
					break;
				case 'PUT':
					curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
					if (!empty($data)) {
						curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));
						array_push($this->header, "Content-Type: ".$this->contentType);
					}
					curl_setopt($this->ch, CURLOPT_URL, $this->address);
					break;
				case 'POST':
					curl_setopt($this->ch, CURLOPT_POST, true);
					if (!empty($data)) {
						curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
						array_push($this->header, "Content-Type: ".$this->contentType);
					}
					curl_setopt($this->ch, CURLOPT_URL, $this->address);
					break;
				case 'DELETE':
					curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
					curl_setopt($this->ch, CURLOPT_URL, $this->address);
					break;
			}

			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
			curl_setopt($this->ch, CURLOPT_PORT, $this->port);
			curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->header);
			curl_setopt($this->ch, CURLOPT_HEADER, 1);

			$to_return = null;

		}
		else {

			if ($this->authenticationMethod == 'NTLM') {
				comodojo_debug("NTLM auth with FSOCKS not supported","ERROR","http");
				throw new Exception("NTLM auth with FSOCKS not supported", 1505);
			}

			comodojo_debug("Using http requests in compatible mode. Some features will not be available","WARNING","http");

			$crlf = "\r\n";

			$header  = $this->method.' '.$this->remotePath.$this->remoteQuery.' HTTP/'.$this->httpVersion.$crlf;
			$header .= "User-Agent: ".$this->userAgent.$crlf;
			$header .= "Host: ".$this->remoteHost.$crlf;

			if ($this->authenticationMethod == "BASIC") $header .= "Authorization: Basic ".base64_encode($this->userName.":".$this->userPass).$crlf;
			
			if ($this->proxy_auth !== null) $header .= "Proxy-Authorization: Basic ".base64_encode($this->proxy_auth).$crlf;

			foreach ($this->header as $head) {
				$header .= $head.$crlf;
			}

			if (!empty($data)) {
				//$data = urlencode($data);
				$header .= "Content-Type: ".$this->contentType.$crlf;
				$header .= "Content-Length: ".strlen($data).$crlf.$crlf;
				$to_return = $header.$data;
			}
			else {
				$to_return = $header.$crlf;
			}

			if (is_null($this->proxy)) {
				$this->ch = fsockopen($this->remoteHost, $this->port, $errno, $errstr, $this->timeout);
			}
			else {
				$proxy = parse_url($this->proxy);
				$proxy_host = $url['host'];
				$proxy_port = isset($url['port']) ? $url['port'] : '80';
				$this->ch = fsockopen($proxy_host, $proxy_port, $errno, $errstr, $this->timeout);	
			}

			stream_set_timeout($this->ch, $this->timeout); 
			
			if (!$this->ch) {
				comodojo_debug("Cannot init data channel, fsock error: ".$errno." - ".$errstr,"ERROR","http");
				throw new Exception("Cannot init data channel", 1501);
			}

		}

		comodojo_debug("------ Ready to send data ------ ","DEBUG","http");
		comodojo_debug($data,"DEBUG","http");
		comodojo_debug("---------- End of data --------- ","DEBUG","http");

		return $to_return;

	}


	/**
	 * Close transport layer
	 */
	private function close_transport() {
		if ($this->curl) {
			curl_close($this->ch);
		}
		else {
			fclose($this->ch);
		}
	}
/********************* PRIVATE METHODS *******************/

}

?>