<?php
/**
 * Abstract controller.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Recipe_Controller_Abstract
{
	/**
	 * Request method.
	 *
	 * @var string
	 */
	private $_requestMethod = "";

	/**
	 * The requested action.
	 *
	 * @var string
	 */
	protected $_action = "index";

	/**
	 * The output template.
	 *
	 * @var string
	 */
	protected $_template = null;

	/**
	 * Layout template.
	 *
	 * @var string
	 */
	private $_mainTemplate = "layout";

	/**
	 * Flag to display no template.
	 *
	 * @var boolean
	 */
	private $_noDisplay = false;

	/**
	 * Request is AJAX requets.
	 *
	 * @var boolean
	 */
	private $_isAjax = false;

	/**
	 * Append to action method.
	 *
	 * @var string
	 */
	private $_actionNameSuffix = "Action";

	/**
	 * Creates a new controller object.
	 *
	 * @param array		Configuration
	 *
	 * @return Recipe_Controller_Abstract
	 */
	public function __construct(array $args = array())
	{
		if(!empty($args["action"]))
		{
			$this->_action = strtolower($args["action"]);
		}
		$this->_requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);
		$this->init();
		return $this;
	}

	/**
	 * Assigns a new variable to the template.
	 *
	 * @param string	Variable name
	 * @param mixed		Value
	 *
	 * @return Recipe_Controller_Abstract
	 */
	public function __set($var, $value)
	{
		Core::getTPL()->assign($var, $value);
		return $this;
	}

	/**
	 * Gets an assigned variable from the template.
	 *
	 * @param string	Variable name
	 *
	 * @return mixed
	 */
	public function __get($var)
	{
		return Core::getTPL()->get($var);
	}

	/**
	 * Assigns a new variable to the template.
	 *
	 * @param string	Variable name
	 * @param mixed		Value
	 *
	 * @return Recipe_Controller_Abstract
	 */
	public function assign($var, $value = null)
	{
		Core::getTPL()->assign($var, $value);
		return $this;
	}

	/**
	 * Initializing method.
	 *
	 * @return Recipe_Controller_Abstract
	 */
	protected function init()
	{
		return $this;
	}

	/**
	 * Starts the controller.
	 *
	 * @return Recipe_Controller_Abstract
	 */
	public function run()
	{
		$action = $this->_action;
		if(!method_exists($this, $action.$this->getActionNameSuffix()))
		{
			$action = "noroute";
		}
		$args = array();
		for($i = 1; $i < 5; $i++)
		{
			$args[] = $this->getParam(strval($i));
		}
		call_user_func_array(array($this, $action.$this->getActionNameSuffix()), $args);
		if(!$this->_noDisplay)
		{
			Core::getTPL()->display($this->getTemplate($action), $this->_isAjax, $this->_mainTemplate);
		}
		return $this;
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
		return strtolower($this->getParam("controller", "Main"))."_".$action;
	}

	/**
	 * Was the request made by POST?
	 *
	 * @return boolean
	 */
	public function isPost()
	{
		return ($this->_requestMethod == "post") ? true : false;
	}

	/**
	 * Defines an action as an AJAX request.
	 *
	 * @param boolean
	 *
	 * @return Recipe_Controller_Abstract
	 */
	protected function setIsAjax($isAjax = true)
	{
		$this->_isAjax = (bool) $isAjax;
		if($this->_isAjax)
		{
			$this->_mainTemplate = null;
		}
		return $this;
	}

	/**
	 * Sets the layout template.
	 *
	 * @param string
	 *
	 * @return Recipe_Controller_Abstract
	 */
	protected function setMainTemplate($mainTemplate)
	{
		$this->_mainTemplate = $mainTemplate;
		return $this;
	}

	/**
	 * Sets the template.
	 *
	 * @param string
	 *
	 * @return Recipe_Controller_Abstract
	 */
	protected function setTemplate($template)
	{
		$this->_template = $template;
		return $this;
	}

	/**
	 * Called when no action method has been found.
	 *
	 * @return Recipe_Controller_Abstract
	 */
	protected function norouteAction()
	{
		$this->setTemplate("noroute");
		$this->action = $this->_action;
		Recipe_Header::statusCode(404);
		return $this;
	}

	/**
	 * Returns a request parameter.
	 *
	 * @param string	Parameter name
	 * @param mixed		Default return value
	 *
	 * @return mixed
	 */
	protected function getParam($param, $default = null)
	{
		if(empty($param))
		{
			return $default;
		}
		if($value = Core::getRequest()->getPOST($param))
		{
			return $value;
		}
		return Core::getRequest()->getGET($param, $default);
	}

	/**
	 * Set no display.
	 *
	 * @param boolean
	 *
	 * @return Recipe_Controller_Abstract
	 */
	public function setNoDisplay($noDisplay = true)
	{
		$this->_noDisplay = (bool) $noDisplay;
		return $this;
	}

	/**
	 * Executes a redirection.
	 *
	 * @param string	Uri or Url
	 * @param boolean	Append session? [optional]
	 *
	 * @return Bengine_Page_Abstract
	 */
	protected function redirect($url, $session = false)
	{
		Recipe_Header::redirect($url, $session);
		return $this;
	}

	/**
	 * Sets the action name suffix.
	 *
	 * @param string
	 *
	 * @return Recipe_Controller_Abstract
	 */
	public function setActionNameSuffix($actionNameSuffix)
	{
		$this->_actionNameSuffix = $actionNameSuffix;
		return $this;
	}

	/**
	 * Returns the action name suffix.
	 *
	 * @return string
	 */
	public function getActionNameSuffix()
	{
		return $this->_actionNameSuffix;
	}
}
?>