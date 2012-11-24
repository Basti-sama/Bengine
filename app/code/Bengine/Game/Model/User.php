<?php
/**
 * User model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: User.php 46 2011-07-30 14:38:11Z secretchampion $
 */

class Bengine_Game_Model_User extends Recipe_Model_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see lib/Object#init()
	 */
	protected function init()
	{
		$this->setTableName("user");
		$this->setPrimaryKey("userid");
		$this->setModelName("game/user");
		return parent::init();
	}

	/**
	 * Returns the points for this user as a formatted number.
	 *
	 * @return string
	 */
	public function getFormattedPoints()
	{
		return fNumber(floor($this->getPoints()));
	}

	/**
	 * Returns the rank for this user as a formatted number.
	 *
	 * @return string
	 */
	public function getFormattedRank()
	{
		return fNumber($this->getRank());
	}

	/**
	 * Returns the rank for this user.
	 *
	 * @return integer
	 */
	public function getRank()
	{
		if(!$this->exists("rank"))
		{
			$result = Core::getQuery()->select("user", array("COUNT(`userid`)+1 AS rank"), "", "(`username` < '".$this->get("username")."' AND `points` >= ".$this->get("points", 0).") OR `points` > ".$this->get("points", 0), "", 1);
			$this->set("rank", (int) Core::getDB()->fetch_field($result, "rank"));
			$result->closeCursor();
		}
		return $this->get("rank");
	}

	/**
	 * Returns the status String in this form "i I g v".
	 *
	 * @return string
	 */
	public function getStatusString()
	{
		if(!$this->exists("status_string"))
		{
			$this->initNewbieProtectionStatus();
			$this->initUmodeStatus();
			$this->initUmodeStatus();
			$status_string = sprintf("%s%s%s", $this->getInactiveStatus(""), $this->getUmodeStatus(""), $this->getNewbieStatus(""));
			$this->set("status_string", trim($status_string));
		}
		return $this->get("status_string");
	}

	/**
	 * Initializing the newbie protection status.
	 *
	 * @return Bengine_Game_Model_User
	 */
	public function initNewbieProtectionStatus()
	{
		$isProtected = isNewbieProtected(Core::getUser()->get("points"), $this->getPoints());
		if($isProtected == 1)
		{
			$this->setNewbieStatus(" <span class=\"weak-player\">n</span> ");
			$this->setUsernameClass("weak-player");
		}
		else if($isProtected == 2)
		{
			$this->setNewbieStatus(" <span class=\"strong-player\">s</span> ");
			$this->setUsernameClass("strong-player");
		}
		else
		{
			$this->setNewbieStatus("");
		}
		return $this;
	}

	/**
	 * Initializing the vacation mode status.
	 *
	 * @return Bengine_Game_Model_User
	 */
	public function initUmodeStatus()
	{
		if($this->getUmode())
		{
			$this->setUmodeStatus(" <span class=\"vacation-mode\">v</span> ");
			$this->setUsernameClass("vacation-mode");
		}
		return $this;
	}

	/**
	 * Initializing the inactive status.
	 *
	 * @return Bengine_Game_Model_User
	 */
	public function initInactiveStatus()
	{
		$inactive = "";
		if($this->getLast() <= TIME - Core::getConfig()->get("INACTIVE_USER_TIME_1"))
		{
			$inactive = " i ";
		}
		if($this->getLast() <= TIME - Core::getConfig()->get("INACTIVE_USER_TIME_2"))
		{
			$inactive = " i I ";
		}
		$this->setInacticeStatus($inactive);
		return $this;
	}

	/**
	 * Returns the formatted registration date.
	 *
	 * @return string
	 */
	public function getFormattedRegDate()
	{
		return Date::timeToString(2, $this->getRegtime());
	}

	/**
	 * Returns the PM link for the user.
	 *
	 * @return string
	 */
	public function getPMLink()
	{
		$img = Image::getImage("pm.gif", Core::getLanguage()->getItem("WRITE_MESSAGE"));
		return Link::get("game/".SID."/MSG/Write/".rawurlencode($this->getUsername()), $img);
	}

	/**
	 * Returns the add-to-friend-list link.
	 *
	 * @return string
	 */
	public function getFriendLink()
	{
		$img = Image::getImage("b.gif", Core::getLanguage()->getItem("ADD_TO_BUDDYLIST"));
		return Link::get("game/".SID."/Friends/Add/".$this->getUserid(), $img);
	}

	/**
	 * Returns the moderator link.
	 *
	 * @return string
	 */
	public function getModerateLink()
	{
		$img = Image::getImage("moderator.gif", Core::getLanguage()->getItem("MODERATE"));
		return Link::get("game/".SID."/Moderator/Index/".$this->getUserid(), $img);
	}

	/**
	 * Returns the alliance.
	 *
	 * @return Bengine_Game_Model_Alliance
	 */
	public function getAlliance()
	{
		return Application::getModel("game/alliance")->load($this->getAid());
	}

	/**
	 * Returns the home planet model.
	 *
	 * @return Bengine_Game_Model_Planet
	 */
	public function getHomePlanet()
	{
		if(!$this->exists("home_planet"))
		{
			$this->set("home_planet", Application::getModel("game/planet")->load($this->getHp()));
		}
		return $this->get("home_planet");
	}
}
?>