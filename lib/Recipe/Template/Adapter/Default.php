<?php
/**
 * Default template adapter.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Default.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Recipe_Template_Adapter_Default extends Recipe_Template_Adapter_Abstract
{
	/**
	 * The view object.
	 *
	 * @var object
	 */
	protected $view = null;

	/**
	 * Holds html head data.
	 *
	 * @var Map
	 */
	protected $htmlHead = array();

	/**
	 * Holds template variables.
	 *
	 * @var array
	 */
	protected $templateVars = array();

	/**
	 * If compilation will always performed.
	 *
	 * @var boolean
	 */
	protected $forceCompilation = false;

	/**
	 * Loop stack.
	 *
	 * @var array
	 */
	protected $loopStack = array();

	/**
	 * Contains log messages.
	 *
	 * @var string
	 */
	protected $log = null;

	/**
	 * Initializing method.
	 *
	 * @return Recipe_Template_Adapter_Default
	 */
	protected function init()
	{
		$this->forceCompilation = false;
		$this->log = new Map();
		$this->assign("LOG", "");
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
			if(Str::length($variable) > 0) { $this->templateVars[$variable] = $value; }
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#addLoop($loop, $data)
	 */
	public function addLoop($loop, $data)
	{
		$this->loopStack[$loop] = $data;
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
			unset($this->templateVars[$variable]);
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#deallocateAllAssignment()
	 */
	public function deallocateAllAssignment()
	{
		$this->templateVars = array();
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#display($template, $sendOnlyContent, $mainTemplate)
	 */
	function display($template, $sendOnlyContent = false, $mainTemplate = null)
	{
		$view = $this->getView();
		if($this->log->size() > 0)
		{
			$this->assign("LOG", $this->log->toString("\n"));
		}
		if(count($this->htmlHead) > 0)
		{
			if(isset($this->htmlHead["js"]))
			{
				$this->assign("JS_FILES", implode(",", $this->htmlHead["js"]));
			}
			if(isset($this->htmlHead["css"]))
			{
				$this->assign("CSS_FILES", implode(",", $this->htmlHead["css"]));
			}
		}
		$this->sendHeader();
		Hook::event("TemplateStartOutstream", array($this, &$template, $sendOnlyContent, &$mainTemplate));
		if(!$sendOnlyContent || $mainTemplate != null)
		{
			if($mainTemplate == null) { $mainTemplate = $this->mainTemplateFile; }
			if(!$this->cachedTemplateAvailable($mainTemplate) || $this->forceCompilation)
			{
				new Recipe_Template_Default_Compiler($this->getTemplatePath($mainTemplate));
			}
			require_once(Core::getCache()->getTemplatePath($mainTemplate));
		}
		if(!$this->cachedTemplateAvailable($template) || $this->forceCompilation)
		{
			new Recipe_Template_Default_Compiler($this->getTemplatePath($template));
		}
		require_once(Core::getCache()->getTemplatePath($template));
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
		$this->log->push($message);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#addHTMLHeaderFile($file, $type)
	 */
	public function addHTMLHeaderFile($file, $type = "js")
	{
		$this->htmlHead[strtolower($type)][] = $file;
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
			$this->htmlHead = array();
		}
		else
		{
			$this->htmlHead[$type] = array();
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#get($var, $default)
	 */
	public function get($var, $default = null)
	{
		$default = ($default == "[var]") ? $var : $default;
		return ($this->exists($var)) ? $this->templateVars[$var] : $default;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#exists($var)
	 */
	public function exists($var)
	{
		return isset($this->templateVars[$var]);
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#getLoop($loop)
	 */
	public function getLoop($loop)
	{
		return (isset($this->loopStack[$loop])) ? $this->loopStack[$loop] : array();
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#setView($view)
	 */
	public function setView($view)
	{
		if(is_object($view))
		{
			$this->view = $view;
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#getView()
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 * Checks if template needs to be compiled.
	 *
	 * @param string	Template name
	 *
	 * @return boolean
	 */
	protected function cachedTemplateAvailable($template)
	{
		$cached = Core::getCache()->getTemplatePath($template);
		$template = $this->getTemplatePath($template);
		if(!file_exists($template))
		{
			throw new Recipe_Exception_Generic($template." does not exist.");
			return false;
		}
		if(!$this->forceCompilation && file_exists($cached))
		{
			if(filemtime($template) <= filemtime($cached))
			{
				return true;
			}
		}
		return false;
	}
}
?>