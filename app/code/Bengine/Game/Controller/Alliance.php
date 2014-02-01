<?php
/**
 * Found alliances, shows alliance page and manage it.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Alliance.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_Alliance extends Bengine_Game_Controller_Abstract
{
	/**
	 * Displaying alliance id.
	 *
	 * @var integer
	 */
	protected $aid = null;

	/**
	 * Validation pattern for alliance name and tag.
	 *
	 * @var string
	 */
	protected $namePattern = "/^[A-z0-9_\-\s\.]+$/i";

	/**
	 * Holds all actions to set the first parameter to aid.
	 *
	 * @var array
	 */
	protected $aidActions = array(
		"Page", "Apply", "Memberlist"
	);

	/**
	 * Constructor: Handles post and get actions.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function init()
	{
		Core::getLanguage()->load("Alliance");

		// Set alliance id for the current session.
		$this->aid = Core::getUser()->get("aid");
		if(Core::getRequest()->getGET("1") && in_array($this->getParam("action"), $this->aidActions))
		{
			$this->aid = Core::getRequest()->getGET("1");
		}
		else if(Core::getRequest()->getPOST("aid"))
		{
			$this->aid = Core::getRequest()->getPOST("aid");
		}
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function indexAction()
	{
		if(Core::getUser()->get("aid"))
		{
			if($this->isPost() && $this->getParam("leave"))
			{
				$this->leaveAlliance();
			}
			$this->setTemplate("alliance/page");
			$this->pageAction($this->aid);
		}
		else
		{
			if($this->isPost() && $this->getParam("cancel"))
			{
				$this->cancelApplication($this->getParam("alliance", array()));
			}
			$found = Link::get("game/".SID."/Alliance/Found", Core::getLanguage()->getItem("FOUND_ALLIANCE"));
			$join = Link::get("game/".SID."/Alliance/Join", Core::getLanguage()->getItem("JOIN_ALLIANCE"));

			$apps = array();
			$result = Core::getQuery()->select("allyapplication aa", array("a.aid", "a.tag", "a.name", "aa.date", "aa.application"), "LEFT JOIN ".PREFIX."alliance a ON (a.aid = aa.aid)", Core::getDB()->quoteInto("aa.userid = ?", Core::getUser()->get("userid")));
			foreach($result->fetchAll() as $row)
			{
				$apps[$row["aid"]]["tag"] = Link::get("game/".SID."/Alliance/Page/".$row["aid"], $row["tag"], $row["name"]);
				$apps[$row["aid"]]["date"] = Date::timeToString(1, $row["date"]);
				$apps[$row["aid"]]["apptext"] = nl2br($row["application"]);
				$apps[$row["aid"]]["aid"] = $row["aid"];
			}
			$result->closeCursor();
			Hook::event("AllianceOverview", array(&$apps));
			Core::getTPL()->addLoop("applications", $apps);
			Core::getTPL()->assign("foundAlly", $found);
			Core::getTPL()->assign("joinAlly", $join);
		}
		return $this;
	}

	/**
	 * Displays the form to found an alliance.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function foundAction()
	{
		if(!Core::getUser()->get("aid"))
		{
			if($this->isPost())
			{
				$this->foundAlliance($this->getParam("tag"), $this->getParam("name"));
			}
		}
		return $this;
	}

	/**
	 * Cancels an application.
	 *
	 * @param array $alliance
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function cancelApplication($alliance)
	{
		foreach($alliance as $aid)
		{
			Core::getQuery()->delete("allyapplication", "userid = ? AND aid = ?", null, null, array(Core::getUser()->get("userid"), $aid));
		}
		return $this;
	}

	/**
	 * Create the posted alliance.
	 *
	 * @param string $tag
	 * @param string $name
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function foundAlliance($tag, $name)
	{
		$tag = trim($tag);
		$name = trim($name);
		$minCharsTag = Core::getOptions()->get("MIN_CHARS_ALLY_TAG");
		$maxCharsTag = Core::getOptions()->get("MAX_CHARS_ALLY_TAG");
		$minCharsName = Core::getOptions()->get("MIN_CHARS_ALLY_NAME");
		$maxCharsName = Core::getOptions()->get("MAX_CHARS_ALLY_NAME");
		Hook::event("FoundAlliance", array(&$tag, &$name));
		if(Str::length($tag) >= $minCharsTag && Str::length($tag) <= $maxCharsTag && Str::length($name) >= $minCharsName && Str::length($name) <= $maxCharsName && preg_match($this->namePattern, $tag) && preg_match($this->namePattern, $name))
		{
			$where = Core::getDB()->quoteInto("tag = ? OR name = ?", array($tag, $name));
			$result = Core::getQuery()->select("alliance", array("tag", "name"), "", $where);
			if($result->rowCount() == 0)
			{
				Core::getQuery()->insert("alliance", array("tag" => $tag, "name" => $name, "founder" => Core::getUser()->get("userid"), "open" => 1));
				$aid = Core::getDB()->lastInsertId();
				Core::getQuery()->insert("user2ally", array("userid" => Core::getUser()->get("userid"), "aid" => $aid, "joindate" => TIME, "rank" => null));
				Core::getUser()->rebuild();
				Hook::event("AllianceFounded", array($tag, $name, $aid));
				$this->redirect("game/".SID."/Alliance");
			}
			else
			{
				Logger::addMessage("ALLIANCE_ALREADY_EXISTS");
			}
		}
		else
		{
			if(Str::length($tag) < $minCharsTag || Str::length($tag) > $maxCharsTag || !preg_match($this->namePattern, $tag))
			{
				Core::getTPL()->assign("tagError", Logger::getMessageField("ALLIANCE_TAG_INVALID"));
			}

			if(Str::length($name) < $minCharsName || Str::length($name) > $maxCharsName || !preg_match($this->namePattern, $name))
			{
				Core::getTPL()->assign("nameError", Logger::getMessageField("ALLIANCE_NAME_INVALID"));
			}
		}
		return $this;
	}

	/**
	 * Shows the alliance page of given id.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function pageAction()
	{
		$select = array("u2a.userid", "u2a.joindate", "a.aid", "a.name", "a.tag", "a.textextern", "a.textintern", "a.founder", "a.foundername", "a.logo", "a.homepage", "a.showmember", "a.showhomepage", "count(aa.userid) as applications");
		$join  = "LEFT JOIN ".PREFIX."alliance a ON (u2a.aid = a.aid)";
		$join .= " LEFT JOIN ".PREFIX."allyapplication aa ON (u2a.aid = aa.aid)";
		$result = Core::getQuery()->select("user2ally u2a", $select, $join, Core::getDB()->quoteInto("u2a.aid = ?", $this->aid), "", "", "u2a.userid");

		$totalmember = $result->rowCount();
		$row = $result->fetchRow();
		$result->closeCursor();
		Hook::event("ShowAlliancePage", array(&$row));

		if(Core::getUser()->get("aid") == $row["aid"] && $row["founder"] != Core::getUser()->get("userid"))
		{
			// Load rights
			$result = Core::getQuery()->select("allyrank ar", array("ar.name", "ar.CAN_SEE_MEMBERLIST", "ar.CAN_SEE_APPLICATIONS", "ar.CAN_MANAGE", "ar.CAN_WRITE_GLOBAL_MAILS"), "LEFT JOIN ".PREFIX."user2ally u2a ON u2a.rank = ar.rankid", Core::getDB()->quoteInto("u2a.userid = ?", Core::getUser()->get("userid")));
			$rights = $result->fetchRow();
			$result->closeCursor();
			Core::getTPL()->assign("rank", ($rights["name"] != "") ? $rights["name"] : Core::getLanguage()->getItem("NEWBIE"));
			Core::getTPL()->assign("CAN_SEE_MEMBERLIST", $rights["CAN_SEE_MEMBERLIST"]);
			Core::getTPL()->assign("CAN_SEE_APPLICATIONS", $rights["CAN_SEE_APPLICATIONS"]);
			Core::getTPL()->assign("CAN_MANAGE", $rights["CAN_MANAGE"]);
			Core::getTPL()->assign("CAN_WRITE_GLOBAL_MAILS", $rights["CAN_WRITE_GLOBAL_MAILS"]);
			if($rights["CAN_MANAGE"]) { $manage = "(".Link::get("game/".SID."/Alliance/Manage", Core::getLanguage()->getItem("MANAGEMENT")).")"; }
			else { $manage = ""; }
		}
		else if($row["founder"] == Core::getUser()->get("userid"))
		{
			Core::getTPL()->assign("rank", ($row["foundername"] != "") ? $row["foundername"] : Core::getLanguage()->getItem("FOUNDER"));
			$manage = "(".Link::get("game/".SID."/Alliance/Manage", Core::getLanguage()->getItem("MANAGEMENT")).")";
		}
		else
		{
			$where = Core::getDB()->quoteInto("userid = ? AND aid = ?", array(Core::getUser()->get("userid"), $row["aid"]));
			$result = Core::getQuery()->select("allyapplication", "userid", "", $where);
			Core::getTPL()->assign("appInProgress", $result->rowCount());
			$result->closeCursor();
			$manage = "";
		}

		$parser = new Bengine_Game_Alliance_Page_Parser($this->aid);
		$row["textextern"] = (strip_tags($row["textextern"]) != "") ? $parser->startParser($row["textextern"]) : Core::getLanguage()->getItem("WELCOME");
		$row["textintern"] = (strip_tags($row["textintern"]) != "") ? $parser->startParser($row["textintern"]) : "";
		$parser->kill();

		Core::getTPL()->assign("appnumber", $row["applications"]);
		Core::getTPL()->assign("applications", Link::get("game/".SID."/Alliance/ShowCandidates", sprintf(Core::getLanguage()->getItem("CANDIDATES"), fNumber($row["applications"]))));
		Core::getTPL()->assign("founder", $row["founder"]);
		Core::getTPL()->assign("manage", $manage);
		Core::getTPL()->assign("tag", $row["tag"]);
		Core::getTPL()->assign("name", $row["name"]);
		Core::getTPL()->assign("aid", $row["aid"]);
		Core::getTPL()->assign("logo", ($row["logo"] != "") ? Image::getImage($row["logo"], "") : "");

		Core::getTPL()->assign("textextern", $row["textextern"]);
		Core::getTPL()->assign("homepage", ($row["homepage"] != "") ? Link::get($row["homepage"], $row["homepage"]) : "");
		Core::getTPL()->assign("showHomepage", $row["showhomepage"]);
		Core::getTPL()->assign("textintern", $row["textintern"]);
		Core::getTPL()->assign("memberNumber", fNumber($totalmember));
		Core::getTPL()->assign("memberList", Link::get("game/".SID."/Alliance/Memberlist/".$row["aid"], Core::getLanguage()->getItem("MEMBER_LIST")));
		Core::getTPL()->assign("showMember", $row["showmember"]);
		return $this;
	}

	/**
	 * Loads the rights and check for access.
	 *
	 * @param array $rights Rights to check
	 *
	 * @return boolean
	 */
	protected function getRights($rights)
	{
		$select = array("a.aid", "a.founder", "ar.CAN_MANAGE", "ar.CAN_SEE_MEMBERLIST", "ar.CAN_SEE_APPLICATIONS", "ar.CAN_BAN_MEMBER", "ar.CAN_SEE_ONLINE_STATE", "ar.CAN_WRITE_GLOBAL_MAILS");
		$joins  = "LEFT JOIN ".PREFIX."alliance a ON (a.aid = u2a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."allyrank ar ON (ar.rankid = u2a.rank)";
		$result = Core::getQuery()->select("user2ally u2a", $select, $joins, Core::getDB()->quoteInto("u2a.userid = ?", Core::getUser()->get("userid")));
		if($_row = $result->fetchRow())
		{
			$result->closeCursor();
			Hook::event("GetAllianceRights", array($rights, &$_row));
			if($_row["founder"] == Core::getUser()->get("userid"))
			{
				return true;
			}
			if(!is_array($rights)) { $rights = Arr::trim(explode(",", $rights)); }
			foreach($rights as $right)
			{
				if($_row[$right] == 0) { return false; }
			}

			return true;
		}
		else { $result->closeCursor(); }
		return false;
	}

	/**
	 * Displays applications for relationships.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function relApplicationsAction()
	{
		if($this->getRights(array("CAN_MANAGE")))
		{
			$apps = array(); $i = 0;
			$joins  = "LEFT JOIN ".PREFIX."alliance a1 ON (a1.aid = ara.candidate_ally)";
			$joins .= "LEFT JOIN ".PREFIX."alliance a2 ON (a2.aid = ara.request_ally)";
			$joins .= "LEFT JOIN ".PREFIX."ally_relationship_type rt ON (rt.type_id = ara.mode)";
			$select = array("ara.candidate_ally", "ara.request_ally", "rt.name AS type_name", "ara.application", "ara.time", "a1.tag AS tag1", "a1.name AS name1", "a2.tag AS tag2", "a2.name AS name2");
			$result = Core::getQuery()->select("ally_relationships_application ara", $select, $joins, Core::getDB()->quoteInto("ara.candidate_ally = ? OR ara.request_ally = ?", $this->aid));
			foreach($result->fetchAll() as $row)
			{
				$apps[$i]["time"] = Date::timeToString(1, $row["time"]);
				$apps[$i]["application"] = $row["application"];
				$apps[$i]["status"] = Core::getLang()->get($row["type_name"]);
				if($row["candidate_ally"] == $this->aid)
				{
					$apps[$i]["ally"] = Link::get("game/".SID."/Alliance/Page/".$row["request_ally"], $row["tag2"], $row["name2"]);
					$apps[$i]["refuse"] = Link::get("game/".SID."/Alliance/RefuseRelation/".$row["request_ally"], Core::getLang()->getItem("REFUSE"));
				}
				else
				{
					$apps[$i]["ally"] = Link::get("game/".SID."/Alliance/Page/".$row["candidate_ally"], $row["tag1"], $row["name1"]);
					$apps[$i]["accept"] = Link::get("game/".SID."/Alliance/AcceptRelation/".$row["candidate_ally"], Core::getLang()->getItem("ACCEPT"));
					$apps[$i]["refuse"] = Link::get("game/".SID."/Alliance/RefuseRelation/".$row["candidate_ally"], Core::getLang()->getItem("REFUSE"));
				}
				$i++;
			}
			$result->closeCursor();
			Hook::event("ShowRelationApplications", array(&$apps));
			Core::getTPL()->addLoop("apps", $apps);
		}
		return $this;
	}

	/**
	 * Executes a diplomacy application.
	 *
	 * @param integer $status
	 * @param string $message
	 * @param string $tag
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function applyRelationship($status, $message, $tag)
	{
		$tag = trim($tag);
		if($this->getRights(array("CAN_MANAGE")))
		{
			if($status == 0 && Str::length($message) <= Core::getOptions()->get("MAX_PM_LENGTH") && Str::length($message) > 0)
			{
				Hook::event("SendAllianceMessage");
				$result = Core::getQuery()->select("user2ally u2a", array("u2a.userid", "u2a.aid"), "LEFT JOIN ".PREFIX."alliance a ON (a.aid = u2a.aid)", Core::getDB()->quoteInto("a.tag = ?", $tag));
				foreach($result->fetchAll() as $row)
				{
					if($row["aid"] == $this->aid) // You cannot send a message to your own alliance
					{
						Logger::addMessage("RELATION_TO_OWN_ALLIANCE", "warning");
						return $this;
					}
					Core::getQuery()->insert("message", array("mode" => 6, "time" => TIME, "sender" => Core::getUser()->get("userid"), "receiver" => $row["userid"], "message" => Str::validateXHTML($message), "subject" => Core::getLang()->getItem("MESSAGE_BY_ALLIANCE"), "read" => 0));
				}
				if($result->rowCount() == 0)
				{
					Logger::addMessage("NO_MATCHES_ALLIANCE");
				}
				$result->closeCursor();
			}
			else
			{
				$result = Core::getQuery()->select("ally_relationship_type", array("name", "confirm_begin", "confirm_end", "storable"), "", Core::getDB()->quoteInto("type_id = ?", $status));
				$typeData = $result->fetchRow();
				$result->closeCursor();

				$result = Core::getQuery()->select("alliance a", array("a.aid"), "", Core::getDB()->quoteInto("a.tag = ?", $tag));
				if(($row = $result->fetchRow()) && !empty($typeData))
				{
					$result->closeCursor();
					if($row["aid"] == $this->aid)
					{
						Logger::addMessage("RELATION_TO_OWN_ALLIANCE", "warning");
						return $this;
					}
					Hook::event("SendRelationApplication", array($row["aid"]));
					// Check for existing relations and applications
					$where  = "(ar.rel1 = ? AND ar.rel2 = ?) OR ";
					$where .= "(ar.rel1 = ? AND ar.rel2 = ?) OR ";
					$where .= "(ara.candidate_ally = ? AND ara.request_ally = ?) OR ";
					$where .= "(ara.candidate_ally = ? AND ara.request_ally = ?)";
					$where  = Core::getDB()->quoteInto($where, array($row["aid"], $this->aid, $this->aid, $row["aid"], $this->aid, $row["aid"], $row["aid"], $this->aid));
					$result = Core::getQuery()->select("ally_relationships_application ara, ".PREFIX."ally_relationships ar", array("ara.candidate_ally", "ara.request_ally", "ar.rel1", "ar.rel2"), "", $where);
					if($result->rowCount() <= 0 || !$typeData["storable"])
					{
						if(!$typeData["confirm_begin"])
						{
							Core::getQuery()->delete("ally_relationships", "(rel1 = ? AND rel2 = ?) OR (rel1 = ? AND rel2 = ?)", null, null, array(
								$this->aid, $row["aid"], $row["aid"], $this->aid
							));
							Core::getQuery()->insert("ally_relationships", array("rel1" => $this->aid, "rel2" => $row["aid"], "time" => TIME, "mode" => $status));
						}
						else if(Str::length($message) > 0 && Str::length($message) <= Core::getOptions()->get("MAX_PM_LENGTH"))
						{
							$spec = array(
								"candidate_ally" => $this->aid,
								"request_ally" => $row["aid"],
								"userid" => Core::getUser()->get("userid"),
								"mode" => $status,
								"application" => Str::validateXHTML($message),
								"time" => TIME
							);
							Core::getQuery()->insert("ally_relationships_application", $spec);
						}
						else
						{
							Logger::dieMessage("ALLIANCE_RELATION_APPLICATION_MESSAGE_REQUIRED");
						}
						$this->redirect("game/".SID."/Alliance/RelApplications");
					}
				}
				else
				{
					$result->closeCursor();
					Logger::addMessage("NO_MATCHES_ALLIANCE");
				}
			}
		}
		else
		{
			Logger::addMessage("ERROR_WITH_DOPLOMACY_APPLICATION");
		}
		return $this;
	}

	/**
	 * Creates a relationship.
	 *
	 * @param integer $candidateAlly
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function acceptRelationAction($candidateAlly)
	{
		if($this->getRights(array("CAN_MANAGE")))
		{
			$this->aid = Core::getUser()->get("aid");
			// Check existing applications
			$attr = array("rt.type_id", "rt.confirm_begin", "rt.confirm_end", "rt.storable", "rt_c.type_id AS confirm_type_id");
			$joins  = "LEFT JOIN ".PREFIX."ally_relationship_type rt ON (rt.type_id = ara.mode)";
			$joins .= "LEFT JOIN ".PREFIX."ally_relationship_type rt_c ON (rt_c.type_id = rt.confirm_end)";
			$where  = Core::getDB()->quoteInto("ara.request_ally = ? AND ara.candidate_ally = ?", array($this->aid, $candidateAlly));
			$result = Core::getQuery()->select("ally_relationships_application ara", $attr, $joins, $where);
			$row = $result->fetchRow();
			$result->closeCursor();
			if($row)
			{
				Hook::event("AcceptAllianceRelation", array($candidateAlly, $row));
				if($row["storable"])
				{
					Core::getQuery()->delete("ally_relationships", "(rel1 = ? AND rel2 = ?) OR (rel1 = ? AND rel2 = ?)", null, null, array(
						$this->aid, $candidateAlly, $candidateAlly, $this->aid
					));
					Core::getQuery()->insert("ally_relationships", array("rel1" => $this->aid, "rel2" => $candidateAlly, "time" => TIME, "mode" => $row["type_id"]));
				}
				$_result = Core::getQuery()->select("ally_relationship_type", array("type_id"), "", Core::getDB()->quoteInto("confirm_end = ?", $row["type_id"]));
				if($_row = $_result->fetchRow())
				{
					Core::getQuery()->delete("ally_relationships", "((rel1 = ? AND rel2 = ?) OR (rel1 = ? AND rel2 = ?)) AND mode = ?", null, null, array($this->aid, $candidateAlly, $candidateAlly, $this->aid, $_row["type_id"]));
				}
				$this->deleteAllyRelationApplication($this->aid, $candidateAlly);
			}
		}
		$this->setTemplate("alliance/relapplications");
		$this->relApplicationsAction();
		return $this;
	}

	/**
	 * Deletes a relation application.
	 *
	 * @param integer $targetAlly
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function refuseRelationAction($targetAlly)
	{
		if($this->getRights(array("CAN_MANAGE")))
		{
			if($targetAlly > 0)
			{
				$where = "(request_ally = ? AND candidate_ally = ?) OR (candidate_ally = ? AND request_ally = ?)";
				$where = Core::getDB()->quoteInto($where, array($this->aid, $targetAlly, $this->aid, $targetAlly));
				$result = Core::getQuery()->select("ally_relationships_application", array("mode"), "", $where);
				$row = $result->fetchRow();
				$result->closeCursor();
				if($row)
				{
					Hook::event("RefuseAllianceRelation", array($targetAlly, $row));
					$this->deleteAllyRelationApplication($this->aid, $targetAlly);
				}
			}
		}
		$this->setTemplate("alliance/relapplications");
		$this->relApplicationsAction();
		return $this;
	}

	/**
	 * Deletes a relation application.
	 *
	 * @param integer
	 * @param integer
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function deleteAllyRelationApplication($aid1, $aid2)
	{
		Hook::event("DeleteAllianceRelation", array($aid1, $aid2));
		Core::getQuery()->delete("ally_relationships_application", "(request_ally = ? AND candidate_ally = ?) OR (request_ally = ? AND candidate_ally = ?)", null, null, array($aid1, $aid2, $aid2, $aid1));
		return $this;
	}

	/**
	 * Shows diplomacy management.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function diplomacyAction()
	{
		if($this->isPost() && $this->getParam("SendContract"))
		{
			$this->applyRelationship($this->getParam("status"), $this->getParam("message"), $this->getParam("tag"));
		}
		if($this->getRights(array("CAN_MANAGE")))
		{
			$rels = array(); $i = 1;
			$select = array("ar.relid", "rt.name AS relation_name", "rt.confirm_end", "ar.time", "ar.rel1", "a1.name as name1", "a1.tag as tag1", "ar.rel2", "a2.name as name2", "a2.tag as tag2", "u1.username as user1", "u2.username as user2");
			$joins  = "LEFT JOIN ".PREFIX."alliance a1 ON (a1.aid = ar.rel1)";
			$joins .= "LEFT JOIN ".PREFIX."alliance a2 ON (a2.aid = ar.rel2)";
			$joins .= "LEFT JOIN ".PREFIX."user u1 ON (u1.userid = a1.founder)";
			$joins .= "LEFT JOIN ".PREFIX."user u2 ON (u2.userid = a2.founder)";
			$joins .= "LEFT JOIN ".PREFIX."ally_relationship_type rt ON (rt.type_id = ar.mode)";
			$result = Core::getQuery()->select("ally_relationships ar", $select, $joins, Core::getDB()->quoteInto("(ar.rel1 = ? OR ar.rel2 = ?)", $this->aid), "ar.mode ASC");
			foreach($result->fetchAll() as $row)
			{
				$rels[$i]["num"] = $i;
				$rels[$i]["status"] = Core::getLang()->getItem($row["relation_name"]);
				$rels[$i]["time"] = Date::timeToString(1, $row["time"]);
				if(!$row["confirm_end"]) { $rels[$i]["determine"] = "[".Link::get("game/".SID."/Alliance/DetermineRel/".$row["relid"], Core::getLang()->getItem("DETERMINE"))."]"; }
				else { $rels[$i]["determine"] = ""; }
				if($this->aid == $row["rel1"])
				{
					$rels[$i]["alliance"] = Link::get("game/".SID."/Alliance/Page/".$row["rel2"], $row["tag2"], $row["name2"]);
					$rels[$i]["founder"] = Link::get("game/".SID."/MSG/Write/".$row["user2"], $row["user2"]);
				}
				else
				{
					$rels[$i]["alliance"] = Link::get("game/".SID."/Alliance/Page/".$row["rel1"], $row["tag1"], $row["name1"]);
					$rels[$i]["founder"] = Link::get("game/".SID."/MSG/Write/".$row["user1"], $row["user1"]);
				}
				$i++;
			}
			Hook::event("DiplomacyManagement", array(&$rels));

			// Running applications?
			$result = Core::getQuery()->select("ally_relationships_application", array("request_ally", "candidate_ally"), "", Core::getDB()->quoteInto("candidate_ally = ? OR request_ally = ?", $this->aid));
			$applications = $result->rowCount();
			$result->closeCursor();
			if($applications > 0)
			{
				Core::getLang()->assign("applications", $applications);
				Core::getTPL()->assign("applications", Link::get("game/".SID."/Alliance/RelApplications", Core::getLang()->getItem("SHOW_RELATION_APPLICATIONS")));
			}
			else { Core::getTPL()->assign("applications", false); }
			Core::getTPL()->addLoop("statusList", $this->getDiploType());
			Core::getTPL()->assign("maxpmlength", fNumber(Core::getOptions()->get("MAX_PM_LENGTH")));
			Core::getTPL()->addLoop("relations", $rels);
		}
		return $this;
	}

	/**
	 * Determines a relation between two alliances.
	 *
	 * @param integer $relid	Relation to determine
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function determineRelAction($relid)
	{
		if($this->getRights(array("CAN_MANAGE")))
		{
			$this->aid = Core::getUser()->get("aid");
			Hook::event("DetermineAllianceRelation");
			// TODO: Improve check
			Core::getQuery()->delete("ally_relationships", "relid = ? AND mode != ? AND (rel1 = ? OR rel2 = ?)", null, null, array($relid, 3, $this->aid, $this->aid));
			$this->redirect("game/".SID."/Alliance/Diplomacy");
		}
		Logger::addMessage("CANNOT_DELETE_RELATIONSHIP");
		$this->setTemplate("alliance/diplomacy");
		$this->diplomacyAction();
		return $this;
	}

	/**
	 * Returns the diplomacy types.
	 *
	 * @return array
	 */
	protected function getDiploType()
	{
		$ret = array();
		$result = Core::getQuery()->select("ally_relationship_type", array("type_id", "name", "css", "confirm_begin", "confirm_end", "storable", "acs"));
		foreach($result->fetchAll() as $row)
		{
			$row["trans"] = Core::getLang()->get($row["name"]);
			$ret[] = $row;
		}
		$result->closeCursor();
		Hook::event("GetDiplomacyStatus", array(&$ret));
		return $ret;
	}

	/**
	 * Alliance management.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function manageAction()
	{
		if($this->isPost())
		{
			if($this->getParam("abandonally"))
			{
				$this->abandonAlly();
			}
			if($this->getParam("referfounder"))
			{
				$this->referFounderStatus($this->getParam("userid"));
			}
		}
		$select = array("a.aid", "a.founder", "a.foundername", "a.name", "a.tag", "a.textextern", "a.textintern", "a.applicationtext", "a.homepage", "a.logo", "a.showhomepage", "a.showmember", "a.memberlistsort", "a.open", "u2a.rank", "ar.CAN_MANAGE");
		$joins  = "LEFT JOIN ".PREFIX."alliance a ON (a.aid = u2a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."allyrank ar ON (ar.rankid = u2a.rank)";
		$result = Core::getQuery()->select("user2ally u2a", $select, $joins, Core::getDB()->quoteInto("u2a.userid = ?", Core::getUser()->get("userid")));
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			Hook::event("AllianceManagement", array(&$row));
			if($row["founder"] == Core::getUser()->get("userid") || $row["CAN_MANAGE"])
			{
				if($this->isPost())
				{
					if($this->getParam("changeprefs") || $this->getParam("changetext"))
					{
						$this->updateAllyPrefs(
							$this->getParam("showmember"), $this->getParam("showhomepage"), $this->getParam("open"), $this->getParam("foundername"),
							$this->getParam("memberlistsort"), $this->getParam("textextern"), $this->getParam("textintern"), $this->getParam("logo"),
							$this->getParam("homepage"), $this->getParam("applicationtext")
						);
					}
					if($this->getParam("changetag"))
					{
						$row["tag"] = $this->updateAllyTag($this->getParam("tag"), $row["tag"]);
					}
					if($this->getParam("changename"))
					{
						$row["name"] = $this->updateAllyName($this->getParam("name"), $row["name"]);
					}
				}
				Core::getTPL()->assign($row);

				Core::getTPL()->assign("maxallytext", fNumber(Core::getOptions()->get("MAX_ALLIANCE_TEXT_LENGTH")));
				Core::getTPL()->assign("maxapplicationtext", fNumber(Core::getOptions()->get("MAX_APPLICATION_TEXT_LENGTH")));

				if($row["showhomepage"])
				{
					Core::getTPL()->assign("showhp", " checked=\"checked\"");
				}
				if($row["showmember"])
				{
					Core::getTPL()->assign("showmember",  " checked=\"checked\"");
				}
				if($row["open"])
				{
					Core::getTPL()->assign("open",  " checked=\"checked\"");
				}
				switch($row["memberlistsort"])
				{
					case 1: Core::getTPL()->assign("bypoinst",  " selected=\"selected\""); break;
					case 2: Core::getTPL()->assign("byname",  " selected=\"selected\""); break;
				}

				if($row["founder"] == Core::getUser()->get("userid"))
				{
					$referfounder = "";
					$where = Core::getDB()->quoteInto("u2a.aid = ? AND u2a.userid != ?", array($row["aid"], Core::getUser()->get("userid")));
					$result = Core::getQuery()->select("user2ally u2a", array("u2a.userid", "u.username"), "LEFT JOIN ".PREFIX."user u ON (u.userid = u2a.userid)", $where, "u.username ASC");
					foreach($result->fetchAll() as $row)
					{
						$referfounder .= createOption($row["userid"], $row["username"], 0);
					}
					$result->closeCursor();
					Core::getTPL()->assign("referfounder", $referfounder);
				}
			}
		}
		return $this;
	}

	/**
	 * Displays the memberlist.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function memberlistAction()
	{
		if($this->aid == Core::getUser()->get("aid"))
		{
			$select = array("ar.CAN_SEE_MEMBERLIST", "ar.CAN_MANAGE", "ar.CAN_BAN_MEMBER", "ar.CAN_SEE_ONLINE_STATE", "a.founder", "a.showmember");
			$where = Core::getDB()->quoteInto("u2a.userid = ? AND u2a.aid = ?", array(Core::getUser()->get("userid"), $this->aid));
			$result = Core::getQuery()->select("user2ally u2a", $select, "LEFT JOIN ".PREFIX."allyrank ar ON (u2a.rank = ar.rankid) LEFT JOIN ".PREFIX."alliance a ON (a.aid = u2a.aid)", $where);
			$row = $result->fetchRow();
			$result->closeCursor();
			if($row["founder"] != Core::getUser()->get("userid"))
			{
				$can_see_memberlist = ($row["showmember"]) ? 1 : $row["CAN_SEE_MEMBERLIST"];
				$can_manage = $row["CAN_MANAGE"];
				$can_ban_member = $row["CAN_BAN_MEMBER"];
				$can_see_onlie_state = $row["CAN_SEE_ONLINE_STATE"];
			}
			else
			{
				$can_see_memberlist = 1;
				$can_manage = 1;
				$can_ban_member = 1;
				$can_see_onlie_state = 1;
			}
			Core::getTPL()->assign("founder", $row["founder"]);
		}
		else
		{
			$result = Core::getQuery()->select("alliance", "showmember", "", Core::getDB()->quoteInto("aid = ?", $this->aid));
			$row = $result->fetchRow();
			$result->closeCursor();
			$can_see_memberlist = $row["showmember"];
			$can_manage = 0;
			$can_ban_member = 0;
			$can_see_onlie_state = 0;
		}
		unset($row); unset($result);

		if($can_see_memberlist)
		{
			if($can_manage)
			{
				if(Core::getRequest()->getPOST("changeMembers"))
				{
					$result = Core::getQuery()->select("user2ally", "userid", "", Core::getDB()->quoteInto("aid = ?", $this->aid));
					foreach($result->fetchAll() as $row)
					{
						if($rankid = Core::getRequest()->getPOST("rank_".$row["userid"]))
						{
							Core::getQuery()->update("user2ally", array("rank" => $rankid), "userid = ? AND aid = ?", array($row["userid"], $this->aid));
						}
					}
					$result->closeCursor();
				}
				else if(count(Core::getRequest()->getPOST()) > 0)
				{
					foreach(Core::getRequest()->getPOST() as $key => $value)
					{
						if(preg_match("#^kick\_#i", $key))
						{
							$kickuserid = Str::replace("kick_", "", $key);
							Hook::event("KickMember", array($kickuserid));
							Core::getQuery()->delete("user2ally", "userid = ? AND aid = ?", null, null, array($kickuserid, $this->aid));
							$_result = Core::getQuery()->select("user2ally", "userid", "", Core::getDB()->quoteInto("aid = ?", $this->aid));
							foreach($_result->fetchAll() as $_row)
							{
								new Bengine_Game_AutoMsg(102, $_row["userid"], TIME, array());
							}
							$_result->closeCursor();
							unset($_row);
							break;
						}
					}
				}
				$ranks = array();
				$result = Core::getQuery()->select("allyrank", array("rankid", "name"), "", Core::getDB()->quoteInto("aid = ?", $this->aid));
				foreach($result->fetchAll() as $row)
				{
					$ranks[$row["rankid"]]["name"] = $row["name"];
				}
				Hook::event("AllianceRanks", array(&$ranks));
				$result->closeCursor();
			}
			if($can_ban_member && $can_see_onlie_state) { $colspan = "8"; }
			else if($can_ban_member || $can_see_onlie_state) { $colspan = "7"; }
			else { $colspan = "6"; }
			Core::getTPL()->assign("colspan", $colspan);
			Core::getTPL()->assign("can_manage", $can_manage);
			Core::getTPL()->assign("can_ban_member", $can_ban_member);
			Core::getTPL()->assign("can_see_onlie_state", $can_see_onlie_state);

			$select = array("u2a.userid", "u.username", "u.points AS points", "u2a.joindate", "u.last", "g.galaxy", "g.system", "g.position", "ar.rankid", "ar.name AS rankname", "a.tag", "a.founder", "a.foundername");
			$joins  = "LEFT JOIN ".PREFIX."user u ON (u.userid = u2a.userid)";
			$joins .= "LEFT JOIN ".PREFIX."galaxy g ON (g.planetid = u.hp)";
			$joins .= "LEFT JOIN ".PREFIX."allyrank ar ON (ar.rankid = u2a.rank)";
			$joins .= "LEFT JOIN ".PREFIX."alliance a ON (a.aid = u2a.aid)";
			$result = Core::getQuery()->select("user2ally u2a", $select, $joins, Core::getDB()->quoteInto("u2a.aid = ?", $this->aid), "u.points DESC LIMIT 100");
			$sum = 0;
			$membercount = 0;
			$members = array();
			foreach($result->fetchAll() as $row)
			{
				$uid = $row["userid"];
				$members[$uid]["userid"] = $uid;
				$members[$uid]["username"] = $row["username"];
				$members[$uid]["points"] = fNumber(floor($row["points"]));
				$members[$uid]["joindate"] = Date::timeToString(1, $row["joindate"]);
				$members[$uid]["last"] = $row["last"];
				if($can_manage) { $members[$uid]["rankselection"] = $this->getRankSelect($ranks, $row["rankid"]); }
				if($row["founder"] == $row["userid"])
				{
					if($row["foundername"] != "") { $members[$uid]["rank"] = $row["foundername"]; }
					else { $members[$uid]["rank"] = Core::getLanguage()->getItem("FOUNDER"); }
				}
				else if($row["rankname"])
				{
					$members[$uid]["rank"] = $row["rankname"];
				}
				else
				{
					$members[$uid]["rank"] = Core::getLanguage()->getItem("NEWBIE");
				}
				$members[$uid]["position"] = getCoordLink($row["galaxy"], $row["system"], $row["position"]);
				if(Core::getUser()->get("userid") != $uid)
				{
					$members[$uid]["message"] = Link::get("game/".SID."/MSG/Write/".$row["username"], Image::getImage("pm.gif", Core::getLanguage()->getItem("WRITE_MESSAGE")));
				}
				if(TIME - $row["last"] < 900)
				{
					$members[$uid]["online"] = Image::getImage("on.gif", getTimeTerm(TIME - $row["last"]));
				}
				else
				{
					$members[$uid]["online"] = Image::getImage("off.gif", getTimeTerm(TIME - $row["last"]));
				}
				$sum += $row["points"];
				$membercount++;
			}
			$result->closeCursor();
			Hook::event("ShowMemberList", array(&$members));
			Core::getTPL()->assign("totalmembers", fNumber($membercount));
			Core::getTPL()->assign("totalpoints", fNumber(floor($sum)));
			Core::getTPL()->addLoop("members", $members);
		}
		return $this;
	}

	/**
	 * Generates the select list to chose rank.
	 *
	 * @param array $ranks
	 * @param integer $id
	 *
	 * @return string	Select box content
	 */
	protected function getRankSelect($ranks, $id)
	{
		$return = "";
		foreach($ranks as $key => $value)
		{
			if($id == $key) { $s = 1; } else { $s = 0; }
			$return .= createOption($key, $value["name"], $s);
		}
		return $return;
	}

	/**
	 * Checks and saves a new alliance tag.
	 *
	 * @param string $tag
	 * @param string $otag
	 *
	 * @return string	New tag
	 */
	protected function updateAllyTag($tag, $otag)
	{
		$tag = trim($tag);
		$minCharsTag = Core::getOptions()->get("MIN_CHARS_ALLY_TAG");
		$maxCharsTag = Core::getOptions()->get("MAX_CHARS_ALLY_TAG");
		if(!Str::compare($tag, $otag))
		{
			$result = Core::getQuery()->select("alliance", "tag", "", Core::getDB()->quoteInto("tag = ?", $tag));
			if($result->rowCount() > 0)
			{
				$tag = $otag;
				Logger::addMessage("ALLIANCE_ALREADY_EXISTS");
			}
			$result->closeCursor();
			if(Str::length($tag) < $minCharsTag || Str::length($tag) > $maxCharsTag || !preg_match($this->namePattern, $tag))
			{
				$tag = $otag;
				Logger::addMessage("ALLIANCE_TAG_INVALID");
			}
		}
		Hook::event("UpdateAllianceTag", array(&$tag, $otag));
		Core::getQuery()->update("alliance", array("tag" => $tag), "aid = ?", array($this->aid));
		return $tag;
	}

	/**
	 * Checks and saves a new alliance name.
	 *
	 * @param string $name
	 * @param string $oname
	 *
	 * @return string	New name
	 */
	protected function updateAllyName($name, $oname)
	{
		$name = trim($name);
		$minCharsName = Core::getOptions()->get("MIN_CHARS_ALLY_NAME");
		$maxCharsName = Core::getOptions()->get("MAX_CHARS_ALLY_NAME");
		if(!Str::compare($name, $oname))
		{
			$result = Core::getQuery()->select("alliance", "name", "", Core::getDB()->quoteInto("name = ?", $name));
			if($result->rowCount() > 0)
			{
				$name = $oname;
				Logger::addMessage("ALLIANCE_ALREADY_EXISTS");
			}
			$result->closeCursor();
			if(Str::length($name) < $minCharsName || Str::length($name) > $maxCharsName || !preg_match($this->namePattern, $name))
			{
				$name = $oname;
				Logger::addMessage("ALLIANCE_NAME_INVALID");
			}
		}
		Hook::event("UpdateAllianceName", array(&$name, $oname));
		Core::getQuery()->update("alliance", array("name" => $name), "aid = ?", array($this->aid));
		return $name;
	}

	/**
	 * Saves the updated alliance preferences.
	 *
	 * @param boolean $showmember		Show member list to everyone
	 * @param boolean $showhomepage		Show homepage to everyone
	 * @param boolean $open				Open applications
	 * @param string $foundername		Founder rank name
	 * @param integer $memberlistsort	Default memer list sort
	 * @param string $textextern		Extern alliance text
	 * @param string $textintern		Intern alliance text
	 * @param string $logo				Logo URL
	 * @param string $homepage			Homepage URL
	 * @param string $applicationtext	Application template
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function updateAllyPrefs(
		$showmember, $showhomepage, $open, $foundername, $memberlistsort,
		$textextern, $textintern, $logo, $homepage, $applicationtext
	)
	{
		$foundername = trim($foundername);
		$logo = trim($logo);
		$homepage = trim($homepage);
		Hook::event("UpdateAlliancePreferences");
		if($showmember == 1) { $showmember = 1; } else { $showmember = 0; }
		if($showhomepage == 1) { $showhomepage = 1; } else { $showhomepage = 0; }
		if($open == 1) { $open = 1; } else { $open = 0; }
		if(Str::length($foundername) > Core::getOptions()->get("MAX_CHARS_ALLY_NAME")) { $foundername = ""; }
		$further = 1;
		if(Str::length($textextern) > Core::getOptions()->get("MAX_ALLIANCE_TEXT_LENGTH")) { $further = 0; }
		if(Str::length($textintern) > Core::getOptions()->get("MAX_ALLIANCE_TEXT_LENGTH")) { $further = 0; }
		if((!isValidImageURL($logo) || Str::length($logo) > 128) && $logo != "") { $further = 0; }
		if((!isValidURL($homepage) || Str::length($homepage) > 128) && $homepage != "") { $further = 0; }
		if(Str::length($applicationtext) > Core::getOptions()->get("MAX_APPLICATION_TEXT_LENGTH")) { $further = 0; }
		if($further == 1)
		{
			$spec = array(
				"logo" => $logo,
				"textextern" => richText($textextern),
				"textintern" => richText($textintern),
				"applicationtext" => Str::validateXHTML($applicationtext),
				"homepage" => $homepage,
				"showmember" => $showmember,
				"showhomepage" => $showhomepage,
				"memberlistsort" => $memberlistsort,
				"open" => $open,
				"foundername" => Str::validateXHTML($foundername),
			);
			Core::getQuery()->update("alliance", $spec, "aid = ?", array($this->aid));
			$this->redirect("game/".SID."/Alliance/Manage");
		}
		else
		{
			if(Str::length($textextern) > Core::getOptions()->get("MAX_ALLIANCE_TEXT_LENGTH"))
			{
				Core::getTPL()->assign("externerr", Logger::getMessageField("TEXT_INVALID"));
			}
			if(Str::length($textintern) > Core::getOptions()->get("MAX_ALLIANCE_TEXT_LENGTH"))
			{
				Core::getTPL()->assign("internerr", Logger::getMessageField("TEXT_INVALID"));
			}
			if(Str::length($applicationtext) > Core::getOptions()->get("MAX_APPLICATION_TEXT_LENGTH"))
			{
				Core::getTPL()->assign("apperr", Logger::getMessageField("TEXT_INVALID"));
			}
			if((!isValidImageURL($logo) || Str::length($logo) > 128) && $logo != "")
			{
				Core::getTPL()->assign("logoerr", Logger::getMessageField("LOGO_INVALID"));
			}
			if((!isValidURL($homepage) || Str::length($homepage) > 128) && $homepage != "")
			{
				Core::getTPL()->assign("hperr", Logger::getMessageField("HOMEPAGE_INVALID"));
			}
		}
		return $this;
	}

	/**
	 * Rank management for the alliance.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function rightManagementAction()
	{
		$post = Core::getRequest()->getPOST();
		if($this->getRights(array("CAN_MANAGE")))
		{
			if(isset($post["createrank"]))
			{
				if(Str::length($post["name"]) <= 30)
				{
					Core::getQuery()->insert("allyrank", array("aid" => $this->aid, "name" => Str::validateXHTML($post["name"])));
				}
			}
			else if(isset($post["changerights"]))
			{
				$result = Core::getQuery()->select("allyrank", "rankid", "", Core::getDB()->quoteInto("aid = ?", $this->aid));
				foreach($result->fetchAll() as $row)
				{
					if(isset($post["CAN_SEE_MEMBERLIST_".$row["rankid"]])) { $can_see_memberlist = 1; }
					else { $can_see_memberlist = 0; }
					if(isset($post["CAN_SEE_APPLICATIONS_".$row["rankid"]])) { $can_sse_applications = 1; }
					else { $can_sse_applications = 0; }
					if(isset($post["CAN_MANAGE_".$row["rankid"]])) { $can_manage = 1; }
					else { $can_manage = 0; }
					if(isset($post["CAN_BAN_MEMBER_".$row["rankid"]])) { $can_ban_member = 1; }
					else { $can_ban_member = 0; }
					if(isset($post["CAN_SEE_ONLINE_STATE_".$row["rankid"]])) { $can_see_onlie_state = 1; }
					else { $can_see_onlie_state = 0; }
					if(isset($post["CAN_WRITE_GLOBAL_MAILS_".$row["rankid"]])) { $can_write_global_mails = 1; }
					else { $can_write_global_mails = 0; }

					$spec = array(
						"CAN_SEE_MEMBERLIST" => $can_see_memberlist,
						"CAN_SEE_APPLICATIONS" => $can_sse_applications,
						"CAN_MANAGE" => $can_manage,
						"CAN_BAN_MEMBER" => $can_ban_member,
						"CAN_SEE_ONLINE_STATE" => $can_see_onlie_state,
						"CAN_WRITE_GLOBAL_MAILS" => $can_write_global_mails
					);
					Core::getQuery()->update("allyrank", $spec, "rankid = ? AND aid = ?", array($row["rankid"], $this->aid));
				}
				$result->closeCursor();
			}
			else if(!empty($post))
			{
				foreach($post as $key => $value)
				{
					if(preg_match("#^delete\_#i", $key)) { Core::getQuery()->delete("allyrank", "rankid = ? AND aid = ?", null, null, array(Str::replace("delete_", "", $key), $this->aid)); break; }
				}
			}

			$select = array("rankid", "name", "CAN_SEE_MEMBERLIST", "CAN_SEE_APPLICATIONS", "CAN_MANAGE", "CAN_BAN_MEMBER", "CAN_SEE_ONLINE_STATE", "CAN_WRITE_GLOBAL_MAILS");
			$result = Core::getQuery()->select("allyrank", $select, "", Core::getDB()->quoteInto("aid = ?", $this->aid));
			Core::getTPL()->assign("num", $result->rowCount());
			Core::getTPL()->addLoop("ranks", $result);
		}
		return $this;
	}

	/**
	 * Displays application form.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function applyAction()
	{
		if($this->isPost())
		{
			$this->apply($this->getParam("application"));
		}
		$result = Core::getQuery()->select("alliance", array("open", "tag", "applicationtext"), "", Core::getDB()->quoteInto("aid = ?", $this->aid));
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			if($row["open"])
			{
				Hook::event("WriteAllianceApplication", array(&$row));
				Core::getTPL()->assign("alliance", $row["tag"]);
				Core::getTPL()->assign("applicationtext", $row["applicationtext"]);
				Core::getTPL()->assign("aid", $this->aid);
				Core::getTPL()->assign("maxapplicationtext", fNumber(Core::getOptions()->get("MAX_APPLICATION_TEXT_LENGTH")));
			}
		}
		return $this;
	}

	/**
	 * Sends an application to the alliance.
	 *
	 * @param string	Application text
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function apply($text)
	{
		$text = Str::validateXHTML($text);
		Hook::event("ApplyForAlliance", array(&$text));
		$where = Core::getDB()->quoteInto("userid = ? AND aid = ?", array(Core::getUser()->get("userid"), $this->aid));
		$result1 = Core::getQuery()->select("allyapplication", "userid", "", $where);
		$result2 = Core::getQuery()->select("user2ally", "userid", "", Core::getDB()->quoteInto("userid = ?", Core::getUser()->get("userid")));

		if($result1->rowCount() == 0 && $result2->rowCount() == 0 && Str::length($text) > 0)
		{
			$result3 = Core::getQuery()->select("alliance", "aid", "", Core::getDB()->quoteInto("aid = ? AND open = '1'", $this->aid));
			if($result3->rowCount() > 0)
			{
				Core::getQuery()->insert("allyapplication", array("userid" => Core::getUser()->get("userid"), "aid" => $this->aid, "date" => TIME, "application" => $text));
				$this->redirect("game/".SID."/Alliance");
				return $this;
			}
			$result3->closeCursor();
		}
		$result1->closeCursor();
		$result2->closeCursor();
		return $this;
	}

	/**
	 * If user search for an alliance to apply.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function joinAction()
	{
		if($this->isPost())
		{
			$result = null;
			$searchItem = Str::validateXHTML($this->getParam("searchitem"));
			$searchObj = new String($searchItem);
			$searchObj->setMinSearchLenght(1)
				->trim()
				->prepareForSearch();
			Core::getTPL()->assign("searchitem", $searchItem);
			if($searchObj->validSearchString)
			{
				$select = array("a.aid", "a.tag", "a.name", "a.showhomepage", "a.homepage", "a.open", "COUNT(u2a.userid) AS members", "SUM(u.points) AS points");
				$joins  = "LEFT JOIN ".PREFIX."user2ally u2a ON (a.aid = u2a.aid)";
				$joins .= "LEFT JOIN ".PREFIX."user u ON (u2a.userid = u.userid)";
				$result = Core::getQuery()->select("alliance a", $select, $joins, Core::getDB()->quoteInto("a.tag LIKE ? OR a.name LIKE ?", $searchObj->get()), "", "", "a.aid");
			}
			$results = new Bengine_Game_Alliance_List($result);
			Hook::event("AllianceSearchResult", array(&$results, $searchObj));
			Core::getTPL()->addLoop("results", $results->getArray());
		}
		return $this;
	}

	/**
	 * Refuses or receipts an application.
	 *
	 * @param boolean $receipt
	 * @param boolean $refuse
	 * @param array $action
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function manageCadidates($receipt, $refuse, $action)
	{
		if($this->getRights(array("CAN_SEE_APPLICATIONS")))
		{
			foreach($action as $userid)
			{
				$where = Core::getDB()->quoteInto("ap.userid = ? AND ap.aid = ?", array($userid, $this->aid));
				$result = Core::getQuery()->select("allyapplication ap", array("a.tag", "u.username", "u2a.aid"), "LEFT JOIN ".PREFIX."alliance a ON (a.aid = ap.aid) LEFT JOIN ".PREFIX."user u ON (u.userid = ap.userid) LEFT JOIN ".PREFIX."user2ally u2a ON (u2a.userid = u.userid)", $where);
				if($row = $result->fetchRow())
				{
					if($receipt && !$row["aid"])
					{
						Hook::event("ReceiptAllianceCandidate", array($userid, $row));
						new Bengine_Game_AutoMsg(24, $userid, TIME, $row);
						$_result = Core::getQuery()->select("user2ally", "userid", "", Core::getDB()->quoteInto("aid = ?", $this->aid));
						foreach($_result->fetchAll() as $_row)
						{
							new Bengine_Game_AutoMsg(100, $_row["userid"], TIME, $row);
						}
						$_result->closeCursor();
						Core::getQuery()->insert("user2ally", array("userid" => $userid, "aid" => $this->aid, "joindate" => TIME));
						Core::getQuery()->delete("allyapplication", "userid = ?", null, null, array($userid));
					}
					else
					{
						Hook::event("RejectAllianceCandidate", array($userid, $row));
						new Bengine_Game_AutoMsg(25, $userid, TIME, $row);
						Core::getQuery()->delete("allyapplication", "userid = ? AND aid = ?", null, null, array($userid, $this->aid));
					}
				}
				$result->closeCursor();
			}
		}
		return $this;
	}

	/**
	 * Shows the candidates and their application.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function showCandidatesAction()
	{
		if($this->isPost())
		{
			$this->manageCadidates($this->getParam("receipt"), $this->getParam("refuse"), $this->getParam("users"));
		}
		if($this->getRights(array("CAN_SEE_APPLICATIONS")))
		{
			$apps = array();
			$result = Core::getQuery()->select("allyapplication a", array("a.userid", "a.date", "a.application ", "u.username", "u.points", "g.galaxy", "g.system", "g.position"), "LEFT JOIN ".PREFIX."user u ON (u.userid = a.userid) LEFT JOIN ".PREFIX."galaxy g ON (g.planetid = u.hp)", Core::getDB()->quoteInto("a.aid = ?", $this->aid), "u.username ASC, a.date ASC");
			foreach($result->fetchAll() as $row)
			{
				$apps[$row["userid"]]["date"] = Date::timeToString(1, $row["date"]);
				$apps[$row["userid"]]["message"] = Link::get("game/".SID."/MSG/Write/".$row["username"], Image::getImage("pm.gif", Core::getLanguage()->getItem("WRITE_MESSAGE")));
				$apps[$row["userid"]]["apptext"] = nl2br($row["application"]);
				$apps[$row["userid"]]["userid"] = $row["userid"];
				$apps[$row["userid"]]["username"] = $row["username"];
				$apps[$row["userid"]]["points"] = fNumber(floor($row["points"]));
				$apps[$row["userid"]]["position"] = getCoordLink($row["galaxy"], $row["system"], $row["position"]);
			}
			Core::getTPL()->assign("candidates", sprintf(Core::getLanguage()->getItem("CANDIDATES"), $result->rowCount()));
			$result->closeCursor();
			Hook::event("ShowAllianceCandidates", array(&$apps));
			Core::getTPL()->addLoop("applications", $apps);
		}
		return $this;
	}

	/**
	 * User leaves the alliance.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function leaveAlliance()
	{
		$result = Core::getQuery()->select("alliance", "founder", "", Core::getDB()->quoteInto("aid = ?", Core::getUser()->get("aid")));
		if($row = $result->fetchRow())
		{
			if($row["founder"] != Core::getUser()->get("userid"))
			{
				Core::getQuery()->delete("user2ally", "userid = ?", null, null, array(Core::getUser()->get("userid")));
				Core::getUser()->rebuild();
				Hook::event("LeaveAlliance", array($row));
				$_result = Core::getQuery()->select("user2ally", "userid", "", Core::getDB()->quoteInto("aid = ?", $this->aid));
				foreach($_result->fetchAll() as $_row)
				{
					$data = array(
						"username"	=> Core::getUser()->get("username")
					);
					new Bengine_Game_AutoMsg(101, $_row["userid"], TIME, $data);
				}
				$_result->closeCursor();
			}
		}
		$result->closeCursor();
		$this->redirect("game/".SID."/Index");
		return $this;
	}

	/**
	 * Refers the founder status to a different alliance member.
	 *
	 * @param integer $userid
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function referFounderStatus($userid)
	{
		$result = Core::getQuery()->select("alliance", "founder", "", Core::getDB()->quoteInto("aid = ?", Core::getUser()->get("aid")));
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			if($row["founder"] == Core::getUser()->get("userid"))
			{
				$where = Core::getDB()->quoteInto("userid = ? AND aid = ?", array($userid, Core::getUser()->get("aid")));
				$result = Core::getQuery()->select("user2ally", "rank", "", $where);
				if($row = $result->fetchRow())
				{
					$result->closeCursor();
					Hook::event("PassFounderStatus", array($row));
					Core::getQuery()->update("alliance", array("founder" => $userid), "aid = ?", array(Core::getUser()->get("aid")));
					Core::getQuery()->update("user2ally", array("rank" => $row["rank"]), "aid = ? AND userid = ?", array(Core::getUser()->get("aid"), Core::getUser()->get("userid")));
				}
			}
		}
		else
		{
			$result->closeCursor();
		}
		$this->redirect("game/".SID."/Alliance");
		return $this;
	}

	/**
	 * Abandons an alliance, if user has permissions.
	 *
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function abandonAlly()
	{
		$result = Core::getQuery()->select("alliance", "founder", "", Core::getDB()->quoteInto("aid = ?", Core::getUser()->get("aid")));
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			if($row["founder"] == Core::getUser()->get("userid"))
			{
				Hook::event("AbandonAlliance");
				deleteAlliance(Core::getUser()->get("aid"));
				Core::getUser()->rebuild();
			}
		}
		$this->redirect("game/".SID."/Alliance");
		return $this;
	}

	/**
	 * Allows the user to write a global mail to all alliance member.
	 *
	 * @param string $reply
	 * @return Bengine_Game_Controller_Alliance
	 */
	protected function globalMailAction($reply)
	{
		$result = Core::getQuery()->select("user2ally u2a", array("a.founder", "ar.CAN_WRITE_GLOBAL_MAILS"), "LEFT JOIN ".PREFIX."alliance a ON (a.aid = u2a.aid) LEFT JOIN ".PREFIX."allyrank ar ON (ar.rankid = u2a.rank)", Core::getDB()->quoteInto("u2a.userid = ?", Core::getUser()->get("userid")));
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			if($row["CAN_WRITE_GLOBAL_MAILS"] || $row["founder"] == Core::getUser()->get("userid"))
			{
				Core::getLanguage()->load("Message");
				if($this->isPost())
				{
					$message = richtext($this->getParam("message"));
					$length = Str::length(strip_tags($message));
					$subject = Str::validateXHTML(trim($this->getParam("subject")));
					$receiver = $this->getParam("receiver");
					if($length > 2 && $length <= Core::getOptions()->get("MAX_PM_LENGTH") && Str::length($subject) > 0 && Str::length($subject) < 101)
					{
						Hook::event("SendGlobalMail", array($subject, &$message));
						if($receiver == "foo")
						{
							$where = Core::getDB()->quoteInto("aid = ?", $this->aid);
						}
						else
						{
							$where = Core::getDB()->quoteInto("rank = ? AND aid = ?", array($receiver, $this->aid));
						}
						$_result = Core::getQuery()->select("user2ally", "userid", "", $where);
						foreach($_result->fetchAll() as $_row)
						{
							Core::getQuery()->insert("message", array("mode" => 6, "time" => TIME, "sender" => Core::getUser()->get("userid"), "receiver" => $_row["userid"], "message" => $message, "subject" => $subject, "read" => (($_row["userid"] == Core::getUser()->get("userid")) ? 1 : 0)));
						}
						$_result->closeCursor();
						Logger::addMessage("SENT_SUCCESSFUL", "success");
					}
					else
					{
						if($length < 3) { Core::getTPL()->assign("messageError", Logger::getMessageField("MESSAGE_TOO_SHORT")); }
						if($length > Core::getOptions()->get("MAX_PM_LENGTH")) { Core::getTPL()->assign("messageError", Logger::getMessageField("MESSAGE_TOO_LONG")); }
						if(Str::length($subject) == 0) { Core::getTPL()->assign("subjectError", Logger::getMessageField("SUBJECT_TOO_SHORT")); }
						if(Str::length($subject) > 100) { Core::getTPL()->assign("subjectError", Logger::getMessageField("SUBJECT_TOO_LONG")); }
						Core::getTPL()->assign("subject", $this->getParam("subject"))
							->assign("message", $this->getParam("message"));
					}
				}
				else
				{
					if($reply)
					{
						$reply = preg_replace("#((RE|FW):\s)+#is", "\\1", $reply);
						Core::getTPL()->assign("subject", $reply);
					}
				}
				$ranks = Core::getQuery()->select("allyrank", array("rankid", "name"), "", Core::getDB()->quoteInto("aid = ?", $this->aid));
				Core::getTPL()->assign("maxpmlength", fNumber(Core::getOptions()->get("MAX_PM_LENGTH")));
				Core::getTPL()->addLoop("ranks", $ranks);
			}
			else
			{
				Logger::dieMessage("MISSING_RIGHTS_FOR_GLOBAL_MAIL", "warning");
			}
		}
		else
		{
			Logger::dieMessage("MISSING_RIGHTS_FOR_GLOBAL_MAIL", "warning");
		}
		return $this;
	}
}
?>