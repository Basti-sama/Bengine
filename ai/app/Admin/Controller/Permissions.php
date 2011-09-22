<?php
class Admin_Controller_Permissions extends Admin_Controller_Abstract
{
	protected function init()
	{
		Core::getLanguage()->load("AI_User");
		return $this;
	}

	protected function indexAction()
	{
		if($this->getParam("add_permission"))
		{
			$this->add($this->getParam("permission"));
		}
		else if($this->getParam("update_permission"))
		{
			$this->update($this->getParam("delete"), $this->getParam("perm"));
		}
		$perms = Core::getQuery()->select("permissions", array("permissionid", "permission"), "", "", "permission ASC");
		Core::getTPL()->addLoop("perms", $perms);
		return $this;
	}

	protected function add($perm)
	{
		Core::getQuery()->insert("permissions", "permission", $perm);
		return $this;
	}

	protected function update($delete, $perms)
	{
		foreach($delete as $pid)
		{
			Core::getQuery()->delete("permissions", "permissionid = '".$pid."'");
		}
		foreach($perms as $pid)
		{
			Core::getQuery()->update("permissions", "permission", Core::getRequest()->getPOST("perm_".$pid), "permissionid = '".$pid."'");
		}
		return $this;
	}
}
?>