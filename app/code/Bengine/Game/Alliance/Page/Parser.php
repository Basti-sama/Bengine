<?php
/**
 * Parse the alliance page.
 * {{war|yes|no|yes}} - List of wars [as links|show points|show member]
 * {{protection|yes|no|yes}} - List of protection contracts [as links|show points|show member]
 * {{confed|yes|yes|yes}} - List of confederations [as links|show points|show member]
 * {{trage|yes|no|no}} - List of trade agreements [as links|show points|show member]
 * {{member|yes|points}} - List of current member [show points|show member|order]
 * {{points}} - Total alliance points
 * {{totalmember}} - Total number of member
 * {{avarage}} - Point avarage
 * {{no}} - Number of alliance
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Parser.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Alliance_Page_Parser
{
	/**
	 * Alliance id.
	 *
	 * @var integer
	 */
	protected $aid = 0;

	/**
	 * Alliance text to parse.
	 *
	 * @var string
	 */
	protected $text = "";

	/**
	 * Holds alliance relationships.
	 *
	 * @var array
	 */
	protected $rels = array();

	/**
	 * Holds list of alliance member.
	 *
	 * @var array
	 */
	protected $member = array();

	/**
	 * Total number of member.
	 *
	 * @var integer
	 */
	protected $totalMember = 0;

	/**
	 * Total points.
	 *
	 * @var integer
	 */
	protected $points = 0;

	/**
	 * Alliance rank.
	 *
	 * @var integer
	 */
	protected $number = 0;

	/**
	 * Text has background or not.
	 *
	 * @var boolean
	 */
	protected $hasBg = false;

	/**
	 * @var boolean
	 */
	protected $relsLoaded = false;

	/**
	 * @var boolean
	 */
	protected $memberLoaded = false;

	/**
	 * Sets alliance id.
	 *
	 * @param integer $aid
	 *
	 * @return \Bengine_Game_Alliance_Page_Parser
	 */
	public function __construct($aid)
	{
		$this->aid = $aid;
		return;
	}

	/**
	 * Loads a list of all relations for this alliance.
	 *
	 * @return Bengine_Game_Alliance_Page_Parser
	 */
	protected function loadRelations()
	{
		if($this->relsLoaded)
		{
			return $this;
		}
		$this->relsLoaded = true;

		$joins  = "LEFT JOIN ".PREFIX."alliance a ON (ar.rel1 = a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."user2ally u2a ON (ar.rel1 = u2a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."user u ON (u2a.userid = u.userid)";
		$joins .= "LEFT JOIN ".PREFIX."ally_relationship_type rt ON (rt.type_id = ar.mode)";
		$select = array("rt.name AS relation_name", "a.aid", "a.tag", "a.name", "COUNT(u2a.userid) AS member", "FLOOR(SUM(u.points)) AS points");
		$result = Core::getQuery()->select("ally_relationships ar", $select, $joins, Core::getDB()->quoteInto("ar.rel2 = ?", $this->aid), "a.tag ASC", "", "u2a.aid");
		foreach($result->fetchAll() as $row)
		{
			$this->rels[$row["relation_name"]][] = $row;
		}
		$result->closeCursor();

		$joins  = "LEFT JOIN ".PREFIX."alliance a ON (ar.rel2 = a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."user2ally u2a ON (ar.rel2 = u2a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."user u ON (u2a.userid = u.userid)";
		$joins .= "LEFT JOIN ".PREFIX."ally_relationship_type rt ON (rt.type_id = ar.mode)";
		$result = Core::getQuery()->select("ally_relationships ar", $select, $joins, Core::getDB()->quoteInto("ar.rel1 = ?", $this->aid), "a.tag ASC", "", "u2a.aid");
		foreach($result->fetchAll() as $row)
		{
			$this->rels[$row["relation_name"]][] = $row;
		}
		$result->closeCursor();
		return $this;
	}

	/**
	 * Loads list of member.
	 *
	 * @param string $order	Order by term
	 *
	 * @return Bengine_Game_Alliance_Page_Parser
	 */
	protected function loadMember($order = "")
	{
		if($this->memberLoaded)
		{
			return $this;
		}
		$this->memberLoaded = true;
		switch($order)
		{
			case "points":
			case "fpoints":
			case "rpoints":
			case "dpoints":
				$sort = "DESC";
			break;
			case "name":
			default:
				$order = "username";
				$sort = "ASC";
			break;
		}

		$result = Core::getQuery()->select("user2ally u2a", array("u.username", "u.points"), "LEFT JOIN ".PREFIX."user u ON (u.userid = u2a.userid)", Core::getDB()->quoteInto("u2a.aid = ?", $this->aid), "u.".$order." ".$sort);
		foreach($result->fetchAll() as $row)
		{
			$this->member[] = $row;
			$this->points += $row["points"];
		}
		$this->points = floor($this->points);
		$this->totalMember = $result->rowCount();
		$result->closeCursor();
		return $this;
	}

	/**
	 * Starts parsing a text.
	 *
	 * @param string $text	Text to parse
	 *
	 * @return string	Parsed text
	 */
	public function startParser($text)
	{
		$this->text = $text;
		Hook::event("AllyPageParserBegin", array($this));
		$this->text = preg_replace_callback("/\{list\:([^\"]+)\|(yes|no)\|(yes|no)\|(yes|no)\}/siU", function($matches) {
			return $this->replaceList($matches[1], $matches[2], $matches[3], $matches[4]);
		}, $this->text);
		$this->text = preg_replace_callback("/\{member\|(yes|no)\|([^\"]+)\}/siU", function($matches) {
			return $this->replaceMember($matches[1], $matches[2]);
		}, $this->text);
		$this->text = str_replace("{points}", $this->getPoints(), $this->text);
		$this->text = str_replace("{totalmember}", $this->getTotalMember(), $this->text);
		$this->text = str_replace("{average}", $this->getAverage(), $this->text);
		$this->text = str_replace("{no}", $this->getNumber(), $this->text);
		$this->text = preg_replace("~src=['\"]([^'\"]+)['\"]~i", "src=\"".BASE_URL."img/remote.php?file=\\1\"", $this->text);
		Hook::event("AllyPageParserEnd", array($this));
		return $this->text;
	}

	/**
	 * Returns points.
	 *
	 * @return string	Points
	 */
	public function getPoints()
	{
		$this->loadMember();
		return fNumber($this->points);
	}

	/**
	 * Returns total number of member.
	 *
	 * @return string	Member
	 */
	public function getTotalMember()
	{
		$this->loadMember();
		return fNumber($this->totalMember);
	}

	/**
	 * Calculates points avarage for this alliance.
	 *
	 * @return string	Avarage points
	 */
	public function getAverage()
	{
		$this->loadMember();
		return fNumber($this->points / $this->totalMember);
	}

	/**
	 * Number in ranking.
	 *
	 * @return string
	 */
	public function getNumber()
	{
		if($this->number > 0) { return $this->number; }
		$this->loadMember();

		$joins  = "LEFT JOIN ".PREFIX."user2ally u2a ON (u2a.aid = a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."user u ON (u.userid = u2a.userid)";
		$result = Core::getQuery()->select("alliance a", "a.aid", $joins, "", "", "", "u2a.aid", Core::getDB()->quoteInto("HAVING SUM(u.points) >= ?", $this->points));
		$this->number = fNumber($result->rowCount());
		$result->closeCursor();
		return $this->number;
	}

	/**
	 * Generates a diplomacy list of the given mode.
	 *
	 * @param string $mode			List mode (confed, war, ...)
	 * @param string $asLink		Make names clickable
	 * @param string $showPoints	Append points
	 * @param string $showMember	Show number of members
	 *
	 * @return string	Formatted diplomacy list
	 */
	protected function replaceList($mode, $asLink, $showPoints, $showMember)
	{
		$this->loadRelations();
		$mode = strtoupper($mode);
		$asLink = $this->term($asLink);
		$showPoints = $this->term($showPoints);
		$showMember = $this->term($showMember);

		Hook::event("AllyPageParserReplaceList", array($mode, $this));

		if(!isset($this->rels[$mode]) || count($this->rels[$mode]) <= 0)
		{
			return Core::getLang()->getItem("NOTHING");
		}

		$out = "";
		foreach($this->rels[$mode] as $rels)
		{
			if($asLink)
			{
				$out .= Link::get("game/".SID."/Alliance/Page/".$rels["aid"], $rels["tag"], $rels["name"]);
			}
			else
			{
				$out .= $rels["tag"];
			}
			if($showPoints)
			{
				$out .= " - ".fNumber($rels["points"]);
			}
			if($showMember)
			{
				$out .= " - ".fNumber($rels["member"]);
			}
			$out .= "<br/>";
		}
		$out = Str::substring($out, 0, -5);
		return $out;
	}

	/**
	 * Generates a list of alliance member.
	 *
	 * @param string $showPoints	Show points of members
	 * @param string $order			List order
	 *
	 * @return string	Formatted list
	 */
	protected function replaceMember($showPoints, $order)
	{
		$this->loadMember($order);
		$showPoints = $this->term($showPoints);

		$out = "";
		foreach($this->member as $member)
		{
			$out .= $member["username"];
			if($showPoints)
			{
				$out .= " - ".fNumber($member["points"]);
			}
			$out .= "<br/>";
		}
		$out = Str::substring($out, 0, -5);
		return $out;
	}

	/**
	 * Makes an boolean value of the given expression.
	 *
	 * @param string $expr
	 *
	 * @return boolean
	 */
	protected function term($expr)
	{
		if($expr == "yes") { return true; }
		return false;
	}

	/**
	 * Kills parser.
	 *
	 * @return void
	 */
	public function kill()
	{
		unset($this);
		return;
	}
}
?>