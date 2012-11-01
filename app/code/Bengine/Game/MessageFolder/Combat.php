<?php
/**
 * Combat report message folder.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Combat.php 46 2011-07-30 14:38:11Z secretchampion $
 */

class Bengine_Game_MessageFolder_Combat extends Bengine_Game_MessageFolder_Abstract
{
	/**
	 * Formats the message.
	 *
	 * @param Bengine_Game_Model_Message $message
	 *
	 * @return Bengine_Game_MessageFolder_Combat
	 */
	protected function _format(Bengine_Game_Model_Message $message)
	{
		if(!$message->get("sender"))
		{
			$message->set("sender", Core::getLanguage()->getItem("FLEET_COMMAND"));
		}
		$assault = Game::getModel("game/assault")->load((int) $message->get("message"));
		if($assault->get("assaultid"))
		{
			if(!$assault->get("galaxy"))
			{
				$data = Core::getDB()->fetch_field(Core::getQuery()->select("assaultparticipant ap", "data", "", "ap.assaultid = '".$assault->get("assaultid")."' && mode = 0"), "data");
				$tokens = explode(",", $data);
				foreach($tokens as $token)
				{
					$valuePairs = explode(":", $token);
					if(trim($valuePairs[0]) == "galaxy")
					{
						$assault->set("galaxy", (int) $valuePairs[1]);
					}
					else if(trim($valuePairs[0]) == "system")
					{
						$assault->set("system", (int) $valuePairs[1]);
					}
					else if(trim($valuePairs[0]) == "position")
					{
						$assault->set("position", (int) $valuePairs[1]);
					}
					else if(trim($valuePairs[0]) == "username")
					{
						$assault->set("planetname", Core::getLang()->get(substr($valuePairs[1], 6, -7)));
					}
				}
			}
			$subject = getCoordLink($assault->get("galaxy"), $assault->get("system"), $assault->get("position"));
			$subject .= " ".$assault->get("planetname");
			$message->set("subject", $subject);
			$url = BASE_URL.Core::getLang()->getOpt("langcode")."/combat/report/".$assault->get("assaultid")."/".$assault->get("key");
			$gentime = $assault->get("gentime") / 1000;
			$label = Core::getLanguage()->getItem("ASSAULT_REPORT")." (A: ".fNumber($assault->get("lostunits_attacker")).", D: ".fNumber($assault->get("lostunits_defender")).") ".$gentime."s";
			$message->set("message", "<div class=\"assault-report\" onclick=\"openWindow('".$url."');\">".$label."</div>");
		}
		return $this;
	}

	/**
	 * Formats the message for news feeds.
	 *
	 * @param Bengine_Game_Model_Message $message
	 *
	 * @return Bengine_Game_MessageFolder_Combat
	 */
	protected function _formatFeed(Bengine_Game_Model_Message $message)
	{
		$assaultId = (int) $message->get("message");
		$assault = Game::getModel("game/assault")->load($assaultId);
		$link = BASE_URL.Core::getLang()->getOpt("langcode")."/combat/report/".$assaultId."/".$assault->get("key");
		$gentime = $assault->get("gentime") / 1000;
		$text = Core::getLanguage()->getItem("ASSAULT_REPORT")." (A: ".fNumber($assault->get("lostunits_attacker")).", D: ".fNumber($assault->get("lostunits_defender")).") ".$gentime."s";
		$subject = Core::getLang()->get($message->get("subject")).": ".$assault->get("planetname")." [".$assault->getCoords(false)."]";
		$message->set(array("message" => $text, "subject" => $subject, "link" => $link));
		return $this;
	}
}