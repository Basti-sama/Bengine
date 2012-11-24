<?php
/**
 * Admin abstract controller.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

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