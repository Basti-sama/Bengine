<?php
/**
 * Create accounts.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Creator.php 35 2011-07-16 19:27:58Z secretchampion $
 */

require_once(APP_ROOT_DIR."app/code/Functions.inc.php");

class Bengine_Game_Account_Creator extends Bengine_Game_Account_Ajax
{
	/**
	 * User group id of administrators.
	 *
	 * @var integer
	 */
	const ADMIN_USER_GROUP_ID = 2;

	/**
	 * Requested username.
	 *
	 * @var string
	 */
	protected $username = "";

	/**
	 * Entered password.
	 *
	 * @var string
	 */
	protected $password = "";

	/**
	 * Entered email address.
	 *
	 * @var string
	 */
	protected $email = "";

	/**
	 * Language id.
	 *
	 * @var integer
	 */
	protected $lang = 0;

	/**
	 * Activation key.
	 *
	 * @var string
	 */
	protected $activation = "";

	/**
	 * Total number of registered users.
	 *
	 * @var integer
	 */
	protected $totalUser = null;

	/**
	 * The user ID.
	 *
	 * @var integer
	 */
	protected $userId = 0;

	/**
	 * Planet creator object.
	 *
	 * @var Bengine_Game_Planet_Creator
	 */
	protected $planetCreator = null;

	/**
	 * Length of the generated activation key.
	 *
	 * @var integer
	 */
	const ACTIVATION_KEY_LENGTH = 8;

	/**
	 * Starts account factory.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $email
	 * @param integer $lang
	 *
	 * @return Bengine_Game_Account_Creator
	 */
	public function __construct($username, $password, $email, $lang)
	{
		$this->username = trim(str_replace("  ", " ", $username));
		$this->password = $password;
		$this->email = $email;
		$this->lang = (!is_numeric($lang)) ? Core::getOptions()->defaultlanguage : $lang;
		return;
	}

	/**
	 * Loads the total number of all registered users.
	 *
	 * @return Bengine_Game_Account_Creator
	 */
	protected function loadTotalUser()
	{
		$result = Core::getQuery()->select("user", array("userid"));
		$this->totalUser = $result->rowCount();
		return $this;
	}

	/**
	 * Checks the entered data for validation.
	 *
	 * @return Bengine_Game_Account_Creator
	 */
	protected function checkIt()
	{
		Hook::event("UserRegistrationCheckInput", array($this));
		$error = array();
		if(Core::getConfig()->get("REGISTRATION_DISABLED"))
		{
			$error[] = "REGISTRATION_CLOSED";
		}
		if(Core::getConfig()->get("MAX_ALLOWED_USER") > 0 && Core::getConfig()->get("MAX_ALLOWED_USER") <= $this->getTotalUser())
		{
			$error[] = "MAX_USER_REACHED";
		}
		$checkTime = TIME - Core::getOptions()->get("WATING_TIME_REGISTRATION") * 60;
		$result = Core::getQuery()->select("registration", array("time"), "", Core::getDB()->quoteInto("ipaddress = '".IPADDRESS."' AND time >= ?", $checkTime));
		if($row = $result->fetchRow())
		{
			$minutes = ceil(($row["time"] - $checkTime) / 60);
			Core::getLang()->assign("minutes", $minutes);
			$error[] = "REGISTRATION_BANNED_FOR_IP";
		}
		$result->closeCursor();
		if(!checkCharacters($this->getUsername()))
		{
			$error[] = "USERNAME_INVALID";
		}
		if(!checkEmail($this->getEmail()))
		{
			$error[] = "EMAIL_INVALID";
		}
		if(Str::length($this->getPassword()) < Core::getOptions()->get("MIN_PASSWORD_LENGTH") || Str::length($this->getPassword()) > Core::getOptions()->get("MAX_PASSWORD_LENGTH"))
		{
			$error[] = "PASSWORD_INVALID";
		}
		$where  = Core::getDB()->quoteInto("username = ?", $this->getUsername());
		$where .= Core::getDB()->quoteInto(" OR email = ?", $this->getEmail());
		$result = Core::getQuery()->select("user", array("username", "email"), "", $where);
		if($row = $result->fetchRow())
		{
			if(Str::compare($this->getUsername(), $row["username"])) { $error[] = "USERNAME_EXISTS"; }
			if(Str::compare($this->getEmail(), $row["email"])) { $error[] = "EMAIL_EXISTS"; }
		}
		$result->closeCursor();

		$result = Core::getQuery()->select("languages", array("languageid"), "", Core::getDB()->quoteInto("languageid = ?", $this->getLanguage()));
		if($result->rowCount() <= 0)
		{
			$error[] = "UNKOWN_LANGUAGE";
		}
		$result->closeCursor();

		if(count($error) > 0)
		{
			$this->printIt($error);
		}
		return $this;
	}

