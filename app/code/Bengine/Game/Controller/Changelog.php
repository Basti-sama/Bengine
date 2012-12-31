<?php
/**
 * Changelog and easter egg.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Changelog.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Controller_Changelog extends Bengine_Game_Controller_Abstract
{
	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_Changelog
	 */
	protected function indexAction()
	{
		Core::getLang()->load("Main");
		$meta = Game::getMeta();
		define("BENGINE_REVISION", $meta["packages"]["bengine"]["game"]["revision"]);
		$ip = rawurlencode($_SERVER["SERVER_ADDR"]);
		$host = rawurlencode(HTTP_HOST);
		// Fetching changelog data from remote server
		$json = file_get_contents(VERSION_CHECK_PAGE."?ip=".$ip."&host=".$host."&vers=".Game::getVersion());
		$data = json_decode($json, true);
		$latestVersion = $data["releases"][0]["versionNumber"];
		$latestRevision = $data["releases"][0]["versionCode"];
		Core::getTPL()->assign("latestVersion", $latestVersion);
		Core::getTPL()->assign("latestRevision", $latestRevision);
		Core::getTPL()->addLoop("releases", $data["releases"]);
		Core::getTPL()->assign("languageKey", Core::getLang()->getOpt("langcode"));
		return $this;
	}

	/**
	 * Secret action without essential purpose.
	 *
	 * @param string $key
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Game_Controller_Changelog
	 */
	protected function infoAction($key)
	{
		if(empty($key))
		{
			throw new Recipe_Exception_Generic("Please provide a key to view info.");
		}
		$content = file_get_contents('http://bengine.de/sasuke.php?action='.$key);
		if(empty($content))
		{
			throw new Recipe_Exception_Generic("Could not found any info on your key.");
		}
		Core::getTemplate()->assign("content", $content);
		return $this;
	}
}
?>