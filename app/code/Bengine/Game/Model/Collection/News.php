<?php
/**
 * News collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: News.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Collection_News extends Recipe_Model_Collection_Abstract
{
	/**
	 * Sorts the news by time.
	 *
	 * @param string	Sorting (DESC, ASC) [optional]
	 *
	 * @return Bengine_Game_Model_Collection_News
	 */
	public function addTimeOrder($sort = "DESC")
	{
		$this->getSelect()->order(array("n" => "time"), $sort);
		return $this;
	}

	/**
	 * Sorts the news by id.
	 *
	 * @param string	Sorting (DESC, ASC) [optional]
	 *
	 * @return Bengine_Game_Model_Collection_News
	 */
	public function addIdOrder($sort = "ASC")
	{
		$this->getSelect()->order(array("n" => "news_id"), $sort);
		return $this;
	}

	/**
	 * Adds a user id filter.
	 *
	 * @param integer|Bengine_Game_Model_User
	 *
	 * @return Bengine_Game_Model_Collection_News
	 */
	public function addUserFilter($user)
	{
		if($user instanceof Bengine_Game_Model_User)
		{
			$user = $user->getId();
		}
		$this->getSelect()->where(array("n" => "user_id"), $user);
		return $this;
	}

	/**
	 * Adds the enabled filter.
	 *
	 * @param integer	Enabled? [optional]
	 *
	 * @return Bengine_Game_Model_Collection_News
	 */
	public function addEnabledFilter($enabled = 1)
	{
		$this->getSelect()->where(array("n" => "enabled"), (int) $enabled);
		return $this;
	}

	/**
	 * Adds a language filter.
	 *
	 * @param integer	Language id [optional]
	 *
	 * @return Bengine_Game_Model_Collection_News
	 */
	public function addLanguageFilter($langId = null)
	{
		if(is_null($langId))
		{
			$langId = Core::getLang()->getOpt("languageid");
		}
		$this->getSelect()->where(array("n" => "language_id"), (int) $langId);
		return $this;
	}

	/**
	 * Adds sort index order filter.
	 *
	 * @return Bengine_Game_Model_Collection_News
	 */
	public function addSortIndexOrder()
	{
		$this->getSelect()->order(array("n" => "sort_index"), "ASC");
		$this->addTimeOrder();
		return $this;
	}
}
?>