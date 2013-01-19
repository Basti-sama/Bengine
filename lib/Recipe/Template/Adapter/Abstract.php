<?php
/**
 * Abstract template adapter.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Abstract.php 30 2011-06-30 19:07:34Z secretchampion $
 */

abstract class Recipe_Template_Adapter_Abstract
{
	const TEMPLATE_TYPE_VIEWS = "views";
	const TEMPLATE_TYPE_LAYOUTS = "layouts";

	/**
	 * File Extension.
	 *
	 * @var string
	 */
	protected $templateExtension = "";

	/**
	 * Name of main template file.
	 *
	 * @var string
	 */
	protected $layoutTemplate = "";

	/**
	 * Absolute path to template directory.
	 *
	 * @var string
	 */
	protected $templatePath = "";

	/**
	 * Package directory.
	 *
	 * @var string
	 */
	protected $templatePackage = "";

	/**
	 * If out stream is compressed.
	 *
	 * @var boolean
	 */
	protected $compressed = false;

	/**
	 * Template buffer.
	 *
	 * @var string
	 */
	protected $templateBuffer = null;

	/**
	 * Constructor.
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function __construct()
	{
		$this->setTemplatePath(APP_ROOT_DIR."app/templates/");
		$this->setTemplatePackage();
		$this->setExtension();
		$this->init();
		return $this;
	}

	/**
	 * Initializing method.
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	protected function init()
	{
		return $this;
	}

	/**
	 * Setting up the template engine.
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	protected function _setup()
	{
		return $this;
	}

	/**
	 * Assigns values to template variables.
	 *
	 * @param string|array $variable	The template variable names
	 * @param mixed $value				The value to assign
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function assign($variable, $value = null);

	/**
	 * Append a loop to loop stack.
	 *
	 * @param string $loop	Loop name
	 * @param array|IteratorAggregate $data		Loop data
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function addLoop($loop, $data);

	/**
	 * Executes and displays the template results.
	 *
	 * @param string $template	The to displayed template
	 * @param boolean $noLayout	For AJAX requests
	 * @param string $layout	Custom main template
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function display($template, $noLayout = false, $layout = null);

	/**
	 * Sets the view class.
	 *
	 * @param object $view
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function setView($view);

	/**
	 * Returns the view class.
	 *
	 * @return object
	 */
	abstract public function getView();

	/**
	 * Adds a message to log.
	 *
	 * @param string $message	Message to add
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function addLogMessage($message);

	/**
	 * Adds an HTML header file to layout template.
	 *
	 * @param string $file	File to include
	 * @param string $type	File type [optional]
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function addHTMLHeaderFile($file, $type = "js");

	/**
	 * Cleans all HTML header files.
	 *
	 * @param string $type	File type [optional]
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function clearHTMLHeaderFiles($type = null);

	/**
	 * Returns an assigned template variable.
	 *
	 * @param string $var		Variable name
	 * @param mixed $default	Default value to return
	 *
	 * @return mixed
	 */
	abstract public function get($var, $default = null);

	/**
	 * Checks a template assignment for existance.
	 *
	 * @param string $var	Variable name
	 *
	 * @return boolean
	 */
	abstract public function exists($var);

	/**
	 * Returns an assigned loop.
	 *
	 * @param string $loop	Loop index
	 *
	 * @return mixed
	 */
	abstract public function getLoop($loop);

	/**
	 * Includes a template.
	 *
	 * @param string $template	Template name
	 * @param string $type
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function render($template, $type = self::TEMPLATE_TYPE_VIEWS);

	/**
	 * Clears assignment of all template variables.
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function deallocateAllAssignment();

	/**
	 * Clears the given assigned template variable.
	 *
	 * @param string $variable	The template variable to flush
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function deallocateAssignment($variable);

	/**
	 * Returns relative path of a template.
	 *
	 * @param string $template
	 * @param string $type
	 *
	 * @return string	Path to template
	 */
	public function getTemplatePath($template, $type)
	{
		$path = $this->getTemplatePackage().$type."/".$template.$this->templateExtension;
		// If the template does not exist, try the default package
		if(!file_exists($this->templatePath.$path))
		{
			return "default/".$type."/".$template.$this->templateExtension;
		}
		return $path;
	}

	/**
	 * Returns full path of a template.
	 *
	 * @param string $template
	 * @param string $type
	 * @return string	Path to template
	 */
	public function getAbsoluteTemplatePath($template, $type)
	{
		return $this->templatePath.$this->getTemplatePath($template, $type);
	}

	/**
	 * Sets a new template path.
	 *
	 * @param string $path	Path
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function setTemplatePath($path)
	{
		if(Str::substring($path, - 1) != "/")
		{
			$path .= "/";
		}
		$this->templatePath = $path;
		return $this;
	}

	/**
	 * Sets a new layout template.
	 *
	 * @param string $template	Template name
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function setLayoutTemplate($template = null)
	{
		$this->layoutTemplate = $template;
		return $this;
	}

	/**
	 * Returns the layout template.
	 *
	 * @return string
	 */
	public function getLayoutTemplate()
	{
		if(empty($this->layoutTemplate))
		{
			$this->layoutTemplate = Core::getConfig()->maintemplate;
		}
		return $this->layoutTemplate;
	}

	/**
	 * Sets a new template file extension.
	 *
	 * @param string $extension
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function setExtension($extension = null)
	{
		if($extension === null)
		{
			$extension = Core::getConfig()->templateextension;
		}
		if(Str::substring($extension, 0, 1) != ".")
		{
			$extension = ".".$extension;
		}
		$this->templateExtension = $extension;
		return $this;
	}

	/**
	 * Sets the template package.
	 *
	 * @param string $package	Package directory
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function setTemplatePackage($package = null)
	{
		if(empty($package))
		{
			$package = Core::getConfig()->templatepackage;
		}
		if(Str::substring($package, -1) != "/")
		{
			$package .= "/";
		}
		$this->templatePackage = $package;
		$this->_setup();
		return $this;
	}

	/**
	 * Returns the template package.
	 *
	 * @return string
	 */
	public function getTemplatePackage()
	{
		if(is_dir($this->templatePath.$this->templatePackage))
		{
			return $this->templatePackage;
		}
		return "local/";
	}

	/**
	 * Sends header information to client and compress output.
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function sendHeader()
	{
		if(!Recipe_Header::isSend())
		{
			if(extension_loaded("zlib") && strtolower(ini_get("zlib.output_compression")) == "off" && !$this->compressed && GZIP_ACITVATED)
			{
				ob_start("ob_gzhandler");
				$this->compressed = true;
			}
			Recipe_Header::add("Content-Type", "text/html; charset=".Core::getLanguage()->getOpt("charset"));
			Recipe_Header::send();
		}
		return $this;
	}

	/**
	 * Inaccessible member variables become an assigment.
	 *
	 * @param string $var	Variable name
	 * @param mixed $value	Value
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function __set($var, $value)
	{
		$this->assign($var, $value);
		return $this;
	}

	/**
	 * Returns inaccessible member variables as template assignments.
	 *
	 * @param string $var	Variable name
	 *
	 * @return mixed
	 */
	public function __get($var)
	{
		return $this->get($var);
	}

	/**
	 * Wrapper for exists().
	 *
	 * @param string $var	Variable name
	 *
	 * @return boolean
	 */
	public function __isset($var)
	{
		return $this->exists($var);
	}

	/**
	 * Unsets inaccessible member variables.
	 *
	 * @param string $var	Variable name
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function __unset($var)
	{
		return $this->deallocateAssignment($var);
	}
}
?>