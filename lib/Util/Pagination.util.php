<?php
/**
 * Creates pagination.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Pagination.util.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Pagination
{
	/**
	 * Arrow images.
	 *
	 * @var string
	 */
	const	ARROW_BOLD_LEFT = "pointer-left-big.gif", ARROW_SMALL_LEFT = "pointer-left.gif",
			ARROW_BOLD_RIGHT = "pointer-right-big.gif", ARROW_SMALL_RIGHT = "pointer-right.gif";

	/**
	 * Current active page.
	 *
	 * @var integer
	 */
	protected $currentPage = 0;

	/**
	 * Items viewed per page.
	 *
	 * @var integer
	 */
	protected $itemsPerPage = 0;

	/**
	 * Total available items.
	 *
	 * @var integer
	 */
	protected $totalItems = 0;

	/**
	 * Maximum pages that will be shown.
	 *
	 * @var integer
	 */
	protected $maxPagesToShow = 5;

	/**
	 * Pagination HTML-code.
	 *
	 * @var string
	 */
	protected $html = null;

	/**
	 * Configuration variables.
	 *
	 * @var array
	 */
	protected $config = array(
		"show_prev_next" => true,
		"show_images" => true,
		"page_wrapper" => "li",
		"pagination_wrapper" => "ul",
		"active_element" => "span",
		"main_element" => "div",
		"main_element_class" => "pagination",
		"rewrite_on" => false,
		"page_url" => null,
		"base_url" => false,
	);

	/**
	 * Creates a new pagination object.
	 *
	 * @param integer $itemsPerPage	Items per page [optional]
	 * @param integer $totalItems	Total items [optional]
	 * @param integer $currentPage	Current page [optional]
	 *
	 * @return Pagination
	 */
	public function __construct($itemsPerPage = 0, $totalItems = 0, $currentPage = 0)
	{
		$this->setCurrentPage($currentPage)
			->setItemsPerPage($itemsPerPage)
			->setTotalItems($totalItems);
		return $this;
	}

	/**
	 * Sets a configuration variable.
	 *
	 * @param string|array $var	Variable name
	 * @param mixed $value		Value [optional]
	 *
	 * @return Pagination
	 */
	public function setConfig($var, $value = null)
	{
		if(is_array($var))
		{
			$this->config = array_merge($this->config, $var);
		}
		else
		{
			$this->config[$var] = $value;
		}
		return $this;
	}

	/**
	 * Returns a configuration variable.
	 *
	 * @param string $var	Variable name [optional]
	 *
	 * @return mixed	Value
	 */
	public function getConfig($var = null)
	{
		if(is_null($var))
		{
			return $this->config;
		}
		return (!empty($this->config[$var])) ? $this->config[$var] : null;
	}

	/**
	 * Returns the number of pages.
	 *
	 * @return integer
	 */
	public function getPageNumber()
	{
		return ceil($this->getTotalItems() / $this->getItemsPerPage());
	}

	/**
	 * Generates a page link
	 *
	 * @param integer $page	Page number
	 * @param string $title	Title [optional]
	 * @param string $image	Image [optional]
	 *
	 * @return string
	 */
	protected function getPageLink($page, $title = null, $image = null)
	{
		$title = (is_null($title)) ? $page : Core::getLang()->get($title);
		$image = (is_null($image) || !$this->getConfig("show_images")) ? "" : Image::getImage($image, $title);
		if($page > $this->getCurrentPage())
		{
			$label = $title." ".$image;
		}
		else
		{
			$label = $image." ".$title;
		}
		$label = trim($label);
		if($page == 1 && $this->getConfig("base_url"))
		{
			$url = $this->getConfig("base_url");
		}
		else if($this->getConfig("page_url"))
		{
			$url = sprintf($this->getConfig("page_url"), $page);
		}
		else
		{
			$url = $this->getPageUrl($page);
		}
		return Link::get($url, $label);
	}

	/**
	 * Generates the page Url.
	 *
	 * @param integer $page	Page number
	 *
	 * @return string
	 */
	protected function getPageUrl($page)
	{
		$op = "=";
		$sp = "&amp;";
		$in = "?";
		$param = "page";
		$requestVars = Core::getRequest()->getGET();
		$queryStr = array();
		$containsPage = false;
		foreach($requestVars as $key => $value)
		{
			if(Str::compare($key, $param))
			{
				$value = $page;
				$containsPage = true;
			}
			$queryStr[] = $key.$op.$value;
		}
		if(!$containsPage)
		{
			$queryStr[] = $param.$op.$page;
		}
		$queryStr = $in.implode($sp, $queryStr);
		return $queryStr;
	}

	/**
	 * Generates the HTML-code.
	 *
	 * @return Pagination
	 */
	public function render()
	{
		$this->html = "";
		if($this->getTotalItems() > $this->getItemsPerPage())
		{
			$pageNumber = $this->getPageNumber();
			$from = $this->getCurrentPage() - floor($this->getMaxPagesToShow()/2);
			$to = $this->getCurrentPage() + floor($this->getMaxPagesToShow()/2);
			$pagination = "";

			if($from <= 0)
			{
				$from = 1;
				$to = $from + $this->getMaxPagesToShow() - 1;
			}
			if($to > $pageNumber)
			{
				$to = $pageNumber;
				$from = $pageNumber - $this->getMaxPagesToShow() + 1;
				if($from <= 0)
				{
					$from = 1;
				}
			}
			if($to == 0)
			{
				$to = $pageNumber;
			}

			if($this->getCurrentPage() > 2 && $this->getConfig("show_prev_next"))
			{
				$pagination .= "<".$this->getConfig("page_wrapper").">".$this->getPageLink(1, "FIRST", self::ARROW_BOLD_LEFT)."</".$this->getConfig("page_wrapper").">";
			}
			if($this->getCurrentPage() > 1 && $this->getConfig("show_prev_next"))
			{
				$previous = $this->getCurrentPage() - 1;
				$pagination .= "<".$this->getConfig("page_wrapper").">".$this->getPageLink($previous, "PREVIOUS", self::ARROW_SMALL_LEFT)."</".$this->getConfig("page_wrapper").">";
			}
			for($i = $from; $i <= $to; ++$i)
			{
				if ($i == $this->getCurrentPage())
				{
					$pagination .= "<".$this->getConfig("page_wrapper")."><".$this->getConfig("active_element").">".$i."</".$this->getConfig("active_element")."></".$this->getConfig("page_wrapper").">";
				}
				else
				{
					$pagination .= "<".$this->getConfig("page_wrapper").">".$this->getPageLink($i)."</".$this->getConfig("page_wrapper").">";
				}
			}
			if($this->getCurrentPage() < $pageNumber && $this->getConfig("show_prev_next"))
			{
				$next = $this->getCurrentPage() + 1;
				$pagination .= "<".$this->getConfig("page_wrapper").">".$this->getPageLink($next, "NEXT", self::ARROW_SMALL_RIGHT)."</".$this->getConfig("page_wrapper").">";
				if($this->getCurrentPage() < ($pageNumber - 1))
				{
					$pagination .= "<".$this->getConfig("page_wrapper").">".$this->getPageLink($pageNumber, "LAST", self::ARROW_BOLD_RIGHT)."</".$this->getConfig("page_wrapper").">";
				}
			}
			$this->html = "<".$this->getConfig("main_element")." class=\"".$this->getConfig("main_element_class")."\"><".$this->getConfig("pagination_wrapper").">".$pagination."</".$this->getConfig("pagination_wrapper")."></".$this->getConfig("main_element").">";
		}
		return $this;
	}

	/**
	 * Returns the HTML-code
	 *
	 * @return string
	 */
	public function getHtml()
	{
		if(is_null($this->html))
		{
			$this->render();
		}
		return $this->html;
	}

	/**
	 * Sets the current page.
	 *
	 * @param integer
	 *
	 * @return Pagination
	 */
	public function setCurrentPage($currentPage = 0)
	{
		if($currentPage <= 0)
		{
			$currentPage = ($this->getConfig("rewrite_on")) ? Core::getRequest()->getGET("Page", 1) : Core::getRequest()->getGET("page", 1);
			if(empty($currentPage))
			{
				$currentPage = 1;
			}
		}
		$this->currentPage = $currentPage;
		return $this;
	}

	/**
	 * Sets the items per page.
	 *
	 * @param integer
	 *
	 * @return Pagination
	 */
	public function setItemsPerPage($itemsPerPage)
	{
		$this->itemsPerPage = $itemsPerPage;
		return $this;
	}

	/**
	 * Sets the total number of items.
	 *
	 * @param integer
	 *
	 * @return Pagination
	 */
	public function setTotalItems($totalItems)
	{
		$this->totalItems = $totalItems;
		return $this;
	}

	/**
	 * Sets the maximum pages to show.
	 *
	 * @param integer
	 *
	 * @return Pagination
	 */
	public function setMaxPagesToShow($maxPagesToShow)
	{
		$this->maxPagesToShow = $maxPagesToShow;
		return $this;
	}

	/**
	 * Returns the current page.
	 *
	 * @return integer
	 */
	public function getCurrentPage()
	{
		return $this->currentPage;
	}

	/**
	 * Returns the items per page.
	 *
	 * @return integer
	 */
	public function getItemsPerPage()
	{
		return $this->itemsPerPage;
	}

	/**
	 * Returns the total number of items.
	 *
	 * @return integer
	 */
	public function getTotalItems()
	{
		return $this->totalItems;
	}

	/**
	 * Returns the number of maximum pages to show.
	 *
	 * @return integer
	 */
	public function getMaxPagesToShow()
	{
		return $this->maxPagesToShow;
	}

	/**
	 * Returns the start position.
	 *
	 * @return integer
	 */
	public function getStart()
	{
		return ($this->getCurrentPage() - 1) * $this->getItemsPerPage();
	}

	/**
	 * Converts this class to a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getHtml();
	}
}
?>