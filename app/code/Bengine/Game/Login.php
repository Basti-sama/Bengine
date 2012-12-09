<?php
/**
 * Extended login.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Login.php 17 2011-05-19 15:25:04Z secretchampion $
 */

class Bengine_Game_Login extends Login
{
	/**
	 * Check input data.
	 *
	 * @return Bengine_Game_Login
	 */
	public function checkData()
	{
		$this->dataChecked = true;
		$select = array("u.userid", "u.username", "p.password", "u.activation", "b.banid", "b.reason", "u.umode");
		$joins  = "LEFT JOIN ".PREFIX."password p ON (u.userid = p.userid)";
		$joins .= "LEFT JOIN ".PREFIX."ban_u b ON (b.userid = u.userid AND b.to > '".TIME."')";
		$result = Core::getQuery()->select("user u", $select, $joins, "u.username = '".$this->usr."'", "b.to DESC");
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			if(Str::compare($row["username"], $this->usr) && Str::compare($row["password"], $this->pw) && Str::length($row["activation"]) == 0 && !$row["banid"])
			{
				$this->userid = $row["userid"];
				Core::getQuery()->delete("loginattempts", "ip = ? OR username = ?", null, null, array(IPADDRESS, $this->usr));
				Core::getQuery()->update("sessions", array("logged" => "0"), "userid = ?", array($this->userid));
				if($row["umode"])
				{
					Core::getQuery()->update("planet", array("last" => TIME), "userid = ?", array($row["userid"]));
				}
				$this->canLogin = true;
			}
			else
			{
				$this->canLogin = false;
				if(!Str::compare($row["username"], $this->usr))
				{
					$this->loginFailed("USERNAME_DOES_NOT_EXIST");
				}
				if(Str::length($row["activation"]) > 0)
				{
			 		$this->loginFailed("NO_ACTIVATION");
				}
				if($row["banid"])
				{
					Core::getLanguage()->load(array("Prefs"));
					Core::getLanguage()->assign("banReason", empty($row["reason"]) ? Core::getLanguage()->get("NO_BAN_REASON") : $row["reason"]);
					Core::getLanguage()->assign("pilloryLink", Link::get(Core::getLanguage()->getOpt("langcode")."/pillory", Core::getLanguage()->get("PILLORY")));
					$this->loginFailed("ACCOUNT_BANNED");
				}
				$this->loginFailed("PASSWORD_INVALID");
			}
		}
		else
		{
			$result->closeCursor();
			$this->canLogin = false;
			$this->loginFailed("USERNAME_DOES_NOT_EXIST");
		}
		return $this;
	}

	/**
	 * Start a new session and destroy old sessions.
	 *
	 * @return Bengine_Game_Login
	 */
	public function startSession()
	{
		if($this->canLogin)
		{
			Core::getDB()->query("UPDATE ".PREFIX."user SET curplanet = hp WHERE userid = ?", array($this->userid));
		}
		return parent::startSession();
	}
}
?>