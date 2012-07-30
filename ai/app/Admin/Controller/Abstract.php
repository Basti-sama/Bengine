<?php
abstract class Admin_Controller_Abstract extends Recipe_Controller_Abstract
{
	/**
	 * @return Admin_Controller_Abstract
	 */
	protected function init()
	{
		if($this->getParam("controller") != "auth" && !Core::getUser()->ifPermissions(array("HAS_AI_ACCESS")))
		{
			$this->redirect("auth");
		}
		return parent::init();
	}
}
?>