<?php
/**
 * Commercial controller.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Bengine_Admin_Controller_Commercial extends Bengine_Admin_Controller_Abstract
{
	/**
	 * @return Bengine_Admin_Controller_Commercial
	 */
	protected function indexAction()
	{
		if($this->isPost())
		{
			if($this->getParam("add_ad"))
			{
				$this->addAd($this->getParam("name"), $this->getParam("position"), $this->getParam("max_views"), $this->getParam("enabled", 0), $this->getParam("html_code"));
			}
			else if($this->getParam("delete"))
			{
				$this->delete($this->getParam("ads"));
			}
		}
		$ads = array();
		$result = Core::getQuery()->select("ad", array("*"));
		foreach($result->fetchAll() as $row)
		{
			$row["edit_link"] = Link::get("admin/commercial/edit/".$row["ad_id"], Core::getLang()->get("Edit"));
			$row["reset_link"] = Link::get("admin/commercial/reset/".$row["ad_id"], "Reset");
			$ads[] = $row;
		}
		Core::getTPL()->addLoop("ads", $ads);
		return $this;
	}

	/**
	 * @param string $name
	 * @param string $position
	 * @param integer $maxViews
	 * @param integer $enabled
	 * @param string $html
	 * @return Bengine_Admin_Controller_Commercial
	 */
	protected function addAd($name, $position, $maxViews, $enabled, $html)
	{
		if(!$maxViews)
		{
			$maxViews = null;
		}
		$spec = array("name" => $name, "position" => $position, "max_views" => $maxViews, "enabled" => $enabled, "html_code" => $html);
		Core::getQuery()->insert("ad", $spec);
		return $this;
	}

	/**
	 * @param array $ads
	 * @return Bengine_Admin_Controller_Commercial
	 */
	protected function delete($ads)
	{
		if(is_array($ads))
		{
			foreach($ads as $adId)
			{
				Core::getQuery()->delete("ad", "ad_id = ?", null, null, array($adId));
			}
		}
		return $this;
	}

	/**
	 * @param integer $adId
	 * @return Bengine_Game_Controller_Abstract
	 */
	protected function resetAction($adId)
	{
		Core::getQuery()->update("ad", array("views" => 0), "ad_id = ?", array($adId));
		return $this->redirect("admin/commercial");
	}

	/**
	 * @param integer $adId
	 * @return Bengine_Admin_Controller_Commercial
	 */
	protected function editAction($adId)
	{
		if($this->isPost())
		{
			$this->save($this->getParam("ad_id"), $this->getParam("name"), $this->getParam("position"), $this->getParam("max_views"), $this->getParam("enabled", 0), $this->getParam("html_code"));
		}
		$result = Core::getQuery()->select("ad", array("*"), "", Core::getDB()->quoteInto("ad_id = ?", $adId));
		$row = $result->fetchRow();
		$row["html_code"] = htmlspecialchars($row["html_code"]);
		Core::getTPL()->assign($row);
		return $this;
	}

	/**
	 * @param integer $adId
	 * @param string $name
	 * @param string $position
	 * @param integer $maxViews
	 * @param integer $enabled
	 * @param string $html
	 * @return Bengine_Admin_Controller_Commercial
	 */
	protected function save($adId, $name, $position, $maxViews, $enabled, $html)
	{
		if(!$maxViews)
		{
			$maxViews = null;
		}
		$spec = array("name" => $name, "position" => $position, "max_views" => $maxViews, "enabled" => $enabled, "html_code" => $html);
		Core::getQuery()->update("ad", $spec, "ad_id = ?", array($adId));
		return $this;
	}
}
?>