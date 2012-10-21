<?php
/**
 * Generates auto-report after a fleet event.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: AutoMsg.php 46 2011-07-30 14:38:11Z secretchampion $
 */

class Bengine_Game_AutoMsg
{
	/**
	 * Mode id. Identifies what message should be sent.
	 *
	 * @var integer
	 */
	protected $mode = 0;

	/**
	 * Target user.
	 *
	 * @var integer
	 */
	protected $userid = 0;

	/**
	 * Time when message has actually been sent.
	 *
	 * @var integer
	 */
	protected $time = 0;

	/**
	 * The datafield holds the fleet informations.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Start the message generator.
	 *
	 * @param integer
	 * @param integer
	 * @param integer
	 * @param array
	 *
	 * @return void
	 */
	public function __construct($mode, $userid, $time, array $data)
	{
		$this->mode = $mode;
		$this->userid = $userid;
		$this->time = $time;
		$this->data = $data;
		Core::getLanguage()->load("AutoMessages");
		Hook::event("AutoMsgStart", array($this));
		switch($this->mode)
		{
			case 6:
				$this->folder = 3;
				$this->positionReport();
			break;
			case 7:
				$this->folder = 3;
				$this->transportReport();
			break;
			case 8:
				$this->folder = 3;
				$this->colonizeReport();
			break;
			case 9:
				$this->folder = 3;
				$this->recyclingReport();
			break;
			case 11:
				$this->folder = 3;
				$this->espionageBengine_Game_Committed();
			break;
			case 20:
				$this->folder = 3;
				$this->returnReport();
			break;
			case 21:
				$this->folder = 3;
				$this->transportReport_other();
			break;
			case 22:
				$this->folder = 3;
				$this->asteroid();
			break;
			case 23:
				$this->folder = 6;
				$this->allyAbandoned();
			break;
			case 24:
				$this->folder = 6;
				$this->memberReceipted();
			break;
			case 25:
				$this->folder = 6;
				$this->memberRefused();
			break;
			case 100:
				$this->folder = 6;
				$this->newMember();
			break;
			case 101:
				$this->folder = 6;
				$this->memberLeft();
			break;
			case 102:
				$this->folder = 6;
				$this->memberKicked();
			break;
			case 201:
				$this->folder = 7;
				$this->creditsGained();
			break;
			case 202:
				$this->folder = 7;
				$this->creditsLost();
			break;
		}
		return;
	}

