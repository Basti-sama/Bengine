<?php
/**
 * News controller.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Bengine_Admin_Controller_News extends Bengine_Admin_Controller_Abstract
{
	/**
	 * Holds a list of all languages.
	 *
	 * @var array
	 */
	protected $languages = null;

	/**
	 * @return Bengine_Admin_Controller_Abstract
	 */
	protected function init()
	{
		Core::getLanguage()->load("AI_News");
		return parent::init();
	}

	/**
	 * @return Bengine_Admin_Controller_News
	 */
	protected function indexAction()
	{
		if($this->getParam("add"))
		{
			$this->add($this->getParam("language_id"), $this->getParam("title"), $this->getParam("text"));
		}

		$first = true;
		$i = 1;
		$news = array();
		$result = Core::getQuery()->select("news", array("news_id", "title", "text", "time", "enabled", "sort_index"), "", "", "sort_index ASC, news_id DESC");
		$total = $result->rowCount();
		foreach($result->fetchAll() as $row)
		{
			$down = "";
			$up = "";

			if(!$first)
			{
				$up = Image::getImage("admin/up.gif", "", 16, 16);
				$up = Link::get("admin/news/moveup/".$row["news_id"], $up);
			}
			$first = false;

			if($i < $total)
			{
				$down = Image::getImage("admin/down.gif", "", 16, 16);
				$down = Link::get("admin/news/movedown/".$row["news_id"], $down);
			}
			$i++;

			$news[] = array(
				"news_id"	=> $row["news_id"],
				"title"		=> $row["title"],
				"text"		=> $row["text"],
				"time"		=> Date::timeToString(1, $row["time"]),
				"enabled"	=> $row["enabled"],
				"edit"		=> Link::get("admin/news/edit/".$row["news_id"], Core::getLang()->get("Edit")),
				"delete"	=> Link::get("admin/news/delete/".$row["news_id"], Core::getLang()->get("Delete")),
				"enable"	=> Link::get("admin/news/enable/".$row["news_id"], Core::getLang()->get("Enable"), "", "green"),
				"disable"	=> Link::get("admin/news/disable/".$row["news_id"], Core::getLang()->get("Disable"), "", "red"),
				"down"		=> $down,
				"up"		=> $up,
			);
		}
		Core::getTemplate()->addLoop("news", $news);

		$languages = $this->getLanguages();
		Core::getTPL()->assign("languageCount", count($languages));
		if(count($languages) == 1)
		{
			Core::getTPL()->assign("languages", key($languages));
		}
		else
		{
			Core::getTPL()->assign("languages", $this->getLanguageSelect());
		}
		return $this;
	}

	/**
	 * @param integer $languageId
	 * @param string $title
	 * @param string $text
	 * @return Bengine_Admin_Controller_News
	 */
	protected function add($languageId, $title, $text)
	{
		$result = Core::getQuery()->select("news", array("MAX(sort_index) AS sort_index"), "", "", "", 1);
		$sortIndex = (int) $result->fetchColumn();
		$sortIndex++;
		$spec = array("language_id" => $languageId, "title" => $title, "text" => $text, "user_id" => Core::getUser()->get("userid"), "time" => TIME, "sort_index" => $sortIndex);
		Core::getQuery()->insert("news", $spec);
		return $this;
	}

	/**
	 * @param integer $newsId
	 * @return Bengine_Admin_Controller_News
	 */
	protected function editAction($newsId)
	{
		if($this->getParam("save"))
		{
			Core::getQuery()->update("news", array("language_id" => $this->getParam("language_id"), "title" => $this->getParam("title"), "text" => $this->getParam("text")), "news_id = ?", array($newsId));
		}

		$result = Core::getQuery()->select("news", array("news_id", "language_id", "title", "text"), "", Core::getDB()->quoteInto("news_id = ?", $newsId));
		if($row = $result->fetchRow())
		{
			Core::getTPL()->assign($row);
			$languages = $this->getLanguages();
			Core::getTPL()->assign("languageCount", count($languages));
			if(count($languages) == 1)
			{
				Core::getTPL()->assign("languages", key($languages));
			}
			else
			{
				Core::getTPL()->assign("languages", $this->getLanguageSelect($row["language_id"]));
			}
		}
		else
		{
			$this->redirect("admin/news");
		}
		return $this;
	}

	/**
	 * @param integer $newsId
	 * @return Bengine_Admin_Controller_News
	 */
	protected function deleteAction($newsId)
	{
		Core::getQuery()->delete("news", "news_id = ?", null, null, array($newsId));
		$this->redirect("admin/news");
		return $this;
	}

	/**
	 * @param integer $newsId
	 * @return Bengine_Admin_Controller_News
	 */
	protected function enableAction($newsId)
	{
		Core::getQuery()->update("news", array("enabled" => 1), "news_id = ?", array($newsId));
		$this->redirect("admin/news");
		return $this;
	}

	/**
	 * @param integer $newsId
	 * @return Bengine_Admin_Controller_News
	 */
	protected function disableAction($newsId)
	{
		Core::getQuery()->update("news", array("enabled" => 0), "news_id = ?", array($newsId));
		$this->redirect("admin/news");
		return $this;
	}

	/**
	 * @param integer $newsId
	 * @return Bengine_Admin_Controller_News
	 */
	protected function moveupAction($newsId)
	{
		$result = Core::getQuery()->select("news", array("sort_index"), "", Core::getDB()->quoteInto("news_id = ?", $newsId), "", "1");
		$sortIndex = $result->fetchColumn();
		$_result = Core::getQuery()->select("news", array("MAX(sort_index) as max_sort_index"), "", Core::getDB()->quoteInto("sort_index < ?", $sortIndex), "", "1");
		$maxSortIndex = (int) $_result->fetchColumn();
		$maxSortIndex = ($maxSortIndex-1<0) ? 0 : $maxSortIndex-1;
		Core::getQuery()->update("news", array("sort_index" => $maxSortIndex), "news_id = ?", array($newsId));
		$this->redirect("admin/news");
		return $this;
	}

	/**
	 * @param integer $newsId
	 * @return Bengine_Admin_Controller_News
	 */
	protected function movedownAction($newsId)
	{
		$result = Core::getQuery()->select("news", array("sort_index"), "", Core::getDB()->quoteInto("news_id = ?", $newsId), "", "1");
		$sortIndex = $result->fetchColumn();
		$_result = Core::getQuery()->select("news", array("MIN(sort_index) as min_sort_index"), "", Core::getDB()->quoteInto("sort_index > ?", $sortIndex), "", "1");
		$minSortIndex = (int) $_result->fetchColumn();
		$minSortIndex++;
		Core::getQuery()->update("news", array("sort_index" => $minSortIndex), "news_id = ?", array($newsId));
		$this->redirect("admin/news");
		return $this;
	}

	/**
	 * @return array
	 */
	protected function getLanguages()
	{
		if($this->languages === null)
		{
			$this->languages = array();
			$result = Core::getQuery()->select("languages", array("languageid", "title"), "", "", "title ASC");
			foreach($result->fetchAll() as $row)
			{
				$this->languages[$row["languageid"]] = $row["title"];
			}
		}
		return $this->languages;
	}

	/**
	 * @param integer $langId
	 * @return string
	 */
	protected function getLanguageSelect($langId = 0)
	{
		$select = "";
		foreach($this->getLanguages() as $id => $title)
		{
			$select .= "<option value=\"".$id."\"".(($id == $langId) ? " selected=\"selected\"" : "").">".$title."</option>";
		}
		return $select;
	}
}