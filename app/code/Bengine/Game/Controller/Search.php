<?php
/**
 * Search the universe.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Search.php 46 2011-07-30 14:38:11Z secretchampion $
 */

class Bengine_Game_Controller_Search extends Bengine_Game_Controller_Abstract
{
	/**
	 * The current entered search item.
	 *
	 * @var string
	 */
	protected $searchItem = "";

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_Search
	 */
	protected function indexAction()
	{
		$what = "";
		$where = null;
		if($this->isPost())
		{
			$what = $this->getParam("what");
			$where = $this->getParam("where");
		}
		$this->seek($what, $where);
		return $this;
	}

	/**
	 * Starts the search.
	 *
	 * @param string	The search item
	 * @param string	The search mode
	 *
	 * @return Bengine_Game_Controller_Search
	 */
	protected function seek($what, $where)
	{
		Core::getLanguage()->load("Statistics");
		$this->searchItem = new String($what);
		$this->searchItem->trim()->prepareForSearch();
		if(!$this->searchItem->validSearchString)
		{
			$this->searchItem->set("");
			if(!empty($what))
				Logger::addMessage("NO_VALID_SEARCH", "warning");
		}

		Core::getTPL()->assign("what", $what);
		switch($where)
		{
			case 1:
				Core::getTPL()->assign("players", " selected=\"selected\"");
				$this->playerSearch();
			break;
			case 2:
				Core::getTPL()->assign("planets", " selected=\"selected\"");
				$this->planetSearch();
			break;
			case 3:
				Core::getTPL()->assign("allys", " selected=\"selected\"");
				$this->allianceSearch();
			break;
		}
		return $this;
	}

	/**
	 * Displays the player search result.
	 *
	 * @return Bengine_Game_Controller_Search
	 */
	protected function playerSearch()
	{
		$select = array("u.userid", "u.username", "u.usertitle", "u.points", "u.last as useractivity", "u.umode", "p.planetname", "g.galaxy", "g.system", "g.position", "a.aid", "a.tag", "a.name", "b.to");
		$joins  = "LEFT JOIN ".PREFIX."planet p ON (p.planetid = u.hp)";
		$joins .= "LEFT JOIN ".PREFIX."galaxy g ON (g.planetid = u.hp)";
		$joins .= "LEFT JOIN ".PREFIX."user2ally u2a ON (u2a.userid = u.userid)";
		$joins .= "LEFT JOIN ".PREFIX."alliance a ON (a.aid = u2a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."ban_u b ON (b.userid = u.userid AND b.to > '".TIME."')";
		$result = Core::getQuery()->select("user u", $select, $joins, "u.username LIKE '".$this->searchItem->get()."'", "u.username ASC", "25", "u.userid");

		$UserList = new Bengine_Game_User_List();
		$UserList->load($result);
		Hook::event("SearchResultPlayer", array($UserList));
		Core::getTPL()->addLoop("result", $UserList->getArray());
		Core::getTPL()->addLoop("searchSuggestions", $this->getSimilarWords($this->searchItem, "user"));
		$this->setTemplate("search/player");
		return $this;
	}

	/**
	 * Displays the planet search result.
	 *
	 * @return Bengine_Game_Controller_Search
	 */
	protected function planetSearch()
	{
		$sr = array(); $i = 0;
		$select = array(
			"u.userid", "u.username", "u.usertitle", "u.points", "u.last as useractivity", "u.umode", "u.hp", "b.to",
			"p.planetid", "p.planetname", "p.ismoon",
			"g.galaxy", "g.system", "g.position",
			"a.aid", "a.tag", "a.name",
			"gm.galaxy as moongala", "gm.system as moonsys", "gm.position as moonpos"
		);
		$joins  = "LEFT JOIN ".PREFIX."user u ON (p.userid = u.userid)";
		$joins .= "LEFT JOIN ".PREFIX."galaxy g ON (g.planetid = p.planetid)";
		$joins .= "LEFT JOIN ".PREFIX."galaxy gm ON (gm.moonid = p.planetid)";
		$joins .= "LEFT JOIN ".PREFIX."user2ally u2a ON (u2a.userid = u.userid)";
		$joins .= "LEFT JOIN ".PREFIX."alliance a ON (a.aid = u2a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."ban_u b ON (b.userid = u.userid)";
		$result = Core::getQuery()->select("planet p", $select, $joins, "p.planetname LIKE '".$this->searchItem->get()."' AND u.username IS NOT NULL", "p.planetname ASC, u.username ASC", "25", "u.userid");
		while($row = Core::getDB()->fetch($result))
		{
			$sr[$i] = $row;
			if($row["planetid"] == $row["hp"])
			{
				$p_addition = " (HP)";
			}
			else if($row["ismoon"])
			{
				$p_addition = " (".Core::getLanguage()->getItem("MOON").")";
			}
			else { $p_addition = ""; }
			$sr[$i]["planetname"] = $row["planetname"].$p_addition;
			$i++;
		}
		Core::getDB()->free_result($result);

		$UserList = new Bengine_Game_User_List();
		$UserList->setByArray($sr);
		Hook::event("SearchResultPlanet", array($UserList));
		Core::getTPL()->addLoop("result", $UserList->getArray());
		Core::getTPL()->addLoop("searchSuggestions", $this->getSimilarWords($this->searchItem, "planet"));
		$this->setTemplate("search/player");
		return $this;
	}

	/**
	 * Displays the alliance search result.
	 *
	 * @return Bengine_Game_Controller_Search
	 */
	protected function allianceSearch()
	{
		$sr = array();
		$select = array("SUM(u.points) as points", "COUNT(u2a.userid) as members", "a.aid", "a.tag", "a.name", "a.homepage", "a.showhomepage", "a.showmember");
		$joins  = "LEFT JOIN ".PREFIX."user2ally u2a ON (u2a.aid = a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."user u ON (u.userid = u2a.userid)";
		$result = Core::getQuery()->select("alliance a", $select, $joins, "a.tag LIKE '".$this->searchItem->get()."' OR a.name LIKE '".$this->searchItem->get()."'", "a.tag ASC", "25", "u2a.aid");
		$AllianceList = new Bengine_Game_Alliance_List($result);
		Hook::event("SearchResultAlliance", array($AllianceList));
		Core::getTPL()->addLoop("result", $AllianceList->getArray());
		Core::getTPL()->addLoop("searchSuggestions", $this->getSimilarWords($this->searchItem, "alliance"));
		$this->setTemplate("search/ally");
		return $this;
	}

	/**
	 * Returns a similar word for a search item.
	 *
	 * @param string	Search type
	 * @param string	Search item
	 *
	 * @return array
	 */
	protected function getSimilarWords(String $item, $type = "")
	{
		$type = strtolower($type);
		switch($type)
		{
			case "planet":
				$table = "planet";
				$field = "planetname";
			break;
			case "alliance":
				$table = "alliance";
				$field = "name";
			break;
			case "user": default:
				$table = "user";
				$field = "username";
			break;
		}
		$ret = array();
		$where = "SUBSTRING(SOUNDEX(".$field."), 2, 4) = SUBSTRING(SOUNDEX('".$item->replace("%", "")."'), 2, 4)";
		$result = Core::getQuery()->select($table, array($field), "", $where);
		while($row = Core::getDB()->fetch($result))
		{
			if(!$item->compareTo($row[$field]))
			{
				$ret[] = $row[$field];
			}
		}
		return $ret;
	}
}
?>