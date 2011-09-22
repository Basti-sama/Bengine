<?php
/**
 * Dwoo template adapter.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Dwoo.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Template_Adapter_Dwoo extends Recipe_Template_Adapter_Abstract
{
	/**
	 * The dwoo object.
	 *
	 * @var Dwoo
	 */
	protected $_dwoo = null;

	/**
	 * The data object.
	 *
	 * @var Dwoo_Data
	 */
	protected $_data = null;

	/**
	 * The compiler object.
	 *
	 * @var Dwoo_Compiler
	 */
	protected $_compiler = null;

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#init()
	 */
	protected function init()
	{
		$this->_dwoo = new Dwoo();
		$this->_data = new Dwoo_Data();
		$this->_compiler = new Dwoo_Compiler();
		$this->getDwoo()->setCharset(Core::getLang()->getOpt("charset"));
		$this->_setup();
		return parent::init();
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#_setup()
	 */
	protected function _setup()
	{
		if(!is_null($this->_dwoo))
		{
			$this->getDwoo()->setCompileDir(APP_ROOT_DIR."var/cache/templates/".$this->getTemplatePackage());
		}
		return parent::_setup();
	}

	/**
	 * Retrieves the dwoo object.
	 *
	 * @return Dwoo
	 */
	public function getDwoo()
	{
		return $this->_dwoo;
	}

	/**
	 * Retrieves the dwoo data object.
	 *
	 * @return Dwoo_Data
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Retrieves the dwoo compiler object.
	 *
	 * @return Dwoo_Compiler
	 */
	public function getCompiler()
	{
		return $this->_compiler;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#assign($variable, $value)
	 */
	public function assign($variable, $value = null)
	{
		$this->getData()->assign($variable, $value);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#addLoop($loop, $data)
	 */
	public function addLoop($loop, $data)
	{
		$this->getData()->assign($loop, $data);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#display($template, $sendOnlyContent, $mainTemplate)
	 */
	public function display($template, $sendOnlyContent = false, $mainTemplate = null)
	{
		$template = $this->getTemplatePath($template);
		if($sendOnlyContent)
		{
			$this->sendHeader();
			$this->getDwoo()->output($template, $this->getData(), $this->getCompiler());
		}
		else
		{
			$out = $this->getDwoo()->get($template, $this->getData(), $this->getCompiler());
			$this->assign("CONTENT_TEMPLATE", $out);
			if(is_null($mainTemplate))
			{
				$mainTemplate = $this->mainTemplateFile;
			}
			$this->display($mainTemplate, true);
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#setView($view)
	 */
	public function setView($view)
	{
		if(is_object($view))
		{
			$this->assign("this", $view);
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#getView()
	 */
	public function getView()
	{
		return $this->get("this", new stdClass());
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#get($var, $default)
	 */
	public function get($var, $default = null)
	{
		return ($this->exists($var)) ? $this->getData()->get($var) : $default;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#exists($var)
	 */
	public function exists($var)
	{
		return $this->getData()->isAssigned($var);
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#getLoop($loop)
	 */
	public function getLoop($loop)
	{
		$value = $this->get($loop, array());
		return (is_array($value) || $value instanceof Traversable) ? $value : array();
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#includeTemplate($template)
	 */
	public function includeTemplate($template)
	{
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#deallocateAllAssignment()
	 */
	public function deallocateAllAssignment()
	{
		$this->getData()->clear();
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#deallocateAssignment($variable)
	 */
	public function deallocateAssignment($variable)
	{
		$this->getData()->clear($variable);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#addLogMessage($message)
	 */
	public function addLogMessage($message)
	{
		$log = $this->get("LOG", array());
		$log[] = $message;
		$this->getData()->assign($log);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#addHTMLHeaderFile($file, $type)
	 */
	public function addHTMLHeaderFile($file, $type = "js")
	{
		$files = $this->get($type."_files", array());
		$files[] = $file;
		$this->assign($type."_files", $files[]);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Template/Adapter/Recipe_Template_Adapter_Abstract#clearHTMLHeaderFiles($type)
	 */
	public function clearHTMLHeaderFiles($type = null)
	{
		if(!is_null($type))
		{
			$this->deallocateAssignment($type."_files");
		}
		return $this;
	}
}
?>