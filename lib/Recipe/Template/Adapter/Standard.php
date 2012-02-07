<?php
/**
 * Standard template adapter.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Standard.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Template_Adapter_Standard extends Recipe_Template_Adapter_Abstract
{
	/**
	 * Holds all template variables.
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Initializing method.
	 *
	 * @return Recipe_Template_Adapter_Default
	 */
	protected function init()
	{
		$this->_data["html_header"] = array();
		$this->_data["LOG"] = "";
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#assign($variable, $value)
	 */
	public function assign($variable, $value = null)
	{
		if(is_array($variable))
		{
			foreach($variable as $key => $val)
			{
				if(Str::length($key) > 0) { $this->assign($key, $val); }
			}
		}
		else if(is_string($variable) || is_numeric($variable))
		{
			if(Str::length($variable) > 0) { $this->_data[$variable] = $value; }
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#addLoop($loop, $data)
	 */
	public function addLoop($loop, $data)
	{
		$this->_data[$loop] = $data;
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#deallocateAssignment($variable)
	 */
	public function deallocateAssignment($variable)
	{
		if(is_array($variable))
		{
			foreach($variable as $v)
			{
				$this->deallocateAssignment($v);
			}
		}
		else
		{
			unset($this->_data[$variable]);
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#deallocateAllAssignment()
	 */
	public function deallocateAllAssignment()
	{
		$this->_data = array();
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#display($template, $sendOnlyContent, $mainTemplate)
	 */
	function display($template, $sendOnlyContent = false, $mainTemplate = false)
	{
		$view = $this->getView();
		$this->sendHeader();
		if($sendOnlyContent)
		{
			require_once($this->getTemplatePath($template, "views"));
		}
		else
		{
			if($mainTemplate === false)
			{
				$mainTemplate = $this->mainTemplateFile;
			}
			require_once($this->getTemplatePath($mainTemplate, "layouts"));
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#includeTemplate($template)
	 */
	public function includeTemplate($template)
	{
		$this->display($template, true);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#addLogMessage($message)
	 */
	public function addLogMessage($message)
	{
		$this->_data["LOG"] .= $message;
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#addHTMLHeaderFile($file, $type)
	 */
	public function addHTMLHeaderFile($file, $type = "js")
	{
		$this->_data["html_header"][strtolower($type)][] = $file;
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#clearHTMLHeaderFiles($type)
	 */
	public function clearHTMLHeaderFiles($type = null)
	{
		if(is_null($type))
		{
			$this->_data["html_header"] = array();
		}
		else
		{
			$this->_data["html_header"][$type] = array();
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#get($var, $default)
	 */
	public function get($var, $default = null)
	{
		return ($this->exists($var)) ? $this->_data[$var] : $default;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#exists($var)
	 */
	public function exists($var)
	{
		return (isset($this->_data[$var]));
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#getLoop($loop)
	 */
	public function getLoop($loop)
	{
		return ($this->exists($loop) && (is_array($this->_data[$loop]) || $this->_data[$loop] instanceof Traversable)) ? $this->_data[$loop] : array();
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#setView($view)
	 */
	public function setView($view)
	{
		if(is_object($view))
		{
			$this->assign("__view", $view);
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#getView()
	 */
	public function getView()
	{
		return $this->get("__view");
	}
}
?>