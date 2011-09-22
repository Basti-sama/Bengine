<?php
/**
 * Exception for SQL errors.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 */

class Recipe_Exception_Sql extends Recipe_Exception_Generic
{
	/**
	 * Creates a new SQL exception.
	 *
	 * @param string	Error message
	 * @param integer	Error number
	 * @param string	SQL query code
	 *
	 * @return void
	 */
	public function __construct($message, $code, $sql)
	{
		$message = "SQL Error ({$code}): {$message}<br/><br/>Query Code: {$sql}";
		return parent::__construct($message, $code);
	}
}
?>