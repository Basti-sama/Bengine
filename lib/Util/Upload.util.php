<?php
/**
 * This class is used for a file upload.
 * Note: Perform always the check()-method before upload.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Upload.util.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Upload
{
	/**
	 * Path to upload.
	 *
	 * @var string
	 */
	protected $path = "";

	/**
	 * File to upload.
	 *
	 * @var array
	 */
	protected $file = array();

	/**
	 * Filename.
	 *
	 * @var string
	 */
	protected $filename = "";

	/**
	 * Allowed file extensions.
	 *
	 * @var Map
	 */
	protected $allowedExtensions = null;

	/**
	 * Maximum file size.
	 *
	 * @var integer
	 */
	protected $maxSize = 0;

	/**
	 * Whether upload can be performed.
	 *
	 * @var boolean
	 */
	protected $canUpload = true;

	/**
	 * Extension of filename.
	 *
	 * @var string
	 */
	protected $extension = "";

	/**
	 * Tells how to handle double file names.
	 *
	 * @var integer
	 */
	protected $uploadMode = 0;

	/**
	 * Constructor.
	 *
	 * @param string	Path to upload
	 * @param string	File to upload
	 *
	 * @return void
	 */
	public function __construct($path, $file)
	{
		$this->path = $this->validatePath($path);
		$this->file = is_array($file) ? $file : Core::getRequest()->getFILES($file);
		$this->filename = $this->file["name"];
		$this->setExtension($this->filename);
		Hook::event("StartFileUpload", array($this));
		return;
	}

	/**
	 * Check path, existing files, extensions and size for incoming file.
	 *
	 * @param boolean	Disable exceptions (default: true) [optional]
	 *
	 * @return Upload
	 */
	public function check($throwExceptions = true)
	{
		if(!$this->file || !isset($this->file) || !$this->filename || $this->filename == "" || strlen($this->filename) == 0)
		{
			$this->canUpload = false;
			if($throwExceptions) { throw new Recipe_Exception_Issue("NO_FILE_CHOSEN"); }
			return $this;
		}

		if(!is_dir($this->path))
		{
			$this->canUpload = false;
			if($throwExceptions) { throw new Recipe_Exception_Issue("PATH_DOES_NOT_EXIST"); }
			return $this;
		}
		if($this->validateFileName())
		{
			$this->canUpload = false;
			if($throwExceptions) { throw new Recipe_Exception_Issue("FILE_ALREADY_EXISTS"); }
			return $this;
		}

		if($allowedExtensions = $this->getAllowedFileExtensions())
		{
			if(!preg_match("#(".$allowedExtensions->toString("|").")$#i", $this->extension))
			{
				$this->canUpload = false;
				if($throwExceptions) { throw new Recipe_Exception_Issue("INVALID_FILE_EXTENSION"); }
				return $this;
			}
		}

		if($this->getFileSize() >= $this->getMaxSize())
		{
			$this->canUpload = false;
			if($throwExceptions) { throw new Recipe_Exception_Issue("FILE_SIZE_TOO_LARGE"); }
			return $this;
		}
		Hook::event("CheckFileBeforeUpload", array($this));
		return $this;
	}

	/**
	 * Checks for double file names.
	 *
	 * @return boolean
	 */
	protected function validateFileName()
	{
		$checkExistence = false;
		$unique = false;
		switch($this->uploadMode)
		{
			case 0:
				if(file_exists($this->path.$this->filename))
				{
					return false;
				}
				return true;
			break;
			case 1:
				return true;
			break;
			case 2:
				$checkExistence = true;
			break;
			case 3:
			default:
				$unique = true;
			break;
		}

		if($unique || ($checkExistence && file_exists($this->path.$this->filename)))
		{
			$this->addStringToFilename(md5(strval(TIME)));
		}
		return true;
	}

	/**
	 * Adds a specified string into filename.
	 *
	 * @param string	String to add
	 *
	 * @return Upload
	 */
	public function addStringToFilename($string)
	{
		if(Str::length($string) > 0)
		{
			$pos = strrpos($this->filename, ".");
			$this->filename = Str::substring($this->filename, 0, $pos).$string.".".$this->extension;
			Hook::event("AddStringToFilename", array($this));
		}
		return $this;
	}

	/**
	 * Upload file to server.
	 *
	 * @return Upload
	 */
	public function doUpload()
	{
		if($this->canUpload && ini_get("file_uploads"))
		{
			if(!@move_uploaded_file($this->file["tmp_name"], $this->path.$this->filename))
			{
				throw new Recipe_Exception_Generic("Error with uploading file '".$this->filename."'.");
			}
			Hook::event("PerformFileUpload", array($this));
		}
		return $this;
	}

	/**
	 * Convert a shorthand byte value from a PHP configuration directive to an integer value.
	 *
	 * @return integer	Byte value
	 */
	protected function getPHPMaxUploadSize()
	{
		$ini_get = ini_get("upload_max_filesize");
		if(is_numeric($ini_get)) { return $ini_get; }
		$value_length = strlen($ini_get);
		$qty = (int) Str::substring($ini_get, 0, $value_length - 1);
		$unit = strtolower(Str::substring($ini_get, $value_length - 1));
		switch($unit)
		{
			case "k":
				$qty *= 1024;
			break;
			case "m":
				$qty *= 1048576;
			break;
			case "g":
				$qty *= 1073741824;
			break;
		}
		return $qty;
	}

	/**
	 * Validate path depending on slashes and absolute path.
	 *
	 * @param string	Path name
	 *
	 * @return string	Validated path name
	 */
	protected function validatePath($path)
	{
		$pattern = "/^".str_replace("/", "\/", APP_ROOT_DIR)."/";
		if(preg_match($pattern, $path) == 0)
		{
			$path = APP_ROOT_DIR.$path;
		}
		if($path{Str::length($path) - 1} == "/" || $path{Str::length($path) - 1} == "\\")
		{
			return $path;
		}
		else
		{
			$path .= "/";
		}
		return $path;
	}

	/**
	 * Filters extensions from file name and store it.
	 *
	 * @param string	File name
	 *
	 * @return Upload
	 */
	protected function setExtension($filename)
	{
		$this->extension = File::getFileExtension($filename);
		return $this;
	}

	/**
	 * Sets the allowed file extensions during upload check.
	 *
	 * @param mixed		The extensions
	 *
	 * @return Upload
	 */
	public function setAllowedFileExtensions($extensions)
	{
		if($extensions instanceof Map)
		{
			$this->allowedExtensions = $extensions;
		}
		else if(is_string($extensions))
		{
			$extensions = explode(",", $extensions);
			$this->allowedExtensions = new Map($extensions);
		}
		else if(is_array($extensions))
		{
			$this->allowedExtensions = new Map($extensions);
		}
		else
		{
			throw new Recipe_Exception_Generic("The supplied extensions must be an array, string or map.");
		}
		return $this;
	}

	/**
	 * Returns the allowed file extensions.
	 *
	 * @return mixed	The extension map or false
	 */
	protected function getAllowedFileExtensions()
	{
		if(!is_null($this->allowedExtensions) && $this->allowedExtensions instanceof Map && $this->allowedExtensions->size() > 0)
		{
			return $this->allowedExtensions->trim();
		}
		return false;
	}

	/**
	 * Sets the max size for file uploads.
	 *
	 * @param integer	Max size
	 *
	 * @return Upload
	 */
	public function setMaxSize($maxSize)
	{
		$this->maxSize = $maxSize;
		return $this;
	}

	/**
	 * Returns the max size for file uploads.
	 *
	 * @param boolean	Formatting size (default: false) [optional]
	 *
	 * @return integer
	 */
	public function getMaxSize($format = false)
	{
		if(!is_numeric($this->maxSize) || $this->maxSize == 0)
		{
			$this->maxSize = $this->getPHPMaxUploadSize();
		}
		return ($format) ? File::bytesToString($this->maxSize) : $this->maxSize;
	}

	/**
	 * Returns the file name.
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return $this->filename;
	}

	/**
	 * Sets the file name.
	 *
	 * @param string
	 *
	 * @return Upload
	 */
	public function setFileName($filename)
	{
		$this->filename = $filename;
		$this->setExtension($filename);
		return $this;
	}

	/**
	 * Returns file size.
	 *
	 * @param boolean	Formatting size (default: false) [optional]
	 *
	 * @return integer	File size
	 */
	public function getFileSize($format = false)
	{
		return ($format) ? File::bytesToString($this->file["size"]) : $this->file["size"];
	}

	/**
	 * Tells how to handle double uploaded file names.
	 * 0: Cancle upload and trigger error
	 * 1: Overwrite
	 * 2: Set name unique when file exists, otherwise no changes
	 * 3: Set ALWAYS unique file names
	 *
	 * @param integer
	 *
	 * @return Upload
	 */
	public function setUploadMode($uploadMode)
	{
		$this->uploadMode = $uploadMode;
		return $this;
	}

	/**
	 * Destructor.
	 *
	 * @return void
	 */
	public function kill()
	{
		unset($this);
		return;
	}
}
?>