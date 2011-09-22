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
	protected $mainTemplateFile = "";

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
	 * Constructor.
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function __construct()
	{
		$this->setTemplatePath(APP_ROOT_DIR."app/templates/");
		$this->setTemplatePackage();
		$this->setLayoutTemplate();
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
	 * @param string|array	The template variable names
	 * @param mixed		The value to assign
	 *
	 * @return Recipe_Template_Adapter
	 */
	abstract public function assign($variable, $value = null);

	/**
	 * Append a loop to loop stack.
	 *
	 * @param string	Loop name
	 * @param array		Loop data
	 *
	 * @return Recipe_Template_Adapter
	 */
	abstract public function addLoop($loop, $data);

	/**
	 * Executes and displays the template results.
	 *
	 * @param string	The to displayed template
	 * @param boolean	For AJAX requests
	 * @param string	Custome main template
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function display($template, $sendOnlyContent = false, $mainTemplate = null);

	/**
	 * Sets the view class.
	 *
	 * @param object
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
	 * @param string	Message to add
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function addLogMessage($message);

	/**
	 * Adds an HTML header file to layout template.
	 *
	 * @param string	File to include
	 * @param string	File type [optional]
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function addHTMLHeaderFile($file, $type = "js");

	/**
	 * Cleans all HTML header files.
	 *
	 * @param string	File type [optional]
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function clearHTMLHeaderFiles($type = null);

	/**
	 * Returns an assigned template variable.
	 *
	 * @param string	Variable name
	 * @param mixed		Default value to return
	 *
	 * @return mixed
	 */
	abstract public function get($var, $default = null);

	/**
	 * Checks a template assignment for existance.
	 *
	 * @param string	Variable name
	 *
	 * @return boolean
	 */
	abstract public function exists($var);

	/**
	 * Returns an assigned loop.
	 *
	 * @param string	Loop index
	 *
	 * @return mixed
	 */
	abstract public function getLoop($loop);

	/**
	 * Includes a template.
	 *
	 * @param string	Template name
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function includeTemplate($template);

	/**
	 * Clears assignment of all template variables.
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function deallocateAllAssignment();

	/**
	 * Clears the given assigned template variable.
	 *
	 * @param string	The template variable to flush
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	abstract public function deallocateAssignment($variable);

	/**
	 * Return full path of a template.
	 *
	 * @param string	Template name
	 *
	 * @return string	Path to template
	 */
	protected function getTemplatePath($template)
	{
		$path = $this->templatePath.$this->getTemplatePackage().$template.$this->templateExtension;
		// If the template does not exist, try the standard package
		if(!file_exists($path))
		{
			$path = $this->templatePath."standard/".$template.$this->templateExtension;
		}
		return $path;
	}

	/**
	 * Sets a new template path.
	 *
	 * @param string	Path
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
	 * @param string	Template name
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function setLayoutTemplate($template = null)
	{
		if(is_null($template))
		{
			$template = Core::getConfig()->maintemplate;
		}
		$this->mainTemplateFile = $template;
		return $this;
	}

	/**
	 * Sets a new template file extension.
	 *
	 * @param string
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function setExtension($extension = null)
	{
		if(is_null($extension))
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
	 * @param string	Package direcotry
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function setTemplatePackage($package = null)
	{
		if(is_null($package))
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
		return "standard/";
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
			if(@extension_loaded('zlib') && !$this->compressed && GZIP_ACITVATED)
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
	 * @param string	Variable name
	 * @param mixed		Value
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function __set($var, $value)
	{
		$this->assign($var, $value);
		return $this;
	}

	/**
	 * Returns incaccessible member variables as template assignments.
	 *
	 * @param string	Variable name
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
	 * @param string	Variable name
	 *
	 * @return boolean
	 */
	public function __isset($var)
	{
		return $this->exists($var);
	}

	/**
	 * Unsets inaccessbile member variables.
	 *
	 * @param string	Variable name
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function __unset($var)
	{
		return $this->deallocateAssignment($var);
	}
}
?>