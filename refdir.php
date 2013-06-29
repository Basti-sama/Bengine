<?php
/**
 * Assures anonym refering to hide session id.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: refdir.php 8 2010-10-17 20:55:04Z secretchampion $
 */

require_once("./global.inc.php");
new Core();

if($url = Core::getRequest()->getGET("url"))
{
	Core::getTPL()->assign("link", $url);
	Core::getTPL()->assign("charset", CHARACTER_SET);
	Core::getTPL()->addHTMLHeaderFile("style.css", "css");

	$pageTitle = array();
	if($titlePrefix = Core::getConfig()->get("TITLE_PREFIX"))
	{
		$pageTitle[] = $titlePrefix;
	}
	$pageTitle[] = Core::getConfig()->get("pagetitle");
	if($titleSuffix = Core::getConfig()->get("TITLE_SUFFIX"))
	{
		$pageTitle[] = $titleSuffix;
	}
	Core::getTPL()->assign("pageTitle", implode(Core::getConfig()->get("TITLE_GLUE"), $pageTitle));

	Core::getTPL()->display("externlink", true);
}
?>