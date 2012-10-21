<?php
/**
 * A really light CMS method.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: CMS.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Comm_CMS
{
	/**
	 * Language id.
	 *
	 * @var integer
	 */
	protected $langid = 0;

	/**
	 * Holds all menu items.
	 *
	 * @var array
	 */
	protected $menuItems = array();

	/**
	 * Creates a new CMS object.
	 *
	 * @return \Bengine_Comm_CMS
	 */
	public function __construct()
	{
		$this->langid = Core::getLang()->getOpt("languageid");
		$this->loadMenuItems();
		return;
	}

	/**
	 * Returns page data.
	 *
	 * @param string $page	Page label
	 *
	 * @return array|boolean
	 */
	public function getPage($page)
	{
		if(empty($page)) { return false; }
		$result = Core::getQuery()->select("page", array("title", "content"), "", "label = '".$page."' AND languageid = '".$this->langid."' AND label != ''");
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		return ($row) ? $row : false;
	}

	/**
	 * Loads all menu items.
	 *
	 * @return Bengine_Comm_CMS
	 */
	protected function loadMenuItems()
	{
		$result = Core::getQuery()->select("page", array("position", "title", "label", "link"), "", "languageid = '".$this->langid."'", "displayorder ASC");
		while($row = Core::getDB()->fetch($result))
		{
			$position = $row["position"];
			if(!empty($row["link"]))
			{
				$this->menuItems[$position][]["link"] = Link::get($row["link"], $row["title"], $row["title"]);
			}
			else
			{
				$this->menuItems[$position][]["link"] = Link::get(LANG."index/".$row["label"], $row["title"], $row["title"]);
			}
		}
		return $this;
	}

	/**
	 * Returns an array with all menu items of the given location.
	 * (e.g. f = footer, h = header)
	 *
	 * @param string $position		Menu location
	 *
	 * @return array	Menu items
	 */
	public function getMenu($position)
	{
		if(isset($this->menuItems[$position]) && count($this->menuItems[$position]) > 0)
		{
			return $this->menuItems[$position];
		}
		return array();
	}
}
?>