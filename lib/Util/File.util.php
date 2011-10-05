<?php
/**
 * All file system related functions.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: File.util.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class File
{
	/**
	 * Fetch the extension of a file name.
	 *
	 * @param string	The file name.
	 *
	 * @return string 	The file extension.
	 */
	public static function getFileExtension($filename)
	{
		return strtolower(Str::substring(strrchr($filename, "."), 1));
	}

	/**
	 * Deletes a file.
	 *
	 * @param string	Path + file name
	 *
	 * @return boolean	True on success or false on failure
	 */
	public static function rmFile($file)
	{
		if($file instanceof SplFileInfo)
		{
			$file = $file->getPathname();
		}
		if(is_dir($file))
		{
			return self::rmDirectory($file);
		}
		if(file_exists($file))
		{
			if(!@unlink($file))
			{
				throw new Recipe_Exception_Generic("Cannot delete file \"".$file."\".");
				return false;
			}
		}
		else
		{
			throw new Recipe_Exception_Generic("Cannot delete a non-existing file (\"".$file."\").");
			return false;
		}
		return true;
	}

	/**
	 * Deletes a complete direcotory including its contents.
	 *
	 * @param string	Path
	 *
	 * @return boolean	True on success or false on failure
	 */
	public static function rmDirectory($dir)
	{
		if(is_dir($dir))
		{
			if(self::rmDirectoryContent($dir))
			{
				@rmdir($dir);
				return true;
			}
		}
		return false;
	}

	/**
	 * Deletes the complete directory content.
	 *
	 * @param string	Directory path
	 *
	 * @return boolean	True on success or false on failure
	 */
	public static function rmDirectoryContent($dir)
	{
		if(is_dir($dir))
		{
			$openDir = self::getRecursiveDirectoryIterator($dir);
			foreach($openDir as $file)
			{
				if($file->isWritable() && !$file->isDot())
				{
					if($file->isDir())
					{
						@rmdir($file->getPathname());
					}
					else if($file->isFile())
					{
						self::rmFile($file->getPathname());
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Moves the complete direcotory content into another directory.
	 *
	 * @param string	Folder to move
	 * @param string	Destination folder
	 *
	 * @return boolean	True on success or false on failure
	 */
	public static function mvDirectoryContent($from, $to)
	{
		if(is_dir($from))
		{
			if(!is_dir($to)) { @mkdir($to); }
			$from = (substr($from, -1) != "/") ? $from."/" : $from;
			$to = (substr($to, -1) != "/") ? $to."/" : $to;

			$openDir = new DirectoryIterator($from);
			foreach($openDir as $file)
			{
				if(!$file->isDot() && $file->isWritable())
				{
					if($file->isDir())
					{
						self::mvDirectory($file->getPathname(), $to.$file->getFilename());
					}
					else if($file->isFile() && $file->isWritable())
					{
						copy($file->getPathname(), $to.$file->getFilename());
						self::rmFile($file->getPathname());
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Copies the complete direcotory content into another directory.
	 *
	 * @param string	Folder to copy
	 * @param string	Destination folder
	 *
	 * @return boolean	True on success or false on failure
	 */
	public static function cpDirectoryContent($from, $to)
	{
		if(is_dir($from))
		{
			if(!is_dir($to)) { @mkdir($to); }
			$from = (substr($from, -1) != "/") ? $from."/" : $from;
			$to = (substr($to, -1) != "/") ? $to."/" : $to;

			$openDir = new DirectoryIterator($from);
			foreach($openDir as $file)
			{
				if(!$file->isDot() && $file->isReadable())
				{
					if($file->isDir())
					{
						self::cpDirectoryContent($file->getPathname(), $to.$file->getFilename());
					}
					else if($file->isFile() && $file->isWritable())
					{
						copy($file->getPathname(), $to.$file->getFilename());
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Makes a copy of the file source to destination.
	 *
	 * @param string	File to copy
	 * @param string	Destination path
	 *
	 * @return boolean	True on success or false on failure
	 */
	public static function cpFile($file, $dest)
	{
		if(is_dir($file))
		{
			return self::cpDirectoryContent($file, $dest);
		}
		if(!file_exists($file))
		{
			throw new Recipe_Exception_Generic("Cannot copy a non-existing file (\"".$file."\").");
			return false;
		}
		if(!is_dir(dirname($dest)))
		{
			throw new Recipe_Exception_Generic("Copy destination is not writable (\"".$file."\").");
			return false;
		}
		if(!copy($file, $dest))
		{
			throw new Recipe_Exception_Generic("Unable to copy \"".$file."\" to \"".$dest."\".");
			return false;
		}
		return true;
	}

	/**
	 * Returns file size.
	 *
	 * @param string	Path to file
	 * @param boolean	Format size or return raw byte value?
	 *
	 * @return mixed	File size
	 */
	public static function getFileSize($file, $format = true)
	{
		$size = (is_dir($file)) ? self::getDirectorySize($file, false) : filesize($file);
		return ($format) ? self::bytesToString($size) : $size;
	}

	/**
	 * Returns directory size.
	 *
	 * @param string	Path to directory
	 * @param boolean	Format size or return raw byte value?
	 *
	 * @return mixed	Directory size
	 */
	public static function getDirectorySize($dir, $format = true)
	{
		$size = 0;
		$dir = (substr($dir, -1) != "/") ? $dir."/" : $dir;
		$handle = new DirectoryIterator($dir);
		foreach($handle as $file)
		{
			if(!$file->isDot())
			{
				$size += ($file->isDir()) ? self::getDirectorySize($file->getPathname()) : $file->getSize();
			}
		}
		return ($format) ? self::bytesToString($size) : $size;
	}

	/**
	 * Converts byte number into readable string.
	 *
	 * @param integer	Bytes to convert
	 *
	 * @return string
	 */
	public static function bytesToString($bytes)
	{
		if($bytes == 0) { return number_format($bytes, 2)." Byte"; }
		$s = array("Byte", "Kb", "MB", "GB", "TB", "PB");
		$e = floor(log($bytes)/log(1024));
		return sprintf("%.2f ".$s[$e], ($bytes/pow(1024, floor($e))));
	}

	/**
	 * Returns a complete recursive directory iterator.
	 *
	 * @param string	Path to direcotry
	 * @param integer	Access Mode (default: child first) [optional]
	 *
	 * @return RecursiveIteratorIterator
	 */
	public static function getRecursiveDirectoryIterator($dir, $mode = RecursiveIteratorIterator::CHILD_FIRST)
	{
		$dir = (substr($dir, -1) != "/") ? $dir."/" : $dir;
		return new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), $mode);
	}

	/**
	 * Recursive change mode funciton.
	 *
	 * @param string			File or directory path
	 * @param integer|string	Mode (default: 0777) [optional]
	 *
	 * @return boolean
	 */
	public static function chmod($path, $mode = 0777)
	{
		return self::recursiveDirectoryCallback("chmod", $path, $mode);
	}

	/**
	 * Recursive change group funciton.
	 *
	 * @param string	File or directory path
	 * @param string	Group name
	 *
	 * @return boolean
	 */
	public static function chgrp($path, $group)
	{
		return self::recursiveDirectoryCallback("chgrp", $path, $group);
	}

	/**
	 * Recursive change owner funciton.
	 *
	 * @param string	File or directory path
	 * @param string	User name
	 *
	 * @return boolean
	 */
	public static function chown($path, $user)
	{
		return self::recursiveDirectoryCallback("chown", $path, $user);
	}

	/**
	 * Executes a recursive callback function to a direcotry.
	 *
	 * @param callback		The callback function
	 * @param string		File or directory path
	 * @param mixed			Callback data
	 *
	 * @return boolean
	 */
	public static function recursiveDirectoryCallback($callback, $path, $data)
	{
		if(is_dir($path))
		{
			foreach(self::getRecursiveDirectoryIterator($path) as $file)
			{
				$callback($file->getPathname(), $data);
			}
		}
		else if(file_exists($path))
		{
			$callback($path, $data);
		}
		else
		{
			return false;
		}
		return true;
	}
}
?>