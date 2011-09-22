<?php
/**
 * Main page, contains overview and planet preferences.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Main.php 42 2011-07-24 11:03:09Z secretchampion $
 */

class Bengine_Page_Main extends Bengine_Page_Abstract
{
	/**
	 * Holds building events for this user.
	 *
	 * @var array
	 */
	protected $buildingEvent = array();

	/**
	 * Holds all fleet events (own events and other events heading to user's planets)
	 *
	 * @var array
	 */
	protected $fleetEvent = array();

	/**
	 * Shows account and planet overview.
	 *
	 * @return Bengine_Page_Main
	 */
	protected function init()
	{
		Core::getLanguage()->load(array("Main", "info"));
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Page_Main
	 */
	protected function indexAction()
	{
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.countdown.js", "js");
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.news.js", "js");
		$this->buildingEvent = Bengine::getEH()->getCurPlanetBuildingEvent();

		// Messages
		$result = Core::getQuery()->select("message", "msgid", "", "`receiver` = '".Core::getUser()->get("userid")."' AND `read` = '0'");
		$msgs = Core::getDB()->num_rows($result);
		Core::getDB()->free_result($result);
		Core::getTPL()->assign("unreadmsg", $msgs);
		if($msgs == 1) { Core::getTPL()->assign("newMessages", Link::get("game.php/".SID."/MSG", Core::getLanguage()->getItem("F_NEW_MESSAGE"))); }
		else if($msgs > 1)  { Core::getTPL()->assign("newMessages", Link::get("game.php/".SID."/MSG", sprintf(Core::getLanguage()->getItem("F_NEW_MESSAGES"), $msgs))); }

		// Fleet events
		$fleetEvent = Bengine::getEH()->getFleetEvents();
		$fe = array();
		if($fleetEvent)
		{
			foreach($fleetEvent as $f)
			{
				$fe[$f["eventid"]] = $this->parseEvent($f);
				if(!is_array($fe[$f["eventid"]]))
				{
					unset($fe[$f["eventid"]]);
				}
			}
			Hook::event("MainFleetEventsOutput", array(&$fe));
		}
		Core::getTPL()->addLoop("fleetEvents", $fe);

		Core::getTPL()->assign("serverTime", Date::timeToString(1, TIME, "", false));
		$planetAction = Core::getLanguage()->getItem(($this->buildingEvent) ? $this->buildingEvent->getData("buildingname") : "PLANET_FREE");
		if($this->buildingEvent)
		{
			$timeleft = $this->buildingEvent->get("time") - TIME;
			$timeFinished = Date::timeToString(1, $this->buildingEvent->get("time"));
			$abort = "";
			$timer = "<script type=\"text/javascript\">
			//<![CDATA[
			$(function () {
				$('#bCountDown').countdown({until: ".$timeleft.", description: '<span class=\"finish-time\">".$timeFinished."</span>', compact: true, onExpiry: function() {
					$('#bCountDown').text('-');
				}});
			});
			//]]>
			</script>
			<span id=\"bCountDown\">".getTimeTerm($timeleft)."<br />".$abort."</span>";
		}
		else { $timer = ""; }
		$planetAction = Link::get("game.php/".SID."/Constructions", $planetAction).$timer;
		Core::getTPL()->assign("planetAction", $planetAction); unset($planetAction);
		Core::getTPL()->assign("occupiedFields", Bengine::getPlanet()->getFields(true));
		Core::getTPL()->assign("planetImage", Image::getImage("planets/".Bengine::getPlanet()->getData("picture").Core::getConfig()->get("PLANET_IMG_EXT"), Bengine::getPlanet()->getData("planetname"), "200px", "200px"));
		Core::getTPL()->assign("freeFields", Bengine::getPlanet()->getMaxFields());
		Core::getTPL()->assign("planetDiameter", fNumber(Bengine::getPlanet()->getData("diameter")));
		Core::getTPL()->assign("planetNameLink", Link::get("game.php/".SID."/Main/PlanetOptions", Bengine::getPlanet()->getData("planetname")));
		Core::getTPL()->assign("planetPosition", Bengine::getPlanet()->getCoords());
		Core::getTPL()->assign("planetTemp", Bengine::getPlanet()->getData("temperature"));
		Core::getTPL()->assign("points", Link::get("game.php/".SID."/Ranking", fNumber(floor(Core::getUser()->get("points")))));

		// Points
		$result = Core::getQuery()->select("user", "userid");
		Core::getLang()->assign("totalUsers", fNumber(Core::getDB()->num_rows($result)));
		Core::getDB()->free_result($result);
		$result = Core::getQuery()->select("user", array("COUNT(`userid`)+1 AS rank"), "", "(`username` < '".Core::getUser()->get("username")."' AND `points` >= ".Core::getUser()->get("points").") OR `points` > ".Core::getUser()->get("points"), "", 1);
		Core::getLang()->assign("rank", fNumber(Core::getDB()->fetch_field($result, "rank")));
		Core::getDB()->free_result($result);

		if(Bengine::getPlanet()->getData("moonid") > 0)
		{
			if(Bengine::getPlanet()->getData("ismoon"))
			{
				// Planet has moon
				$result = Core::getQuery()->select("galaxy g", array("p.planetid", "p.planetname", "p.picture"), "LEFT JOIN ".PREFIX."planet p ON (p.planetid = g.planetid)", "g.galaxy = '".Bengine::getPlanet()->getData("moongala")."' AND g.system = '".Bengine::getPlanet()->getData("moonsys")."' AND g.position = '".Bengine::getPlanet()->getData("moonpos")."'");
			}
			else
			{
				// Planet of current moon
				$result = Core::getQuery()->select("galaxy g", array("p.planetid", "p.planetname", "p.picture"), "LEFT JOIN ".PREFIX."planet p ON (p.planetid = g.moonid)", "g.galaxy = '".Bengine::getPlanet()->getData("galaxy")."' AND g.system = '".Bengine::getPlanet()->getData("system")."' AND g.position = '".Bengine::getPlanet()->getData("position")."'");
			}
			$row = Core::getDB()->fetch($result);
			Core::getDB()->free_result($result);
			Core::getTPL()->assign("moon", $row["planetname"]);
			$img = Image::getImage("planets/".$row["picture"].Core::getConfig()->get("PLANET_IMG_EXT"), $row["planetname"], 50, 50);
			Core::getTPL()->assign("moonImage", "<a title=\"".$row["planetname"]."\" class=\"goto pointer\" rel=\"".$row["planetid"]."\">".$img."</a>");
		}
		else
		{
			Core::getTPL()->assign("moon", "");
			Core::getTPL()->assign("moonImage", "");
		}

		// Current events
		$research = Bengine::getEH()->getResearchEvent();
		Core::getTPL()->assign("research", $research);
		$result = Core::getQuery()->select("shipyard s", array("s.quantity", "s.one", "s.time", "s.finished", "b.name"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = s.unitid)", "s.planetid = '".Core::getUser()->get("curplanet")."' ORDER BY s.time ASC");
		$shipyardMissions = array();
		while($row = Core::getDB()->fetch($result))
		{
			$quantity = ($row["time"] < TIME) ? ceil(($row["finished"] - TIME) / $row["one"]) : $row["quantity"];
			$shipyardMissions[] = array(
				"time_left" => $row["finished"] - TIME,
				"time_finished" => $row["finished"],
				"quantity" => $quantity,
				"mission" => Core::getLanguage()->getItem($row["name"])
			);
		}
		Core::getTemplate()->assign("shipyardMissions", $shipyardMissions);

		$news = Bengine::getCollection("news");
		$news->addSortIndexOrder()
			->addEnabledFilter()
			->addLanguageFilter();
		Core::getTPL()->addLoop("news", $news);

		return $this;
	}

	/**
	 * Shows form for planet options.
	 *
	 * @return Bengine_Page_Main
	 */
	protected function planetOptionsAction()
	{
		if($this->isPost() && $this->getParam("changeplanetoptions"))
		{
			$this->changePlanetOptions($this->getParam("planetname"), $this->getParam("abandon"), $this->getParam("password"));
		}
		Core::getTPL()->assign("position", Bengine::getPlanet()->getCoords());
		Core::getTPL()->assign("planetName", Bengine::getPlanet()->getData("planetname"));
		Hook::event("SHOW_PLANET_OPTIONS");
		return $this;
	}

	/**
	 * Shows form for planet options.
	 *
	 * @param array		_POST
	 *
	 * @return Bengine_Page_Main
	 */
	protected function changePlanetOptions($planetname, $abandon, $password)
	{
		$planetname = trim($planetname);
		Hook::event("SAVE_PLANET_OPTIONS", array(&$planetname, &$abandon));
		if($abandon == 1)
		{
			$ok = true;
			if(Bengine::getEH()->getPlanetFleetEvents())
			{
				Logger::addMessage("CANNOT_DELETE_PLANET");
				$ok = false;
			}
			if(Core::getUser()->get("hp") == Core::getUser()->get("curplanet"))
			{
				Logger::addMessage("CANNOT_DELETE_HOMEPLANET");
				$ok = false;
			}
			$result = Core::getQuery()->select("password", "password", "", "userid = '".Core::getUser()->get("userid")."'");
			$row = Core::getDB()->fetch($result);
			Core::getDB()->free_result($result);
			$encryption = Core::getOptions("USE_PASSWORD_SALT") ? "md5_salt" : "md5";
			$password = Str::encode($password, $encryption);
			if(!Str::compare($row["password"], $password))
			{
				Logger::addMessage("WRONG_PASSWORD");
				$ok = false;
			}
			if($ok)
			{
				deletePlanet(Bengine::getPlanet()->getPlanetId(), Core::getUser()->get("userid"), Bengine::getPlanet()->getData("ismoon"));
				Core::getQuery()->update("user", "curplanet", Core::getUser()->get("hp"), "userid = '".Core::getUser()->get("userid")."'");
				Core::getUser()->rebuild();
				$this->redirect("game.php/".SID."/Main");
			}
		}
		else if(checkCharacters($planetname))
		{
			Core::getQuery()->update("planet", "planetname", $planetname, "planetid = '".Core::getUser()->get("curplanet")."'");
			$this->redirect("game.php/".SID."/Main");
		}
		else
		{
			Logger::addMessage("INVALID_PLANET_NAME");
		}
		return $this;
	}

	/**
	 * Parses an event.
	 *
	 * @param Bengine_Model_Event		Event data
	 *
	 * @return array	Parsed event data
	 */
	protected function parseEvent(Bengine_Model_Event $f)
	{
		if($f->getCode() == "alliedFleet" && $f->getUserid() != Core::getUser()->get("userid"))
		{
			return false; // Hide foreign formations
		}
		$event = array();
		$event["time_r"] = $f->getTimeLeft();
		$event["time"] = $f->getFormattedTimeLeft();
		$event["eventid"] = $f->getEventid();
		$event["time_finished"] = Date::timeToString(1, $f->getTime());
		Core::getLanguage()->assign("rockets", $f->getData("rockets", 0));
		Core::getLanguage()->assign("planet", (!$f->getData("oldmode") || $f->getData("oldmode") != 9) ? $f->getPlanetname() : Core::getLanguage()->getItem("DEBRIS")); // TODO: Old mode should be translated to code
		Core::getLanguage()->assign("coords", $f->getPlanetCoords());
		Core::getLanguage()->assign("target", ($f->getCode() != "recycling") ? $f->getDestinationPlanetname() : Core::getLanguage()->getItem("DEBRIS"));
		Core::getLanguage()->assign("targetcoords", $f->getDestinationCoords());
		Core::getLanguage()->assign("metal", fNumber($f->getData("metal", 0)));
		Core::getLanguage()->assign("silicon", fNumber($f->getData("silicon", 0)));
		Core::getLanguage()->assign("hydrogen", fNumber($f->getData("hydrogen", 0)));
		Core::getLanguage()->assign("username", $f->getUsername());
		Core::getLanguage()->assign("message", Link::get("game.php/".SID."/MSG/Write/".rawurlencode($f->getUsername()), Image::getImage("pm.gif", Core::getLanguage()->getItem("WRITE_MESSAGE"))));
		Core::getLanguage()->assign("mission", ($f->getCode() == "return") ? $f->getOrgModeName() : $f->getModeName());

		Core::getLanguage()->assign("fleet", $f->getFleetString());

		$event["class"] = $f->getCssClass();
		if($f->getCode() == "allianceAttack")
		{
			if($f->getUserid() == Core::getUser()->get("userid"))
			{
				$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_OWN");
			}
			else
			{
				$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_OTHER");
			}
			$allyFleets = Bengine::getEH()->getFormationFleets($f->getEventid());
			foreach($allyFleets as $af)
			{
				$coords = $af->getPlanetCoords();
				$msg = Core::getLanguage()->getItem("FLEET_MESSAGE_FORMATION");
				$msg = sprintf($msg, $af->getUsername(), $af->getPlanetname(), $coords, $af->getFleetString());
				$event["message"] .= $msg;
			}
		}
		else if($f->getCode() == "alliedFleet")
		{
			$mainFleet = Bengine::getModel("event")->load($f->getParentId());
			if($mainFleet->getUsierid() == Core::getUser()->get("userid"))
			{
				return false;
			}
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_OWN");
			$allyFleets = Bengine::getEH()->getFormationFleets($mainFleet->getEventid());
			$allyFleets->add($mainFleet);
			foreach($allyFleets as $af)
			{
				if($af->getUserid() == Core::getUser()->get("userid"))
				{
					continue;
				}
				$coords = $af->getPlanetCoords();
				$msg = Core::getLanguage()->getItem("FLEET_MESSAGE_FORMATION");
				$msg = sprintf($msg, $af->getUsername(), $af->getPlanetname(), $coords, $af->getFleetString());
				$event["message"] .= $msg;
			}
		}
		else if($f->getCode() == "holding" && $f->getUserid() == Core::getUser()->get("userid"))
		{
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_HOLDING_1");
		}
		else if($f->getCode() == "holding")
		{
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_HOLDING_2");
		}
		else if($f->getCode() == "return" && $f->getUserid() == Core::getUser()->get("userid"))
		{
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_RETURN");
		}
		else if($f->getUserid() == Core::getUser()->get("userid") && $f->getCode() == "missileAttack")
		{
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_ROCKET_ATTACK");
		}
		else if($f->getCode() == "missileAttack")
		{
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_ROCKET_ATTACK_FOREIGN");
		}
		else if($f->getUserid() == Core::getUser()->get("userid"))
		{
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_OWN");
		}
		else
		{
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_OTHER");
		}
		return $event;
	}
}
?>