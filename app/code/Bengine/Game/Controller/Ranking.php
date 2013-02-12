<?php
/**
 * Shows ranking for users and alliances.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Ranking.php 36 2011-07-20 07:00:37Z secretchampion $
 */

class Bengine_Game_Controller_Ranking extends Bengine_Game_Controller_Abstract
{
	/**
	 * Avarage mode enabled?
	 *
	 * @var boolean
	 */
	protected $average = false;

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_Ranking
	 */
	protected function indexAction()
	{
		$mode = 1;
		$type = "points";
		$avg = false;
		$position = null;
		if($this->isPost())
		{
			$mode = $this->getParam("mode");
			$type = $this->getParam("type");
			$avg = $this->getParam("avg");
			$position = $this->getParam("pos");
		}
		return $this->getRanking($mode, $type, $avg, $position);
	}

	/**
	 * Checks for ranking type and calls the page.
	 *
	 * @param integer $mode		Ranking mode (Alliance or Player)
	 * @param integer $type		Points type and order
	 * @param boolean $avg		Avarage mode
	 * @param integer $position	Start position
	 *
	 * @return Bengine_Game_Controller_Ranking
	 */
	protected function getRanking($mode, $type, $avg, $position)
	{
		Core::getLanguage()->load(array("Statistics", "Galaxy", "Alliance"));
		$this->assignRelationTypes();
		$validTypes = array("points", "fpoints", "rpoints");
		if(!in_array($type, $validTypes))
		{
			$type = "points";
		}

		if($avg)
		{
 			$this->average = true;
		}

		Core::getTPL()->assign("type1Sel", $type == "points" ? " selected=\"selected\"" : "");
		Core::getTPL()->assign("type2Sel", $type == "fpoints" ? " selected=\"selected\"" : "");
		Core::getTPL()->assign("type3Sel", $type == "rpoints" ? " selected=\"selected\"" : "");
		Core::getTPL()->assign("avg_on", $this->average);

		if($mode == 2)
		{
			Core::getTPL()->assign("mod1Sel", "");
			Core::getTPL()->assign("mod2Sel", " selected=\"selected\"");
			$this->allianceRanking($type, $position);
		}
		else
		{
			Core::getTPL()->assign("mod2Sel", "");
			Core::getTPL()->assign("mod1Sel", " selected=\"selected\"");
			$this->playerRanking($type, $position);
		}
		return $this;
	}

	/**
	 * Displays player ranking table.
	 *
	 * @param integer $type	Type of ranking (Fleet, Research, Points)
	 * @param integer $pos	Position to start ranking
	 *
	 * @return Bengine_Game_Controller_Ranking
	 */
	protected function playerRanking($type, $pos)
	{
		$where = Core::getDB()->quoteInto("(`username` < ? AND `{$type}` >= {points}) OR `{$type}` > {points}", array(
			Core::getUser()->get("username"),
		));
		$where = str_replace("{points}", (float) Core::getUser()->get($type), $where);
		$result = Core::getQuery()->select("user", array("COUNT(`userid`)+1 AS rank"), "", $where, "", 1);
		$rank = (int) $result->fetchColumn();
		$currentPage = ceil($rank / Core::getOptions()->get("USER_PER_PAGE"));
		$result->closeCursor();

		$result = Core::getQuery()->select("user u", "userid", "", "u.userid > '0'");
		$pages = ceil($result->rowCount() / Core::getOptions()->get("USER_PER_PAGE"));
		$result->closeCursor();
		if(!is_numeric($pos))
		{
			$pos = $currentPage;
		}
		else if($pos > $pages) { $pos = $pages; }
		else if($pos < 1) { $pos = 1; }

		$ranks = "";
		for($i = 0; $i < $pages; $i++)
		{
			$n = $i * Core::getOptions()->get("USER_PER_PAGE") + 1;
			if($i + 1 == $pos) { $s = 1; } else { $s = 0; }
			if($i + 1 == $currentPage) { $c = "ownPosition"; } else { $c = ""; }
			$ranks .= createOption($i + 1, fNumber($n)." - ".fNumber($n + Core::getOptions()->get("USER_PER_PAGE") - 1), $s, $c);
		}
		Core::getTPL()->assign("rankingSel", $ranks);
		$rank = abs(($pos - 1) * Core::getOptions()->get("USER_PER_PAGE"));
		$max = Core::getOptions()->get("USER_PER_PAGE");
		$select = array("u.userid", "u.username", "u.usertitle", "u.last as useractivity", "u.umode", "g.galaxy", "g.system", "g.position", "a.aid", "a.tag", "a.name", "b.to");
		if($this->average)
		{
			$select[] = "(u.".$type."/(('".TIME."' - u.regtime)/60/60/24)) AS points";
		}
		else
		{
			$select[] = "u.".$type." AS points";
		}
		$joins  = "LEFT JOIN ".PREFIX."galaxy g ON u.hp = g.planetid ";
		$joins .= "LEFT JOIN ".PREFIX."user2ally u2a ON u2a.userid = u.userid ";
		$joins .= "LEFT JOIN ".PREFIX."alliance a ON a.aid = u2a.aid ";
		$joins .= "LEFT JOIN ".PREFIX."ban_u b ON (b.userid = u.userid AND b.to > '".TIME."')";
		$result = Core::getQuery()->select("user u", $select, $joins, "u.userid > '0'", "points DESC, u.username ASC", $rank.", ".$max, "u.userid");
		// Creating the user list object to handle the output
		$UserList = new Bengine_Game_User_List($result, $rank);
		Hook::event("ShowRankingPlayer", array($UserList));

		Core::getTPL()->addLoop("ranking", $UserList->getArray());
		$this->setTemplate("ranking/player");
		return $this;
	}

