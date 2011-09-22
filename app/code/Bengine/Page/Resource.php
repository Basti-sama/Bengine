<?php
/**
 * Resources page. Shows resource production.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Resource.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Page_Resource extends Bengine_Page_Abstract
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
	 * @return Bengine_Page_Resource
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
	 * @return Bengine_Page_Resource
	 */
	protected function indexAction()
	{
		Hook::event("ShowResourcesBefore", array($this));
		Core::getTPL()->addLoop("data", $this->data);

		// Basic prod
		Core::getTPL()->assign("basicMetal", fNumber(Core::getOptions()->get("METAL_BASIC_PROD")));
		Core::getTPL()->assign("basicSilicon", fNumber(Core::getOptions()->get("SILICON_BASIC_PROD")));
		Core::getTPL()->assign("basicHydrogen", fNumber(Core::getOptions()->get("HYDROGEN_BASIC_PROD")));

		Core::getTPL()->assign("sats", Bengine::getPlanet()->getBuilding(Bengine_Planet::SOLAR_SAT_ID));
		if(Bengine::getPlanet()->getBuilding(Bengine_Planet::SOLAR_SAT_ID) > 0)
		{
			Core::getTPL()->assign("satsNum", fNumber(Bengine::getPlanet()->getBuilding(Bengine_Planet::SOLAR_SAT_ID)));
			Core::getTPL()->assign("satsProd", fNumber(Bengine::getPlanet()->getBuildingProd("energy", 39)));
			Core::getLang()->assign("prodPerSat", floor(Bengine::getPlanet()->getBuildingProd("energy", 39) / Bengine::getPlanet()->getBuilding(Bengine_Planet::SOLAR_SAT_ID)));
			Core::getTPL()->assign("solar_satellite_prod", Bengine::getPlanet()->getData("solar_satellite_prod"));
		}

		// Storage capicity
		Core::getTPL()->assign("storageMetal", fNumber(Bengine::getPlanet()->getStorage("metal") / 1000)."k");
		Core::getTPL()->assign("storageSilicon", fNumber(Bengine::getPlanet()->getStorage("silicon") / 1000)."k");
		Core::getTPL()->assign("sotrageHydrogen", fNumber(Bengine::getPlanet()->getStorage("hydrogen") / 1000)."k");

		// Total prod
		Core::getTPL()->assign("totalMetal", fNumber(Bengine::getPlanet()->getProd("metal")));
		Core::getTPL()->assign("totalSilicon", fNumber(Bengine::getPlanet()->getProd("silicon")));
		Core::getTPL()->assign("totalHydrogen", fNumber(Bengine::getPlanet()->getProd("hydrogen")));
		Core::getTPL()->assign("totalEnergy", fNumber(Bengine::getPlanet()->getEnergy()));

		// Daily prod
		Core::getTPL()->assign("dailyMetal", fNumber(Bengine::getPlanet()->getProd("metal") * 24));
		Core::getTPL()->assign("dailySilicon", fNumber(Bengine::getPlanet()->getProd("silicon") * 24));
		Core::getTPL()->assign("dailyHydrogen", fNumber(Bengine::getPlanet()->getProd("hydrogen") * 24));

		// Weekly prod
		Core::getTPL()->assign("weeklyMetal", fNumber(Bengine::getPlanet()->getProd("metal") * 168));
		Core::getTPL()->assign("weeklySilicon", fNumber(Bengine::getPlanet()->getProd("silicon") * 168));
		Core::getTPL()->assign("weeklyHydrogen", fNumber(Bengine::getPlanet()->getProd("hydrogen") * 168));

		// Monthly prod
		Core::getTPL()->assign("monthlyMetal", fNumber(Bengine::getPlanet()->getProd("metal") * 720));
		Core::getTPL()->assign("monthlySilicon", fNumber(Bengine::getPlanet()->getProd("silicon") * 720));
		Core::getTPL()->assign("monthlyHydrogen", fNumber(Bengine::getPlanet()->getProd("hydrogen") * 720));

		$selectbox = "";
		for($i = 10; $i >= 0; $i--)
		{
			$selectbox .= createOption($i * 10, $i * 10, 0);
		}
		Core::getTPL()->assign("selectProd", $selectbox);
		Hook::event("ShowResourcesAfter");

		$this->assign("updateAction", BASE_URL."game.php/".SID."/Resource/Update");
		return $this;
	}

	/**
	 * Loads all production and consumption data of all available buildings.
	 *
	 * @return Bengine_Page_Resource
	 */
	protected function loadBuildingData()
	{
		$where = "c.prod_metal != '' OR c.prod_silicon != '' OR c.prod_hydrogen != '' OR c.prod_energy != '' OR c.cons_metal != '' OR c.cons_silicon != '' OR c.cons_hydrogen != '' OR c.cons_energy != ''";
		$result = Core::getQuery()->select("construction c", array("c.buildingid", "c.name"), "", $where, "c.display_order ASC, c.buildingid ASC");
		while($row = Core::getDB()->fetch($result))
		{
			$id = $row["buildingid"];
			$this->data[$id]["id"] = $id;
			$this->data[$id]["name"] = Core::getLang()->getItem($row["name"]);
			$this->data[$id]["level"] = Bengine::getPlanet()->getBuilding($id);
			$this->data[$id]["factor"] = Bengine::getPlanet()->getBuildingFactor($id);
			$this->data[$id]["metal"] = fNumber(Bengine::getPlanet()->getBuildingProd("metal", $id));
			$this->data[$id]["silicon"] = fNumber(Bengine::getPlanet()->getBuildingProd("silicon", $id));
			$this->data[$id]["hydrogen"] = fNumber(Bengine::getPlanet()->getBuildingProd("hydrogen", $id));
			$this->data[$id]["energy"] = fNumber(Bengine::getPlanet()->getBuildingProd("energy", $id));
			$this->data[$id]["metalCons"] = fNumber(Bengine::getPlanet()->getBuildingCons("metal", $id));
			$this->data[$id]["siliconCons"] = fNumber(Bengine::getPlanet()->getBuildingCons("silicon", $id));
			$this->data[$id]["hydrogenCons"] = fNumber(Bengine::getPlanet()->getBuildingCons("hydrogen", $id));
			$this->data[$id]["energyCons"] = fNumber(Bengine::getPlanet()->getBuildingCons("energy", $id));
		}
		return $this;
	}

	/**
	 * Saves resource production factor.
	 *
	 * @return Bengine_Page_Resource
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
					Core::getQuery()->update("building2planet", array("prod_factor"), array($factor), "buildingid = '".$key."' AND planetid = '".Core::getUser()->get("curplanet")."'");
				}
			}

			if(Bengine::getPlanet()->getBuilding(Bengine_Planet::SOLAR_SAT_ID) > 0)
			{
				$satelliteProd = abs($post[39]);
				$satelliteProd = ($satelliteProd > 100) ? 100 : $satelliteProd;
				Core::getQuery()->update("planet", array("solar_satellite_prod"), array($satelliteProd), "planetid = '".Core::getUser()->get("curplanet")."'");
			}
		}
		$this->redirect("game.php/".SID."/Resource");
		return $this;
	}
}
?>