<?php
/**
 * This class helps to create a alliance list.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: List.php 21 2011-06-01 09:52:07Z secretchampion $
 */

class Bengine_Game_Alliance_List implements IteratorAggregate
{
	/**
	 * Map which contains all alliances of a list.
	 *
	 * @var Map
	 */
	protected $list = null;

	/**
	 * Which type should be shown as points.
	 *
	 * @var string
	 */
	protected $pointType = "points";

	/**
	 * The rank will be fetched by an extra query.
	 *
	 * @var boolean
	 */
	protected $fetchRankByQuery = false;

	/**
	 * Relation object.
	 *
	 * @var Bengine_Game_User_Relation
	 */
	protected $relation = null;

	/**
	 * The tag will be formatted as a link.
	 *
	 * @var boolean
	 */
	protected $tagAsLink = true;

	/**
	 * Specific key when loading from database.
	 *
	 * @var string
	 */
	protected $key = null;

	/**
	 * Creates a new alliance list object.
	 *
	 * @param resource $list Query result for a list
	 * @param int $start
	 * @return \Bengine_Game_Alliance_List
	 */
	public function __construct($list = null, $start = 0)
	{
		$this->list = new Map();
		$this->relation = new Bengine_Game_User_Relation(Core::getUser()->get("userid"), Core::getUser()->get("aid"));
		if(is_array($list))
		{
			$this->setByArray($list, $start);
		}
		else if(!is_null($list))
		{
			$this->load($list, $start);
		}
	}

	/**
	 * Loads the list from a sql query.
	 *
	 * @param Recipe_Database_Statement_Abstract $result	Result set
	 * @param integer $start	Rank/Counter start
	 *
	 * @return Bengine_Game_Alliance_List
	 */
	public function load($result, $start = 0)
	{
		foreach($result->fetchAll() as $row)
		{
			$row["counter"] = $start;
			$start++;
			$row["rank"] = $start;
			$row = $this->formatRow($row);
			$key = (!is_null($this->key)) ? (int) $row[$this->key] : $start;
			$this->list->set($key, $row);
		}
		$result->closeCursor();
		return $this;
	}

	/**
	 * Sets the list by an array.
	 *
	 * @param array $list		Contains a list of all elements
	 * @param integer $start	Rank/Counter start
	 *
	 * @return Bengine_Game_Alliance_List
	 */
	public function setByArray(array $list, $start = 0)
	{
		foreach($list as $key => $value)
		{
			$list[$key]["counter"] = $start;
			$start++;
			$row["rank"] = $start;
			$list[$key] = $this->formatRow($list[$key]);
		}
		$this->list = new Map($list);
		return $this;
	}

	/**
	 * Formats an alliance record.
	 *
	 * @param array $row Alliance data
	 *
	 * @return array	Formatted alliance data
	 */
	protected function formatRow(array $row)
	{
		$row["tag"] = $this->formatAllyTag($row["tag"], $row["name"], $row["aid"]);
		if(isset($row["showhomepage"]) && isset($row["homepage"]) && $row["showhomepage"] && $row["homepage"] != "")
		{
			$row["name"] = Link::get($row["homepage"], $row["name"]);
		}
		if(!Core::getUser()->get("aid") && $row["open"] > 0)
		{
			$row["join"] = Link::get("game/".SID."/Alliance/Apply/".$row["aid"], Image::getImage("apply.gif", Core::getLanguage()->getItem("JOIN")));
		}
		$row["average"] = ($row["members"] > 0) ? fNumber(floor($row["points"] / $row["members"])) : 0;
		$row["members"] = fNumber($row["members"]);
		if($this->fetchRankByQuery)
		{
			$row["rank"] = $this->getAllianceRank($row["aid"], $this->pointType);
		}
		else
		{
			$row["rank"] = fNumber($row["rank"]);
		}
		$row["totalpoints"] = fNumber(floor($row[$this->pointType]));
		$row["points"] = fNumber(floor($row["points"]));
		if(isset($row["fpoints"]))
		{
			$row["fpoints"] = fNumber(floor($row["fpoints"]));
		}
		if(isset($row["rpoints"]))
		{
			$row["rpoints"] = fNumber(floor($row["rpoints"]));
		}
		return $row;
	}

	/**
	 * Fetches the alliance rank from database.
	 *
	 * @param int $aid
	 * @param string $pointType
	 *
	 * @return integer
	 */
	protected function getAllianceRank($aid, $pointType)
	{
		if($pointType != "points" & $pointType != "fpoints" && $pointType != "rpoints")
		{
			throw new Recipe_Exception_Generic("Unknown point type supplied.");
		}
		$joins  = "LEFT JOIN ".PREFIX."user2ally u2a ON (u2a.aid = a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."user u ON (u.userid = u2a.userid)";
		$subselect = "(SELECT SUM(u.".$pointType.") FROM ".PREFIX."user2ally u2a LEFT JOIN ".PREFIX."user u ON (u.userid = u2a.userid) WHERE u2a.aid = ?)";
		$result = Core::getQuery()->select("alliance a", "a.aid", $joins, "", "", "", "u2a.aid", "HAVING SUM(u.".$pointType.") >= ".$subselect, array($aid));
		$rank = fNumber($result->rowCount());
		$result->closeCursor();
		return $rank;
	}

	/**
	 * Formats an alliance tag as a link.
	 *
	 * @param string $tag
	 * @param string $name
	 * @param int $aid
	 *
	 * @return string    Formatted alliance tag
	 */
	protected function formatAllyTag($tag, $name, $aid)
	{
		$class = $this->relation->getAllyRelationClass($aid);
		if($this->tagAsLink)
		{
			return Link::get("game/".SID."/Alliance/Page/".$aid, $tag, $name, $class);
		}
		return "<span class=\"".$class."\">".$tag."</span>";
	}

	/**
	 * Returns the user list as an array.
	 *
	 * @return array
	 */
	public function getArray()
	{
		return $this->list->getArray();
	}

	/**
	 * Returns the user list as a map.
	 *
	 * @return Map
	 */
	public function getMap()
	{
		return $this->list;
	}

	/**
	 * Setter-function for point type.
	 *
	 * @param string $pointType	Point type (points, fpoints or rpoints)
	 *
	 * @return Bengine_Game_Alliance_List
	 */
	public function setPointType($pointType = "points")
	{
		$this->pointType = $pointType;
		return $this;
	}

	/**
	 * Setter-method for ranks.
	 *
	 * @param boolean
	 *
	 * @return Bengine_Game_Alliance_List
	 */
	public function setFetchRank($fetchRankByQuery)
	{
		$this->fetchRankByQuery = $fetchRankByQuery;
		return $this;
	}

	/**
	 * Setter-method for tags.
	 *
	 * @param boolean
	 *
	 * @return Bengine_Game_Alliance_List
	 */
	public function setTagAsLink($tagAsLink)
	{
		$this->tagAsLink = $tagAsLink;
		return $this;
	}

	/**
	 * Setter-method for map key.
	 *
	 * @param string	Map key label
	 *
	 * @return Bengine_Game_Alliance_List
	 */
	public function setKey($key)
	{
		$this->key = $key;
		return $this;
	}

	/**
	 * Retrieves an external iterator.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return $this->list->getIterator();
	}
}
?>