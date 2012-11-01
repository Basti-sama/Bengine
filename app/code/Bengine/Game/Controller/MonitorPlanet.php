<?php
/**
 * Star surveillance: show planet's events.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: MonitorPlanet.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_MonitorPlanet extends Bengine_Game_Controller_Abstract
{
	/**
	 * Target planet id.
	 *
	 * @var integer
	 */
	protected $planetid = 0;

	/**
	 * Target planet data.
	 *
	 * @var Bengine_Game_Model_Planet
	 */
	protected $planetData = null;

	/**
	 * Shows the fleet events of the stated planet.
	 * Firstly, check for validation (Range, consumption, ...)
	 *
	 * @return Bengine_Game_Controller_MonitorPlanet
	 */
	protected function init()
	{
		$this->planetid = Core::getRequest()->getGET("1");
		$this->planetData = Game::getModel("game/planet")->load($this->planetid);
		return parent::init();
	}

	/**
	 * Checks for validation.
	 *
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Game_Controller_MonitorPlanet
	 */
	protected function validate()
	{
		if(!$this->planetData->getPlanetid() || $this->planetData->getIsmoon() || Core::getUser()->get("curplanet") == $this->planetData->getPlanetid())
		{
			throw new Recipe_Exception_Generic("The selected planet is unavailable.");
		}

		// Check range
		if(Game::getPlanet()->getBuilding("STAR_SURVEILLANCE") <= 0)
		{
			throw new Recipe_Exception_Generic("Range exceeded.");
		}
		$range = pow(Game::getPlanet()->getBuilding("STAR_SURVEILLANCE"), 2) - 1;
		$diff = abs(Game::getPlanet()->getData("system") - $this->planetData->getSystem());
		if($this->planetData->getGalaxy() != Game::getPlanet()->getData("galaxy") || $range < $diff)
		{
			throw new Recipe_Exception_Generic("Range exceeded.");
		}

		// Check consumption
		Core::getLanguage()->load(array("Galaxy", "Main", "info"));
		if(Game::getPlanet()->getData("hydrogen") < Core::getOptions()->get("STAR_SURVEILLANCE_CONSUMPTION"))
		{
			Logger::dieMessage("DEFICIENT_CONSUMPTION");
		}
		return $this;
	}

	/**
	 * Subtracts hydrogen for surveillance consumption.
	 *
	 * @return Bengine_Game_Controller_MonitorPlanet
	 */
	protected function subtractHydrogen()
	{
		Core::getDB()->query("UPDATE ".PREFIX."planet SET hydrogen = hydrogen - '".Core::getOptions()->get("STAR_SURVEILLANCE_CONSUMPTION")."' WHERE planetid = '".Core::getUser()->get("curplanet")."'");
		return $this;
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_MonitorPlanet
	 */
	protected function indexAction()
	{
		$this->validate()->subtractHydrogen();

		Hook::event("StarSurveillanceStart", array($this->planetData));

		/* @var Bengine_Game_Model_Collection_Event $collection */
		$collection = Game::getCollection("game/event");
		$collection
			->addTimeFilter()
			->addPlanetFilter($this->planetid)
			->addBaseTypeFilter("fleet");
		Hook::event("StarSurveillanceEventsLoaded", array($collection));
		$events = array();
		/* @var Bengine_Game_Model_Event $row */
		foreach($collection as $row)
		{
			// Mission is missle attack >> hide
			if($row->getCode() == "missileAttack")
				continue;
			// Do not show mission on return fly from original planet
			if($row->getCode() == "return" && $this->planetData->getPlanetid() == $row->getPlanetid() && $row->getDestination() != $row->getPlanetid())
				continue;
			// Mission is recycling AND destination planet is the scanned planet >> hide
			if($row->getCode() == "recycling" && $row->getDestination() == $this->planetData->getPlanetid() && $row->getDestination() != $row->getPlanetid())
				continue;
			// Original mission is recycling AND original planet is the scanned planet >> hide
			if($row->getOrgMode() == 9 && $row->getPlanetid() == $this->planetData->getPlanetid() && $row->getDestination() != $row->getPlanetid())
				continue;
			// Hide return missions if destination is a moon (always)
			if($row->getCode() == "return" && $row->getDestinationIsmoon())
			 	continue;
			// Mission is position and on return >> hide
			if($row->getOrgMode() == 6)
				continue;

			Hook::event("StarSurveillanceEventItem", array($row));

			Core::getLanguage()->assign("planet", $row->getPlanetname());
			Core::getLanguage()->assign("coords", $row->getPlanetCoords());
			Core::getLanguage()->assign("target", ($row->getCode() != "recycling") ? $row->getDestinationPlanetname() : Core::getLanguage()->getItem("DEBRIS"));
			Core::getLanguage()->assign("targetcoords", $row->getDestinationCoords());
			Core::getLanguage()->assign("username", $row->getUsername());
			Core::getLanguage()->assign("mission", ($row->getCode() == "return") ? $row->getOrgModeName() : $row->getModeName());
			Core::getLanguage()->assign("fleet", $row->getFleetString());

			if($row->getCode() == "holding")
			{
				$message = Core::getLanguage()->getItem("FLEET_MESSAGE_HOLDING_2");
			}
			else if($row->getCode() == "return" && $row->getUserid() == $this->planetData->getUserid())
			{
				$message = Core::getLanguage()->getItem("STAR_SUR_MSG_RETURN");
			}
			else
			{
				$message = Core::getLanguage()->getItem("STAR_SUR_MSG");
			}

			$events[] = array(
				"eventid" => $row->getEventid(),
				"class" => $row->getCssClass(),
				"time" => $row->getFormattedTimeLeft(),
				"time_r" => $row->getTimeLeft(),
				"message" => $message,
			);
		}

		if(count($events) > 0) { Core::getLanguage()->load("info"); }
		Core::getTPL()->addLoop("events", $events);
		Core::getTPL()->assign("num_rows", count($events));

		// Assignments
		Core::getLanguage()->assign("target", $this->planetData["planetname"]);
		Core::getLanguage()->assign("targetuser", $this->planetData["username"]);
		Core::getLanguage()->assign("targetcoords", "[".$this->planetData["galaxy"].":".$this->planetData["system"].":".$this->planetData["position"]."]");

		$this->setIsAjax();
		return $this;
	}
}
?>