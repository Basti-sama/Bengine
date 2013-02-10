<?php
/**
 * Main page, contains overview and planet preferences.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Main.php 42 2011-07-24 11:03:09Z secretchampion $
 */

class Bengine_Game_Controller_Index extends Bengine_Game_Controller_Abstract
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
	 * @return Bengine_Game_Controller_Index
	 */
	protected function init()
	{
		Core::getLanguage()->load(array("Main", "info", "Galaxy"));
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_Index
	 */
	protected function indexAction()
	{
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.countdown.js", "js");
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.news.js", "js");
		$this->buildingEvent = Game::getEH()->getCurPlanetBuildingEvent();

		// Messages
		$result = Core::getQuery()->select("message", "msgid", "", Core::getDB()->quoteInto("`receiver` = ? AND `read` = '0'", Core::getUser()->get("userid")));
		$msgs = $result->rowCount();
		$result->closeCursor();
		Core::getTPL()->assign("unreadmsg", $msgs);
		if($msgs == 1) { Core::getTPL()->assign("newMessages", Link::get("game/".SID."/MSG", Core::getLanguage()->getItem("F_NEW_MESSAGE"))); }
		else if($msgs > 1)  { Core::getTPL()->assign("newMessages", Link::get("game/".SID."/MSG", sprintf(Core::getLanguage()->getItem("F_NEW_MESSAGES"), $msgs))); }

		// Fleet events
		$fleetEvent = Game::getEH()->getFleetEvents();
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
		$planetAction = Link::get("game/".SID."/Constructions", $planetAction).$timer;
		Core::getTPL()->assign("planetAction", $planetAction); unset($planetAction);
		Core::getTPL()->assign("occupiedFields", Game::getPlanet()->getFields(true));
		Core::getTPL()->assign("planetImage", Image::getImage("planets/".Game::getPlanet()->getData("picture").Core::getConfig()->get("PLANET_IMG_EXT"), Game::getPlanet()->getData("planetname"), "200px", "200px"));
		Core::getTPL()->assign("freeFields", Game::getPlanet()->getMaxFields());
		Core::getTPL()->assign("planetDiameter", fNumber(Game::getPlanet()->getData("diameter")));
		Core::getTPL()->assign("planetNameLink", Link::get("game/".SID."/Index/PlanetOptions", Game::getPlanet()->getData("planetname")));
		Core::getTPL()->assign("planetPosition", Game::getPlanet()->getCoords());
		Core::getTPL()->assign("planetTemp", Game::getPlanet()->getData("temperature"));
		Core::getTPL()->assign("points", Link::get("game/".SID."/Ranking", fNumber(floor(Core::getUser()->get("points")))));

		// Points
		$result = Core::getQuery()->select("user", "userid");
		Core::getLang()->assign("totalUsers", fNumber($result->rowCount()));
		$result->closeCursor();
		$where = Core::getDB()->quoteInto("(`username` < ? AND `points` >= {points}) OR `points` > {points}", array(
			Core::getUser()->get("username"),
		));
		$where = str_replace("{points}", (float) Core::getUser()->get("points"), $where);
		$result = Core::getQuery()->select("user", array("COUNT(`userid`)+1 AS rank"), "", $where, "", 1);
		Core::getLang()->assign("rank", fNumber($result->fetchColumn()));
		$result->closeCursor();

		if(Game::getPlanet()->getData("moonid") > 0)
		{
			if(Game::getPlanet()->getData("ismoon"))
			{
				// Planet has moon
				$where = Core::getDB()->quoteInto("g.galaxy = ? AND g.system = ? AND g.position = ?", array(
					Game::getPlanet()->getData("moongala"), Game::getPlanet()->getData("moonsys"), Game::getPlanet()->getData("moonpos")
				));
				$result = Core::getQuery()->select("galaxy g", array("p.planetid", "p.planetname", "p.picture"), "LEFT JOIN ".PREFIX."planet p ON (p.planetid = g.planetid)", $where);
			}
			else
			{
				// Planet of current moon
				$where = Core::getDB()->quoteInto("g.galaxy = ? AND g.system = ? AND g.position = ?", array(
					Game::getPlanet()->getData("galaxy"), Game::getPlanet()->getData("system"), Game::getPlanet()->getData("position")
				));
				$result = Core::getQuery()->select("galaxy g", array("p.planetid", "p.planetname", "p.picture"), "LEFT JOIN ".PREFIX."planet p ON (p.planetid = g.moonid)", $where);
			}
			$row = $result->fetchRow();
			$result->closeCursor();
			Core::getTPL()->assign("moon", $row["planetname"]);
			$img = Image::getImage("planets/".$row["picture"].Core::getConfig()->get("PLANET_IMG_EXT"), $row["planetname"], 50, 50);
			Core::getTPL()->assign("moonImage", "<a title=\"".$row["planetname"]."\" class=\"goto pointer\" href=\"".$row["planetid"]."\">".$img."</a>");
		}
		else
		{
			Core::getTPL()->assign("moon", "");
			Core::getTPL()->assign("moonImage", "");
		}

		// Current events
		$research = Game::getEH()->getResearchEvent();
		Core::getTPL()->assign("research", $research);
		$result = Core::getQuery()->select("shipyard s", array("s.quantity", "s.one", "s.time", "s.finished", "b.name"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = s.unitid)", Core::getDB()->quoteInto("s.planetid = ?", Core::getUser()->get("curplanet")), "s.time ASC");
		$shipyardMissions = array();
		foreach($result->fetchAll() as $row)
		{
			if($row["finished"] > TIME)
			{
				$quantity = ($row["time"] < TIME) ? ceil(($row["finished"] - TIME) / $row["one"]) : $row["quantity"];
				$shipyardMissions[] = array(
					"time_left" => $row["finished"] - TIME,
					"time_finished" => $row["finished"],
					"quantity" => $quantity,
					"mission" => Core::getLanguage()->getItem($row["name"])
				);
			}
		}
		Core::getTemplate()->assign("shipyardMissions", $shipyardMissions);

		/* @var Bengine_Game_Model_Collection_News $news */
		$news = Game::getCollection("game/news");
		$news->addSortIndexOrder()
			->addEnabledFilter()
			->addLanguageFilter();
		Core::getTPL()->addLoop("news", $news);

		return $this;
	}

	/**
	 * Shows form for planet options.
	 *
	 * @return Bengine_Game_Controller_Index
	 */
	protected function planetOptionsAction()
	{
		if($this->isPost() && $this->getParam("changeplanetoptions"))
		{
			$this->changePlanetOptions($this->getParam("planetname"), $this->getParam("abandon"), $this->getParam("password"));
		}
		Core::getTPL()->assign("position", Game::getPlanet()->getCoords());
		Core::getTPL()->assign("planetName", Game::getPlanet()->getData("planetname"));
		Hook::event("SHOW_PLANET_OPTIONS");
		return $this;
	}

	/**
	 * Shows form for planet options.
	 *
	 * @param string $planetname
	 * @param boolean $abandon
	 * @param string $password
	 *
	 * @return Bengine_Game_Controller_Index
	 */
	protected function changePlanetOptions($planetname, $abandon, $password)
	{
		$planetname = trim($planetname);
		Hook::event("SAVE_PLANET_OPTIONS", array(&$planetname, &$abandon));
		if($abandon == 1)
		{
			$ok = true;
			if(Game::getEH()->getPlanetFleetEvents())
			{
				Logger::addMessage("CANNOT_DELETE_PLANET");
				$ok = false;
			}
			if(Core::getUser()->get("hp") == Core::getUser()->get("curplanet"))
			{
				Logger::addMessage("CANNOT_DELETE_HOMEPLANET");
				$ok = false;
			}
			$result = Core::getQuery()->select("password", "password", "", Core::getDB()->quoteInto("userid = ?", Core::getUser()->get("userid")));
			$row = $result->fetchRow();
			$result->closeCursor();
			$encryption = Core::getOptions("USE_PASSWORD_SALT") ? "md5_salt" : "md5";
			$password = Str::encode($password, $encryption);
			if(!Str::compare($row["password"], $password))
			{
				Logger::addMessage("WRONG_PASSWORD");
				$ok = false;
			}
			if($ok)
			{
				deletePlanet(Game::getPlanet()->getPlanetId(), Core::getUser()->get("userid"), Game::getPlanet()->getData("ismoon"));
				Core::getQuery()->update("user", array("curplanet" => Core::getUser()->get("hp")), "userid = ?", array(Core::getUser()->get("userid")));
				Core::getUser()->rebuild();
				$this->redirect("game/".SID."/Index");
			}
		}
		else if(checkCharacters($planetname))
		{
			Core::getQuery()->update("planet", array("planetname" => $planetname), "planetid = ?", array(Core::getUser()->get("curplanet")));
			$this->redirect("game/".SID."/Index");
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
	 * @param Bengine_Game_Model_Event $f
	 *
	 * @return array	Parsed event data
	 */
	protected function parseEvent(Bengine_Game_Model_Event $f)
	{
		if($f->getCode() == "game/alliedFleet" && $f->getUserid() != Core::getUser()->get("userid"))
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
		Core::getLanguage()->assign("target", ($f->getCode() != "game/recycling") ? $f->getDestinationPlanetname() : Core::getLanguage()->getItem("DEBRIS"));
		Core::getLanguage()->assign("targetcoords", $f->getDestinationCoords());
		Core::getLanguage()->assign("metal", fNumber($f->getData("metal", 0)));
		Core::getLanguage()->assign("silicon", fNumber($f->getData("silicon", 0)));
		Core::getLanguage()->assign("hydrogen", fNumber($f->getData("hydrogen", 0)));
		Core::getLanguage()->assign("username", $f->getUsername());
		Core::getLanguage()->assign("message", Link::get("game/".SID."/MSG/Write/".rawurlencode($f->getUsername()), Image::getImage("pm.gif", Core::getLanguage()->getItem("WRITE_MESSAGE"))));
		Core::getLanguage()->assign("mission", ($f->getCode() == "game/return") ? $f->getOrgModeName() : $f->getModeName());

		Core::getLanguage()->assign("fleet", $f->getFleetString());

		$event["class"] = $f->getCssClass();
		if($f->getCode() == "game/allianceAttack")
		{
			if($f->getUserid() == Core::getUser()->get("userid"))
			{
				$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_OWN");
			}
			else
			{
				$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_OTHER");
			}
			$allyFleets = Game::getEH()->getFormationFleets($f->getEventid());
			foreach($allyFleets as $af)
			{
				$coords = $af->getPlanetCoords();
				$msg = Core::getLanguage()->getItem("FLEET_MESSAGE_FORMATION");
				$msg = sprintf($msg, $af->getUsername(), $af->getPlanetname(), $coords, $af->getFleetString());
				$event["message"] .= $msg;
			}
		}
		else if($f->getCode() == "game/alliedFleet")
		{
			$mainFleet = Game::getModel("game/event")->load($f->getParentId());
			if($mainFleet->getUsierid() == Core::getUser()->get("userid"))
			{
				return false;
			}
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_OWN");
			$allyFleets = Game::getEH()->getFormationFleets($mainFleet->getEventid());
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
		else if($f->getCode() == "game/holding" && $f->getUserid() == Core::getUser()->get("userid"))
		{
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_HOLDING_1");
		}
		else if($f->getCode() == "game/holding")
		{
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_HOLDING_2");
		}
		else if($f->getCode() == "game/return" && $f->getUserid() == Core::getUser()->get("userid"))
		{
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_RETURN");
		}
		else if($f->getUserid() == Core::getUser()->get("userid") && $f->getCode() == "game/missileAttack")
		{
			$event["message"] = Core::getLanguage()->getItem("FLEET_MESSAGE_ROCKET_ATTACK");
		}
		else if($f->getCode() == "game/missileAttack")
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