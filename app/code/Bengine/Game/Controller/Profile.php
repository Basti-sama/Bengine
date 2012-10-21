<?php
/**
 * Shows user profiles.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Profile.php 46 2011-07-30 14:38:11Z secretchampion $
 */

class Bengine_Game_Controller_Profile extends Bengine_Game_Controller_Abstract
{
	/**
	 * Loads basic language groups.
	 *
	 * @return Bengine_Game_Controller_MSG
	 */
	protected function init()
	{
		Core::getLanguage()->load(array("Statistics", "Galaxy", "Profile"));
		return parent::init();
	}

	/**
	 * Display own profile.
	 *
	 * @return Bengine_Game_Controller_Profile
	 */
	protected function indexAction()
	{
		$this->setTemplate("profile/page");
		$this->pageAction(Core::getUser()->get("userid"));
		return $this;
	}

	/**
	 * Edit user profile.
	 *
	 * @param integer	User id
	 *
	 * @return Bengine_Game_Controller_Profile
	 */
	protected function editAction($user_id = 0)
	{
		if(!$user_id)
		{
			$user_id = Core::getUser()->get("userid");
		}
		if($user_id != Core::getUser()->get("userid"))
		{
			Core::getUser()->checkPermissions(array("CAN_EDIT_PROFILES"));
		}

		$this->assign("user_id", $user_id);
		$profile = Game::getCollection("profile");
		$profile->addUserFilter($user_id)
			->addSortIndex();

		if($this->isPost() && $this->getParam("save"))
		{
			$this->save(Core::getRequest()->getPOST(), $profile);
		}
		$this->backLink = Link::get("game.php/".SID."/Profile/Page/".$user_id, Core::getLang()->get("BACK_TO_PROFILE"));

		Core::getTPL()->addLoop("profile", $profile);
		return $this;
	}

	/**
	 * Displays the profile page for an user.
	 *
	 * @param integer	User id
	 *
	 * @return Bengine_Game_Controller_Profile
	 */
	protected function pageAction($user_id)
	{
		$user = Game::getModel("user")->load($user_id);
		$profile = Game::getCollection("profile");
		$profile->addUserFilter($user_id)
			->addSortIndex()
			->addListFilter();

		$avatar = Game::getModel("profile")->loadByCode("AVATAR", $user_id);
		$about = Game::getModel("profile")->loadByCode("ABOUT_ME", $user_id);

		$this->assign("user", $user);
		$this->assign("aboutMe", $about->getData());
		$this->assign("avatar", $avatar->getData());
		$this->assign("username", $user->getUsername());
		$this->assign("points", $user->getFormattedPoints());
		$this->assign("status", $user->getStatusString());
		$this->assign("rank", $user->getFormattedRank());
		$this->assign("regdate", $user->getFormattedRegDate());
		$this->assign("pm", $user->getPmLink());
		$this->assign("moderate", $user->getModerateLink());
		$this->assign("addToBuddylist", $user->getFriendLink());
		$this->assign("pm", $user->getPmLink());
		$this->assign("allianceName", ($user->getAid()) ? $user->getAlliance()->getPageLink() : "&nbsp;");
		Core::getTPL()->addLoop("profile", $profile);
		$this->assign("canEdit", Core::getUser()->get("userid") == $user_id || Core::getUser()->ifPermissions(array("CAN_EDIT_PROFILES")));
		$this->assign("editLink", Link::get("game.php/".SID."/Profile/Edit/".$user_id, Core::getLang()->get("EDIT_PROFILE")));
		Core::getLang()->assign("profileUsername", $user->getUsername());
		return $this;
	}

	/**
	 * Saves the profile data.
	 *
	 * @param array		$_POST
	 * @param Bengine_Game_Model_Collection_Profile
	 *
	 * @return Bengine_Game_Controller_Profile
	 */
	protected function save($post, Bengine_Game_Model_Collection_Profile $profile)
	{
		foreach($profile as $field)
		{
			if(isset($post[$field->getName()]))
			{
				$field->setData($post[$field->getName()]);
				if($field->getFieldObject()->validate())
				{
					if(!$field->getUserId())
					{
						$field->setIsNew(true);
						$field->setUserId($this->getParam("user_id"));
					}
					$field->save();
				}
			}
		}
		return $this;
	}

	/**
	 * Saves the about me text.
	 *
	 * @param integer	User id
	 *
	 * @return Bengine_Game_Controller_Profile
	 */
	protected function saveAboutAction($user_id)
	{
		if(!$user_id)
		{
			$user_id = Core::getUser()->get("userid");
		}
		if($user_id != Core::getUser()->get("userid"))
		{
			Core::getUser()->checkPermissions(array("CAN_EDIT_PROFILES"));
		}
		$this->setNoDisplay();
		if($this->isPost())
		{
			$aboutText = $this->getParam("text");
			$about = Game::getModel("profile")->loadByCode("ABOUT_ME", $user_id);

			if(!$about->getUserId())
			{
				$about->setIsNew(true);
			}

			$about->setData($aboutText)
				->setUserId($user_id);
			if($about->getFieldObject()->validate())
			{
				$about->save();
			}
			else
			{
				echo implode("\n", $about->getFieldObject()->getErrors());
			}
		}
		return $this;
	}
}
?>