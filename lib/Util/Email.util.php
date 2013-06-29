<?php
/**
 * Automatic mailer.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Email.util.php 35 2011-07-16 19:27:58Z secretchampion $
 */

class Email
{
	/**
	 * Default email adapter.
	 *
	 * @var string
	 */
	const DEFAULT_ADAPTER = "Sendmail";

	/**
	 * @var string
	 */
	const PLAIN_MIME_TYPE = "text/plain";

	/**
	 * @var string
	 */
	const HTML_MIME_TYPE = "text/html";

	/**
	 * @var string
	 */
	const MULTIPART_MIME_TYPE = "multipart/alternative";

	/**
	 * The receiver.
	 *
	 * @var string
	 */
	protected $receiver = "";

	/**
	 * The receiver's name.
	 *
	 * @var string
	 */
	protected $receiverName = "";

	/**
	 * The subject.
	 *
	 * @var string
	 */
	protected $subject = "";

	/**
	 * Mail body.
	 *
	 * @var array
	 */
	protected $messages = array();

	/**
	 * Sender name.
	 *
	 * @var string
	 */
	protected $sender = "";

	/**
	 * Sender mail address.
	 *
	 * @var string
	 */
	protected $senderMail = "";

	/**
	 * Email header.
	 *
	 * @var array
	 */
	protected $header = array();

	/**
	 * Content type.
	 *
	 * @var string
	 */
	protected $contentType = self::PLAIN_MIME_TYPE;

	/**
	 * Multipart boundary template.
	 *
	 * @var string
	 */
	protected $mimeBoundary = "==Multipart_Boundary_x%sx";

	/**
	 * Separates email header fields.
	 *
	 * @var string
	 */
	protected $headerSeparator = "\n";

	/**
	 * Holds the mail transmission status.
	 *
	 * @var boolean
	 */
	protected $success = null;

	/**
	 * Mail charset.
	 *
	 * @var string
	 */
	protected $charset = null;

	/**
	 * Email adapter.
	 *
	 * @var Recipe_Email_Abstract
	 */
	protected $adapter = null;

	/**
	 * Constructor: Builds the header and starts mailer.
	 *
	 * @param string $receiver	The receiver's mail address [optional]
	 * @param string $subject	Mail subject [optional]
	 * @param string $messages	The message [optional]
	 *
	 * @return \Email
	 */
	public function __construct($receiver = null, $subject = null, $messages = null)
	{
		if($receiver !== null)
		{
			$this->setReceiver($receiver);
		}
		if($subject !== null)
		{
			$this->setSubject($subject);
		}
		if($messages !== null)
		{
			$this->setMessages($messages);
		}
		$options = Core::getOptions();
		$senderName = $options->get("MAIL_SENDER_NAME");
		if(!$senderName)
			$senderName = $options->get("pagetitle");
		$this->setSender($senderName);
		$this->setSenderMail($options->get("MAIL_SENDER"));
		$this->setAdapter($options->get("MAIL_ADAPTER"));
		return;
	}

	/**
	 * Checks if mail address is valid.
	 *
	 * @param string $mail				EMail address
	 * @param boolean $throwException	Disable exceptions (default: true) [optional]
	 *
	 * @return boolean
	 */
	protected function isMail($mail, $throwException = true)
	{
		if(!preg_match("#^[a-zA-Z0-9-]+([._a-zA-Z0-9.-]+)*@[a-zA-Z0-9.-]+\.([a-zA-Z]{2,4})$#is", $mail))
		{
			$this->success = false;
			if($throwException)
			{
				throw new Recipe_Exception_Issue("NO_VALID_EMAIL_ADDRESS");
			}
			return false;
		}
		return true;
	}

	/**
	 * Sends mail.
	 *
	 * @param boolean $throwException	Disable exceptions (default: true) [optional]
	 *
	 * @return Email
	 */
	public function sendMail($throwException = true)
	{
		$this->buildHeader();
		Hook::event("SendMail", array($this));
		try {
			$this->getAdapter()->send();
			$this->success = true;
		} catch(Exception $e) {
			if($throwException)
				throw $e;
			else
				$this->success = false;
		}
		return $this;
	}

	/**
	 * Builds the mail header.
	 *
	 * @return Email
	 */
	protected function buildHeader()
	{
		$header = array(
			"From" => $this->sender.(($this->senderMail != "") ? " <".$this->senderMail.">" : ""),
			"Reply-To" => $this->senderMail == "" ? "noreply@local" : $this->senderMail,
			"MIME-Version" => "1.0",
		);
		$contentType = $this->contentType;
		if(1 < count($this->messages))
		{
			$contentType = self::MULTIPART_MIME_TYPE.";";
			$this->generateBoundary();
			$contentType .= $this->headerSeparator." boundary=\"{$this->mimeBoundary}\"";
		}
		else
		{
			$contentType .= "; charset=".$this->getCharset();
			$header["Content-Transfer-Encoding"] = "8bit";
		}
		$header["Content-Type"] = $contentType;
		$this->header = array_merge($this->header, $header);
		return $this;
	}

