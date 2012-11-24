<?php
/**
 * Permissions controller.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Bengine_Admin_Controller_Permissions extends Bengine_Admin_Controller_Abstract
{
	/**
	 * @return Bengine_Admin_Controller_Abstract
	 */
	protected function init()
	{
		Core::getLanguage()->load("AI_User");
		return parent::init();
	}

	/**
	 * @return Bengine_Admin_Controller_Permissions
	 */
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
		Core::getTPL()->addLoop("perms", $perms->fetchAll());
		return $this;
	}

	/**
	 * @param string $perm
	 * @return Bengine_Admin_Controller_Permissions
	 */
	protected function add($perm)
	{
		Core::getQuery()->insert("permissions", array("permission" => $perm));
		return $this;
	}

	/**
	 * @param array $delete
	 * @param array $perms
	 * @return Bengine_Admin_Controller_Permissions
	 */
	protected function update(array $delete, array $perms)
	{
		foreach($delete as $pid)
		{
			Core::getQuery()->delete("permissions", "permissionid = '".$pid."'");
		}
		foreach($perms as $pid)
		{
			Core::getQuery()->update("permissions", array("permission" => Core::getRequest()->getPOST("perm_".$pid)), "permissionid = '".$pid."'");
		}
		return $this;
	}
}
?>