<?php
class Admin_Controller_News extends Admin_Controller_Abstract
{
	/**
	 * Holds a list of all languages.
	 *
	 * @var array
	 */
	protected $languages = null;

	protected function init()
	{
		Core::getLanguage()->load("AI_News");
		return parent::init();
	}

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
		$total = Core::getDB()->num_rows($result);
		while($row = Core::getDatabase()->fetch($result))
		{
			$down = "";
			$up = "";

			if(!$first)
			{
				$up = Image::getImage("up.gif", "", 16, 16);
				$up = Link::get("news/moveup/".$row["news_id"], $up);
			}
			$first = false;

			if($i < $total)
			{
				$down = Image::getImage("down.gif", "", 16, 16);
				$down = Link::get("news/movedown/".$row["news_id"], $down);
			}
			$i++;

			$news[] = array(
				"news_id"	=> $row["news_id"],
				"title"		=> $row["title"],
				"text"		=> $row["text"],
				"time"		=> Date::timeToString(1, $row["time"]),
				"enabled"	=> $row["enabled"],
				"edit"		=> Link::get("news/edit/".$row["news_id"], Core::getLang()->get("Edit")),
				"delete"	=> Link::get("news/delete/".$row["news_id"], Core::getLang()->get("Delete")),
				"enable"	=> Link::get("news/enable/".$row["news_id"], Core::getLang()->get("Enable"), "", "green"),
				"disable"	=> Link::get("news/disable/".$row["news_id"], Core::getLang()->get("Disable"), "", "red"),
				"down"		=> $down,
				"up"		=> $up,
			);
		}
		Core::getDB()->free_result($result);
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

	protected function add($languageId, $title, $text)
	{
		$result = Core::getQuery()->select("news", array("MAX(sort_index) AS sort_index"), "", "", "", 1);
		$sortIndex = (int) Core::getDB()->fetch_field($result, "sort_index");
		$sortIndex++;
		Core::getQuery()->insert("news", array("language_id", "title", "text", "user_id", "time", "sort_index"), array($languageId, $title, $text, Core::getUser()->get("userid"), TIME, $sortIndex));
		return $this;
	}

	protected function editAction($newsId)
	{
		if($this->getParam("save"))
		{
			Core::getQuery()->update("news", array("language_id", "title", "text"), array($this->getParam("language_id"), $this->getParam("title"), $this->getParam("text")), "news_id = '".$newsId."'");
		}

		$result = Core::getQuery()->select("news", array("news_id", "language_id", "title", "text"), "", "news_id = '".$newsId."'");
		if($row = Core::getDB()->fetch($result))
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
			$this->redirect("news");
		}
		Core::getDB()->free_result($result);
		return $this;
	}

	protected function deleteAction($newsId)
	{
		Core::getQuery()->delete("news", "news_id = '".$newsId."'");
		$this->redirect("news");
		return $this;
	}

	protected function enableAction($newsId)
	{
		Core::getQuery()->update("news", array("enabled"), array(1), "news_id = '".$newsId."'");
		$this->redirect("news");
		return $this;
	}

	protected function disableAction($newsId)
	{
		Core::getQuery()->update("news", array("enabled"), array(0), "news_id = '".$newsId."'");
		$this->redirect("news");
		return $this;
	}

	protected function moveupAction($newsId)
	{
		$result = Core::getQuery()->select("news", array("sort_index"), "", "news_id = '{$newsId}'", "", "1");
		$sortIndex = Core::getDB()->fetch_field($result, "sort_index");
		$_result = Core::getQuery()->select("news", array("MAX(sort_index) as max_sort_index"), "", "sort_index < '{$sortIndex}'", "", "1");
		$maxSortIndex = (int) Core::getDB()->fetch_field($_result, "max_sort_index");
		$maxSortIndex = ($maxSortIndex-1<0) ? 0 : $maxSortIndex-1;
		Core::getQuery()->update("news", array("sort_index"), array($maxSortIndex), "news_id = '{$newsId}'");
		Core::getDB()->free_result($_result);
		Core::getDB()->free_result($result);
		$this->redirect("news");
		return $this;
	}

	protected function movedownAction($newsId)
	{
		$result = Core::getQuery()->select("news", array("sort_index"), "", "news_id = '{$newsId}'", "", "1");
		$sortIndex = Core::getDB()->fetch_field($result, "sort_index");
		$_result = Core::getQuery()->select("news", array("MIN(sort_index) as min_sort_index"), "", "sort_index > '{$sortIndex}'", "", "1");
		$minSortIndex = (int) Core::getDB()->fetch_field($_result, "min_sort_index");
		$minSortIndex++;
		Core::getQuery()->update("news", array("sort_index"), array($minSortIndex), "news_id = '{$newsId}'");
		Core::getDB()->free_result($_result);
		Core::getDB()->free_result($result);
		$this->redirect("news");
		return $this;
	}

	protected function getLanguages()
	{
		if(is_null($this->languages))
		{
			$this->languages = array();
			$result = Core::getQuery()->select("languages", array("languageid", "title"), "", "", "title ASC");
			while($row = Core::getDB()->fetch($result))
			{
				$this->languages[$row["languageid"]] = $row["title"];
			}
			Core::getDB()->free_result($result);
		}
		return $this->languages;
	}

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