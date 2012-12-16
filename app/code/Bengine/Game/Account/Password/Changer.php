<?php
/**
 * Changes the password, if the key passed the check.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Changer.php 19 2011-05-27 10:30:33Z secretchampion $
 */

require_once(APP_ROOT_DIR."app/code/Functions.inc.php");

class Bengine_Game_Account_Password_Changer extends Bengine_Game_Account_Ajax
{
	/**
	 * Key check and set new password.
	 *
	 * @param integer $userid
	 * @param string $key Key, transmitted by email
	 * @param string $newpw New password
	 *
	 * @return Bengine_Game_Account_Password_Changer
	 */
	public function __construct($userid, $key, $newpw)
	{
		Hook::event("ChangePassword", array($userid, $key));
		if(empty($key) || Str::length($newpw) < Core::getOptions()->get("MIN_PASSWORD_LENGTH") || Str::length($newpw) > Core::getOptions()->get("MAX_PASSWORD_LENGTH"))
		{
			$this->printIt("PASSWORD_INVALID");
		}

		$where  = Core::getDB()->quoteInto("userid = ? AND ", $userid);
		$where .= Core::getDB()->quoteInto("activation = ?", $key);
		$result = Core::getQuery()->select("user", "userid", "", $where);
		if($result->rowCount())
		{
			$result->closeCursor();
			$encryption = Core::getOptions("USE_PASSWORD_SALT") ? "md5_salt" : "md5";
			$newpw = Str::encode($newpw, $encryption);
			Core::getQuery()->update("password", array("password" => $newpw, "time" => TIME), "userid = ?", array($userid));
			Core::getQuery()->update("user", array("activation" => ""), "userid = ?", array($userid));
			$this->printIt("PASSWORD_CHANGED", false);
		}
		$result->closeCursor();
		$this->printIt("ERROR_PASSWORD_CHANGED");
		return;
	}

	/**
	 * Prints either an error or success message.
	 *
	 * @param string $output
	 * @param boolean $error
	 *
	 * @return Bengine_Game_Account_Password_Changer
	 */
	protected function printIt($output, $error = true)
	{
		if($error === true)
		{
			$this->display("<div class=\"error\">".Core::getLanguage()->getItem($output)."</div>");
		}
		$this->display("<div class=\"success\">".Core::getLanguage()->getItem($output)."</div><br />");
		return $this;
	}
}
?>