<?php
/**
 * Sends email, if password or username has been forgotten.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Lost.php 19 2011-05-27 10:30:33Z secretchampion $
 */

require_once(APP_ROOT_DIR."app/code/Functions.inc.php");

class Bengine_Account_Password_Lost extends Bengine_Page_Ajax_Abstract
{
	/**
	 * @var string
	 */
	protected $username = "";

	/**
	 * @var string
	 */
	protected $email = "";

	/**
	 * Email message which will be sent.
	 *
	 * @var string
	 */
	protected $message = "";

	/**
	 * Security key length.
	 *
	 * @var integer
	 */
	const SECURITY_KEY_LENGTH = 8;

	/**
	 * Security key transmitted as link.
	 * Serves as verification.
	 *
	 * @var string
	 */
	protected $secKey = "";

	/**
	 * Handles lost password requests.
	 *
	 * @param string	Entered username
	 * @param string	Entered email address
	 *
	 * @return void
	 */
	public function __construct($username, $email)
	{
		$this->username = $username;
		$this->email = $email;

		$mode = 1;
		if(!$this->getUsername())
		{
			$mode = 0;
		}

		if(!checkEmail($this->getEmail()))
		{
			$this->printIt("EMAIL_INVALID");
		}

		$result = Core::getQuery()->select("user", array("userid", "username"), "", "email = '".$this->getEmail()."'");
		if(Core::getDB()->num_rows($result) <= 0)
		{
			$this->printIt("EMAIL_NOT_FOUND");
		}
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		Core::getLanguage()->assign("req_username", $row["username"]);
		Core::getLanguage()->assign("req_ipaddress", IPADDRESS);
		Hook::event("LostPassword", array($this, &$row));

		if($mode == 0)
		{
			$this->message = Core::getLanguage()->getItem("REQUEST_USERNAME");
		}
		else if(Str::compare($this->getUsername(), $row["username"]))
		{
			$reactivate = BASE_URL.Core::getLang()->getOpt("langcode")."/signup/activation/key:".$this->getSecurityKey();
			$url = BASE_URL.Core::getLang()->getOpt("langcode")."/password/set/key:".$this->getSecurityKey()."/user:".$row["userid"];
			Core::getLanguage()->assign("new_pw_url", $url);
			Core::getLanguage()->assign("reactivate_url", $reactivate);
			$this->message = Core::getLanguage()->getItem("REQUEST_PASSWORD");
			$this->setNewPw();
		}
		else
		{
			$this->printIt("USERNAME_DOES_NOT_EXIST");
		}

		$this->sendMail($mode);
		return;
	}

	/**
	 * Saves the new password.
	 *
	 * @return Bengine_Account_Password_Lost
	 */
	protected function setNewPw()
	{
		Core::getQuery()->update("user", "activation", $this->getSecurityKey(), "username = '".$this->getUsername()."'");
		return $this;
	}

	/**
	 * Sends email with lost password message.
	 *
	 * @param integer	Mode
	 *
	 * @return Bengine_Account_Password_Lost
	 */
	protected function sendMail($mode)
	{
		Hook::event("LostPasswordSendMail", array($this));
		$mail = new Email($this->getEmail(), Core::getLanguage()->getItem("LOST_PW_SUBJECT_".$mode), $this->getMessage());
		$mail->sendMail();
		$this->printIt("PW_LOST_EMAIL_SUCCESS", false);
		return $this;
	}

	/**
	 * Prints either an error or success message.
	 *
	 * @param string	Output stream
	 * @param boolean	Message is error
	 *
	 * @return Bengine_Account_Password_Lost
	 */
	protected function printIt($output, $error = true)
	{
		if($error)
		{
			$this->display("<div class=\"error\">".Core::getLanguage()->getItem($output)."</div>");
		}
		$this->display("<div class=\"success\">".Core::getLanguage()->getItem($output)."</div><br />");
		return $this;
	}

 	/**
 	 * Returns the email address.
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Returns the username.
	 *
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Returns the mail message.
	 *
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * Returns the security key. If it's empty, generate it.
	 *
	 * @return string
	 */
	public function getSecurityKey()
	{
		if(empty($this->secKey))
		{
			$this->secKey = randString(self::SECURITY_KEY_LENGTH);
		}
		return $this->secKey;
	}
}
?>