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
	 * Default controller name.
	 *
	 * @var string
	 */
	const DEFAULT_CONTROLLER_NAME = "index";

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
	 * The package name.
	 *
	 * @var string
	*/
	protected $_package = null;

	/**
	 * The output template.
	 *
	 * @var string
	 */
	protected $_template = null;

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
	 * @param array $args
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
		$this->setPackage(Core::getRequest()->getGET("package"));
		$this->setMainTemplate($this->getPackage());
		$this->init();
		return $this;
	}

	/**
	 * Assigns a new variable to the template.
	 *
	 * @param string $var	Variable name
	 * @param mixed $value	Value
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
	 * @param string $var	Variable name
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
	 * @param string $var	Variable name
	 * @param mixed $value		Value
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
		$method = $action.$this->getActionNameSuffix();
		if(!method_exists($this, $method))
		{
			$method = "noroute".$this->getActionNameSuffix();
		}
		$args = array();
		for($i = 1; $i < 5; $i++)
		{
			$args[] = $this->getParam(strval($i));
		}
		call_user_func_array(array($this, $method), $args);
		if(!$this->_noDisplay)
		{
			Core::getTPL()->display($this->getTemplate($action), $this->_isAjax);
		}
		return $this;
	}

	/**
	 * Returns the called template.
	 *
	 * @param string $action	Action name
	 *
	 * @return string	Template name
	 */
	public function getTemplate($action)
	{
		$package = $this->getPackage();
		if(!empty($package))
		{
			$package .= "/";
		}
		if(!empty($this->_template))
		{
			return strtolower($package.$this->_template);
		}
		$controller = $this->getParam("controller", self::DEFAULT_CONTROLLER_NAME);
		return strtolower($package.$controller."/".$action);
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
			$this->setMainTemplate(false);
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
		Core::getTemplate()->setLayoutTemplate($mainTemplate);
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
		$this->assign("action", $this->_action);
		Recipe_Header::statusCode(404);
		return $this;
	}

	/**
	 * Returns a request parameter.
	 *
	 * @param string $param		Parameter name
	 * @param mixed $default	Default return value
	 *
	 * @return mixed
	 */
	protected function getParam($param, $default = null)
	{
		if(empty($param))
		{
			return $default;
		}
		if(($value = Core::getRequest()->getPOST($param, null)) !== null)
		{
			return $value;
		}
		return Core::getRequest()->getGET($param, $default);
	}

	/**
	 * Set no display.
	 *
	 * @param boolean $noDisplay
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
	 * @param string $url		Uri or Url
	 * @param boolean $session	Append session? [optional]
	 *
	 * @return Bengine_Game_Controller_Abstract
	 */
	protected function redirect($url, $session = false)
	{
		Recipe_Header::redirect($url, $session);
		return $this;
	}

	/**
	 * Sets the action name suffix.
	 *
	 * @param string $actionNameSuffix
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

	/**
	 * @param string $package
	 * @return Recipe_Controller_Abstract
	 */
	public function setPackage($package)
	{
		$this->_package = $package;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPackage()
	{
		return $this->_package;
	}
}
?>