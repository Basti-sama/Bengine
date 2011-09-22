<?php
/**
 * Exception class. For error handling.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 */

class Recipe_Exception_Generic extends Exception implements Recipe_Exception_Global
{
	/**
	 * The stack trace
	 *
	 * @var array
	 */
	protected $_trace = array();

	/**
	 * Prints this error.
	 *
	 * @return Recipe_Exception_Generic
	 */
	public function printError()
	{
		if(LOG_EXCEPTIONS)
		{
			ob_start();
			$file = randString(8);
			require_once(AD."app/templates/error.phtml");
			$report = ob_get_contents();
			$path = AD."var/reports/exception_".$file.".html";
			file_put_contents($path, $report);
			chmod($path, 0766);
			exit;
			ob_end_flush();
		}
		require_once(AD."app/templates/error.phtml");
		exit;
		return $this;
	}

	/**
	 * Returns the error message.
	 *
	 * @return string	The error description
	 */
	public function getErrorMessage()
	{
		return $this->message;
	}

	/**
	 * Sets the line.
	 *
	 * @param integer $line
	 *
	 * @return Install_Exception
	 */
	public function setLine($line)
	{
		$this->line = $line;
		return $this;
	}

	/**
	 * Sets the file.
	 *
	 * @param string $file
	 *
	 * @return Install_Exception
	 */
	public function setFile($file)
	{
		$this->file = $file;
		return $this;
	}

	/**
	 * Sets the debug backtrace.
	 *
	 * @param array $trace
	 *
	 * @return Install_Exception
	 */
	public function setTrace(array $trace)
	{
		$this->_trace = $trace;
		return $this;
	}

	/**
	 * Gets the stack trace.
	 *
	 * @return array
	 */
	public function getStackTrace()
	{
		return (!empty($this->_trace)) ? $this->_trace : $this->getTrace();
	}
}
?>