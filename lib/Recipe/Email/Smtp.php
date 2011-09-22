<?php
/**
 * Email smtp adapter.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2011, Sebastian Noll
 * @license Proprietary
 * @version $Id$
 */
class Recipe_Email_Smtp extends Recipe_Email_Abstract
{
	/**
	 * Server timeout.
	 *
	 * @var integer
	 */
	const SMTP_TIMEOUT = 10;

	/**
	 * End of line.
	 *
	 * @var string
	 */
	const EOL = "\r\n";

	/**
	 * Host of the server.
	 *
	 * @var string
	 */
	protected $host = "";

	/**
	 * SMTP user.
	 *
	 * @var string
	 */
	protected $user = "";

	/**
	 * SMTP password.
	 *
	 * @var string
	 */
	protected $pwd = "";

	/**
	 * SMTP auth type.
	 *
	 * @var string
	 */
	protected $auth = "";

	/**
	 * Server port.
	 *
	 * @var integer
	 */
	protected $port = 0;

	/**
	 * Indicates that a session is requested to be secure.
	 *
	 * @var string
	 */
	protected $secure = "none";

	/**
	 * Sock handle.
	 *
	 * @var resource
	 */
	protected $_socket = null;

	/**
	 * The last server response.
	 *
	 * @var array
	 */
	protected $_response = array();

	/**
	 * Holds the connection logging.
	 *
	 * @var array
	 */
	protected $log = "";

	/**
	 * Types of authetification.
	 *
	 * @var array
	 */
	protected $authTypes = array(
		"LOGIN",
		"PLAIN",
		"CRAM-MD5"
	);

	/**
	 * Initializing method.
	 *
	 * @return Recipe_Email_Smtp
	 */
	protected function _init()
	{
		$options = Core::getOptions();
		$this->setHost($options->get("MAIL_SMTP_HOST"))
			->setPort($options->get("MAIL_SMTP_PORT"))
			->setUser($options->get("MAIL_SMTP_USER"))
			->setPwd($options->get("MAIL_SMTP_PWD"))
			->setAuth($options->get("MAIL_SMTP_AUTH"))
			->setSecure($options->get("MAIL_SMTP_SECURE"));
		return $this;
	}

	/**
	 * Smtp logic.
	 *
	 * @return Recipe_Email_Smtp
	 */
	protected function _send()
	{
		$this->_connect();
		$this->_helo();
		$this->_auth();
		$this->_from();
		$this->_to();
		$this->_data();
		$this->_quit();
		$this->_disconnect();
		return $this;
	}

	/**
	 * Connect to the server using the supplied transport and target
	 *
	 * @return boolean
	 */
	protected function _connect()
	{
		$protocol = $this->getSecure() == "ssl" ? "ssl" : "tcp";
		$remote = $protocol."://".$this->getHost().":".$this->getPort();

		$errorNum = 0;
		$errorStr = "";

		$this->_socket = @stream_socket_client($remote, $errorNum, $errorStr, self::SMTP_TIMEOUT);

		if($this->_socket === false)
		{
			if($errorNum == 0)
				$errorStr = "Could not open socket.";
			throw new Recipe_Exception_Generic($errorStr);
		}

		if(($result = stream_set_timeout($this->_socket, self::SMTP_TIMEOUT)) === false)
		{
			throw new Recipe_Exception_Generic("Could not set stream timeout.");
		}

		return $result;
	}

	/**
	 * Send the given request followed by a LINEEND to the server.
	 *
	 * @param  string $request
	 * @return integer
	 */
	protected function _transmit($request)
	{
		$this->_request = $request;
		$result = fwrite($this->_socket, $request.self::EOL);

		// Save request to internal log
		$this->log("send", $request);

		if($result === false)
			throw new Recipe_Exception_Generic("Could not send request to ".$this->getHost().".");

		return $result;
	}

	/**
	 * Initiate HELO/EHLO sequence and set flag to indicate valid smtp session
	 *
	 * @return Recipe_Email_Smtp
	 */
	protected function _helo()
	{
		$this->_expect(220);
		$this->_ehlo();
   		if($this->getSecure() == "tls")
		{
			$this->_transmit("STARTTLS");
			$this->_expect(220, 180);
			if(!stream_socket_enable_crypto($this->_socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT))
			{
				throw new Recipe_Exception_Generic("Unable to connect via TLS.");
			}
			$this->_ehlo();
		}
		return $this;
	}

