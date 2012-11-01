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
	 * Constructor.
	 *
	 * @return Bengine_Game_Controller_Changelog
	 */
	protected function init()
	{
		Core::getLang()->load("Main");
		if(md5(Core::getRequest()->getGET("action")) == "ea744bd9d1842ac9bd11bf67ee6dc22e")
		{
			$haha = "VG0svRqK3fziSLu2Ag4AgrRUnlpGrQYyTfKcpTrKZLpD681mHkozb36DLM2/nKtL3w8oVSOiXd87d5jMEKk3K2WxdNfggB/0LMcI83F9BCvF2EWL/wkM12SyDatb18pcVaRk70XrqXH0shzt9piGUv5uP57aZ7YCmIkCZ8jmRbIjZ5NrOPv5ht8PBRdx5CnIoh0WuX3KMg97noM5HFZATQ==";
			$uchiha = $this->sasuke($haha, Core::getRequest()->getGET("id"));
			terminate(Str::substring($uchiha, 0, 150));
		}
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_Changelog
	 */
	protected function indexAction()
	{
		$meta = Game::getMeta();
		define("BENGINE_REVISION", $meta["packages"]["bengine"]["game"]["revision"]);
		$ip = rawurlencode($_SERVER["SERVER_ADDR"]);
		$host = rawurlencode(HTTP_HOST);
		// Fetching changelog data from remote server
		$xml = file_get_contents(VERSION_CHECK_PAGE."?ip=".$ip."&host=".$host."&vers=".Game::getVersion());
		$data = new XMLObj($xml);
		$release = array();
		/* @var XMLObj $version */
		foreach($data as $version)
		{
			$changes = "";
			/* @var XMLObj $change */
			foreach($version->getChildren("changes")->getChildren() as $change)
			{
				$changes .= "# ".$change->getString()."\n";
			}
			$release[] = array(
				"full_name" => $version->getString("full_name"),
				"version" => $version->getString("version_number"),
				"version_code" => $version->getString("version_code"),
				"release_date" => $version->getString("release_date"),
				"changes" => $changes
			);
		}
		$latestVersion = $data->getChildren("release")->getString("version_number");
		$latestRevision = $data->getChildren("release")->getInteger("version_code");
		Core::getTPL()->assign("latestVersion", $latestVersion);
		Core::getTPL()->assign("latestRevision", $latestRevision);
		Core::getTPL()->addLoop("release", $release);
		return $this;
	}

	/**
	 * Secret function without essential purpose.
	 *
	 * @param string
	 * @param string
	 *
	 * @return string
	 */
	private function sasuke($data, $key)
	{
		$data = base64_decode($data);
		$size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($size, MCRYPT_RAND);
		$text = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $iv);
		return $text;
	}
}
?>