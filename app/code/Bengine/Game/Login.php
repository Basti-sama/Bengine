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
		if($row = Core::getDB()->fetch($result))
		{
			Core::getDatabase()->free_result($result);
			if(Str::compare($row["username"], $this->usr) && Str::compare($row["password"], $this->pw) && Str::length($row["activation"]) == 0 && !$row["banid"])
			{
				$this->userid = $row["userid"];
				Core::getQuery()->delete("loginattempts", "ip = '".IPADDRESS."' OR username = '".$this->usr."'");
				Core::getQuery()->update("sessions", "logged", "0", "userid = '".$this->userid."'");
				if($row["umode"])
				{
					Core::getQuery()->update("planet", array("last"), array(TIME), "userid = '".$row["userid"]."'");
				}
				$this->canLogin = true;
			}
			else
			{
				$this->canLogin = false;
				if(!Str::compare($row["username"], $this->usr)) { $this->loginFailed("USERNAME_DOES_NOT_EXIST"); }
				if(Str::length($row["activation"]) > 0) { $this->loginFailed("NO_ACTIVATION"); }
				if(isset($row["to"]) && $row["to"] > TIME) { $this->loginFailed("ACCOUNT_BANNED"); }
				$this->loginFailed("PASSWORD_INVALID");
			}
		}
		else
		{
			Core::getDatabase()->free_result($result);
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
			Core::getDB()->query("UPDATE ".PREFIX."user SET curplanet = hp WHERE userid = '".$this->userid."'");
		}
		return parent::startSession();
	}
}
?>