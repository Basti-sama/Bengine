<?php
/**
 * Account activation function.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Activation.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Account_Activation
{
	/**
	 * Activation key to verify email address.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * Constructor.
	 *
	 * @param string $key Activation key
	 *
	 * @return \Bengine_Game_Account_Activation
	 */
	public function __construct($key)
	{
		$this->key = $key;
		$this->activateAccount();
		return;
	}

	/**
	 * Returns the activation key.
	 *
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Activates an account, if key exists.
	 * Starts log in on success.
	 *
	 * @return Bengine_Game_Account_Activation
	 */
	protected function activateAccount()
	{
		if(!empty($this->key))
		{
			$result = Core::getQuery()->select("user u", array("u.userid", "u.username", "p.password", "temp_email"), "LEFT JOIN ".PREFIX."password p ON (p.userid = u.userid)", "u.activation = '".$this->getKey()."'");
			if($row = $result->fetchRow())
			{
				$result->closeCursor();
				Hook::event("ActivateAccount", array($this));
				Core::getQuery()->update("user", array("activation" => "", "email" => $row["temp_email"]), "userid = '".$row["userid"]."'");
				$login = new Bengine_Game_Login($row["username"], $row["password"], "game", "trim");
				$login->setCountLoginAttempts(false);
				$login->checkData();
				$login->startSession();
				return $this;
			}
			$result->closeCursor();
		}
		Recipe_Header::redirect("?error=ACTIVATION_FAILED", false);
		return $this;
	}
}
?>