	/**
	 * Sends email with activation key.
	 *
	 * @return Bengine_Game_Account_Creator
	 */
	protected function sendMail()
	{
		if(!Core::getConfig()->get("EMAIL_ACTIVATION_DISABLED"))
		{
			$url = BASE_URL.Core::getLang()->getOpt("langcode")."/signup/activation/key:".$this->getActivation();
			Core::getLang()->assign("username", $this->getUsername());
			Core::getLang()->assign("regPassword", $this->getPassword());
			Core::getTemplate()->assign("activationLink", $url);
			$template = new Recipe_Email_Template("registration");
			$mail = new Email(array($this->getEmail() => $this->getUsername()), Core::getLanguage()->getItem("REGISTRATION"));
			$template->send($mail);
		}
		return $this;
	}

	/**
	 * Creates the user account.
	 *
	 * @return Bengine_Game_Account_Creator
	 */
	public function create()
	{
		$this->checkIt()
			->sendMail();
		Core::getQuery()->insert("registration", array("time" => TIME, "ipaddress" => IPADDRESS, "useragent" => (isset($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : ""));
		Core::getQuery()->insert("user", array("username" => $this->getUsername(), "email" => $this->getEmail(), "temp_email" => $this->getEmail(), "languageid" => $this->getLanguage(), "activation" => $this->getActivation(), "regtime" => TIME, "last" => TIME));
		$userid = Core::getDB()->lastInsertId();
		$this->userId = $userid;
		Core::getQuery()->insert("password", array("userid" => $userid, "password" => $this->getPassword(true), "time" => TIME));
		$planet = $this->getPlanetCreator($userid);
		$planetid = $planet->getPlanetId();
		Core::getQuery()->update("user", array("curplanet" => $planetid, "hp" => $planetid), "userid = ?", array($userid));

		// First user obtains admin permissions
		if($this->getTotalUser() <= 0)
		{
			Core::getQuery()->insert("user2group", array("userid" => $userid, "usergroupid" => self::ADMIN_USER_GROUP_ID));
		}

		// Send start-up message
		Core::getLang()->assign("regUsername", $this->getUsername());
		Core::getQuery()->insert("message", array("mode" => 1, "time" => TIME, "sender" => null, "receiver" => $userid, "message" => Core::getLang()->getItem("START_UP_MESSAGE"), "subject" => Core::getLang()->getItem("START_UP_MESSAGE_SUBJECT"), "read" => 0));
		Hook::event("UserRegistrationSuccess", array($this, $planet));
		// Delete Registrations older than 7 days
		Core::getQuery()->delete("registration", "time < ?", null, null, array(TIME - 604800));
		if(!Core::getConfig()->get("EMAIL_ACTIVATION_DISABLED"))
		{
			$this->printIt("SUCCESS_REGISTRATION");
		}
		else
		{
			$this->printIt("SUCCESS_REGISTRATION_INST");
		}
		return $this;
	}

	/**
	 * Displays error or success message.
	 *
	 * @param string|array $output	Message to display
	 *
	 * @return Bengine_Game_Account_Creator
	 */
	protected function printIt($output)
	{
		if(is_string($output))
		{
			$this->display("<div class=\"alert alert-success\">".Core::getLanguage()->getItem($output)."</div>");
		}
		if(is_array($output))
		{
			$outstream = "";
			foreach($output as $_output)
			{
				$outstream .= "<div class=\"alert alert-danger\">".Core::getLanguage()->getItem($_output)."</div>";
			}
			$this->display($outstream);
		}
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
	 * Returns the language id or code.
	 *
	 * @return string|integer
	 */
	public function getLanguage()
	{
		return $this->lang;
	}

	/**
	 * Returns the password.
	 *
	 * @param boolean $encrypted	Encrypt password
	 *
	 * @return string
	 */
	public function getPassword($encrypted = false)
	{
		if($encrypted)
		{
			$encryption = Core::getOptions("USE_PASSWORD_SALT") ? "md5_salt" : "md5";
			return Str::encode($this->password, $encryption);
		}
		return $this->password;
	}

	/**
	 * Returns the number of registered users.
	 *
	 * @return integer
	 */
	public function getTotalUser()
	{
		if(is_null($this->totalUser))
		{
			$this->loadTotalUser();
		}
		return $this->totalUser;
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
	 * Returns the activation code. If it's empty, generate it.
	 *
	 * @return string
	 */
	public function getActivation()
	{
		if(!Core::getConfig()->get("EMAIL_ACTIVATION_DISABLED") && empty($this->activation))
		{
			$this->activation = randString(self::ACTIVATION_KEY_LENGTH);
		}
		return $this->activation;
	}

	/**
	 * Returns the ID of the last created user.
	 *
	 * @return integer
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * Returns the planet creator object.
	 *
	 * @param integer $userId
	 * @return Bengine_Game_Planet_Creator
	 */
	public function getPlanetCreator($userId = null)
	{
		if($userId !== null && $this->planetCreator === null)
			$this->planetCreator = new Bengine_Game_Planet_Creator($userId);
		return $this->planetCreator;
	}
}
?>