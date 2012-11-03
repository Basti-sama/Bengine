<?php
abstract class Bengine_Admin_Controller_Abstract extends Recipe_Controller_Abstract
{
	/**
	 * @return Bengine_Admin_Controller_Abstract
	 */
	protected function init()
	{
		if($this->getParam("controller") != "auth" && !Core::getUser()->ifPermissions(array("HAS_AI_ACCESS")))
		{
			$this->redirect("admin/auth");
		}
		return parent::init();
	}
}
?>