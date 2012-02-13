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
	 * @var Map
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
	 * Assigns a variable.
	 *
	 * @param string|array $variable
	 * @param mixed $value
	 * @return \Recipe_Template_Adapter_Default
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
	 * Adds a loop.
	 *
	 * @param string $loop
	 * @param array $data
	 * @return \Recipe_Template_Adapter_Default
	 */
	public function addLoop($loop, $data)
	{
		$this->loopStack[$loop] = $data;
		return $this;
	}

	/**
	 * Removes an assignment.
	 *
	 * @param string|array $variable
	 * @return \Recipe_Template_Adapter_Default
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
	 * Removes all assignments.
	 *
	 * @return \Recipe_Template_Adapter_Default
	 */
	public function deallocateAllAssignment()
	{
		$this->templateVars = array();
		return $this;
	}

	/**
	 * @param string $template
	 * @param bool $noLayout
	 * @param string $layout
	 * @return Recipe_Template_Adapter_Default
	 */
	public function display($template, $noLayout = false, $layout = null)
	{
		$this->templateBuffer = null;
		if($this->log->size() > 0)
		{
			$this->assign("LOG", $this->log->toString("\n"));
		}
		if(!empty($this->htmlHead))
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
		Hook::event("TemplatePreDisplay", array($this, $template, $noLayout, $layout));
		if($noLayout)
		{
			$outStream = $this->render($template, self::TEMPLATE_TYPE_VIEWS);
		}
		else
		{
			$this->templateBuffer = $template;
			if($layout === null)
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
	 * Renders a template.
	 *
	 * @param string $template
	 * @param string $type
	 * @return string
	 */
	public function render($template, $type)
	{
		Hook::event("TemplateRenderBegin", array($this, $template));
		if($this->forceCompilation || !$this->cachedTemplateAvailable($template, $type))
		{
			new Recipe_Template_Default_Compiler($template, $type);
		}
		$templatePath = Core::getCache()->getTemplatePath($template, $type);
		$view = $this->getView();
		if($this->templateBuffer !== null)
		{
			$template = $this->templateBuffer;
		}
		ob_start();
		require $templatePath;
		$outStream = ob_get_contents();
		ob_end_clean();
		Hook::event("TemplateRenderEnd", array($this, $outStream, $templatePath));
		return $outStream;
	}

	/**
	 * Add log message.
	 *
	 * @param string $message
	 * @return \Recipe_Template_Adapter_Default
	 */
	public function addLogMessage($message)
	{
		$this->log->push($message);
		return $this;
	}

	/**
	 * Add html header file.
	 *
	 * @param string $file
	 * @param string $type
	 * @return \Recipe_Template_Adapter_Default
	 */
	public function addHTMLHeaderFile($file, $type = "js")
	{
		$this->htmlHead[strtolower($type)][] = $file;
		return $this;
	}

	/**
	 * Clear html header files.
	 *
	 * @param string $type
	 * @return \Recipe_Template_Adapter_Default
	 */
	public function clearHTMLHeaderFiles($type = null)
	{
		if($type === null)
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
	 * Getter for assignments.
	 *
	 * @param string $var
	 * @param string $default
	 * @return mixed
	 */
	public function get($var, $default = null)
	{
		$default = ($default == "[var]") ? $var : $default;
		return ($this->exists($var)) ? $this->templateVars[$var] : $default;
	}

	/**
	 * Check for existence in assignments.
	 *
	 * @param string $var
	 * @return bool
	 */
	public function exists($var)
	{
		return isset($this->templateVars[$var]);
	}

	/**
	 * @param string $loop
	 * @return array
	 */
	public function getLoop($loop)
	{
		return (isset($this->loopStack[$loop])) ? $this->loopStack[$loop] : array();
	}

	/**
	 * @param object $view
	 * @return \Recipe_Template_Adapter_Default
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
	 * @return object
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 * Checks if template needs to be compiled.
	 *
	 * @param string $template	Template name
	 * @param string $type
	 *
	 * @return boolean
	 */
	protected function cachedTemplateAvailable($template, $type)
	{
		$cached = Core::getCache()->getTemplatePath($template, $type);
		$template = $this->getTemplatePath($template, $type);
		if(!file_exists($template))
		{
			throw new Recipe_Exception_Generic($template." does not exist.");
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

	/**
	 * Set force compilation.
	 *
	 * @param bool $forceCompilation
	 * @return Recipe_Template_Adapter_Default
	 */
	public function setForceCompilation($forceCompilation = true)
	{
		$this->forceCompilation = (bool) $forceCompilation;
		return $this;
	}
}
?>