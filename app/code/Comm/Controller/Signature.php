<?php
/**
 * Signature controller to provide dynamic signature pictures.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Signature.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Comm_Controller_Signature extends Comm_Controller_Abstract
{
	/**
	 * Path to configuration file.
	 *
	 * @var string
	 */
	const ETC_FILE_PATH = "etc/Signature.xml";

	/**
	 * Image file extension.
	 *
	 * @var string
	 */
	const IMAGE_FILE_EXTENSION = ".png";

	/**
	 * The user id.
	 *
	 * @var integer
	 */
	protected $_userid = 0;

	/**
	 * Holds the configuration.
	 *
	 * @var XMLObj
	 */
	protected $_config = null;

	/**
	 * Holds the user model.
	 *
	 * @var Bengine_Model_User
	 */
	protected $_user = null;

	/**
	 * Shows the signature image.
	 *
	 * @param string	User id + file extension (png)
	 *
	 * @return Comm_Controller_Signature
	 */
	public function imageAction($user_id)
	{
		$modified = false;

		if(empty($_SERVER["HTTP_IF_MODIFIED_SINCE"]))
		{
			$modified = true;
		}

		$this->setNoDisplay();

		$this->setUserId($user_id);
		if(!$this->getConfig()->getChildren("cache")->getInteger("enabled") || !$this->hasCacheFile($this->getUserId()))
		{
			$this->_render();
			$modified = true;
		}

		$lastModified = "Last-Modified: ".gmdate("D, d M Y H:i:s\G\M\T", $this->_fileModifiedTime);
		if($modified)
		{
			header($lastModified, true, 200);
		}
		else
		{
			header($lastModified, true, 304);
			exit;
		}

		$this->_displayCacheFile($this->getUserId());
		return $this;
	}

	/**
	 * Renders the signature image.
	 *
	 * @return Comm_Controller_Signature
	 */
	protected function _render()
	{
		Core::getLang()->load("Signature");
		$config = $this->getConfig()->getChildren("image");
		$statsConfig = $config->getChildren("stats");
		$planetsConfig = $config->getChildren("planets");

		$user = $this->getUser();
		$img = imagecreatefrompng(AD."img/".$config->getString("background"));

		if($statsConfig->getInteger("show"))
		{
			$result = Core::getQuery()->select("user", array("userid"));
			$totalUser = fNumber(Core::getDB()->num_rows($result));
			Core::getDB()->free_result($result);
			$this->_addParamToImage($img, "stats", Core::getLang()->get("RANK")." ".$user->getFormattedRank()."/".$totalUser);
		}

		if($planetsConfig->getInteger("show"))
		{
			$result = Core::getQuery()->select("planet", array("planetid"), "", "userid = '{$user->getUserid()}' AND ismoon = 0");
			$planets = Core::getLang()->get("NUMBER_OF_COLONY")." ".fNumber(Core::getDB()->num_rows($result) - 1);
			Core::getDB()->free_result($result);
			$this->_addParamToImage($img, "planets", $planets);
		}

		$this->_addParamToImage($img, "username", $user->getUsername());
		$this->_addParamToImage($img, "gamename", Core::getConfig()->get("pagetitle"));
		$this->_addParamToImage($img, "planet", $user->getHomePlanet()->getPlanetname()." [".$user->getHomePlanet()->getCoords(false)."]");
		$this->_addParamToImage($img, "points", Core::getLang()->get("POINTS")." ".$user->getFormattedPoints());

		if($user->getAid())
		{
			$this->_addParamToImage($img, "alliance", Core::getLang()->get("ALLIANCE")." ".$user->getAlliance()->getTag());
		}

		imagepng($img, $this->getCachePath().$this->getUserId().self::IMAGE_FILE_EXTENSION, $config->getInteger("quality"));
		imagedestroy($img);
		return $this;
	}

	/**
	 * Adds a part to image.
	 *
	 * @param resource	Image
	 * @param string	Part name
	 * @param string	Text
	 *
	 * @return resource
	 */
	protected function _addParamToImage($img, $name, $text)
	{
		$config = $this->getConfig()->getChildren("image")->getChildren($name);
		if(!$config->getInteger("show"))
		{
			return $img;
		}
		$color = $this->getColor($name, $img);
		$shColor = $this->getShadowColor($name, $img);
		if($config->getInteger("show") && $config->getInteger("shadow"))
		{
			imagettftext($img, $config->getInteger("size"), 0, $this->getShadowCoord("x", $config), $this->getShadowCoord("y", $config), $shColor, $this->getFont($config), $text);
		}
		imagettftext($img, $config->getInteger("size"), 0, $config->getInteger("x"), $config->getInteger("y"), $color, $this->getFont($config), $text);
		return $img;
	}

	/**
	 * Retrurns the shadow coordinate for an expression.
	 *
	 * @param char		Axis (x or y)
	 * @param XMLObj	Expression configuration
	 *
	 * @return integer	Coordinate
	 */
	public function getShadowCoord($axis, XMLObj $config)
	{
		return $config->getInteger($axis)+1;
	}

	/**
	 * Returns a font name.
	 *
	 * @param XMLObj	Expression configuration
	 *
	 * @return string	Font name + path
	 */
	public function getFont(XMLObj $name)
	{
		return AD.$this->getConfig()->getChildren("image")->getString("fontPath").$name->getString("font");
	}

	/**
	 * Loads the user data to output.
	 *
	 * @return Bengine_Model_User
	 */
	public function getUser()
	{
		if(is_null($this->_user))
		{
			$this->_user = Application::getModel("user")->load($this->getUserId());
			if(!$this->_user->getUserid())
			{
				throw new Recipe_Exception_Generic("Unkown user signature.");
			}
		}
		return $this->_user;
	}

	/**
	 * Returns a color for an expression.
	 *
	 * @param string	Expression name
	 * @param resource	Image
	 * @param string	Color type sdasd
	 *
	 * @return resource	The color
	 */
	public function getColor($name, $image, $type = "color")
	{
		$hex = $this->getConfig()->getChildren("image")->getChildren($name)->getString($type);
		$red = hexdec(substr($hex, 0, 2));
		$green = hexdec(substr($hex, 2, 2));
		$blue = hexdec(substr($hex, 4, 2));
		return imagecolorallocate($image, $red, $green, $blue);
	}

	/**
	 * Returns the shadow color for an expression.
	 *
	 * @param string	Expression name
	 * @param resource	Image
	 *
	 * @return resource	The color
	 */
	public function getShadowColor($name, $image)
	{
		return $this->getColor($name, $image, "shadow_color");
	}

	/**
	 * Writes the image into cache.
	 *
	 * @param integer	User id
	 * @param binary	Image data
	 *
	 * @return Comm_Controller_Signature
	 */
	public function putIntoCache($user_id, $image)
	{
		file_put_contents($this->getCachePath().$user_id.self::IMAGE_FILE_EXTENSION, $image);
		return $this;
	}

	/**
	 * Displays the image file.
	 *
	 * @param integer	user id
	 *
	 * @return Comm_Controller_Signature
	 */
	protected function _displayCacheFile($user_id)
	{
		header("Content-type: image/png");
		echo file_get_contents($this->getCachePath().$user_id.self::IMAGE_FILE_EXTENSION);
		return $this;
	}

	/**
	 * Checks if a cache file exists.
	 *
	 * @param integer	User id
	 *
	 * @return boolean
	 */
	public function hasCacheFile($image)
	{
		$image = $this->getCachePath().$image.self::IMAGE_FILE_EXTENSION;
		if(file_exists($image))
		{
			if(filemtime($image) < TIME - $this->getConfig()->getChildren("cache")->getInteger("expires"))
			{
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * Returns cache file path. Creates the directory if it doesn't exist.
	 *
	 * @return string
	 */
	public function getCachePath()
	{
		$path = AD.$this->getConfig()->getChildren("cache")->getString("path");
		if(!is_dir($path))
		{
			mkdir($path, 0777, true);
		}
		return $path;
	}

	/**
	 * Returns the user id.
	 *
	 * @return integer
	 */
	public function getUserId()
	{
		return $this->_userid;
	}

	/**
	 * Sets the user id.
	 *
	 * @param string
	 *
	 * @return Comm_Controller_Signature
	 */
	public function setUserId($_userid)
	{
		$_userid = Str::replace(self::IMAGE_FILE_EXTENSION, "", $_userid);
		$this->_userid = (int) $_userid;
		return $this;
	}

	/**
	 * Returns the configuration
	 *
	 * @return XMLObj
	 */
	public function getConfig()
	{
		if(is_null($this->_config))
		{
			$this->_config = new XMLObj(file_get_contents(AD.self::ETC_FILE_PATH));
		}
		return $this->_config;
	}
}
?>