<?php
class Admin_Controller_Cms extends Admin_Controller_Abstract
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
	 * @return Admin_Controller_Cms
	 */
	protected function init()
	{
		Core::getLanguage()->load("AI_CMS");
		return parent::init();
	}

	/**
	 * Index page
	 *
	 * @return Admin_Controller_Cms
	 */
	protected function indexAction()
	{
		if($this->getParam("update_pages"))
		{
			$this->updatePagesAction($this->getParam("delete"), $this->getParam("pages"));
		}

		$pages = array();
		$result = Core::getQuery()->select("page", array("pageid", "position", "displayorder", "title"), "", "", "position DESC, displayorder ASC");
		while($row = Core::getDB()->fetch($result))
		{
			$id = $row["pageid"];
			$pages[$id]["pageid"] = $id;
			$pages[$id]["position"] = $row["position"];
			$pages[$id]["displayorder"] = $row["displayorder"];
			$pages[$id]["title"] = $row["title"];
			$pages[$id]["edit_link"] = Link::get("cms/edit/".$id, Core::getLanguage()->getItem("Edit"));
		}

		Core::getTPL()->addLoop("pages", $pages);
		return $this;
	}

	/**
	 * Edit existing page
	 *
	 * @return Admin_Controller_Cms
	 */
	protected function editAction()
	{
		if($this->getParam("edit_page"))
		{
			$this->updatePageAction($this->getParam("pageid"));
		}

		$pageid = Core::getRequest()->getGET("1");

		$page = Core::getDB()->fetch(Core::getQuery()->select("page", array("pageid", "position", "languageid", "displayorder", "title", "label", "link", "content"), "", "pageid = ".$pageid, ""));
		Core::getTPL()->assign($page);

		$result = Core::getQuery()->select("languages", array("languageid", "title"), "", "", "title ASC");
		Core::getTPL()->addLoop("langselection", $result);

		return $this;
	}

	/**
	 * Add new page
	 *
	 * @return Admin_Controller_Cms
	 */
	protected function newPageAction()
	{
		if($this->getParam("add_page"))
		{
			$this->addPageAction($this->getParam("position"), $this->getParam("languageid"), $this->getParam("displayorder"), $this->getParam("title"), $this->getParam("label"), $this->getParam("link"), $this->getParam("content"));
		}

		$result = Core::getQuery()->select("languages", array("languageid", "title"), "", "", "title ASC");
		Core::getTPL()->addLoop("langselection", $result);
		return $this;
	}

	/**
	 * Update pages in DB (from main page)
	 *
	 * @param array delete-pages
	 * @param array pages
	 *
	 * @return Admin_Controller_Cms
	 */
	protected function updatePagesAction($delete, $pages)
	{
		foreach($delete as $id)
		{
			Core::getQuery()->delete("page", "pageid = '".$id."'");
		}
		foreach($pages as $id)
		{
			$atts = array("displayorder", "title", "position");
			$vals = array(Core::getRequest()->getPOST("displayorder_".$id), Core::getRequest()->getPOST("title_".$id), Core::getRequest()->getPOST("position_".$id));
			Core::getQuery()->update("page", $atts, $vals, "pageid = '".$id."'");
		}
		return $this;
	}

	/**
	 * Update page in DB
	 *
	 * @param integer Page ID
	 *
	 * @return Admin_Controller_Cms
	 */
	protected function updatePageAction($pageid)
	{
		$atts = array(
			"position",
			"languageid",
			"displayorder",
			"title",
			"label",
			"link",
			"content"
			);
		$vals = array(
			$this->getParam("position"),
			$this->getParam("languageid"),
			$this->getParam("displayorder"),
			$this->getParam("title"),
			$this->getParam("label"),
			$this->getParam("link"),
			$this->getParam("content")
		);

		$this->title = $vals[3];
		$this->label = $vals[4];
		$this->link = $vals[5];
		$this->checkLink();
		Core::getQuery()->update("page", $atts, $vals, "pageid = '".$pageid."'");
		return $this;
	}

	/**
	 * Inserts new page into DB
	 *
	 * @param string	Position (header/footer)
	 * @param integer	Language ID
	 * @param integer	Displayorder
	 * @param string	Title
	 * @param string	Label
	 * @param string	Link
	 * @param string	Content
	 *
	 * @return Admin_Controller_Cms
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
		if(Core::getDB()->fetch($result))
		{
			//throw new Recipe_Exception_Generic("Label already in use", __FILE__, __LINE__);
			throw new Recipe_Exception_Generic("Label already in use");
		}
		else
		{
			Core::getQuery()->insert("page", array("position", "languageid", "displayorder", "title", "label", "link", "content"), array($position, $languageid, $displayorder, $this->title, $this->label, $this->link, $content));
		}
		return $this;
	}

	/**
	 * Creates a valid label from title
	 *
	 * @return Admin_Controller_Cms
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