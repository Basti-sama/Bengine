<?php
/**
 * Shows and manages friend list.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Friends.php 54 2011-08-14 16:19:51Z secretchampion $
 */

class Bengine_Page_Friends extends Bengine_Page_Abstract
{
	/**
	 * Index action.
	 *
	 * @return Bengine_Page_Friends
	 */
	protected function indexAction()
	{
		Core::getLanguage()->load(array("Statistics", "Buddylist"));
		if($this->isPost())
		{
			if($this->getParam("delete"))
			{
				$this->remove($this->getParam("remove", array()));
			}
			if($this->getParam("accept"))
			{
				$this->acceptRequest($this->getParam("relid"));
			}
		}
		$bl = array();
		$select = array(
			"b.relid", "b.friend1", "b.friend2", "b.accepted",
			"u1.username as user1", "u1.points as points1", "u1.last as lastlogin1",
			"u2.points as points2", "u2.username as user2", "u2.last as lastlogin2",
			"a1.tag as ally1", "a1.aid as allyid1",
			"a2.tag as ally2", "a2.aid as allyid2",
			"g1.galaxy as gala1", "g1.system as sys1", "g1.position as pos1",
			"g2.galaxy as gala2", "g2.system as sys2", "g2.position as pos2"
		);
		$joins  = "LEFT JOIN ".PREFIX."user u1 ON (u1.userid = b.friend1)";
		$joins .= "LEFT JOIN ".PREFIX."user u2 ON (u2.userid = b.friend2)";
		$joins .= "LEFT JOIN ".PREFIX."galaxy g1 ON (g1.planetid = u1.hp)";
		$joins .= "LEFT JOIN ".PREFIX."galaxy g2 ON (g2.planetid = u2.hp)";
		$joins .= "LEFT JOIN ".PREFIX."user2ally u2a1 ON (u2a1.userid = b.friend1)";
		$joins .= "LEFT JOIN ".PREFIX."user2ally u2a2 ON (u2a2.userid = b.friend2)";
		$joins .= "LEFT JOIN ".PREFIX."alliance a1 ON (a1.aid = u2a1.aid)";
		$joins .= "LEFT JOIN ".PREFIX."alliance a2 ON (a2.aid = u2a2.aid)";
		$result = Core::getQuery()->select("buddylist b", $select, $joins, "b.friend1 = '".Core::getUser()->get("userid")."' OR b.friend2 = '".Core::getUser()->get("userid")."'", "u1.points DESC, u2.points DESC, u1.username ASC, u2.username ASC");
		while($row = Core::getDB()->fetch($result))
		{
			Hook::event("ShowBuddyFirst", array(&$row));
			if($row["friend1"] == Core::getUser()->get("userid"))
			{
				if($row["lastlogin2"] > TIME - 900)
				{
					$status = Image::getImage("on.gif", getTimeTerm(TIME - $row["lastlogin2"]));
				}
				else
				{
					$status = Image::getImage("off.gif", getTimeTerm(TIME - $row["lastlogin2"]));
				}
				$username = Link::get("game.php/".SID."/MSG/Write/".rawurlencode($row["user2"]), Image::getImage("pm.gif", Core::getLanguage()->getItem("WRITE_MESSAGE")))." ".Link::get("game.php/".SID."/MSG/Write/".rawurlencode($row["user2"]), $row["user2"]);
				$points = $row["points2"];
				$position = getCoordLink($row["gala2"], $row["sys2"], $row["pos2"]);
				$ally = Link::get("game.php/".SID."/Alliance/Page/".$row["allyid2"], $row["ally2"]);
			}
			else
			{
				if($row["lastlogin1"] > TIME - 900)
				{
					$status = Image::getImage("on.gif", getTimeTerm(TIME - $row["lastlogin1"]));
				}
				else
				{
					$status = Image::getImage("off.gif", getTimeTerm(TIME - $row["lastlogin1"]));
				}
				$username = Link::get("game.php/".SID."/MSG/Write/".rawurlencode($row["user1"]), Image::getImage("pm.gif", Core::getLanguage()->getItem("WRITE_MESSAGE")))." ".Link::get("game.php/".SID."/MSG/Write/".rawurlencode($row["user1"]), $row["user1"]);
				$points = $row["points1"];
				$position = getCoordLink($row["gala1"], $row["sys1"], $row["pos1"]);
				$ally = Link::get("game.php/".SID."/Alliance/Page/".$row["allyid1"], $row["ally1"]);
			}
			$bl[$row["relid"]]["f1"] = $row["friend1"];
			$bl[$row["relid"]]["f2"] = $row["friend2"];
			$bl[$row["relid"]]["relid"] = $row["relid"];
			$bl[$row["relid"]]["username"] = $username;
			$bl[$row["relid"]]["accepted"] = $row["accepted"];
			$bl[$row["relid"]]["points"] = fNumber($points);
			$bl[$row["relid"]]["status"] = $status;
			$bl[$row["relid"]]["position"] = $position;
			$bl[$row["relid"]]["ally"] = $ally;
			Hook::event("ShowBuddyLast", array($row, &$bl));
		}
		Core::getDB()->free_result($result);
		Core::getTPL()->addLoop("buddylist", $bl);
		return $this;
	}

