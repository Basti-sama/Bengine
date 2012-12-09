<?php
/**
 * Sends email, if password or username has been forgotten.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Lost.php 19 2011-05-27 10:30:33Z secretchampion $
 */

require_once(APP_ROOT_DIR."app/code/Functions.inc.php");

class Bengine_Game_Account_Password_Lost extends Bengine_Game_Account_Ajax
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
	 * @var Recipe_Email_Template
	 */
	protected $message = null;

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
	 * @param string $username	Entered username
	 * @param string $email		Entered email address
	 *
	 * @return Bengine_Game_Account_Password_Lost
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
		if($result->rowCount() <= 0)
		{
			$this->printIt("EMAIL_NOT_FOUND");
		}
		$row = $result->fetchRow();
		$result->closeCursor();
		Core::getLanguage()->assign("username", $row["username"]);
		Core::getLanguage()->assign("ipaddress", IPADDRESS);
		Hook::event("LostPassword", array($this, &$row));

		if($mode == 0)
		{
			$this->message = new Recipe_Email_Template("lost_password_username");
		}
		else if(Str::compare($this->getUsername(), $row["username"]))
		{
			$reactivate = BASE_URL.Core::getLang()->getOpt("langcode")."/signup/activation/key:".$this->getSecurityKey();
			$url = BASE_URL.Core::getLang()->getOpt("langcode")."/password/set/key:".$this->getSecurityKey()."/user:".$row["userid"];
			Core::getTemplate()->assign("newPasswordUrl", $url);
			Core::getTemplate()->assign("reactivationUrl", $reactivate);
			$this->message = new Recipe_Email_Template("lost_password_password");
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
	 * @return Bengine_Game_Account_Password_Lost
	 */
	protected function setNewPw()
	{
		Core::getQuery()->update("user", array("activation" => $this->getSecurityKey()), "username = ?", array($this->getUsername()));
		return $this;
	}

	/**
	 * Sends email with lost password message.
	 *
	 * @param integer $mode
	 *
	 * @return Bengine_Game_Account_Password_Lost
	 */
	protected function sendMail($mode)
	{
		Hook::event("LostPasswordSendMail", array($this));
		$mail = new Email($this->getEmail(), Core::getLanguage()->getItem("LOST_PW_SUBJECT_".$mode));
		$this->getMessage()->send($mail);
		$this->printIt("PW_LOST_EMAIL_SUCCESS", false);
		return $this;
	}

	/**
	 * Prints either an error or success message.
	 *
	 * @param string $output	Output stream
	 * @param boolean $error	Message is error
	 *
	 * @return Bengine_Game_Account_Password_Lost
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
	 * @return Recipe_Email_Template
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