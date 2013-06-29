<?php
/**
 * MySQL pdo driver class.
 *
 * @package Recipe 1.3
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Recipe_Database_Pdo_MySQL extends Recipe_Database_Pdo_Abstract
{
	/**
	 * @var string
	 */
	protected $driver = "mysql";

	/**
	 * @var array
	 */
	protected $pdoConnectionOptions = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
	);
}