<?php
// Required constants (DO NOT MODIFY!)
define("MICROTIME", microtime());
define("TIME", time());

// Required constants (Just for advanced)
if(!defined("__DIR__")) define("__DIR__", dirname(__FILE__));
define("APP_ROOT_DIR", str_replace("\\", "/", __DIR__)."/");
define("RECIPE_ROOT_DIR", APP_ROOT_DIR."lib/");
define("HTTP_HOST", "http://".$_SERVER["SERVER_NAME"]."/");
define("REQUEST_DIR", (strlen(dirname($_SERVER["SCRIPT_NAME"])) > 1) ? substr(dirname($_SERVER["SCRIPT_NAME"]), 1)."/" : "");
define("BASE_URL", HTTP_HOST.REQUEST_DIR);
define("IPADDRESS", !empty($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"]);
define("ERROR_REPORTING_TYPE", E_ALL | E_STRICT);
define("AUTOLOAD_PATH", "lib,app/code,ext/plugins,ext/modules");
define("LOG_EXCEPTIONS", false);
define("REQUEST_ADAPTER", "default");
define("DEFAULT_PACKAGE", "comm");
define("MOD_REWRITE", true);
define("CHARACTER_SET", "utf-8");

// Required constants (Global preferences)
define("COOKIE_PREFIX", "bengine_");	// Prefix for cookies.
define("CACHE_ACTIVE", true); 		// Global switch to enable/disable cache funcion.
define("GZIP_ACITVATED", true);		// Enables GZIP compression.
define("IPCHECK", true);			// Enables IP check for sessions.

// Short constants
define("RD", RECIPE_ROOT_DIR);
define("BU", BASE_URL);
define("AD", APP_ROOT_DIR);

// Starts programm
require_once(RECIPE_ROOT_DIR."init.php");
?>