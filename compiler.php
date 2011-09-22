<?php
/**
 * Compiler for performance optimization.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: compiler.php 8 2010-10-17 20:55:04Z secretchampion $
 */

define("INGAME", false);
define("EXEC_CRON", false);
define("LOGIN_REQUIRED", false);
define("MOD_REWRITE", true);
define("REQUEST_LEVEL_NAMES", "lang,controller,action,1,2,3,4");

require_once("./global.inc.php");

$files = array(
	"./global.inc.php",
	"lib/init.php",
	"lib/functions.php",
	"app/code/Functions.inc.php",
);

$classes = array(
	"AutoLoader",
	"Plugin/Abstract",
	"Plugin/Commercials",
	"Hook",
	"Plugin/GoogleAnalytics",

	"Recipe/Timer",
	"Recipe/Database/MySQLi",
	"Recipe/Database/Abstract",
	"Recipe/Request",
	"Recipe/Request/Default",
	"Recipe/Request/Adapter",
	"Recipe/QueryParser",
	"Recipe/Cache",
	"Recipe/Collection",
	"Recipe/Options",
	"Recipe/User",
	"Recipe/Language",
	"Recipe/Cron",
	"Recipe/Template/Adapter/Default",
	"Recipe/Template/Adapter/Abstract",
	"Recipe/Database/Table",
	"Recipe/Database/Select",
	"Recipe/Database/Expr",
	"Recipe/Header",

	"Core",

	"Util/Str.util",
	"Util/XML.util",
	"Util/XMLObj.util",
	"Util/Arr.util",
	"Util/Map.util",
	"Util/Type.util",
	"Util/Image.util",
	"Util/Link.util",
	"Util/Date.util",

	"Object",
	"Recipe/Model/Abstract",
	"Recipe/Model/Resource/Abstract",
	"Recipe/Model/Collection/Abstract",
	"Recipe/Controller/Abstract",

	"Bengine/EventHandler",
	"Bengine/Model/Event",
	"Bengine/Planet",
	"Bengine/Model/Collection/Event",
	"Bengine/Model/Resource/Event",
	"Bengine/Menu",
	"Bengine/Page/Abstract",

	"Application",
	"Bengine",
);

$includePath = explode(PATH_SEPARATOR, get_include_path());
$numInc = count($includePath);

function compress($file, $removeIncudes = false)
{
	$out = '';
	foreach(token_get_all(file_get_contents($file)) as $x => $token)
	{
		if(is_string($token))
		{
			$out .= $token;
		}
		else
		{
			switch($token[0])
			{
				case T_INCLUDE_ONCE:
				case T_COMMENT:
				case T_DOC_COMMENT:
				case T_OPEN_TAG:
				case T_CLOSE_TAG:
				break;
				case T_WHITESPACE:
					$out .= ' ';
				break;
				default:
					$out .= $token[1];
				break;
			}
		}
	}
	return str_replace('  ', ' ', $out);
}

$compiled = "";
$compiledFiles = 0;
$compiledClasses = 0;

foreach($files as $_file)
{
	$compiled .= compress($_file);
	$compiledFiles++;
}

foreach($classes as $class)
{
	foreach($includePath as $n => $path)
	{
		$file = $path.$class.'.php';
		if(file_exists($file))
		{
			$compiled .= compress($file);
			$compiledClasses++;
			break;
		}
		if($n +1 == $numInc)
		{
			trigger_error("Class {$class} not found.", E_USER_WARNING);
		}
	}
}
$strBefore = 'define("INGAME",true);define("EXEC_CRON",true);define("LOGIN_REQUIRED",true);define("MOD_REWRITE",false);define("REQUEST_LEVEL_NAMES","sid,controller,action,1,2,3,4,5");';
$strAfter = 'Bengine::run();';
file_put_contents('compiled.php', '<?php '.$strBefore.$compiled.$strAfter.' ?>');


echo $compiledFiles." files compiled of ".count($files)." total.<br/>";
echo $compiledClasses." classes compiled of ".count($classes)." total.<br/>";