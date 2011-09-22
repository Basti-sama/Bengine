<?php
class Admin_Controller_Commercial extends Admin_Controller_Abstract
{
	protected function indexAction()
	{
		if($this->isPost())
		{
			if($this->getParam("add_ad"))
			{
				$this->addAdd($this->getParam("name"), $this->getParam("position"), $this->getParam("max_views"), $this->getParam("enabled"), $this->getParam("html_code"));
			}
			else if($this->getParam("delete"))
			{
				$this->delete($this->getParam("ads"));
			}
		}
		$ads = array();
		$result = Core::getQuery()->select("ad", array("*"));
		while($row = Core::getDB()->fetch($result))
		{
			$row["edit_link"] = Link::get("commercial/edit/".$row["ad_id"], Core::getLang()->get("Edit"));
			$row["reset_link"] = Link::get("commercial/reset/".$row["ad_id"], "Reset");
			$ads[] = $row;
		}
		Core::getTPL()->addLoop("ads", $ads);
		return $this;
	}

	protected function addAdd($name, $position, $maxViews, $enabled, $html)
	{
		Core::getQuery()->insert("ad", array("name", "position", "max_views", "html_code"), array($name, $position, $maxViews, $html));
		return $this;
	}

	protected function delete($ads)
	{
		if(is_array($ads))
		{
			foreach($ads as $adId)
			{
				Core::getQuery()->delete("ad", "ad_id = '".$adId."'");
			}
		}
		return $this;
	}

	protected function resetAction()
	{
		Core::getQuery()->update("ad", array("views"), array(0), "ad_id = '".$this->getParam("1")."'");
		return $this->redirect("commercial");
	}

	protected function editAction()
	{
		if($this->isPost())
		{
			$this->save($this->getParam("ad_id"), $this->getParam("name"), $this->getParam("position"), $this->getParam("max_views"), $this->getParam("enabled"), $this->getParam("html_code"));
		}
		$result = Core::getQuery()->select("ad", array("*"), "", "ad_id = '".$this->getParam("1")."'");
		$row = Core::getDB()->fetch($result);
		$row["html_code"] = htmlspecialchars($row["html_code"]);
		Core::getTPL()->assign($row);
		return $this;
	}

	protected function save($adId, $name, $position, $maxViews, $enabled, $html)
	{
		Core::getQuery()->update("ad", array("name", "position", "max_views", "enabled", "html_code"), array($name, $position, $maxViews, $enabled, $html), "ad_id = '".$adId."'");
		return $this;
	}
}
?>