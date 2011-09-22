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
	 * @var String
	 */
	protected $message = "";

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
	protected $contentType = "text/plain";

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
	 * @param string	The receiver's mail address [optional]
	 * @param string	Mail subject [optional]
	 * @param string	The message [optional]
	 *
	 * @return void
	 */
	public function __construct($receiver = null, $subject = null, $message = null)
	{
		if($receiver !== null)
		{
			$this->setReceiver($receiver);
		}
		if($subject !== null)
		{
			$this->setSubject($subject);
		}
		if($message !== null)
		{
			$this->setMessage($message);
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
	 * @param string	EMail address
	 * @param boolean	Disable exceptions (default: true) [optional]
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
	 * @param boolean	Disable exceptions (default: true) [optional]
	 *
	 * @return Email
	 */
	public function sendMail($throwException = true)
	{
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
		$this->header = array_merge($this->header, array(
			"From" => $this->sender.(($this->senderMail != "") ? " <".$this->senderMail.">" : ""),
			"Reply-To" => $this->senderMail == "" ? "noreply@local" : $this->senderMail,
			"MIME-Version" => "1.0",
			"Content-Transfer-Encoding" => "8bit",
			"Content-Type" => $this->contentType."; charset=".$this->getCharset(),
		));
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
	 * @param string	\n\r, \n
	 *
	 * @return Email
	 */
	public function setHeaderSeparator($headerSeparator)
	{
		$this->headerSeparator = $headerSeparator;
		return $this->buildHeader();
	}

	/**
	 * Setter-method for subject.
	 *
	 * @param string
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
	 * @param string
	 *
	 * @return Email
	 */
	public function setMessage($message)
	{
		if(!($message instanceof String))
		{
			$message = new String($message);
		}
		$this->message = $message->trim()->regEx("#(\r\n|\r|\n)#", $this->headerSeparator);
		return $this;
	}

	/**
	 * Returns the mail message.
	 *
	 * @return String
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * Setter-method for sender name.
	 *
	 * @param string
	 *
	 * @return Email
	 */
	public function setSender($sender)
	{
		$this->sender = $sender;
		return $this->buildHeader();
	}

	/**
	 * Setter-method for sender mail address
	 *
	 * @param string
	 *
	 * @return Email
	 */
	public function setSenderMail($senderMail)
	{
		$this->senderMail = $senderMail;
		return $this->buildHeader();
	}

	/**
	 * Setter-method for receiver mail address.
	 *
	 * @param string|array
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
	 * @param string	text/plain or text/html
	 *
	 * @return Email
	 */
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;
		return $this->buildHeader();
	}

	/**
	 * Sets the email charset.
	 *
	 * @param string
	 *
	 * @return Email
	 */
	public function setCharset($charset)
	{
		$this->charset = $charset;
		return $this->buildHeader();
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
			$this->charset = Core::getLang()->getOpt("charset");
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
	 * @param string|Recipe_Email_Abstract	Email adapter
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
			throw new Exception("Unkown email adapter provided");
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
		return implode($this->headerSeparator, $_header).$this->headerSeparator;
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
}
?>