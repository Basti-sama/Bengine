<?php
/**
 * Loads user data.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: User.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Recipe_User extends Recipe_Collection
{
	/**
	 * Session ID.
	 *
	 * @var string
	 */
	protected $sid = "";

	/**
	 * Permissions.
	 *
	 * @var array
	 */
	protected $permissions = array();

	/**
	 * User groups.
	 *
	 * @var array
	 */
	protected $groups = array();

	/**
	 * Particular group data.
	 *
	 * @var array
	 */
	protected $groupdata = array();

	/**
	 * If user is guest.
	 *
	 * @var boolean
	 */
	protected $isGuest = false;

	/**
	 * Enables cache operation.
	 *
	 * @var boolean
	 */
	protected $cacheActive = false;

	/**
	 * Default user group for guests.
	 *
	 * @var integer
	 */
	protected $guestGroupId = 1;

	/**
	 * Allowed timezones.
	 * TODO: Someday we should consider daylight saving time.
	 *
	 * @var array
	 */
	protected $timezones = array(
		"-11" => "Pacific/Samoa",
		"-10" => "Pacific/Honolulu",
		"-9" => "America/Anchorage",
		"-8" => "America/Los_Angeles",
		"-7" => "America/Denver",
		"-6" => "America/Chicago",
		"-5" => "America/New_York",
		"-4.5" => "America/Caracas",
		"-4" => "America/Asuncion",
		"-3.5" => "America/St_Johns",
		"-3" => "America/Argentina/Buenos_Aires",
		"-2" => "Atlantic/South_Georgia",
		"-1" => "Atlantic/Cape_Verde",
		"0" => "Europe/London",
		"1" => "Europe/Paris",
		"2" => "Europe/Istanbul",
		"3" => "Europe/Moscow",
		"4" => "Asia/Baku",
		"5" => "Asia/Bishkek",
		"5.5" => "Asia/Colombo",
		"5.75" => "Asia/Katmandu",
		"6" => "Asia/Dhaka",
		"6.5" => "Indian/Cocos",
		"7" => "Asia/Bangkok",
		"8" => "Asia/Singapore",
		"9" => "Asia/Tokyo",
		"9.5" => "Australia/Adelaide",
		"10" => "Australia/Queensland",
		"10.5" => "Australia/Lord_Howe",
		"11" => "Pacific/Noumea",
		"11.5" => "Pacific/Norfolk",
		"12" => "Pacific/Fiji"
	);

	/**
	 * Constructor: Starts user check.
	 *
	 * @param string	The session id
	 *
	 * @return void
	 */
	public function __construct($sid)
	{
		$this->sid = $sid;
		$this->setGuestGroupId();
		if($this->cacheActive) { $this->cacheActive = CACHE_ACTIVE; }
		$this->getData();
		if(!$this->isGuest) { $this->setGroups(); }
		$this->setPermissions();
		$this->setTimezone();
		return;
	}

	/**
	 * Fetches the user data in term of a session id.
	 *
	 * @return Recipe_User
	 */
	protected function getData()
	{
		Hook::event("LoadUserData", array($this));
		if($this->cacheActive)
		{
			$this->item = Core::getCache()->getUserCache($this->sid);
		}
		else
		{
			$select = array("u.*", "s.ipaddress");
			$joins  = "LEFT JOIN ".PREFIX."user u ON (s.userid = u.userid)";
			// Get custom user data from configuration
			if(Core::getConfig()->exists("userselect"))
			{
				$userConfigSelect = Core::getConfig()->get("userselect");
				$select = array_merge($select, $userConfigSelect["fieldsnames"]);
			}
			if(Core::getConfig()->exists("userjoins"))
			{
				$joins .= " ".Str::replace("PREFIX", PREFIX, Core::getConfig()->get("userjoins"));
			}
			$result = Core::getQuery()->select("sessions s", $select, $joins, "s.sessionid = '".$this->sid."' AND s.logged = '1'");
			$this->item = Core::getDatabase()->fetch($result);
			Core::getDatabase()->free_result($result);
		}
		if($this->size() > 0)
		{
			defined("SID") || define("SID", $this->sid);
			if(IPCHECK && $this->get("ipcheck"))
			{
				if($this->get("ipaddress") != IPADDRESS) { forwardToLogin("IPADDRESS_INVALID"); }
			}
			if($this->get("templatepackage") != "") { Core::getTemplate()->setTemplatePackage($this->get("templatepackage")); }
		}
		else
		{
			if(LOGIN_REQUIRED && !defined("LOGIN_PAGE")) { forwardToLogin("NO_ACCESS"); }
			$this->setGuest();
		}
		Hook::event("UserDataLoaded", array($this));
		return $this;
	}

	/**
	 * Sets the user groups.
	 *
	 * @return Recipe_User
	 */
	protected function setGroups()
	{
		$select = array("usergroupid", "data");
		$result = Core::getQuery()->select("user2group", $select, "", "userid = '".$this->get("userid")."'");
		while($row = Core::getDatabase()->fetch($result))
		{
			array_push($this->groups, $row["usergroupid"]);
			$this->groupdata[$row["usergroupid"]] = $row["data"];
		}
		Core::getDatabase()->free_result($result);
		return $this;
	}

	/**
	 * Fetches permissions of an usergroup. Regard: Permissions are
	 * favoured in opposite of restrictions.
	 *
	 * @return Recipe_User
	 */
	protected function setPermissions()
	{
		if(count($this->groups) > 1)
		{
			foreach($this->groups as $group)
			{
				if($this->cacheActive)
				{
					$permCache = Core::getCache()->getPermissionCache($group);
					foreach($permCache as $key => $value)
					{
						if(!isset($this->permissions[$key]) || $this->permissions[$key] == 0) { $this->permissions[$key] = $value; }
					}
				}
				else
				{
					$select = array("p.permission", "p2g.value");
					$result = Core::getQuery()->select("group2permission p2g", $select, "LEFT JOIN ".PREFIX."permissions AS p ON (p2g.permissionid = p.permissionid)", "p2g.groupid = '".$group."'");
					while($row = Core::getDatabase()->fetch($result))
					{
						if(!isset($this->permissions[$row["permission"]]) || $this->permissions[$row["permission"]] == 0) { $this->permissions[$row["permission"]] = $row["value"]; }
					}
					Core::getDatabase()->free_result($result);
				}
			}
		}
		else if(count($this->groups) > 0)
		{
			if($this->cacheActive)
			{
				$this->permissions = Core::getCache()->getPermissionCache($this->groups[0]);
			}
			else
			{
				$select = array("p.permission", "p2g.value");
				$result = Core::getQuery()->select("group2permission p2g", $select, "LEFT JOIN ".PREFIX."permissions AS p ON (p2g.permissionid = p.permissionid)", "p2g.groupid = '".$this->groups[0]."'");
				while($row = Core::getDatabase()->fetch($result))
				{
					$this->permissions[$row["permission"]] = $row["value"];
				}
				Core::getDatabase()->free_result($result);
			}
		}
		return $this;
	}

	/**
	 * Checks if user has permissions and if so issue an error.
	 *
	 * @param string	The permissions; Splitted with commas
	 *
	 * @return Recipe_User
	 */
	public function checkPermissions($perms)
	{
		Hook::event("CHECK_USER_PERMISSIONS", array($perms));
		if(!$this->ifPermissions($perms))
		{
			forwardToLogin("NO_ACCESS");
		}
		return $this;
	}

	/**
	 * Checks if user has permissions.
	 *
	 * @param mixed		The permissions; Splitted with commas
	 *
	 * @return boolean	True, if all permissions are valid, false, if not
	 */
	public function ifPermissions($perms)
	{
		if(!is_array($perms)) { $perms = Arr::trimArray(explode(",", $perms)); }
		Hook::event("USER_HAS_PERMISSIONS", array($perms));
		foreach($perms as $p)
		{
			if(!isset($this->permissions[$p]) || $this->permissions[$p] == 0 || !$this->permissions[$p])
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Assign user to group "guest".
	 *
	 * @return Recipe_User
	 */
	protected function setGuest()
	{
		$this->isGuest = true;
		array_push($this->groups, $this->guestGroupId);
		return $this;
	}

	/**
	 * Rebuild user cache.
	 *
	 * @return Recipe_User
	 */
	public function rebuild()
	{
		if($this->cacheActive)
		{
			Core::getCache()->buildUserCache($this->sid);
			$this->item = Core::getCache()->getUserCache($this->sid);
		}
		else
		{
			$this->getData();
			$this->item["db_lock"] = 0;
		}
		return $this;
	}

	/**
	 * Checks the timezone.
	 *
	 * @return Recipe_User
	 */
	public function setTimezone()
	{
		$timezone = null;
		if($this->exists("timezone") && is_numeric($this->get("timezone")) && array_key_exists($this->get("timezone"), $this->timezones))
		{
			$timezone = $this->timezones[$this->get("timezone")];
		}
		Core::setDefaultTimeZone($timezone);
		return $this;
	}

	/**
	 * Returns a session value.
	 *
	 * @param string	Session variable
	 *
	 * @return mixed	Value
	 */
	public function get($var)
	{
		if(is_null($var))
		{
			return $this->item;
		}
		if($this->exists($var))
		{
			return $this->item[$var];
		}
		return false;
	}

	/**
	 * Sets a session value.
	 *
	 * @param string	Session variable
	 * @param mixed		Value
	 *
	 * @return Recipe_User
	 */
	public function set($var, $value)
	{
		if(Str::compare($var, "userid"))
		{
			throw new Recipe_Exception_Generic("The primary key of a data record cannot be changed.");
		}
		Core::getQuery()->update("user", array($var), array($value), "userid = '".$this->get("userid")."'");
		$this->rebuild();
		$this->item[$var] = $value;
		return $this;
	}

	/**
	 * Checks group membership.
	 *
	 * @param integer	Group id
	 *
	 * @return boolean
	 */
	public function inGroup($groupid)
	{
		return (in_array($groupid, $this->groups)) ? true : false;
	}

	/**
	 * Returns specific group membership data.
	 *
	 * @param integer	Group id
	 *
	 * @return mixed	Group membership data
	 */
	public function getGroupData($groupid)
	{
		return (isset($this->groupdata[$groupid])) ? $this->groupdata[$groupid] : false;
	}

	/**
	 * Returns the session id.
	 *
	 * @return string
	 */
	public function getSid()
	{
		return $this->sid;
	}

	/**
	 * Returns if the user is in guest mode.
	 *
	 * @return boolean
	 */
	public function isGuest()
	{
		return $this->isGuest;
	}

	/**
	 * Sets the guest group id.
	 *
	 * @param integer	Guest group id [optional]
	 *
	 * @return Recipe_User
	 */
	public function setGuestGroupId($guestGroupId = null)
	{
		if(is_null($guestGroupId))
		{
			$guestGroupId = Core::getConfig()->guestgroupid;
		}
		$this->guestGroupId = $guestGroupId;
		return $this;
	}

	/**
	 * Removes the theme temporarily.
	 *
	 * @return Recipe_User
	 */
	public function removeTheme()
	{
		$this->item['theme'] = '';
		return $this;
	}
}
?>