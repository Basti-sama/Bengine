<?php
/**
 * Allows moderators to change user data and manage bans.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Moderator.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_Moderator extends Bengine_Game_Controller_Abstract
{
	/**
	 * User id of currently moderating user.
	 *
	 * @var integer
	 */
	protected $userid = 0;

	/**
	 * Handles the moderator class.
	 *
	 * @return void
	 */
	protected function init()
	{
		Core::getUser()->checkPermissions("CAN_MODERATE_USER");
		Core::getLanguage()->load(array("Prefs" ,"Statistics", "Registration"));
		$this->userid = (Core::getRequest()->getPOST("userid")) ? Core::getRequest()->getPOST("userid") : Core::getRequest()->getGET("1");
		parent::init();
	}

	/**
	 * Displays the moderator form to edit users.
	 *
	 * @return Bengine_Game_Controller_Moderator
	 */
	protected function indexAction()
	{
		if($this->isPost())
		{
			if($this->getParam("proceedban"))
			{
				$this->proceedBan($this->getParam("ban"), $this->getParam("timeend"), $this->getParam("reason"), $this->getParam("b_umode"));
			}
			if($this->getParam("proceed"))
			{
				$this->updateUser(
					$this->getParam("username"), $this->getParam("usertitle"), $this->getParam("email"), $this->getParam("delete"), $this->getParam("umode"), $this->getParam("activation"),
					$this->getParam("ipcheck"), $this->getParam("usergroupid"), $this->getParam("points"), $this->getParam("fpoints"), $this->getParam("rpoints"),
					$this->getParam("password"), $this->getParam("languageid"), $this->getParam("templatepackage"), $this->getParam("theme"), $this->getParam("js_interface")
				);
			}
		}
		$select = array(
			"u.userid", "u.username", "u.usertitle", "u.email", "u.temp_email", "u.languageid", "u.templatepackage", "u.theme", "u.js_interface", "u.points", "u.fpoints", "u.rpoints",
			"u.ipcheck", "u.activation", "u.last", "u.umode", "u.umode", "u.delete", "u.regtime",
			"a.tag", "a.name", "u2g.usergroupid"
		);
		$joins  = "LEFT JOIN ".PREFIX."user2ally u2a ON (u2a.userid = u.userid)";
		$joins .= "LEFT JOIN ".PREFIX."alliance a ON (a.aid = u2a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."user2group u2g ON (u.userid = u2g.userid)";
		$result = Core::getQuery()->select("user u", $select, $joins, Core::getDB()->quoteInto("u.userid = ?", $this->userid));
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			Hook::event("ModerateUser", array(&$row));
			$row["deletion"] = $row["delete"];
 			unset($row["delete"]);
 			$row["vacation"] = $row["umode"];
 			unset($row["umode"]);

 			$row["last"] = Date::timeToString(1, $row["last"]);
 			$row["regtime"] = Date::timeToString(1, $row["regtime"]);

 			Core::getTPL()->assign($row);

			if($row["usergroupid"] == 4)
			{
				Core::getTPL()->assign("isMod", true);
			}
			else if($row["usergroupid"] == 2)
			{
				Core::getTPL()->assign("isAdmin", true);
			}

			$result = Core::getQuery()->select("languages", array("languageid", "title"), "", "", "title ASC");
			Core::getTPL()->addLoop("langs", $result);

			Core::getTPL()->assign("loginLink", Link::get("game/".SID."/Moderator/SwitchUser/".$row["userid"], Core::getLang()->get("SWITCH_USER")));

			$bans = array(); $i = 0;
			$result = Core::getQuery()->select("ban_u", array("`banid`", "`to`", "`reason`"), "", Core::getDB()->quoteInto("userid = ?", $this->userid));
			foreach($result->fetchAll() as $row)
			{
				$bans[$i]["reason"] = $row["reason"];
				$bans[$i]["to"] = Date::timeToString(1, $row["to"]);

				if($row["to"] > TIME)
				{
					$bans[$i]["annul"] = Link::get("game/".SID."/Moderator/AnnulBan/".$row["banid"], Core::getLanguage()->getItem("ANNUL"));
				}
				else
				{
					$bans[$i]["annul"] = Core::getLanguage()->getItem("ANNUL");
				}
				$i++;
			}
			$result->closeCursor();
			Core::getTPL()->addLoop("bans", $bans);
			Core::getTPL()->assign("eBans", $i);
			Core::getTPL()->assign("sessionsLink", Link::get("game/".SID."/Moderator/Sessions?id=".$this->userid, "Sessions"));
		}
		return $this;
	}

	/**
	 * Bans an user.
	 *
	 * @param integer $ban
	 * @param integer $timeEnd
	 * @param string $reason
	 * @param boolean $forceUmode
	 *
	 * @return Bengine_Game_Controller_Moderator
	 */
	protected function proceedBan($ban, $timeEnd, $reason, $forceUmode)
	{
		$to = TIME + $ban * $timeEnd;
		if($to > 9999999999) { $to = 9999999999; }
		Hook::event("BanUser", array(&$to, $reason, $forceUmode));
		$spec = array("userid" => $this->userid, "from" => TIME, "to" => $to, "reason" => $reason, "modid" => Core::getUser()->get("userid"));
		Core::getQuery()->insert("ban_u", $spec);
		if($forceUmode)
		{
			Core::getQuery()->update("user", array("umode" => 1), "userid = ?", array($this->userid));
			setProdOfUser($this->userid, 0);
		}
		Core::getQuery()->update("sessions", array("logged" => 0), "userid = ?", array($this->userid));
		$user = Game::getModel("game/user")->load($this->userid);
		Core::getTemplate()->assign("banReason", $reason);
		Core::getLang()->assign("banDate", Date::timeToString(1, $to, "", 0));
		Core::getLang()->assign("username", $user->get("username"));
		$template = new Recipe_Email_Template("ban_notification");
		$mail = new Email(array($user->get("email") => $user->get("username")), Core::getLanguage()->getItem("BAN_NOTIFICATION_MAIL_SUBJECT"));
		$template->send($mail);
		return $this;
	}

	/**
	 * Annuls a ban.
	 *
	 * @param integer $banid
	 *
	 * @return Bengine_Game_Controller_Moderator
	 */
	protected function annulBanAction($banid)
	{
		Hook::event("UnbanUser", array($banid));
		Core::getQuery()->update("ban_u", array("to" => TIME, "reason" => Core::getLanguage()->getItem("ANNULED")), "banid = ?", array($banid));
		$result = Core::getQuery()->select("ban_u", "userid", "", Core::getDB()->quoteInto("banid = ?", $banid));
		$row = $result->fetchRow();
		$result->closeCursor();
		$this->redirect("game/".SID."/Moderator/Index/".$row["userid"], false);
		return $this;
	}

	/**
	 * Updates the moderator form.
	 *
	 * @param string $username
	 * @param string $usertitle
	 * @param string $email
	 * @param int $delete
	 * @param int $umode
	 * @param string $activation
	 * @param string $ipcheck
	 * @param int $usergroupid
	 * @param int $points
	 * @param int $fpoints
	 * @param int $rpoints
	 * @param string $password
	 * @param int $languageid
	 * @param string $templatepackage
	 * @param string $theme
	 * @param string $js_interface
	 * @return Bengine_Game_Controller_Moderator
	 */
	protected function updateUser($username, $usertitle, $email, $delete, $umode, $activation, $ipcheck, $usergroupid, $points, $fpoints, $rpoints, $password, $languageid, $templatepackage, $theme, $js_interface)
	{
		$select = array("userid", "username", "email");
		$result = Core::getQuery()->select("user", $select, "", Core::getDB()->quoteInto("userid = ?", $this->userid));
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			Hook::event("SaveUserModeration", array(&$row));
			$delete = ($delete == 1) ? 1 : 0;
			$umode = ($umode == 1) ? 1 : 0;
			$activation = ($activation == 1) ? "" : "1";
			$ipcheck = ($ipcheck == 1) ? 1 : 0;
			$username = trim($username);
			$usertitle = trim($usertitle);
			$js_interface = trim($js_interface);

			if(Core::getUser()->ifPermissions("CAN_EDIT_USER"))
			{
				Core::getQuery()->delete("user2group", "userid = ?", null, null, array($this->userid));
				Core::getQuery()->insert("user2group", array("usergroupid" => $usergroupid, "userid" => $this->userid));
				Core::getQuery()->update("user", array("points" => floatval($points), "fpoints" => (int) $fpoints, "rpoints" => (int) $rpoints), "userid = ?", array($this->userid));
			}

			if($umode)
			{
				setProdOfUser($this->userid, 0);
			}

			if(!Str::compare($username, $row["username"]))
			{
				$num = Core::getQuery()->select("user", "userid", "", Core::getDB()->quoteInto("username = ?", $username))->rowCount();
				if($num > 0)
				{
					$username = $row["username"];
				}
			}

			if(!Str::compare($email, $row["email"]))
			{
				$num = Core::getQuery()->select("user", "userid", "", Core::getDB()->quoteInto("email = ?", $email))->rowCount();
				if($num > 0)
				{
					$email = $row["email"];
				}
			}

			if(Str::length($password) > Core::getOptions()->get("MIN_PASSWORD_LENGTH"))
			{
				$encryption = Core::getOptions("USE_PASSWORD_SALT") ? "md5_salt" : "md5";
				$password = Str::encode($password, $encryption);
				Core::getQuery()->update("password", array("password" => $password, "time" => TIME), "userid = ?", array($this->userid));
			}

			$spec = array(
				"username" => $username,
				"usertitle" => $usertitle,
				"email" => $email,
				"delete" => $delete,
				"umode" => $umode,
				"activation" => $activation,
				"languageid" => $languageid,
				"ipcheck" => $ipcheck,
				"templatepackage" => $templatepackage,
				"theme" => $theme,
				"js_interface" => $js_interface,
			);
			Core::getQuery()->update("user", $spec, "userid = ?", array($this->userid));
		}
		return $this;
	}

	/**
	 * Switch to user account.
	 *
	 * @return Bengine_Game_Controller_Moderator
	 */
	protected function switchUserAction()
	{
		Core::getUser()->checkPermissions(array("CAN_SWITCH_USER"));
		$result = Core::getQuery()->select("user u", array("u.username", "p.password"), "LEFT JOIN ".PREFIX."password p ON p.userid = u.userid", Core::getDB()->quoteInto("u.userid = ?", $this->userid));
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			$login = new Bengine_Game_Login($row["username"], $row["password"], "game", "trim");
			$login->setCountLoginAttempts(false);
			$login->checkData();
			$login->startSession();
		}
		$result->closeCursor();
		return $this;
	}

	/**
	 * Shows all sessions for the user.
	 *
	 * @return Bengine_Game_Controller_Moderator
	 */
	protected function sessionsAction()
	{
		/* @var Bengine_Game_Model_Collection_SessionLog $sessionLog */
		$sessionLog = Game::getCollection("game/sessionLog");
		$sessionLog->addTimeOrder();
		if($ip = $this->getParam("ip"))
		{
			$sessionLog->addIpFilter($ip);
		}
		else if($id = $this->getParam("id"))
		{
			$sessionLog->addUserFilter($id);
		}
		else
		{
			$this->redirect("game/".SID."/Index");
		}

		Core::getTPL()->addLoop("sessionLog", $sessionLog);
		return $this;
	}
}
?>