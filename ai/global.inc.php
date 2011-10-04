<?php
// Required constants (DO NOT MODIFY!)
define("MICROTIME", microtime());
define("TIME", time());

// Required constants (Just for advanced)
define("APP_ROOT_DIR", str_replace("\\", "/", dirname(dirname(__FILE__)))."/");
define("RECIPE_ROOT_DIR", APP_ROOT_DIR."lib/");
define("HTTP_HOST", "http://".$_SERVER["SERVER_NAME"]."/");
define("REQUEST_DIR", (strlen(dirname($_SERVER["SCRIPT_NAME"])) > 1) ? substr(dirname($_SERVER["SCRIPT_NAME"]), 1)."/" : "");
define("BASE_URL", HTTP_HOST.REQUEST_DIR);
define("IPADDRESS", $_SERVER["REMOTE_ADDR"]);
define("ERROR_REPORTING_TYPE", E_ALL &~ E_NOTICE);
define("DATABASE_SUBDOMAIN", false);
define("AUTOLOAD_PATH", "lib,ai/app,app/code");
define("REQUEST_LEVEL_NAMES", "controller,action,1,2,3,4,5");
define("MOD_REWRITE", true);
define("REQUEST_ADAPTER", "default");

// Required constants (Global preferences)
define("COOKIE_PREFIX", "bengine_");	// Prefix for cookies.
define("CACHE_ACTIVE", true); 		// Global switch to enable/disable cache funcion.
define("GZIP_ACITVATED", true);		// Enables GZIP compression.
define("COOKIE_SESSION", true);		// Session will be stored in cookies.
define("URL_SESSION", false);		// Session will be committed via URL.
define("IPCHECK", false);			// Enables IP check for sessions.
define("LOGIN_REQUIRED", false);
define("EXEC_CRON", false);
if(LOGIN_REQUIRED)
{
	define("LOGIN_URL", "auth"); // Where to redirect to log in.
	// Use define("LOGIN_PAGE", true); for this page before loading global.inc.php to avoid redirecting loop.
}

// Short constants
define("RD", RECIPE_ROOT_DIR);
define("BU", BASE_URL);
define("AD", APP_ROOT_DIR);
define("APP_BASE_URL", dirname(BU)."/");
define("RECIPE_AI_VERSION", "Pre-Alpha");

// Starts programm
require_once(RECIPE_ROOT_DIR."init.php");
?>