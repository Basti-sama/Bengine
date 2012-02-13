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
	 * @param string|array $variable
	 * @param mixed $value
	 * @return \Recipe_Template_Adapter_Standard
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
	 * @param string $loop
	 * @param mixed $data
	 * @return \Recipe_Template_Adapter_Standard
	 */
	public function addLoop($loop, $data)
	{
		$this->_data[$loop] = $data;
		return $this;
	}

	/**
	 * @param string|array $variable
	 * @return \Recipe_Template_Adapter_Standard
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
	 * @return \Recipe_Template_Adapter_Standard
	 */
	public function deallocateAllAssignment()
	{
		$this->_data = array();
		return $this;
	}

	/**
	 * @param $template string
	 * @param bool $noLayout
	 * @param bool|string $layout
	 * @return \Recipe_Template_Adapter_Standard
	 */
	function display($template, $noLayout = false, $layout = false)
	{
		$this->templateBuffer = null;
		Hook::event("TemplatePreDisplay", array($this, $template, $noLayout, $layout));
		if($noLayout)
		{
			$outStream = $this->render($template, self::TEMPLATE_TYPE_VIEWS);
		}
		else
		{
			$this->templateBuffer = $template;
			if($layout === false)
			{
				$layout = $this->getLayoutTemplate();
			}
			$outStream = $this->render($layout, self::TEMPLATE_TYPE_LAYOUTS);
		}
		$this->sendHeader();
		Hook::event("TemplateDisplay", array($this, &$outStream));
		echo $outStream;
		Hook::event("TemplatePostDisplay");
		return $this;
	}

	/**
	 * @param string $template
	 * @param string $type
	 * @return string
	 */
	public function render($template, $type)
	{
		Hook::event("TemplateRenderBegin", array($this, $template));
		$templatePath = $this->getTemplatePath($template, $type);
		$view = $this->getView();
		if($this->templateBuffer !== null)
		{
			$template = $this->templateBuffer;
		}
		ob_start();
		require_once($templatePath);
		$outStream = ob_get_contents();
		ob_end_clean();
		Hook::event("TemplateRenderEnd", array($this, $outStream, $templatePath));
		return $outStream;
	}

	/**
	 * @param string $message
	 * @return \Recipe_Template_Adapter_Standard
	 */
	public function addLogMessage($message)
	{
		$this->_data["LOG"] .= $message;
		return $this;
	}

	/**
	 * @param string $file
	 * @param string $type
	 * @return \Recipe_Template_Adapter_Standard
	 */
	public function addHTMLHeaderFile($file, $type = "js")
	{
		$this->_data["html_header"][strtolower($type)][] = $file;
		return $this;
	}

	/**
	 * @param string $type
	 * @return \Recipe_Template_Adapter_Standard
	 */
	public function clearHTMLHeaderFiles($type = null)
	{
		if($type === null)
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
	 * @param string $var
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($var, $default = null)
	{
		return ($this->exists($var)) ? $this->_data[$var] : $default;
	}

	/**
	 * @param string $var
	 * @return bool
	 */
	public function exists($var)
	{
		return (isset($this->_data[$var]));
	}

	/**
	 * @param string $loop
	 * @return array
	 */
	public function getLoop($loop)
	{
		return ($this->exists($loop) && (is_array($this->_data[$loop]) || $this->_data[$loop] instanceof Traversable)) ? $this->_data[$loop] : array();
	}

	/**
	 * @param object $view
	 * @return \Recipe_Template_Adapter_Standard
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
	 * @return object
	 */
	public function getView()
	{
		return $this->get("__view");
	}
}
?>