	/**
	 * Shows alliance ranking.
	 *
	 * @param integer $type	Type of ranking (Fleet, Research, Points)
	 * @param integer $pos	Position to start ranking
	 *
	 * @return Bengine_Game_Controller_Ranking
	 */
	protected function allianceRanking($type, $pos)
	{
		$joins  = "LEFT JOIN ".PREFIX."user2ally u2a ON (u2a.aid = a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."user u ON (u.userid = u2a.userid)";
		$result = Core::getQuery()->select("alliance a", "SUM(u.".$type.") AS points", $joins, "a.aid > '0'", "", "", "a.aid", "HAVING points >= (points)");
		$oPos = $result->rowCount();
		$oPos = ($oPos <= 0) ? 1 : $oPos;
		$oPos = ceil($oPos / Core::getOptions()->get("USER_PER_PAGE"));
		$result->closeCursor();
		$result = Core::getQuery()->select("alliance", "aid", "", "aid > '0'");
		$pages = ceil($result->rowCount() / Core::getOptions()->get("USER_PER_PAGE"));
		$result->closeCursor();
		if(!is_numeric($pos))
		{
			$pos = $oPos;
		}
		else if($pos > $pages) { $pos = $pages; }
		else if($pos < 1) { $pos = 1; }

		$ranks = "";
		for($i = 0; $i < $pages; $i++)
		{
			$n = $i * Core::getOptions()->get("USER_PER_PAGE") + 1;
			if($i + 1 == $pos) { $s = 1; } else { $s = 0; }
			if($i + 1 == $oPos) { $c = "ownPosition"; } else { $c = ""; }
			$ranks .= createOption($i + 1, fNumber($n)." - ".fNumber($n + Core::getOptions()->get("USER_PER_PAGE") - 1), $s, $c);
		}
		Core::getTPL()->assign("rankingSel", $ranks);

		$order = ($this->average) ? "average" : "points";

		$rank = abs(($pos - 1) * Core::getOptions()->get("USER_PER_PAGE"));
		$max = Core::getOptions()->get("USER_PER_PAGE");
		$select = array("a.aid", "a.name", "a.tag", "a.showhomepage", "a.homepage", "a.open", "COUNT(u2a.userid) AS members", "FLOOR(SUM(u.".$type.")) AS points", "AVG(u.".$type.") AS average");
		$joins  = "LEFT JOIN ".PREFIX."user2ally u2a ON u2a.aid = a.aid ";
		$joins .= "LEFT JOIN ".PREFIX."user u ON u2a.userid = u.userid ";
		$result = Core::getQuery()->select("alliance a", $select, $joins, "a.aid > '0'", $order." DESC, members DESC, a.tag ASC", $rank.", ".$max, "a.aid");
		$AllianceList = new Bengine_Game_Alliance_List($result, $rank);
		Hook::event("ShowRankingAlliance", array($AllianceList));
		Core::getTPL()->addLoop("ranking", $AllianceList->getArray());
		$this->setTemplate("ranking/ally");
		return $this;
	}

	/**
	 * Assigns all storable relation types to the template.
	 *
	 * @return Bengine_Game_Controller_Ranking
	 */
	protected function assignRelationTypes()
	{
		$relations = array();
		$result = Core::getQuery()->select("ally_relationship_type", array("name", "css"), "", "storable = '1'");
		foreach($result->fetchAll() as $row)
		{
			$relations[] = array(
				"name"	=> Core::getLang()->get($row["name"]),
				"css"	=> $row["css"]
			);
		}
		$result->closeCursor();
		Core::getTPL()->addLoop("relationTypes", $relations);
		return $this;
	}
}
?>