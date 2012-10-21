<?php
/**
 * Deletes inavtive users.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: RemoveInactiveUser.php 26 2011-06-17 13:09:26Z secretchampion $
 */

class Bengine_Game_Cronjob_RemoveInactiveUser extends Recipe_CronjobAbstract
{
	/**
	 * Removes inactive users.
	 *
	 * @return Bengine_Game_Cronjob_RemoveInactiveUser
	 */
	protected function removeInactiveUsers()
	{
		$deleteTime = TIME - Core::getConfig()->get("USER_DELETE_TIME") * 86400;
		$where = "(u.last < '".$deleteTime."' OR (u.`delete` < '".TIME."' AND u.`delete` > '0')) AND ((u2g.usergroupid != '2' AND u2g.usergroupid != '4') OR u2g.usergroupid IS NULL)";
		$result = Core::getQuery()->select("user u", "u.userid", "LEFT JOIN ".PREFIX."user2group u2g ON (u2g.userid = u.userid)", $where);
		while($row = Core::getDB()->fetch($result))
		{
			$userid = $row["userid"];
			$_result = Core::getQuery()->select("planet", array("planetid", "ismoon"), "", "userid = '".$userid."'");
			while($_row = Core::getDB()->fetch($_result))
			{
				if(!$_row["ismoon"])
				{
					deletePlanet($_row["planetid"], $userid, false);
				}
			}
			Core::getDB()->free_result($_result);
			$_result = Core::getQuery()->select("alliance", "aid", "", "founder = '".$userid."'");
			if($_row = Core::getDB()->fetch($_result))
			{
				deleteAlliance($_row["aid"]);
			}
			Core::getDB()->free_result($_result);
			Core::getQuery()->delete("user", "userid = '".$userid."'");
		}
		Core::getDB()->free_result($result);
		return $this;
	}

	/**
	 * Executes this cronjob.
	 *
	 * @return Bengine_Game_Cronjob_RemoveInactiveUser
	 */
	protected function _execute()
	{
		$this->removeInactiveUsers();
		return $this;
	}
}
?>