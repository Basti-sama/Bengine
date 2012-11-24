<?php
/**
 * This class helps to create a user list.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: List.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_User_List extends Bengine_Game_Alliance_List
{
	/**
	 * Images which can be used in a list.
	 *
	 * @var string
	 */
	protected $pmPic = "", $buddyPic = "", $modPic = "";

	/**
	 * Points of the current user.
	 *
	 * @var integer
	 */
	protected $points = 0;

	/**
	 * Consider newbie protection when formatting the usernames?
	 *
	 * @var boolean
	 */
	protected $newbieProtection = false;

	/**
	 * Creates a new user list object.
	 *
	 * @param resource $list Query result for a list
	 * @param int $start
	 * @return \Bengine_Game_User_List
	 */
	public function __construct($list = null, $start = 0)
	{
		$this->pmPic = Image::getImage("pm.gif", Core::getLanguage()->getItem("WRITE_MESSAGE"));
		$this->buddyPic = Image::getImage("b.gif", Core::getLanguage()->getItem("ADD_TO_BUDDYLIST"));
		$this->modPic = Image::getImage("moderator.gif", Core::getLanguage()->getItem("MODERATE"));
		$this->points = Core::getUser()->get("points");
		parent::__construct($list, $start);
	}

	/**
	 * Formats an user record.
	 *
	 * @param array $row	User data
	 *
	 * @return array		Formatted user data
	 */
	protected function formatRow(array $row)
	{
		Hook::event("FormatUserListRowFirst", array(&$row, $this));
		if(empty($row["userid"]))
		{
			return $row;
		}

		// Quick buttons
		$row["message"] = Link::get("game/".SID."/MSG/Write/".$row["username"], $this->pmPic);
		$row["buddyrequest"] = Link::get("game/".SID."/Friends/Add/".$row["userid"], $this->buddyPic);
		$row["moderator"] = Link::get("game/".SID."/Moderator/Index/".$row["userid"], $this->modPic);

		$userClass = ""; $inactive = ""; $ban = ""; $umode = ""; $status = "";
		// Newbie protection
		if($this->newbieProtection && Core::getUser()->userid != $row["userid"])
		{
			$isProtected = isNewbieProtected($this->points, $row["points"]);
			//var_dump($isProtected);
			if($isProtected == 1)
			{
				$status = " <span class=\"weak-player\">n</span> ";
				$userClass = "weak-player";
			}
			else if($isProtected == 2)
			{
				$status = " <span style=\"strong-player\">s</span> ";
				$userClass = "strong-player";
			}
		}

		// User activity
		if($row["useractivity"] <= TIME - Core::getConfig()->get("INACTIVE_USER_TIME_1"))
		{
			$inactive = " i ";
		}
		if($row["useractivity"] <= TIME - Core::getConfig()->get("INACTIVE_USER_TIME_2"))
		{
			$inactive = " i I ";
		}

		// User banned?
		if($row["to"] >= TIME)
		{
			$ban = " <span class=\"banned\">b</span> ";
			$userClass = "banned";
		}
		// Vacation mode
		if($row["umode"])
		{
			$umode = " <span class=\"vacation-mode\">v</span> ";
			$userClass = "vacation-mode";
		}
		$row["user_status"] = sprintf("%s%s%s", $inactive, $ban, $umode);
		$row["user_status_long"] = sprintf("%s%s%s%s", $inactive, $ban, $umode, $status);
		$title = (!empty($row["usertitle"])) ? $row["usertitle"] : Core::getLang()->get("GO_TO_PROFILE");
		$row["username"] = Link::get("game/".SID."/Profile/Page/".$row["userid"], $this->formatUsername($row["username"], $row["userid"], $row["aid"], $userClass), $title);

		$row["alliance"] = "";
		if(!empty($row["aid"]) && !empty($row["tag"]))
		{
			$row["alliance"] = $this->formatAllyTag($row["tag"], $row["name"], $row["aid"]);
			if($this->fetchRankByQuery)
			{
				$row["alliance_rank"] = $this->getAllianceRank($row["aid"], $this->pointType);
			}
		}

		if(isset($row["ismoon"]) && $row["ismoon"] && !empty($row["moongala"]) && !empty($row["moonsys"]) && !empty($row["moonpos"]))
		{
			$row["position"] = getCoordLink($row["moongala"], $row["moonsys"], $row["moonpos"]);
		}
		else if(!empty($row["galaxy"]) && !empty($row["system"]) && !empty($row["position"]))
		{
			$row["position"] = getCoordLink($row["galaxy"], $row["system"], $row["position"]);
		}

		// Points
		if($this->fetchRankByQuery)
		{
			$row["rank"] = $this->getUserRank($row[$this->pointType], $this->pointType);
		}
		else if(isset($row["rank"]))
		{
			$row["rank"] = fNumber($row["rank"]);
		}
		if(isset($row["points"]))
		{
			$row["points"] = fNumber(floor($row[$this->pointType]));
		}
		if(isset($row["fpoints"]))
		{
			$row["fpoints"] = fNumber(floor($row["fpoints"]));
		}
		if(isset($row["rpoints"]))
		{
			$row["rpoints"] = fNumber(floor($row["rpoints"]));
		}
		Hook::event("FormatUserListRowLast", array(&$row, $this));
		return $row;
	}

	/**
	 * Fetches the user rank from database.
	 *
	 * @param integer $points
	 * @param string $pointType
	 * @return integer
	 */
	protected function getUserRank($points, $pointType)
	{
		$result = Core::getQuery()->select("user", "userid", "", $pointType." >= FLOOR('".$points."')");
		$rank = fNumber($result->rowCount());
		$result->closeCursor();
		return $rank;
	}

	/**
	 * Sets the CSS class for the username.
	 *
	 * @param string $username
	 * @param integer $userid
	 * @param integer $aid
	 * @param string $defaultClass
	 *
	 * @return string	Formatted username
	 */
	protected function formatUsername($username, $userid, $aid, $defaultClass = "")
	{
		$class = $this->relation->getPlayerRelationClass($userid, $aid);
		$class = (empty($class) && !empty($defaultClass)) ? $defaultClass : $class;
		return (!empty($class)) ? "<span class=\"".$class."\">".$username."</span>" : $username;
	}

	/**
	 * Setter-method for newbie protection.
	 *
	 * @param boolean
	 *
	 * @return Bengine_Game_User_List
	 */
	public function setNewbieProtection($newbieProtection)
	{
		$this->newbieProtection = $newbieProtection;
		return $this;
	}
}
?>