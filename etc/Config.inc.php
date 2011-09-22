<?php
/**
 * Database access data.
 * This file is auto-generated. It is recommended to not modify anything here.
 * @package Recipe 1.1
 * @author Sebastian Noll
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */


//### Database connection ###//
$database["host"] = "localhost";
$database["port"] = null;
$database["user"] = "root";
$database["userpw"] = "";
$database["databasename"] = "bengine";
$database["tableprefix"] = "bengine_";
$database["type"] = "MySQLi";

//### Combat system ###//
$cs["jre"] = "java"; // The path to java runtime environment
//$cs["jre"] = '"C:/Program Files/Java/jre6/bin/java.exe"'; // Win7 64bit
$cs["host"] = $database["host"];
$cs["user"] = $database["user"];
$cs["userpw"] = $database["userpw"];

?>