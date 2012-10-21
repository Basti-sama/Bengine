<?php
/**
 * News model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: News.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_News extends Recipe_Model_Abstract
{
	/**
	 * Initializes the model.
	 *
	 * @return Bengine_Game_Model_News
	 */
	protected function init()
	{
		$this->setTableName("news");
		$this->setModelName("game/news");
		return parent::init();
	}

	/**
	 * Returns the formatted time.
	 *
	 * @return string
	 */
	public function getFormattedTime()
	{
		if(!$this->exists("formatted_time"))
		{
			$this->set("formatted_time", Date::timeToString(1, $this->getTime()));
		}
		return $this->get("formatted_time");
	}

	/**
	 * Returns only the date (without time).
	 *
	 * @return string
	 */
	public function getDate()
	{
		if(!$this->exists("date"))
		{
			$this->set("date", Date::timeToString(2, $this->getTime()));
		}
		return $this->get("date");
	}
}
?>