	/**
	 * Adds an user to buddylist.
	 *
	 * @param integer	User to add
	 *
	 * @return Bengine_Page_Friends
	 */
	protected function addAction($userid)
	{
		Core::getLanguage()->load(array("Buddylist"));
		if($userid == Core::getUser()->get("userid"))
		{
			Logger::dieMessage("SELF_REQUEST");
		}
		$result = Core::getQuery()->select("buddylist", array("friend1", "friend2"), "", "friend1 = '".$userid."' AND friend2 = '".Core::getUser()->get("userid")."' OR friend1 = '".Core::getUser()->get("userid")."' AND friend2 = '".$userid."'");
		if(Core::getDB()->num_rows($result) == 0)
		{
			Hook::event("AddToBuddyList", array($userid));
			Core::getQuery()->insertInto("buddylist", array("friend1" => Core::getUser()->get("userid"), "friend2" => $userid));

			$pm = Bengine::getModel("message");
			$pm->set("receiver", $userid)
				->set("mode", Bengine_Model_Message::USER_FOLDER_ID)
				->set("subject", Core::getLang()->get("FRIEND_REQUEST_RECEIVED"))
				->set("message", Core::getLang()->get("FRIEND_REQUEST_RECEIVED_MESSAGE"));
			$pm->send();
		}
		return $this->redirect("game.php/".SID."/Friends");
	}

	/**
	 * Removes an user from the buddylist.
	 *
	 * @param integer	User to remove
	 *
	 * @return Bengine_Page_Friends
	 */
	protected function remove(array $remove)
	{
		foreach($remove as $relid)
		{
			$result = Core::getQuery()->select("buddylist", array("friend1", "friend2", "accepted"), "", "relid = '".$relid."' AND (friend1 = '".Core::getUser()->get("userid")."' OR friend2 = '".Core::getUser()->get("userid")."')");
			if($row = Core::getDatabase()->fetch($result))
			{
				Hook::event("RemoveFromBuddyList", array($relid));
				Core::getQuery()->delete("buddylist", "relid = '".$relid."'");

				$pm = Bengine::getModel("message");
				$pm->set("receiver", $row["friend1"] == Core::getUser()->get("userid") ? $row["friend2"] : $row["friend1"])
					->set("mode", Bengine_Model_Message::USER_FOLDER_ID);
				if($row["accepted"] || $row["friend1"] == Core::getUser()->get("userid"))
				{
					$pm->set("subject", Core::getLang()->get("FRIENDSHIP_DELETED"))
						->set("message", Core::getLang()->get("FRIENDSHIP_DELETED_MESSAGE"));
				}
				else
				{
					$pm->set("subject", Core::getLang()->get("FRIEND_REQUEST_REJECTED"))
						->set("message", Core::getLang()->get("FRIEND_REQUEST_REJECTED_MESSAGE"));
				}
				$pm->send();
			}
		}
		return $this->redirect("game.php/".SID."/Friends");
	}

	/**
	 * Accepts a buddylist request.
	 *
	 * @param array		Post
	 *
	 * @return Bengine_Page_Friends
	 */
	protected function acceptRequest($relid)
	{
		if(!empty($relid) && is_numeric($relid))
		{
			Hook::event("AcceptBuddyListRequest", array($relid));
			$where = "relid = '".$relid."' AND friend2 = '".Core::getUser()->get("userid")."'";
			Core::getQuery()->updateSet("buddylist", array("accepted" => 1), $where);
			$result = Core::getQuery()->select("buddylist", array("friend1"), "", $where);
			if($friend = Core::getDatabase()->fetch_field($result, "friend1"))
			{
				$pm = Bengine::getModel("message");
				$pm->set("receiver", $friend)
					->set("mode", Bengine_Model_Message::USER_FOLDER_ID)
					->set("subject", Core::getLang()->get("FRIEND_REQUEST_ACCEPTED"))
					->set("message", Core::getLang()->get("FRIEND_REQUEST_ACCEPTED_MESSAGE"));
				$pm->send();
			}
		}
		return $this->redirect("game.php/".SID."/Friends");
	}
}
?>