	/**
	 * Returns if send process was successful.
	 *
	 * @return boolean
	 */
	public function getSuccess()
	{
		return (bool) $this->success;
	}

	/**
	 * Setter-method for mail header separator.
	 *
	 * @param string $headerSeparator	\n\r, \n
	 *
	 * @return Email
	 */
	public function setHeaderSeparator($headerSeparator)
	{
		$this->headerSeparator = $headerSeparator;
		return $this;
	}

	/**
	 * Setter-method for subject.
	 *
	 * @param string $subject
	 *
	 * @return Email
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * Returns the subject.
	 *
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * Setter-method for mail message.
	 *
	 * @param string|array $messages
	 *
	 * @return Email
	 */
	public function setMessages($messages)
	{
		if(is_array($messages))
		{
			if(!empty($messages["html"]))
			{
				$this->setHtmlMessage($messages["html"]);
			}
			if(!empty($messages["plain"]))
			{
				$this->setPlainMessage($messages["plain"]);
			}
		}
		else
		{
			$this->setPlainMessage($messages);
		}
		return $this;
	}

	/**
	 * Sets the html message.
	 *
	 * @param string $message
	 * @return Email
	 */
	public function setHtmlMessage($message)
	{
		if(!($message instanceof String))
		{
			$message = new String($message);
		}
		$this->messages["html"] = $message->trim()->regEx("#(\r\n|\r|\n)#", $this->headerSeparator);
		return $this;
	}

	/**
	 * Returns the html message.
	 *
	 * @return string
	 */
	public function getHtmlMessage()
	{
		return isset($this->messages["html"]) ? $this->messages["html"] : null;
	}

	/**
	 * Sets the plain message.
	 *
	 * @param string $message
	 * @return Email
	 */
	public function setPlainMessage($message)
	{
		if(!($message instanceof String))
		{
			$message = new String($message);
		}
		$this->messages["plain"] = $message->trim()->regEx("#(\r\n|\r|\n)#", $this->headerSeparator);
		return $this;
	}

	/**
	 * Returns the plain message.
	 *
	 * @return string
	 */
	public function getPlainMessage()
	{
		return isset($this->messages["plain"]) ? $this->messages["plain"] : null;
	}

	/**
	 * Returns the mail message.
	 *
	 * @param boolean $asArray
	 * @return array|string
	 */
	public function getMessages($asArray = false)
	{
		if($asArray)
		{
			return $this->messages;
		}
		$message = "";
		$plain = $this->getPlainMessage();
		$html = $this->getHtmlMessage();
		if(!empty($plain) && !empty($html))
		{
			$doubleHeaderSeparator = str_repeat($this->headerSeparator, 2);
			$message  = "--{$this->mimeBoundary}".$this->headerSeparator;
			$message .= "Content-Type: ".self::PLAIN_MIME_TYPE."; charset={$this->getCharset()}".$this->headerSeparator;
			$message .= "Content-Transfer-Encoding: quoted-printable".$doubleHeaderSeparator;
			$message .= $plain.str_repeat($this->headerSeparator, 2);
			$message .= "--{$this->mimeBoundary}".$this->headerSeparator;
			$message .= "Content-Type: ".self::HTML_MIME_TYPE."; charset={$this->getCharset()}".$this->headerSeparator;
			$message .= "Content-Transfer-Encoding: quoted-printable".$doubleHeaderSeparator;
			$message .= $html.$doubleHeaderSeparator;
			$message .= "--{$this->mimeBoundary}--";
		}
		else if(!empty($plain))
		{
			$message = $plain;
		}
		else if(!empty($html))
		{
			$message = $html;
		}
		return $message;
	}

	/**
	 * Setter-method for sender name.
	 *
	 * @param string $sender
	 *
	 * @return Email
	 */
	public function setSender($sender)
	{
		$this->sender = $sender;
		return $this;
	}

	/**
	 * Setter-method for sender mail address
	 *
	 * @param string $senderMail
	 *
	 * @return Email
	 */
	public function setSenderMail($senderMail)
	{
		$this->senderMail = $senderMail;
		return $this;
	}

