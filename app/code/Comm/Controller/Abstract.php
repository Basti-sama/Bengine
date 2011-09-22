<?php
/**
 * Abstract controller.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Comm_Controller_Abstract extends Recipe_Controller_Abstract
{
	/**
	 * Template prefix.
	 *
	 * @var string
	 */
	const TEMPLATE_PREFIX = "comm_";

	/**
	 * Creates a new controller object.
	 *
	 * @param array		Configuration
	 *
	 * @return Comm_Controller_Abstract
	 */
	public function __construct(array $args = array())
	{
		$this->setMainTemplate("comm");
		return parent::__construct($args);
	}

	/**
	 * Returns the called template.
	 *
	 * @param string	Action name
	 *
	 * @return string	Template name
	 */
	public function getTemplate($action)
	{
		if(!empty($this->_template))
		{
			return $this->_template;
		}
		return self::TEMPLATE_PREFIX.strtolower($this->getParam("controller", "index"))."_".$action;
	}

	/**
	 * Called when no action method has been found.
	 *
	 * @return Comm_Controller_Abstract
	 */
	protected function norouteAction()
	{
		$this->page = Core::getLang()->get("ERROR_404");
		return parent::norouteAction();
	}
}
?>