	/**
	 * When a user gains credits.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function creditsGained()
	{
		$msg = Core::getLanguage()->getItem("CREDIT_GAINED");

		$credits = fNumber($this->data["credits"]);
		$from = $this->data["username"];
		$tax = fNumber($this->data["tax"]);

		$msg = sprintf($msg, $credits, $from, $tax);
		Hook::event("AutoMsgCreditGained", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("CREDIT_SUBJECT"), $msg);
		return $this;
	}

	/**
	 * When a user gains credits.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function creditsLost()
	{
		$msg = Core::getLanguage()->getItem("CREDIT_LOST");

		$credits = fNumber($this->data["credits"]);
		$to = $this->data["username"];
		$tax = fNumber($this->data["tax"]);

		$msg = sprintf($msg, $credits, $to, $tax);
		Hook::event("AutoMsgCreditLost", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("CREDIT_SUBJECT"), $msg);
		return $this;
	}

	/**
	 * When a fleet reached a planet to position.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function positionReport()
	{
		$msg = Core::getLanguage()->getItem("POSITION_REPORT");

		// Get the important data
		$metal = fNumber($this->data["metal"]);
		$silicon = fNumber($this->data["silicon"]);
		$hydrogen = fNumber($this->data["hydrogen"]);
		$rShips = $this->getShipsString();
		$result = Core::getQuery()->select("planet", array("planetname"), "", "planetid = '".$this->data["destination"]."'");
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		$planet = $row["planetname"];
		$coordinates = getCoordLink($this->data["galaxy"], $this->data["system"], $this->data["position"], true);
		$msg = sprintf($msg, $rShips, $planet, $coordinates, $metal, $silicon, $hydrogen);
		Hook::event("AutoMsgPositionReport", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("POSITION_REPORT_SUBJECT"), $msg);
		return $this;
	}

	/**
	 * Message after transport to own planet has completed.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function transportReport()
	{
		$msg = Core::getLanguage()->getItem("TRANSPORT_ACCOMPLISHED");

		// Get the important data
		$metal = fNumber($this->data["metal"]);
		$silicon = fNumber($this->data["silicon"]);
		$hydrogen = fNumber($this->data["hydrogen"]);
		$planet = $this->data["targetplanet"];
		$coordinates = getCoordLink($this->data["galaxy"], $this->data["system"], $this->data["position"], true);
		$msg = sprintf($msg, $planet, $coordinates, $metal, $silicon, $hydrogen);
		Hook::event("AutoMsgTransportReport", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("TRANPORT_SUBJECT"), $msg);
		return $this;
	}

	/**
	 * Message after transport to another has completed.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function transportReport_other()
	{
		$msg = Core::getLanguage()->getItem("TRANSPORT_ACCOMPLISHED_TO_OTHER");

		$metal = fNumber($this->data["metal"]);
		$silicon = fNumber($this->data["silicon"]);
		$hydrogen = fNumber($this->data["hydrogen"]);
		$planet = $this->data["startplanet"];
		$planetCoords = getCoordLink($this->data["sgalaxy"], $this->data["ssystem"], $this->data["sposition"], true);
		$tplanet = $this->data["targetplanet"];
		$tplanetCoords = getCoordLink($this->data["galaxy"], $this->data["system"], $this->data["position"], true);

		$msg = sprintf($msg, $planet, $planetCoords, $tplanet, $tplanetCoords, $metal, $silicon, $hydrogen);
		$this->sendMsg(Core::getLanguage()->getItem("TRANPORT_SUBJECT"), $msg);

		if(!empty($this->data["targetuser"]))
		{
			Core::getQuery()->insertInto("transport_log", array(
				"user_1" => $this->userid,
				"user_2" => $this->data["targetuser"],
				"message" => strip_tags($msg),
				"time" => TIME,
				"suspicious" => isset($this->data["suspicious"]) ? $this->data["suspicious"] : 0
			));

			$this->userid = $this->data["targetuser"];
			$msg = Core::getLanguage()->getItem("TRANSPORT_ACCOMPLISHED_OTHER");
			$user = $this->data["startuser"];

			$msg = sprintf($msg, $planet, $planetCoords, $user, $tplanet, $tplanetCoords, $metal, $silicon, $hydrogen);
			Hook::event("AutoMsgTransportReportByOther", array($this, &$msg));
			$this->sendMsg(Core::getLanguage()->getItem("TRANPORT_SUBJECT"), $msg);
		}
		return $this;
	}

	/**
	 * Generates a string for all ships.
	 *
	 * @return string	The ships
	 */
	protected function getShipsString()
	{
		Core::getLanguage()->load("info");
		$rShips = "";
		foreach($this->data["ships"] as $ship)
		{
			$rShips .= Core::getLanguage()->getItem($ship["name"]).": ".fNumber($ship["quantity"])." ";
		}
		return substr($rShips, 0, -1);
	}