	/**
	 * Send EHLO or HELO depending on capabilities of smtp host
	 *
	 * @return Recipe_Email_Smtp
	 */
	protected function _ehlo()
	{
		$host = $this->getHost();

		try {
			$this->_transmit("EHLO ".$host);
			$this->_expect(250);
		} catch(Exception $e) {
			$this->_transmit("HELO ".$host);
			$this->_expect(250);
		}
		return $this;
	}

	/**
	 * Perform authentication with supplied credentials.
	 *
	 * @return Recipe_Email_Smtp
	 */
	protected function _auth()
	{
		switch($this->getAuth())
		{
			case "LOGIN":
				$this->_transmit("AUTH LOGIN");
				$this->_expect(334);
				$this->_transmit(base64_encode($this->getUser()));
				$this->_expect(334);
				$this->_transmit(base64_encode($this->getPwd()));
				$this->_expect(235);
			break;
			case "PLAIN":
				$this->_transmit("AUTH PLAIN");
				$this->_expect(334);
				$this->_transmit(base64_encode("\0".$this->getUser()."\0".$this->getPwd()));
				$this->_expect(235);
	   		break;
			case "CRAM-MD5":
				$this->_transmit("AUTH CRAM-MD5");
				$challenge = $this->_expect(334);
				$challenge = base64_decode($challenge);
				$digest = $this->_hmacMd5($this->getPwd(), $challenge);
				$this->_transmit(base64_encode($this->getUser()." ".$digest));
				$this->_expect(235);
			break;
		}
		return $this;
	}

	/**
	 * Prepare CRAM-MD5 response to server's ticket.
	 *
	 * @param  string $key
	 * @param  string $data
	 * @param  string $block
	 * @return string
	 */
	protected function _hmacMd5($key, $data, $block = 64)
	{
		if(strlen($key) > 64)
			$key = pack("H32", md5($key));
		elseif(strlen($key) < 64)
			$key = str_pad($key, $block, "\0");

		$k_ipad = substr($key, 0, 64) ^ str_repeat(chr(0x36), 64);
		$k_opad = substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64);

		$inner = pack("H32", md5($k_ipad . $data));
		$digest = md5($k_opad . $inner);

