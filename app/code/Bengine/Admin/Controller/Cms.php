<?php
/**
 * CMS controller.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Bengine_Admin_Controller_Cms extends Bengine_Admin_Controller_Abstract
{
	/**
	 * Title of new page
	 *
	 * @var string
	 */
	protected $title = "";

	/**
	 * Label of new page
	 *
	 * @var string
	 */
	protected $label = "";

	/**
	 * Link of new page
	 *
	 * @var string
	 */
	protected $link = "";

	/**
	 * Initialisation
	 *
	 * @return Bengine_Admin_Controller_Cms
	 */
	protected function init()
	{
		Core::getLanguage()->load("AI_CMS");
		return parent::init();
	}

	/**
	 * Index page
	 *
	 * @return Bengine_Admin_Controller_Cms
	 */
	protected function indexAction()
	{
		if($this->getParam("update_pages"))
		{
			$this->updatePagesAction($this->getParam("delete"), $this->getParam("pages"));
		}

		$pages = array();
		$result = Core::getQuery()->select("page", array("pageid", "position", "displayorder", "title"), "", "", "position DESC, displayorder ASC");
		foreach($result->fetchAll() as $row)
		{
			$id = $row["pageid"];
			$pages[$id]["pageid"] = $id;
			$pages[$id]["position"] = $row["position"];
			$pages[$id]["displayorder"] = $row["displayorder"];
			$pages[$id]["title"] = $row["title"];
			$pages[$id]["edit_link"] = Link::get("admin/cms/edit/".$id, Core::getLanguage()->getItem("Edit"));
		}

		Core::getTPL()->addLoop("pages", $pages);
		return $this;
	}

	/**
	 * Edit existing page
	 *
	 * @return Bengine_Admin_Controller_Cms
	 */
	protected function editAction()
	{
		if($this->getParam("edit_page"))
		{
			$this->updatePageAction($this->getParam("pageid"));
		}

		$pageid = Core::getRequest()->getGET("1");

		$page = Core::getQuery()->select("page", array("pageid", "position", "languageid", "displayorder", "title", "label", "link", "content"), "", "pageid = ".$pageid, "")->fetchRow();
		Core::getTPL()->assign($page);

		$result = Core::getQuery()->select("languages", array("languageid", "title"), "", "", "title ASC");
		Core::getTPL()->addLoop("langselection", $result);

		return $this;
	}

	/**
	 * Add new page
	 *
	 * @return Bengine_Admin_Controller_Cms
	 */
	protected function newPageAction()
	{
		if($this->getParam("add_page"))
		{
			$this->addPageAction($this->getParam("position"), $this->getParam("languageid"), $this->getParam("displayorder"), $this->getParam("title"), $this->getParam("label"), $this->getParam("link"), $this->getParam("content"));
		}

		$result = Core::getQuery()->select("languages", array("languageid", "title"), "", "", "title ASC");
		Core::getTPL()->addLoop("langselection", $result->fetchAll());
		return $this;
	}

	/**
	 * Update pages in DB (from main page)
	 *
	 * @param array $delete
	 * @param array $pages
	 *
	 * @return Bengine_Admin_Controller_Cms
	 */
	protected function updatePagesAction($delete, $pages)
	{
		foreach($delete as $id)
		{
			Core::getQuery()->delete("page", "pageid = '".$id."'");
		}
		foreach($pages as $id)
		{
			$spec = array(
				"displayorder" => Core::getRequest()->getPOST("displayorder_".$id),
				"title" => Core::getRequest()->getPOST("title_".$id),
				"position" => Core::getRequest()->getPOST("position_".$id),
			);
			Core::getQuery()->update("page", $spec, "pageid = '".$id."'");
		}
		return $this;
	}

	/**
	 * Update page in DB
	 *
	 * @param integer $pageid
	 *
	 * @return Bengine_Admin_Controller_Cms
	 */
	protected function updatePageAction($pageid)
	{
		$spec = array(
			"position" => $this->getParam("position"),
			"languageid" => $this->getParam("languageid"),
			"displayorder" => $this->getParam("displayorder"),
			"title" => $this->getParam("title"),
			"label" => $this->getParam("label"),
			"link" => $this->getParam("link"),
			"content" => $this->getParam("content")
		);

		$this->title = $spec["title"];
		$this->label = $spec["label"];
		$this->link = $spec["link"];
		$this->checkLink();
		Core::getQuery()->update("page", $spec, "pageid = '".$pageid."'");
		return $this;
	}

	/**
	 * Inserts new page into DB
	 *
	 * @param string $position
	 * @param integer $languageid
	 * @param integer $displayorder
	 * @param string $title
	 * @param string $label
	 * @param string $link
	 * @param string $content
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Admin_Controller_Cms
	 */
	protected function addPageAction($position, $languageid, $displayorder, $title, $label, $link, $content)
	{
		$this->title = $title;
		$this->label = $label;
		$this->link = $link;
		$this->checkLink();
		if(empty($displayorder))
		{
			$displayorder = 0;
		}
		if(empty($this->title))
		{
			$this->title = "";
		}
		if(empty($content))
		{
			$content = "";
		}

		$result = Core::getQuery()->select("page", array("pageid"), "", "label = '".$this->label."' && link = ''");
		if($result->fetchRow())
		{
			throw new Recipe_Exception_Generic("Label already in use");
		}
		else
		{
			$spec = array(
				"position" => $position,
				"languageid" => $languageid,
				"displayorder" => $displayorder,
				"title" => $title,
				"label" => $label,
				"link" => $link,
				"content" => $content
			);
			Core::getQuery()->insert("page", $spec);
		}
		return $this;
	}

	/**
	 * Creates a valid label from title
	 *
	 * @return Bengine_Admin_Controller_Cms
	 */
	protected function checkLink()
	{
		if(empty($this->link) && empty($this->label))
		{
			$split = preg_split("/[^A-Za-z0-9]/", $this->title, 2, PREG_SPLIT_NO_EMPTY);
			$this->label = $split[0];
		}

		if(empty($this->link) && !empty($this->label))
		{
			$split = preg_split("/[^A-Za-z0-9]/", $this->label, 2, PREG_SPLIT_NO_EMPTY);
			$this->label = $split[0];
		}

		$this->link = (empty($this->link)) ? "" : $this->link;
		$this->label = (empty($this->label)) ? "" : $this->label;
		$this->label = strtolower($this->label);

		return $this;
	}

}
?>