<?php
/**
 * Pillory controller.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Pillory.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Comm_Controller_Pillory extends Bengine_Comm_Controller_Abstract
{
	/**
	 * Max items per feed.
	 *
	 * @var integer
	 */
	const MAX_FEED_ITEMS = 10;

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine_Comm/Controller/Bengine_Comm_Controller_Abstract#init()
	 */
	protected function init()
	{
		Core::getLanguage()->load(array("Prefs"));
		$this->setIsAjax();
	}

	/**
	 * Shows the pillory.
	 *
	 * @return Bengine_Comm_Controller_Pillory
	 */
	public function indexAction()
	{
		Core::getTPL()->clearHTMLHeaderFiles();
		Core::getTPL()->addHTMLHeaderFile("game.css", "css");
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.js", "js");
		$result = Core::getQuery()->select("ban_u", array("banid"));
		$pagination = new Pagination(Core::getConfig()->get("PILLORY_ITEMS_PER_PAGE"), $result->rowCount());
		$pagination->setConfig("page_url", Core::getLang()->getOpt("langcode")."/pillory/index/%d")
			->setConfig("main_element_class", "pagination center-table")
			->setMaxPagesToShow(Core::getConfig()->get("MAX_PILLORY_PAGES"))
			->setCurrentPage($this->getParam("1"))
			->setConfig("base_url", Core::getLang()->getOpt("langcode")."/pillory");
		Core::getTPL()->addLoop("bans", $this->getBans($pagination->getStart(), Core::getConfig()->get("PILLORY_ITEMS_PER_PAGE")));
		$this->assign("pagination", $pagination);
		return $this;
	}

	/**
	 * Shows the pillory as RSS feed.
	 *
	 * @return Bengine_Comm_Controller_Pillory
	 */
	public function rssAction()
	{
		Core::getTPL()->addLoop("feed", $this->getBans(0, self::MAX_FEED_ITEMS));
		$this->setTemplate("rss");
		$this->assign("title", Core::getLang()->get("PILLORY"));
		$this->assign("langcode", Core::getLang()->getOpt("langcode"));
		return $this;
	}

	/**
	 * Shows the pillory as Atom feed.
	 *
	 * @return Bengine_Comm_Controller_Pillory
	 */
	public function atomAction()
	{
		$this->assign("selfUrl", BASE_URL."pillory/atom");
		$this->assign("alternateUrl", BASE_URL."pillory");
		$this->assign("title", Core::getLang()->get("PILLORY"));
		Core::getTPL()->addLoop("feed", $this->getBans(0, self::MAX_FEED_ITEMS));
		$this->setTemplate("atom");
		return $this;
	}

	/**
	 * Fetches all bans.
	 *
	 * @param int $offset
	 * @param int $count
	 * @return array
	 */
	protected function getBans($offset, $count)
	{
		$offset = (int) $offset;
		$count = (int) $count;
		$bans = array();
		$attr = array("b.`banid`", "b.`from`", "b.`to`", "b.`reason`", "u.`username`", "m.`username` AS moderator");
		$joins  = "LEFT JOIN `".PREFIX."user` u ON (u.`userid` = b.`userid`) ";
		$joins .= "LEFT JOIN `".PREFIX."user` m ON (m.`userid` = b.`modid`)";
		$result = Core::getQuery()->select("ban_u b", $attr, $joins, "", "b.`from` DESC, b.`banid` DESC", $offset.", ".$count);
		foreach($result->fetchAll() as $row)
		{
			$bans[] = array(
				"counter" => $row["banid"],
				"from"	=> Date::timeToString(1, $row["from"]),
				"to"	=> Date::timeToString(1, $row["to"]),
				"reason" => $row["reason"],
				"username" => $row["username"],
				"moderator" => $row["moderator"],
				"date"	=> Date::timeToString(3, $row["from"], "D, d M Y H:i:s O", false),
				"author" => $row["moderator"],
				"title" => $row["username"],
				"text" => $row["username"]." &ndash; ".$row["reason"],
				"link" => "",
				"date_atom" => Date::timeToString(3, $row["from"], "c", false),
				"guid" => md5($row["from"]),
			);
		}
		return $bans;
	}
}
?>