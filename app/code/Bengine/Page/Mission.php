<?php
/**
 * Allows the user to send fleets to a mission.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Mission.php 36 2011-07-20 07:00:37Z secretchampion $
 */

class Bengine_Page_Mission extends Bengine_Page_Abstract
{
	/**
	 * Constructor. Handles requests for this page.
	 *
	 * @return Bengine_Page_Mission
	 */
	protected function init()
	{
		Core::getTPL()->addHTMLHeaderFile("fleet.js", "js");
		Core::getLanguage()->load(array("info", "mission"));
		if(!Core::getUser()->get("umode") && $this->isPost())
		{
			if($this->getParam("stargatejump"))
			{
				$this->starGateJump(Core::getRequest()->getPOST());
			}
			if($this->getParam("execjump"))
			{
				$this->executeJump($this->getParam("moonid"));
			}
			if($this->getParam("retreat"))
			{
				$this->retreatFleet($this->getParam("id"));
			}
			if($this->getParam("formation"))
			{
				$this->formation($this->getParam("id"));
			}
			if($this->getParam("invite"))
			{
				$this->invite($this->getParam("id"), $this->getParam("name"), $this->getParam("username"));
			}
			if($this->getParam("step2"))
			{
				$this->selectCoordinates($this->getParam("galaxy"), $this->getParam("system"), $this->getParam("position"), $this->getParam("targetType"), $this->getParam("code"), Core::getRequest()->getPOST());
			}
			if($this->getParam("step3"))
			{
				$this->selectMission(
					$this->getParam("galaxy"), $this->getParam("system"), $this->getParam("position"),
					$this->getParam("targetType"), $this->getParam("speed"), $this->getParam("code"), $this->getParam("formation")
				);
			}
			if($this->getParam("step4"))
			{
				$this->sendFleet($this->getParam("mode"), $this->getParam("metal"), $this->getParam("silicon"), $this->getParam("hydrogen"), $this->getParam("holdingtime"));
			}
		}
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Page_Mission
	 */
	protected function indexAction()
	{
		$fleetEvents = Bengine::getEH()->getOwnFleetEvents();
		$fleetEvents = (!is_array($fleetEvents)) ? array() : $fleetEvents;
		Hook::event("ShowAllMissions", array(&$fleetEvents));
		Core::getTPL()->addLoop("missions", $fleetEvents);
		Core::getQuery()->delete("temp_fleet", "planetid = '".Core::getUser()->get("curplanet")."'");

		$fleet = Bengine::getModel("fleet")->getCollection();
		$fleet->addPlanetFilter(Core::getUser()->get("curplanet"));
		Hook::event("MissionFlettList", array($fleet));
		Core::getTPL()->addLoop("fleet", $fleet);
		$canSendFleet = false;
		$fleetEvents = Bengine::getEH()->getOwnFleetEvents();
		if(!$fleetEvents || Bengine::getResearch(14) + 1 > count($fleetEvents))
		{
			$canSendFleet = true;
		}
		Core::getTPL()->assign("canSendFleet", $canSendFleet);
		return $this;
	}

	/**
	 * Select the mission's target and speed.
	 *
	 * @param integer	Galaxy
	 * @param integer	System
	 * @param integer	Position
	 * @param array		Fleet to send
	 *
	 * @return Bengine_Page_Mission
	 */
	protected function selectCoordinates($galaxy, $system, $position, $targetType, $code, $ships)
	{
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.countdown.js", "js");
		$galaxy = ($galaxy > 0) ? (int) $galaxy : Bengine::getPlanet()->getData("galaxy");
		$system = ($system > 0) ? (int) $system : Bengine::getPlanet()->getData("system");
		$position = ($position > 0) ? (int) $position : Bengine::getPlanet()->getData("position");
		$targetType = $targetType == "tf" ? "tf" : ($targetType == "moon" ? "moon" : "planet");

		$data = array(
			"ships" => array()
		);
		Core::getQuery()->delete("temp_fleet", "planetid = '".Core::getUser()->get("curplanet")."'");
		$select = array("u2s.unitid", "u2s.quantity", "d.capicity", "d.speed", "d.consume", "b.name");
		$joins  = "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = u2s.unitid)";
		$joins .= "LEFT JOIN ".PREFIX."ship_datasheet d ON (d.unitid = u2s.unitid)";
		$result = Core::getQuery()->select("unit2shipyard u2s", $select, $joins, "b.mode = '3' AND u2s.planetid = '".Core::getUser()->get("curplanet")."'");
		$capacity = 0;
		$consumption = 0;
		$speed = 0;
		while($row = Core::getDB()->fetch($result))
		{
			$id = $row["unitid"];
			$quantity = (isset($ships[$id])) ? (int) $ships[$id] : 0;
			if($quantity > $row["quantity"])
			{
				$quantity = $row["quantity"];
			}
			if($quantity > 0)
			{
				$id = (int) $id;
				$capacity += $row["capicity"] * $quantity;
				$data["ships"][$id]["quantity"] = $quantity;
				$data["ships"][$id]["name"] = $row["name"];
				$data["ships"][$id]["id"] = $id;
				$shipSpeed = Bengine::getSpeed($id, $row["speed"]);
				if($speed == 0 || $shipSpeed < $speed)
				{
					$speed = $shipSpeed;
				}
   				$consumption += $row["consume"] * $quantity;
			}
		}
		Core::getDB()->free_result($result);
		if(count($data["ships"]) > 0 && $speed > 0)
		{
			$distance = Bengine::getDistance($galaxy, $system, $position);
			$time = Bengine::getFlyTime($distance, $speed);
			Hook::event("MissionSetData", array(&$data, &$quantity, &$consumption, &$speed, &$capacity));

			Core::getTPL()->assign(array(
				"galaxy" => $galaxy,
				"system" => $system,
				"position" => $position,
				"targetType" => $targetType,
				"code" => $code,
				"oGalaxy" => Bengine::getPlanet()->getData("galaxy"),
				"oSystem" => Bengine::getPlanet()->getData("system"),
				"oPos" => Bengine::getPlanet()->getData("position"),
				"maxspeedVar" => $speed,
				"distance" => $distance,
				"capacity_raw" => $capacity,
				"basicConsumption" => $consumption,
				"time" => getTimeTerm($time),
				"maxspeed" => fNumber($speed),
				"capacity" => fNumber($capacity - Bengine::getFlyConsumption($consumption, $distance)),
				"fuel" => fNumber(Bengine::getFlyConsumption($consumption, $distance))
			));

			$data["consumption"] = $consumption;
			$data["maxspeed"] = $speed;
			$data["capacity"] = $capacity;
			$data = serialize($data);
			Core::getQuery()->insert("temp_fleet", array("planetid", "data"), array(Core::getUser()->get("curplanet"), $data));

			// Short speed selection
			$selectbox = "";
			for($n = 10; $n > 0; $n--)
			{
				$selectbox .= createOption($n * 10, $n * 10, 0);
			}
			Core::getTPL()->assign("speedFromSelectBox", $selectbox);

			// Invatations for alliance attack
			$invitations = array();
			$joins  = "LEFT JOIN ".PREFIX."events e ON (e.eventid = fi.eventid)";
			$joins .= "LEFT JOIN ".PREFIX."attack_formation af ON (e.eventid = af.eventid)";
			$joins .= "LEFT JOIN ".PREFIX."galaxy g ON (g.planetid = e.destination)";
			$joins .= "LEFT JOIN ".PREFIX."galaxy m ON (m.moonid = e.destination)";
			$select = array("af.eventid", "af.name", "af.time", "g.galaxy", "g.system", "g.position", "m.galaxy AS moongala", "m.system AS moonsys", "m.position AS moonpos");
			$_result = Core::getQuery()->select("formation_invitation fi", $select, $joins, "fi.userid = '".Core::getUser()->get("userid")."' AND af.time > '".TIME."'");
			while($_row = Core::getDB()->fetch($_result))
			{
				$_row["type"] = 0;
				if(!empty($_row["moongala"]) && !empty($_row["moonsys"]) && !empty($_row["moonpos"]))
				{
					$_row["galaxy"] = $_row["moongala"];
					$_row["system"] = $_row["moonsys"];
					$_row["position"] = $_row["moonpos"];
					$_row["type"] = 2;
				}
				$_row["time_r"] = $_row["time"] - TIME;
				$_row["formatted_time"] = getTimeTerm($_row["time_r"]);
				$invitations[] = $_row;
			}
			Core::getDB()->free_result($_result);
			Core::getTPL()->addLoop("invitations", $invitations);

			// Planet shortlinks
			$sl = array();

			$order = getPlanetOrder(Core::getUser()->get("planetorder"));
			$_result = Core::getQuery()->select("planet p", array("p.ismoon", "p.planetname", "g.galaxy", "g.system", "g.position", "gm.galaxy moongala", "gm.system as moonsys", "gm.position as moonpos"), "LEFT JOIN ".PREFIX."galaxy g ON (g.planetid = p.planetid) LEFT JOIN ".PREFIX."galaxy gm ON (gm.moonid = p.planetid)", "p.userid = '".Core::getUser()->get("userid")."' AND p.planetid != '".Core::getUser()->get("curplanet")."'", $order);
			while($_row = Core::getDB()->fetch($_result))
			{
				$sl[] = array(
					"planetname" =>  $_row["planetname"],
					"galaxy" => ($_row["ismoon"]) ? $_row["moongala"] : $_row["galaxy"],
					"system" => ($_row["ismoon"]) ? $_row["moonsys"] : $_row["system"],
					"position" => ($_row["ismoon"]) ? $_row["moonpos"] : $_row["position"],
					"type" => ($_row["ismoon"]) ? 2 : 0
				);
			}
			Core::getDB()->free_result($_result);
			Hook::event("MissionPlanetQuickLinks", array(&$sl));
			Core::getTPL()->addLoop("shortlinks", $sl);
			Core::getTPL()->display("mission_step2");
			Bengine::unlock();
			exit;
		}
		else
		{
			Logger::addMessage("NO_SHIPS_SELECTED");
		}
		return $this;
	}

	/**
	 * Select the mission to start and stored resources.
	 *
	 * @param integer	Galaxy
	 * @param integer	System
	 * @param integer	Position
	 * @param string	Target type
	 * @param integer	Speed
	 *
	 * @return Bengine_Page_Mission
	 */
	protected function selectMission($galaxy, $system, $position, $targetType, $speed, $code, $formation)
	{
		$result = Core::getQuery()->select("temp_fleet", "data", "", "planetid = '".Core::getUser()->get("curplanet")."'");
		if($row = Core::getDB()->fetch($result))
		{
			Core::getDB()->free_result($result);

			$targetMode = ($targetType == "moon") ? "moonid" : "planetid";

			$data = unserialize($row["data"]);
			$data["galaxy"] = (int) $galaxy;
			$data["system"] = (int) $system;
			$data["position"] = (int) $position;
			$speed = (int) $speed;
			if($speed <= 0)
			{
				$speed = 1;
			}
			else if($speed > 100)
			{
				$speed = 100;
			}
			$data["speed"] = $speed;

			$select = array("p.planetid", "p.planetname", "p.ismoon", "u.userid", "u.points", "u.last", "u.umode", "b.to", "u2a.aid");
			$joins  = "LEFT JOIN ".PREFIX."planet p ON (p.planetid = g.".$targetMode.")";
			$joins .= "LEFT JOIN ".PREFIX."user u ON (p.userid = u.userid)";
			$joins .= "LEFT JOIN ".PREFIX."ban_u b ON (u.userid = b.userid)";
			$joins .= "LEFT JOIN ".PREFIX."user2ally u2a ON (u2a.userid = u.userid)";
			$result = Core::getQuery()->select("galaxy g", $select, $joins, "galaxy = '".$galaxy."' AND system = '".$system."' AND position = '".$position."'");
			$target = Core::getDB()->fetch($result);
			Core::getDB()->free_result($result);
			if($target === false)
			{
				$target = null;
			}

			$targetName = Core::getLanguage()->getItem("UNKOWN_PLANET");
			if(!empty($target["planetid"]))
			{
				$targetName = $target["planetname"];
				$data["destination"] = $target["planetid"];
				$target["formation"] = (int) $formation;
			}

			if($targetType == "tf")
			{
				$targetName .= " (".Core::getLanguage()->getItem("TF").")";
			}

			$Relations = new Bengine_User_Relation(Core::getUser()->get("userid"), Core::getUser()->get("aid"));

			$showHoldingTime = false;
			// Check for available missions
			$missions = array();
			$eventTypes = Application::getCollection("event_type");
			$eventTypes->addBaseTypeFilter("fleet");
			foreach($eventTypes as $eventType)
			{
				/* @var $handler Bengine_EventHandler_Handler_Fleet_Abstract */
				$handler = $eventType->getEventHandler();
				$handler->setRelations($Relations)
					->setShips($data["ships"])
					->setTarget($target)
					->setTargetType($targetType);
				if($handler->isValid())
				{
					$missions[$eventType->get("event_type_id")] = array(
						"mode" => $eventType->get("event_type_id"),
						"mission" => $eventType->getModeName(),
						"selected" => ($eventType->getCode() == $code)
					);
					if($eventType->getCode() == "alliedFleet")
					{
						$data["alliance_attack"] = $handler->getSpecialData();
					}
					else if($eventType->getCode() == "halt")
					{
						$showHoldingTime = true;
					}
				}
			}

			Hook::event("SetAvailableMissions", array(&$missions, &$data));

			unset($data["amissions"]);
			foreach($missions as $key => $value)
			{
				$data["amissions"][] = $key;
			}

			Core::getQuery()->update("temp_fleet", "data", serialize($data), "planetid = '".Core::getUser()->get("curplanet")."'");

			$distance = Bengine::getDistance($galaxy, $system, $position);
			$consumption = Bengine::getFlyConsumption($data["consumption"], $distance, $speed);

			Core::getTPL()->assign(array(
				"metal" => Bengine::getPlanet()->getData("metal"),
				"silicon" => Bengine::getPlanet()->getData("silicon"),
				"hydrogen" => Bengine::getPlanet()->getData("hydrogen") - $consumption,
				"capacity" => $data["capacity"] - $consumption,
				"rest" => fNumber($data["capacity"] - $consumption),
				"showHoldingTime" => $showHoldingTime,
				"targetName" => $galaxy.":".$system.":".$position." ".$targetName,
			));
			Core::getTPL()->addLoop("missions", $missions);
			Core::getTPL()->display("mission_step3");
			Bengine::unlock();
			exit;
		}
		Core::getDB()->free_result($result);
		return $this;
	}

	/**
	 * This starts the missions and shows a quick overview of the flight.
	 *
	 * @param integer	Mission type
	 * @param integer	Metal
	 * @param integer	Silicon
	 * @param integer	Hydrogen
	 * @param integer	Holding time
	 *
	 * @return Bengine_Page_Mission
	 */
	protected function sendFleet($mode, $metal, $silicon, $hydrogen, $holdingtime)
	{
		$fleetEvents = Bengine::getEH()->getOwnFleetEvents();
		if($fleetEvents && Bengine::getResearch(14) + 1 <= count(Bengine::getEH()->getOwnFleetEvents()))
		{
			throw new Recipe_Exception_Generic("Too many fleets on missions.");
		}
		$result = Core::getQuery()->select("temp_fleet", "data", "", "planetid = '".Core::getUser()->get("curplanet")."'");
		if($row = Core::getDB()->fetch($result))
		{
			Core::getDB()->free_result($result);
			$temp = unserialize($row["data"]);
			if(!in_array($mode, $temp["amissions"])) { Logger::dieMessage("UNKOWN_MISSION"); }

			$data["ships"] = $temp["ships"];
			$data["galaxy"] = $temp["galaxy"];
			$data["system"] = $temp["system"];
			$data["position"] = $temp["position"];
			$data["sgalaxy"] = Bengine::getPlanet()->getData("galaxy");
			$data["ssystem"] = Bengine::getPlanet()->getData("system");
			$data["sposition"] = Bengine::getPlanet()->getData("position");
			$data["maxspeed"] = $temp["maxspeed"];

			$distance = Bengine::getDistance($data["galaxy"], $data["system"], $data["position"]);
			$data["consumption"] = Bengine::getFlyConsumption($temp["consumption"], $distance, $temp["speed"]);

			if(Bengine::getPlanet()->getData("hydrogen") - $data["consumption"] < 0)
			{
				Logger::dieMessage("NOT_ENOUGH_FUEL");
			}

			Bengine::getPlanet()->setData("hydrogen", Bengine::getPlanet()->getData("hydrogen") - $data["consumption"]);

			if($temp["capacity"] < $data["consumption"])
			{
				Logger::dieMessage("NOT_ENOUGH_CAPACITY");
			}

			$data["metal"] = (int) abs($metal);
			$data["silicon"] = (int) abs($silicon);
			$data["hydrogen"] = (int) abs($hydrogen);

			if($data["metal"] > Bengine::getPlanet()->getData("metal"))
			{
				$data["metal"] = _pos(Bengine::getPlanet()->getData("metal"));
			}
			if($data["silicon"] > Bengine::getPlanet()->getData("silicon"))
			{
				$data["silicon"] = _pos(Bengine::getPlanet()->getData("silicon"));
			}
			if($data["hydrogen"] > Bengine::getPlanet()->getData("hydrogen"))
			{
				$data["hydrogen"] = _pos(Bengine::getPlanet()->getData("hydrogen"));
			}

			if($mode == 13)
			{
				$data["duration"] = _pos($holdingtime);
				if($data["duration"] > 24)
				{
					$data["duration"] = 24;
				}
				$data["duration"] *= 3600;
			}

  			$capa = $temp["capacity"] - $data["consumption"] - $data["metal"] - $data["silicon"] - $data["hydrogen"];
  			// Reduce used capacity automatically
			if($capa < 0)
			{
				if($capa + $data["hydrogen"] > 0)
				{
					$data["hydrogen"] -= abs($capa);
				}
				else
				{
					$capa += $data["hydrogen"];
					$data["hydrogen"] = 0;
					if($capa + $data["silicon"] > 0 && $capa < 0)
					{
						$data["silicon"] -= abs($capa);
					}
					else if($capa < 0)
					{
						$capa += $data["silicon"];
						$data["silicon"] = 0;
						if($capa + $data["metal"] && $capa < 0)
						{
							$data["metal"] -= abs($capa);
						}
						else if($capa < 0)
						{
							$data["metal"] = 0;
						}
					}
				}
			}

  			$data["capacity"] = $temp["capacity"] - $data["metal"] - $data["silicon"] - $data["hydrogen"];

			if($data["capacity"] < 0)
			{
				Logger::dieMessage("NOT_ENOUGH_CAPACITY");
			}

  			// If mission is recycling, get just the capacity of the recyclers.
  			if($mode == 9 && $data["capacity"] > 0)
  			{
  				$_result = Core::getQuery()->select("ship_datasheet", "capicity", "", "unitid = '37'"); // It is __capacity__ and not capicity
  				$_row = Core::getDB()->fetch($_result);
  				Core::getDB()->free_result($_result);
  				$recCapa = $_row["capicity"] * $temp["ships"][37]["quantity"];
  				if($data["capacity"] >= $recCapa)
  				{
  					$data["capacity"] = $recCapa;
  				}
  			}

			$time = Bengine::getFlyTime($distance, $data["maxspeed"], $temp["speed"]);
			$data["time"] = $time;

			if($mode == 18)
			{
				$data["alliance_attack"] = $temp["alliance_attack"];
				$mainFleet = Bengine::getEH()->getMainFormationFleet($data["alliance_attack"]["eventid"]);
				$allFleets = Bengine::getEH()->getFormationFleets($data["alliance_attack"]["eventid"]);
				$numFleets = 1;
				$formationUser[$mainFleet->get("user")] = true;
				foreach($allFleets as $oneFleet)
				{
					$numFleets++;
					$formationUser[$oneFleet->get("user")] = true;
				}
				unset($formationUser[Core::getUser()->get("userid")]);

				if($numFleets >= Core::getOptions()->get("MAX_FORMATION_FLEETS")) { Logger::dieMessage("MAX_FORMATION_FLEETS_EXCEEDED"); }
				if(count($formationUser) >= Core::getOptions()->get("MAX_FORMATION_USER")) { Logger::dieMessage("MAX_FORMATION_USER_EXCEEDED"); }

				if(($data["time"] + TIME) > ((($mainFleet["time"] - TIME) * (1 + Core::getOptions()->get("MAX_FORMATION_DELAY"))) + TIME)) { Logger::dieMessage("MAX_FORMATION_DELAY_EXCEEDED"); }
			}

			Hook::event("SendFleet", array(&$data, &$time, &$temp, $distance));
			Core::getQuery()->delete("temp_fleet", "planetid = '".Core::getUser()->get("curplanet")."'");
			Bengine::getEH()->addEvent($mode, $time + TIME, Core::getUser()->get("curplanet"), Core::getUser()->get("userid"), (isset($temp["destination"])) ? $temp["destination"] : null, $data);

			Core::getTPL()->assign("mission", Bengine::getMissionName($mode));
			Core::getTPL()->assign("mode", $mode);
			Core::getTPL()->assign("distance", fNumber($distance));
			Core::getTPL()->assign("speed", fNumber($temp["maxspeed"]));
			Core::getTPL()->assign("consume", fNumber($data["consumption"]));
			Core::getTPL()->assign("start", Bengine::getPlanet()->getCoords(false));
			Core::getTPL()->assign("target", $data["galaxy"].":".$data["system"].":".$data["position"]);
			Core::getTPL()->assign("arrival", Date::timeToString(1, $time + TIME));
			Core::getTPL()->assign("return", Date::timeToString(1, $time * 2 + TIME));

			$fleet = array();
			foreach($data["ships"] as $key => $value)
			{
				$fleet[$key]["name"] = Core::getLanguage()->getItem($value["name"]);
				$fleet[$key]["quantity"] = fNumber($value["quantity"]);
			}
			Core::getTPL()->addLoop("fleet", $fleet);
			Core::getTPL()->display("mission_step4");
			Bengine::unlock();
			exit;
		}
		Core::getDB()->free_result($result);
		return $this;
	}

	/**
	 * Retreats a fleet back to its origin planet.
	 *
	 * @param integer	Event id to retreat
	 *
	 * @return Bengine_Page_Mission
	 */
	protected function retreatFleet($id)
	{
		Hook::event("RetreatFleet", array(&$id));
		Bengine::getEH()->removeEvent($id);
		$this->redirect("game.php/".SID."/Mission");
		return $this;
	}

	/**
	 * Invite friends for alliance attack.
	 *
	 * @param integer	Event id
	 *
	 * @return Bengine_Page_Mission
	 */
	protected function formation($eventid)
	{
		$result = Core::getQuery()->select("events", "time", "", "(mode = '10' OR mode = '12') AND user = '".Core::getUser()->get("userid")."' AND eventid = '".$eventid."'");
		if($row = Core::getDB()->fetch($result))
		{
			Core::getDB()->free_result($result);
			$invitation = array();
			$joins  = "LEFT JOIN ".PREFIX."formation_invitation fi ON (fi.eventid = af.eventid)";
			$joins .= "LEFT JOIN ".PREFIX."user u ON (u.userid = fi.userid)";
			$result = Core::getQuery()->select("attack_formation af", array("af.name", "fi.userid", "u.username"), $joins, "af.eventid = '".$eventid."'");
			while($_row = Core::getDB()->fetch($result))
			{
				$name = $_row["name"];
				$invitation[] = $_row;
			}
			if(count($invitation) <= 0)
			{
				Core::getQuery()->insert("attack_formation", array("eventid", "time"), array($eventid, $row["time"]));
				Core::getQuery()->update("events", "mode", 12, "eventid = '".$eventid."'");
				$name = $eventid;
				Core::getQuery()->insert("formation_invitation", array("eventid", "userid"), array($eventid, Core::getUser()->get("userid")));
				$invitation[0]["userid"] = Core::getUser()->get("userid");
				$invitation[0]["username"] = Core::getUser()->get("username");
			}
			Core::getTPL()->addLoop("invitation", $invitation);
			Core::getTPL()->assign("formationName", $name);
			Core::getDB()->free_result($result);
			Core::getTPL()->display("mission_formation");
			Bengine::unlock();
			exit;
		}
		return $this;
	}

	/**
	 * Executes an invitation.
	 *
	 * @param integer	Event id
	 * @param string	Formation name
	 * @param string	Invited username
	 *
	 * @return Bengine_Page_Mission
	 */
	protected function invite($eventid, $name, $username)
	{
		$result = Core::getQuery()->select("events", "time", "", "(mode = '10' OR mode = '12') AND user = '".Core::getUser()->get("userid")."' AND eventid = '".$eventid."'");
		if($row = Core::getDB()->fetch($result))
		{
			Core::getDB()->free_result($result);
			$error = "";
			$time = $row["time"];
			$result = Core::getQuery()->select("user u", array("u.userid", "u2a.aid"), "LEFT JOIN ".PREFIX."user2ally u2a ON (u2a.userid = u.userid)", "u.username = '".$username."'");
			$row = Core::getDB()->fetch($result);
			$userid = $row["userid"];
			$aid = $row["aid"];
			$Relation = new Bengine_User_Relation(Core::getUser()->get("userid"), Core::getUser()->get("aid"));
			if(!$Relation->hasRelation($userid, $aid))
			{
				$error[] = "UNABLE_TO_INVITE_USER";
			}
			unset($Relation);
			if(Str::length($name) > 0 && Str::length($name) <= 128)
			{
				$name = Str::validateXHTML($name);
				Core::getQuery()->update("attack_formation", array("name"), array($name), "eventid = '".$eventid."'");
			}
			else
			{
				$error[] = "ENTER_FORMATION_NAME";
			}
			if(empty($error))
			{
				Core::getQuery()->insert("formation_invitation", array("eventid", "userid"), array($eventid, $userid));
			}
			else
			{
				foreach($error as $error)
				{
					Logger::addMessage($error);
				}
			}
		}
		Core::getDB()->free_result($result);
		$this->formation($eventid);
		return $this;
	}

	/**
	 * Executes star gate jump and update shipyard informations.
	 *
	 * @param integer	Target moon id
	 *
	 * @return Bengine_Page_Mission
	 */
	protected function executeJump($moonid)
	{
		$result = Core::getQuery()->select("temp_fleet", "data", "", "planetid = '".Core::getUser()->get("curplanet")."'");
		if($temp = Core::getDB()->fetch($result))
		{
			Core::getDB()->free_result($result);
			$temp = unserialize($temp["data"]);
			Hook::event("ExecuteStarGateJump", array(&$temp));
			if(!in_array($moonid, $temp["moons"]))
			{
				throw new Recipe_Exception_Generic("Unkown moon.");
			}

			foreach($temp["ships"] as $key => $value)
			{
				$_result = Core::getQuery()->select("unit2shipyard", array("quantity"), "", "unitid = '".$key."' AND planetid = '".Core::getUser()->get("curplanet")."'");
				$availQty = Core::getDB()->fetch_field($_result, "quantity");
				Core::getDB()->free_result($_result);

				if($availQty < $value["quantity"])
				{
					throw new Recipe_Exception_Generic("Sorry, you have been attacked. Process stopped.");
				}
				Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity - '".$value["quantity"]."' WHERE unitid = '".$key."' AND planetid = '".Core::getUser()->get("curplanet")."'");
				$shipyard = Bengine::getCollection("fleet");
				$shipyard->addPlanetFilter($moonid);
				$shipyard->getSelect()->where("u2s.unitid = ?", $key);
				if($shipyard->count() > 0)
				{
					Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity + '{$value["quantity"]}' WHERE planetid = '{$moonid}' AND unitid = '{$key}'");
				}
				else
				{
					Core::getQuery()->insert("unit2shipyard", array("unitid", "planetid", "quantity"), array($key, $moonid, $value["quantity"]));
				}
			}

			Core::getQuery()->delete("unit2shipyard", "quantity = '0'");
			Core::getQuery()->insert("stargate_jump", array("planetid", "time", "data"), array(Core::getUser()->get("curplanet"), TIME, serialize($temp)));
			Core::getQuery()->delete("temp_fleet", "planetid = '".Core::getUser()->get("curplanet")."'");
			Logger::addMessage("JUMP_SUCCESSFUL", "success");
		}
		return $this;
	}

	/**
	 * Select the ships for jump.
	 *
	 * @param array		Ships for jump
	 *
	 * @return Bengine_Page_Mission
	 */
	protected function starGateJump($ships)
	{
		$data = array();
		Core::getQuery()->delete("temp_fleet", "planetid = '".Core::getUser()->get("curplanet")."'");
		$select = array("u2s.unitid", "u2s.quantity", "d.capicity", "d.speed", "d.consume", "b.name");
		$joins  = "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = u2s.unitid)";
		$joins .= "LEFT JOIN ".PREFIX."ship_datasheet d ON (d.unitid = u2s.unitid)";
		$result = Core::getQuery()->select("unit2shipyard u2s", $select, $joins, "b.mode = '3' AND u2s.planetid = '".Core::getUser()->get("curplanet")."'");
		while($row = Core::getDB()->fetch($result))
		{
			$id = $row["unitid"];
			if(isset($ships[$id]))
			{
				$quantity = _pos($ships[$id]);
				if($quantity > $row["quantity"])
				{
					$quantity = $row["quantity"];
				}
				if($quantity > 0)
				{
					$data["ships"][$id]["quantity"] = $quantity;
					$data["ships"][$id]["name"] = $row["name"];
				}
			}
		}
		Core::getDB()->free_result($result);
		$result = Core::getQuery()->select("stargate_jump", "time", "", "planetid = '".Core::getUser()->get("curplanet")."'", "time DESC");
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		$requiredReloadTime = ((int) Core::getConfig()->get("STAR_GATE_RELOAD_TIME")) * 60;
		$reloadTime = $row["time"] + $requiredReloadTime;
		if(count($data) > 0 && $reloadTime < TIME)
		{
			$moons = array();
			$select = new Recipe_Database_Select();
			$select->from(array("p" => "planet"))
				->attributes(array(
					"p" => array("planetid", "planetname"),
					"g" => array("galaxy", "system", "position"),
					"sj" => array("time")
				));
			$select->join(array("b2p" => "building2planet"), array("b2p" => "planetid", "p" => "planetid"))
				->join(array("g" => "galaxy"), array("g" => "moonid", "p" => "planetid"))
				->join(array("sj" => "stargate_jump"), array("sj" => "planetid", "p" => "planetid"))
				->where(array("p" => "userid"), Core::getUser()->get("userid"))
				->where(array("p" => "ismoon"), 1)
				->where(array("p" => "planetid", "!="), Core::getUser()->get("curplanet"))
				->where(array("b2p" => "buildingid"), 56)
				->where("IFNULL(sj.time, 0) + ? < UNIX_TIMESTAMP()", $requiredReloadTime)
				->group(array("p" => "planetid"))
				->order(array("g" => "galaxy"))->order(array("g" => "system"))->order(array("g" => "position"));
			$result = $select->getResource();
			while($row = Core::getDB()->fetch($result))
			{
				$moons[] = $row;
				$data["moons"][] = $row["planetid"];
			}
			Core::getDB()->free_result($result);
			Hook::event("ShowStarGates", array(&$moons, &$data));
			Core::getTPL()->addLoop("moons", $moons);
			$data = serialize($data);
			Core::getQuery()->insert("temp_fleet", array("planetid", "data"), array(Core::getUser()->get("curplanet"), $data));
			Core::getTPL()->display("mission_stargatejump");
			Bengine::unlock();
			exit;
		}
		else
		{
			if($reloadTime < TIME)
			{
				$reloadTime = TIME;
			}
			Core::getLanguage()->assign("reloadTime", fNumber(($reloadTime - TIME) / 60), 2);
			Logger::addMessage("RELOADING_STAR_GATE");
		}
		return $this;
	}
}
?>