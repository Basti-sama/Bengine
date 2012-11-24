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
	 * @return \Recipe_Template_Adapter_Dwoo
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
	 * @return \Recipe_Template_Adapter_Dwoo
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
	 * @param string|array $variable
	 * @param mixed $value
	 * @return \Recipe_Template_Adapter_Dwoo
	 */
	public function assign($variable, $value = null)
	{
		$this->getData()->assign($variable, $value);
		return $this;
	}

	/**
	 * @param string $loop
	 * @param mixed $data
	 * @return \Recipe_Template_Adapter_Dwoo
	 */
	public function addLoop($loop, $data)
	{
		$this->getData()->assign($loop, $data);
		return $this;
	}

	/**
	 * @param string $template
	 * @param bool $noLayout
	 * @param string $layout
	 * @return \Recipe_Template_Adapter_Dwoo
	 */
	public function display($template, $noLayout = false, $layout = null)
	{
		$outStream = $this->render($template, self::TEMPLATE_TYPE_VIEWS);
		if($noLayout === false)
		{
			$this->assign("CONTENT_TEMPLATE", $outStream);
			if($layout === null)
			{
				$layout = $this->getLayoutTemplate();
			}
			$outStream = $this->render($layout, self::TEMPLATE_TYPE_LAYOUTS);
		}
		echo $outStream;
		return $this;
	}

	/**
	 * @param object $view
	 * @return \Recipe_Template_Adapter_Dwoo
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
	 * @return object
	 */
	public function getView()
	{
		return $this->get("this", new stdClass());
	}

	/**
	 * @param string $var
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($var, $default = null)
	{
		return ($this->exists($var)) ? $this->getData()->get($var) : $default;
	}

	/**
	 * @param string $var
	 * @return bool
	 */
	public function exists($var)
	{
		return $this->getData()->isAssigned($var);
	}

	/**
	 * @param string $loop
	 * @return array|mixed
	 */
	public function getLoop($loop)
	{
		$value = $this->get($loop, array());
		return (is_array($value) || $value instanceof Traversable) ? $value : array();
	}

	/**
	 * @param string $template
	 * @param string $type
	 * @return string
	 */
	public function render($template, $type = Recipe_Template_Adapter_Abstract::TEMPLATE_TYPE_VIEWS)
	{
		$template = $this->getTemplatePath($template, $type);
		$outStream = $this->getDwoo()->get($template, $this->getData(), $this->getCompiler());
		return $outStream;
	}

	/**
	 * @return \Recipe_Template_Adapter_Dwoo
	 */
	public function deallocateAllAssignment()
	{
		$this->getData()->clear();
		return $this;
	}

	/**
	 * @param string $variable
	 * @return \Recipe_Template_Adapter_Dwoo
	 */
	public function deallocateAssignment($variable)
	{
		$this->getData()->clear($variable);
		return $this;
	}

	/**
	 * @param string $message
	 * @return \Recipe_Template_Adapter_Dwoo
	 */
	public function addLogMessage($message)
	{
		$log = $this->get("LOG", array());
		$log[] = $message;
		$this->getData()->assign($log);
		return $this;
	}

	/**
	 * @param string $file
	 * @param string $type
	 * @return \Recipe_Template_Adapter_Dwoo
	 */
	public function addHTMLHeaderFile($file, $type = "js")
	{
		$files = $this->get($type."_files", array());
		$files[] = $file;
		$this->assign($type."_files", $files);
		return $this;
	}

	/**
	 * @param string $type
	 * @return \Recipe_Template_Adapter_Dwoo
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