	/**
	 * Sends a message that a fleet has returned to planet.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function returnReport()
	{
		$msg = Core::getLanguage()->getItem("FLEET_RETURNED");

		// Get important data
		$metal = fNumber($this->data["metal"]);
		$silicon = fNumber($this->data["silicon"]);
		$hydrogen = fNumber($this->data["hydrogen"]);
		$rShips = $this->getShipsString();
		$coordinates = getCoordLink($this->data["galaxy"], $this->data["system"], $this->data["position"], true);
		$result = Core::getQuery()->select("planet", array("planetname"), "", "planetid = '".$this->data["destination"]."'");
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		$planet = $row["planetname"];
		$coords = getCoordLink($this->data["sgalaxy"], $this->data["ssystem"], $this->data["sposition"], true);
		$msg = sprintf($msg, $rShips, $coordinates, $planet, $coords, $metal, $silicon, $hydrogen);
		Hook::event("AutoMsgReturnReport", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("FLEET_RETURNED_SUBJECT"), $msg);
		return $this;
	}

	/**
	 * When recycling event is completed.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function recyclingReport()
	{
		$msg = Core::getLanguage()->getItem("RECYLCING_REPORT");
		$recycler = fNumber($this->data["ships"][37]["quantity"]);
		$capacity = fNumber($this->data["capacity"]);
		$debrism = fNumber($this->data["debrismetal"]);
		$debriss = fNumber($this->data["debrissilicon"]);
		$recycledm = fNumber($this->data["recycledmetal"]);
		$recycleds = fNumber($this->data["recycledsilicon"]);
		$subject = sprintf(Core::getLanguage()->getItem("RECYLCING_REPORT_SUBJECT"), getCoordLink($this->data["galaxy"], $this->data["system"], $this->data["position"], true));
		$msg = sprintf($msg, $recycler, $capacity, $debrism, $debriss, $recycledm, $recycleds);
		Hook::event("AutoMsgRecyclingReport", array($this, &$msg));
		$this->sendMsg($subject, $msg);
		return $this;
	}

	/**
	 * Sends message after a colony ship reached at a position.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function colonizeReport()
	{
		if(isset($this->data["success"]))
		{
			if($this->data["success"] == "occupied")
			{
				$msg = Core::getLanguage()->getItem("PLANET_OCCUPIED");
			}
			else if($this->data["success"] == "empire")
			{
				$msg = Core::getLanguage()->getItem("TOO_MANY_PLANETS");
			}
		}
		else
		{
			$msg = Core::getLanguage()->getItem("PLANET_COLONIZED");
		}
		$coordinates = getCoordLink($this->data["galaxy"], $this->data["system"], $this->data["position"], true);
		$msg = sprintf($msg, $coordinates);
		Hook::event("AutoMsgColonizeReport", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("COLONY_ATTEMPT_SUBJECT"), $msg);
		return $this;
	}

	/**
	 * Write the message into database.
	 *
	 * @param string	Message subject
	 * @param string	Message
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function sendMsg($subject, $msg)
	{
		Core::getQuery()->insertInto("message", array("mode" => $this->folder, "time" => $this->time, "sender" => null, "receiver" => $this->userid, "message" => $msg, "subject" => $subject, "read" => 0));
		return $this;
	}

	/**
	 * Generates message for an asteroid event.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function asteroid()
	{
		$msg = Core::getLanguage()->getItem("ASTEROID_IMPACT");
		$coordinates = getCoordLink($this->data["galaxy"], $this->data["system"], $this->data["position"], true);
		$msg = sprintf($msg, $this->data["planet"], $coordinates, fNumber($this->data["metal"]), fNumber($this->data["silicon"]));
		Hook::event("AutoMsgAsteroid", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("ASTEROID_IMPACT_SUBJECT"), $msg);
		return $this;
	}

	/**
	 * Generates message for members if alliance has been abandoned.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function allyAbandoned()
	{
		$msg = Core::getLanguage()->getItem("ALLIANCE_ABANDONED");
		$msg = sprintf($msg, $this->data["tag"]);
		Hook::event("AutoMsgAllyAbandoned", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("ALLIANCE_ABANDONED_SUBJECT"), $msg);
		return $this;
	}

	/**
	 * Generates message if user's application has been receipted.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function memberReceipted()
	{
		$msg = Core::getLanguage()->getItem("MEMBER_RECEIPTED");
		$msg = sprintf($msg, $this->data["tag"]);
		Hook::event("AutoMsgMemberRecepted", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("ALLIANCE_APPLICATION"), $msg);
		return $this;
	}

	/**
	 * Generates message if user's application has been refused.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function memberRefused()
	{
		$msg = Core::getLanguage()->getItem("MEMBER_REFUSED");
		$msg = sprintf($msg, $this->data["tag"]);
		Hook::event("AutoMsgMemberRefused", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("ALLIANCE_APPLICATION"), $msg);
		return $this;
	}

	/**
	 * Generates message if user was target of espionage.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function espionageBengine_Game_Committed()
	{
		$msg = Core::getLanguage()->getItem("ESPIOANGE_COMMITTED");
		$event = $this->data["event"];
		$coordss = getCoordLink($event->get("galaxy"), $event->get("system"), $event->get("position"), true);
		$coordinates = getCoordLink($event->get("galaxy2"), $event->get("system2"), $event->get("position2"), true);
		$espLost = ($this->data["probes_lost"]) ? Core::getLanguage()->getItem("ESP_PROBES_DESTROYED_1") : "";
		$msg = sprintf($msg, $this->data["suser"], $this->data["planetname"], $coordss, $this->data["destinationplanet"], $coordinates, $this->data["defending_chance"]."&#37;", $espLost);
		Hook::event("AutoMsgEspionage", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("ESPIOANGE_COMMITTED_SUBJECT"), $msg);
		return $this;
	}

	/**
	 * Generates message for alliance members if someone joined the alliance.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function newMember()
	{
		$msg = Core::getLanguage()->getItem("NEW_MEMBER_JOINED");
		$msg = sprintf($msg, $this->data["username"]);
		Hook::event("AutoMsgNewMember", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("NEW_MEMBER_JOINED_SUBJECT"), $msg);
		return $this;
	}

	/**
	 * Generates message for alliance members if someone has left the alliance.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function memberLeft()
	{
		$msg = Core::getLanguage()->getItem("MEMBER_LEFT");
		$msg = sprintf($msg, $this->data["username"]);
		Hook::event("AutoMsgMemberLeft", array($this, &$msg));
		$this->sendMsg(Core::getLanguage()->getItem("MEMBER_LEFT_SUBJECT"), $msg);
		return $this;
	}

	/**
	 * Generates message for alliance members if someone has been kicked off.
	 *
	 * @return Bengine_Game_AutoMsg
	 */
	protected function memberKicked()
	{
		Hook::event("AutoMsgMemberKicked", array($this));
		$this->sendMsg(Core::getLanguage()->getItem("MEMBER_KICKED_SUBJECT"), Core::getLanguage()->getItem("MEMBER_KICKED"));
		return $this;
	}
}
?>