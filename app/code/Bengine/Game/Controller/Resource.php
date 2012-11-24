<?php
/**
 * Resources page. Shows resource production.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Resource.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_Resource extends Bengine_Game_Controller_Abstract
{
	/**
	 * Holds the building data.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Shows overview on resource production and options.
	 *
	 * @return Bengine_Game_Controller_Resource
	 */
	protected function init()
	{
		Core::getLanguage()->load("Resource,info,buildings");
		$this->loadBuildingData();
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_Resource
	 */
	protected function indexAction()
	{
		Hook::event("ShowResourcesBefore", array($this));
		Core::getTPL()->addLoop("data", $this->data);

		$productFactor = (double) Core::getConfig()->get("PRODUCTION_FACTOR");

		// Basic prod
		Core::getTPL()->assign("basicMetal", fNumber(Core::getOptions()->get("METAL_BASIC_PROD") * $productFactor));
		Core::getTPL()->assign("basicSilicon", fNumber(Core::getOptions()->get("SILICON_BASIC_PROD") * $productFactor));
		Core::getTPL()->assign("basicHydrogen", fNumber(Core::getOptions()->get("HYDROGEN_BASIC_PROD") * $productFactor));

		Core::getTPL()->assign("sats", Game::getPlanet()->getBuilding(Bengine_Game_Planet::SOLAR_SAT_ID));
		if(Game::getPlanet()->getBuilding(Bengine_Game_Planet::SOLAR_SAT_ID) > 0)
		{
			Core::getTPL()->assign("satsNum", fNumber(Game::getPlanet()->getBuilding(Bengine_Game_Planet::SOLAR_SAT_ID)));
			$solarSatProduction = Game::getPlanet()->getBuildingProd("energy", Bengine_Game_Planet::SOLAR_SAT_ID);
			Core::getTPL()->assign("satsProd", fNumber($solarSatProduction));
			Core::getLang()->assign("prodPerSat", floor($solarSatProduction / Game::getPlanet()->getBuilding(Bengine_Game_Planet::SOLAR_SAT_ID)));
			Core::getTPL()->assign("solar_satellite_prod", Game::getPlanet()->getData("solar_satellite_prod"));
		}

		// Storage capacity
		Core::getTPL()->assign("storageMetal", fNumber(Game::getPlanet()->getStorage("metal") / 1000)."k");
		Core::getTPL()->assign("storageSilicon", fNumber(Game::getPlanet()->getStorage("silicon") / 1000)."k");
		Core::getTPL()->assign("sotrageHydrogen", fNumber(Game::getPlanet()->getStorage("hydrogen") / 1000)."k");

		// Total prod
		Core::getTPL()->assign("totalMetal", fNumber(Game::getPlanet()->getProd("metal")));
		Core::getTPL()->assign("totalSilicon", fNumber(Game::getPlanet()->getProd("silicon")));
		Core::getTPL()->assign("totalHydrogen", fNumber(Game::getPlanet()->getProd("hydrogen")));
		Core::getTPL()->assign("totalEnergy", fNumber(Game::getPlanet()->getEnergy()));

		// Daily prod
		Core::getTPL()->assign("dailyMetal", fNumber(Game::getPlanet()->getProd("metal") * 24));
		Core::getTPL()->assign("dailySilicon", fNumber(Game::getPlanet()->getProd("silicon") * 24));
		Core::getTPL()->assign("dailyHydrogen", fNumber(Game::getPlanet()->getProd("hydrogen") * 24));

		// Weekly prod
		Core::getTPL()->assign("weeklyMetal", fNumber(Game::getPlanet()->getProd("metal") * 168));
		Core::getTPL()->assign("weeklySilicon", fNumber(Game::getPlanet()->getProd("silicon") * 168));
		Core::getTPL()->assign("weeklyHydrogen", fNumber(Game::getPlanet()->getProd("hydrogen") * 168));

		// Monthly prod
		Core::getTPL()->assign("monthlyMetal", fNumber(Game::getPlanet()->getProd("metal") * 720));
		Core::getTPL()->assign("monthlySilicon", fNumber(Game::getPlanet()->getProd("silicon") * 720));
		Core::getTPL()->assign("monthlyHydrogen", fNumber(Game::getPlanet()->getProd("hydrogen") * 720));

		$selectBox = "";
		for($i = 10; $i >= 0; $i--)
		{
			$selectBox .= createOption($i * 10, $i * 10, 0);
		}
		Core::getTPL()->assign("selectProd", $selectBox);
		Hook::event("ShowResourcesAfter");

		$this->assign("updateAction", BASE_URL."game/".SID."/Resource/Update");
		return $this;
	}

	/**
	 * Loads all production and consumption data of all available buildings.
	 *
	 * @return Bengine_Game_Controller_Resource
	 */
	protected function loadBuildingData()
	{
		$where = "c.prod_metal != '' OR c.prod_silicon != '' OR c.prod_hydrogen != '' OR c.prod_energy != '' OR c.cons_metal != '' OR c.cons_silicon != '' OR c.cons_hydrogen != '' OR c.cons_energy != ''";
		$result = Core::getQuery()->select("construction c", array("c.buildingid", "c.name"), "", $where, "c.display_order ASC, c.buildingid ASC");
		foreach($result->fetchAll() as $row)
		{
			$id = $row["buildingid"];
			$this->data[$id]["id"] = $id;
			$this->data[$id]["name"] = Core::getLang()->getItem($row["name"]);
			$this->data[$id]["level"] = Game::getPlanet()->getBuilding($id);
			$this->data[$id]["factor"] = Game::getPlanet()->getBuildingFactor($id);
			$this->data[$id]["metal"] = fNumber(Game::getPlanet()->getBuildingProd("metal", $id));
			$this->data[$id]["silicon"] = fNumber(Game::getPlanet()->getBuildingProd("silicon", $id));
			$this->data[$id]["hydrogen"] = fNumber(Game::getPlanet()->getBuildingProd("hydrogen", $id));
			$this->data[$id]["energy"] = fNumber(Game::getPlanet()->getBuildingProd("energy", $id));
			$this->data[$id]["metalCons"] = fNumber(Game::getPlanet()->getBuildingCons("metal", $id));
			$this->data[$id]["siliconCons"] = fNumber(Game::getPlanet()->getBuildingCons("silicon", $id));
			$this->data[$id]["hydrogenCons"] = fNumber(Game::getPlanet()->getBuildingCons("hydrogen", $id));
			$this->data[$id]["energyCons"] = fNumber(Game::getPlanet()->getBuildingCons("energy", $id));
		}
		return $this;
	}

	/**
	 * Saves resource production factor.
	 *
	 * @return Bengine_Game_Controller_Resource
	 */
	protected function updateAction()
	{
		if(!Core::getUser()->get("umode"))
		{
			$post = Core::getRequest()->getPOST();
			Hook::event("SaveResources");
			foreach($this->data as $key => $value)
			{
				if($value["level"] > 0)
				{
					$factor = abs((isset($post[$key])) ? $post[$key] : 100);
					$factor = ($factor > 100) ? 100 : $factor;
					Core::getQuery()->updateSet("building2planet", array("prod_factor" => $factor), "buildingid = '".$key."' AND planetid = '".Core::getUser()->get("curplanet")."'");
				}
			}

			if(Game::getPlanet()->getBuilding(Bengine_Game_Planet::SOLAR_SAT_ID) > 0)
			{
				$satelliteProd = abs($post[39]);
				$satelliteProd = ($satelliteProd > 100) ? 100 : $satelliteProd;
				Core::getQuery()->updateSet("planet", array("solar_satellite_prod" => $satelliteProd), "planetid = '".Core::getUser()->get("curplanet")."'");
			}
		}
		$this->redirect("game/".SID."/Resource");
		return $this;
	}
}
?>