	/**
	 * Setter-method for receiver mail address.
	 *
	 * @param string|array $receiver
	 *
	 * @return Email
	 */
	public function setReceiver($receiver)
	{
		if(is_array($receiver))
		{
			if(isset($receiver["name"]) && isset($receiver["mail"]))
			{
				$this->setReceiverName($receiver["name"]);
				$receiver = $receiver["mail"];
			}
			else
			{
				$this->setReceiverName(current($receiver));
				$receiver = key($receiver);
			}
		}
		$this->isMail($receiver);
		$this->receiver = $receiver;
		return $this;
	}

	/**
	 * Returns the receiver.
	 *
	 * @return string
	 */
	public function getReceiver()
	{
		return $this->receiver;
	}

	/**
	 * Setter-method for the content type.
	 *
	 * @param string $contentType	text/plain or text/html
	 *
	 * @return Email
	 */
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;
		return $this;
	}

	/**
	 * Sets the email charset.
	 *
	 * @param string $charset
	 *
	 * @return Email
	 */
	public function setCharset($charset)
	{
		$this->charset = $charset;
		return $this;
	}

	/**
	 * Returns the sender name.
	 *
	 * @return string
	 */
	public function getSender()
	{
		return $this->sender;
	}

	/**
	 * Returns sender mail address.
	 *
	 * @return string
	 */
	public function getSenderMail()
	{
		return $this->senderMail;
	}

	/**
	 * Returns email charset.
	 *
	 * @return string
	 */
	public function getCharset()
	{
		if(is_null($this->charset))
		{
			$this->charset = CHARACTER_SET;
		}
		return $this->charset;
	}

	/**
	 * Returns the email adapter.
	 *
	 * @return Recipe_Email_Abstract
	 */
	public function getAdapter()
	{
		if($this->adapter === null)
		{
			$this->setAdapter(self::DEFAULT_ADAPTER);
		}
		return $this->adapter;
	}

	/**
	 * Sets the email adapter.
	 *
	 * @param string|Recipe_Email_Abstract $adapter		Email adapter
	 *
	 * @return Email
	 */
	public function setAdapter($adapter)
	{
		if(is_string($adapter))
		{
			$class = "Recipe_Email_".$adapter;
			$adapter = new $class($this);
		}
		if(!($adapter instanceof Recipe_Email_Abstract))
		{
			throw new Exception("Unknown email adapter provided");
		}
		$this->adapter = $adapter;
		return $this;
	}

	/**
	 * Generates the raw header string
	 *
	 * @return string
	 */
	public function getRawHeader()
	{
		$_header = array();
		foreach($this->header as $key => $value)
		{
			$_header[] = $key.": ".$value;
		}
		return implode($this->headerSeparator, $_header);
	}

	/**
	 * Sets the header values.
	 *
	 * @param string|array $key
	 * @param string $value
	 *
	 * @return Email
	 */
	public function setHeaderValue($key, $value = null)
	{
		if(is_array($key))
		{
			foreach($key as $_key => $_val)
			{
				$this->setHeader($_key, $_val);
			}
			return $this;
		}
		$this->header[$key] = $value;
		return $this;
	}

	/**
	 * Replaces the header.
	 *
	 * @param array $header
	 *
	 * @return Email
	 */
	public function setHeader(array $header)
	{
		$this->header = $header;
		return $this;
	}

	/**
	 * Returns the header array.
	 *
	 * @return array
	 */
	public function getHeader()
	{
		return $this->header;
	}

	/**
	 * Sets the receiver's name.
	 *
	 * @param string $receiverName
	 * @return Email
	 */
	public function setReceiverName($receiverName)
	{
		$this->receiverName = $receiverName;
		return $this;
	}

	/**
	 * Returns the receiver's name.
	 *
	 * @return string
	 */
	public function getReceiverName()
	{
		return $this->receiverName;
	}

	/**
	 * Returns the formatted receiver.
	 *
	 * @return string
	 */
	public function getFormattedReceiver()
	{
		$receiverName = $this->getReceiverName();
		if(!empty($receiverName))
		{
			return sprintf("%s <%s>", $receiverName, $this->getReceiver());
		}
		return $this->getReceiver();
	}

	/**
	 * Generates the multipart boundary.
	 *
	 * @return Email
	 */
	protected function generateBoundary()
	{
		if(strpos($this->mimeBoundary, "%") !== false)
		{
			$randomString = md5(uniqid());
			$this->mimeBoundary = sprintf($this->mimeBoundary, $randomString);
		}
		return $this;
	}

	/**
	 * Returns the header separator.
	 *
	 * @return string
	 */
	public function getHeaderSeparator()
	{
		return $this->headerSeparator;
	}
}
?>