		return $digest;
	}

	/**
	 * Issues MAIL command.
	 *
	 * @return Recipe_Email_Smtp
	 */
	protected function _from()
	{
		$this->_transmit("MAIL FROM:<".$this->getEmail()->getSenderMail().">");
		$this->_expect(250, 300);
		return $this;
	}

	/**
	 * Issues RCPT command.
	 *
	 * @return Recipe_Email_Smtp
	 */
	protected function _to()
	{
		$this->_transmit("RCPT TO:<".$this->getEmail()->getReceiver().">");
		$this->_expect(array(250, 251), 300);
		return $this;
	}

	/**
	 * Issues DATA command.
	 *
	 * @return Recipe_Email_Smtp
	 */
	protected function _data()
	{
		$this->_transmit("DATA");
		$this->_expect(354, 120);

		$this->_prepareHeader();

		$data = $this->getEmail()->getRawHeader()."\n".$this->getEmail()->getMessage();
		foreach(explode("\n", $data) as $line)
		{
			if(strpos($line, ".") === 0)
			{
				$line = ".".$line;
			}
			$this->_transmit($line);
		}

		$this->_transmit(".");
		$this->_expect(250, 600);
		return $this;
	}

	/**
	 * Parse server response for successful codes.
	 *
	 * @param string|array $code
	 * @param integer $timeout
	 * @return string
	 */
	protected function _expect($code, $timeout = 300)
	{
		$this->_response = array();
		$cmd  = "";
		$more = "";
		$msg  = "";
		$errMsg = "";

		if(!is_array($code))
		{
			$code = array($code);
		}

		do {
			$this->_response[] = $result = $this->_receive($timeout);
			list($cmd, $more, $msg) = preg_split("/([\s-]+)/", $result, 2, PREG_SPLIT_DELIM_CAPTURE);

			if($errMsg !== "")
			{
				$errMsg .= " ".$msg;
			}
			elseif($cmd === null || !in_array($cmd, $code))
			{
				$errMsg = $msg;
			}
		} while(strpos($more, "-") === 0);

		if($errMsg !== "")
		{
		   throw new Recipe_Exception_Generic($errMsg." ($cmd)");
		}

		return $msg;
	}

	/**
	 * Get a line from the stream.
	 *
	 * @var	integer $timeout Per-request timeout value if applicable
	 * @return string
	 */
	protected function _receive($timeout = null)
	{
		if($timeout !== null)
		{
			stream_set_timeout($this->_socket, $timeout);
		}

		$reponse = fgets($this->_socket, 1024);

		$this->log("receive", $reponse);
		$info = stream_get_meta_data($this->_socket);
		if(!empty($info["timed_out"]))
		{
			throw new Recipe_Exception_Generic($this->getHost()." has timed out.");
		}
		if($reponse === false)
		{
			throw new Recipe_Exception_Generic("Could not read from ".$this->getHost().".");
		}
		return $reponse;
	}

	/**
	 * Issues the QUIT command and clears the current session-
	 *
	 * @return Recipe_Email_Smtp
	 */
	protected function _quit()
	{
		$this->_transmit("QUIT");
		$this->_expect(221, 300);
		return $this;
	}

	/**
	 * Close socket connection.
	 *
	 * @return Recipe_Email_Smtp
	 */
	protected function _disconnect()
	{
		fclose($this->_socket);
		return $this;
	}

	/**
	 * Takes care of header.
	 *
	 * @return Recipe_Email_Smtp
	 */
	protected function _prepareHeader()
	{
		$email = $this->getEmail();
		$email->setHeaderValue("To", $email->getFormattedReceiver());
		$email->setHeaderValue("Subject", $email->getSubject());
		return $this;
	}

	/**
	 * Saves all request to the server and their responses into $this->log.
	 *
	 * @param string $type
	 * @param string $str
	 * @return Recipe_Email_Smtp
	 */
	protected function log($type, $str)
	{
		$this->log[] = array(strtoupper($type) => $str);
		return $this;
	}

	/**
	 * Prints out all requests to the server and their responses
	 *
	 * @return array
	 */
	public function getLog()
	{
		return $this->log;
	}

	/**
	 * Returns the host.
	 *
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Sets the host.
	 *
	 * @param string $host
	 * @return Recipe_Email_Smtp
	 */
	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}

	/**
	 * Returns the server port.
	 *
	 * @return integer
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * Sets the server port.
	 *
	 * @param integer $port
	 * @return Recipe_Email_Smtp
	 */
	public function setPort($port)
	{
		$this->port = (int) $port;
		return $this;
	}

	/**
	 * Returns the user.
	 *
	 * @return string
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Returns the password.
	 *
	 * @return string
	 */
	public function getPwd()
	{
		return $this->pwd;
	}

	/**
	 * Returns the auth type.
	 *
	 * @return string
	 */
	public function getAuth()
	{
		return $this->auth;
	}

	/**
	 * Returns the session security type.
	 *
	 * @return string
	 */
	public function getSecure()
	{
		return $this->secure;
	}

	/**
	 * Sets the user.
	 *
	 * @param string $user
	 * @return Recipe_Email_Smtp
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * Sets the password.
	 *
	 * @param string $pwd
	 * @return Recipe_Email_Smtp
	 */
	public function setPwd($pwd)
	{
		$this->pwd = $pwd;
		return $this;
	}

	/**
	 * Sets the auth type.
	 *
	 * @param string $auth
	 * @return Recipe_Email_Smtp
	 */
	public function setAuth($auth)
	{
		$this->auth = $auth;
		return $this;
	}

	/**
	 * Sets the session security type.
	 *
	 * @param string $secure
	 * @return Recipe_Email_Smtp
	 */
	public function setSecure($secure = "tls")
	{
		$this->secure = $secure;
		return $this;
	}
}