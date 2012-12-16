<?php
/**
 * Signature controller to provide dynamic signature pictures.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Signature.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Comm_Controller_Signature extends Bengine_Comm_Controller_Abstract
{
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
	 * @var Bengine_Game_Model_User
	 */
	protected $_user = null;

	/**
	 * Shows the signature image.
	 *
	 * @param string $user_id	User id + file extension (png)
	 *
	 * @return Bengine_Comm_Controller_Signature
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
		$config = $this->getConfig();
		if(!$config["cache"]["enabled"] || !$this->hasCacheFile($this->getUserId()))
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
	 * @return Bengine_Comm_Controller_Signature
	 */
	protected function _render()
	{
		Core::getLang()->load("Signature");
		$config = $this->getConfig();
		$imageConfig = $config["image"];

		$user = $this->getUser();
		$img = imagecreatefrompng(AD."img/".$imageConfig["background"]);

		if($imageConfig["stats"]["show"])
		{
			$result = Core::getQuery()->select("user", array("userid"));
			$totalUser = fNumber($result->rowCount());
			$this->_addParamToImage($img, "stats", Core::getLang()->get("RANK")." ".$user->getFormattedRank()."/".$totalUser);
		}

		if($imageConfig["planets"]["show"])
		{
			$result = Core::getQuery()->select("planet", array("planetid"), "", Core::getDB()->quoteInto("userid = ? AND ismoon = 0", $user->getUserid()));
			$planets = Core::getLang()->get("NUMBER_OF_COLONY")." ".fNumber($result->rowCount() - 1);
			$this->_addParamToImage($img, "planets", $planets);
		}

		$this->_addParamToImage($img, "username", $user->getUsername());
		$this->_addParamToImage($img, "gameName", Core::getConfig()->get("pagetitle"));
		$this->_addParamToImage($img, "planet", $user->getHomePlanet()->getPlanetname()." [".$user->getHomePlanet()->getCoords(false)."]");
		$this->_addParamToImage($img, "points", Core::getLang()->get("POINTS")." ".$user->getFormattedPoints());

		if($user->getAid())
		{
			$this->_addParamToImage($img, "alliance", Core::getLang()->get("ALLIANCE")." ".$user->getAlliance()->getTag());
		}

		imagepng($img, $this->getCachePath().DIRECTORY_SEPARATOR.$this->getUserId().self::IMAGE_FILE_EXTENSION, $imageConfig["quality"]);
		imagedestroy($img);
		return $this;
	}

	/**
	 * Adds a part to image.
	 *
	 * @param resource $img	Image
	 * @param string $name	Part name
	 * @param string $text	Text
	 *
	 * @return resource
	 */
	protected function _addParamToImage($img, $name, $text)
	{
		$config = $this->getConfig();
		$config = $config["image"][$name];
		if(!$config["show"])
		{
			return $img;
		}
		$color = $this->getColor($config, $img);
		$shColor = $this->getShadowColor($config, $img);
		if($config["show"] && $config["shadow"])
		{
			imagettftext($img, $config["size"], 0, $config["x"]+1, $config["y"]+1, $shColor, $this->getFont($config["font"]), $text);
		}
		imagettftext($img, $config["size"], 0, $config["x"], $config["y"], $color, $this->getFont($config["font"]), $text);
		return $img;
	}

	/**
	 * Returns a font name.
	 *
	 * @param string $name
	 * @return string
	 */
	public function getFont($name)
	{
		$config = $this->getConfig();
		return AD.$config["image"]["fontPath"].$name;
	}

	/**
	 * Loads the user data to output.
	 *
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Game_Model_User
	 */
	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = Application::getModel("game/user")->load($this->getUserId());
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
	 * @param string $config	Configuration
	 * @param resource $image	Image
	 * @param string $type		Color type sdasd
	 *
	 * @return int				The color
	 */
	public function getColor($config, $image, $type = "color")
	{
		$hex = $config[$type];
		$red = hexdec(substr($hex, 0, 2));
		$green = hexdec(substr($hex, 2, 2));
		$blue = hexdec(substr($hex, 4, 2));
		return imagecolorallocate($image, $red, $green, $blue);
	}

	/**
	 * Returns the shadow color for an expression.
	 *
	 * @param array $config		Expression name
	 * @param resource $image	Image
	 *
	 * @return int				The color
	 */
	public function getShadowColor($config, $image)
	{
		return $this->getColor($config, $image, "shadowColor");
	}

	/**
	 * Writes the image into cache.
	 *
	 * @param integer $user_id	User id
	 * @param binary $image		Image data
	 *
	 * @return Bengine_Comm_Controller_Signature
	 */
	public function putIntoCache($user_id, $image)
	{
		file_put_contents($this->getCachePath().DIRECTORY_SEPARATOR.$user_id.self::IMAGE_FILE_EXTENSION, $image);
		return $this;
	}

	/**
	 * Displays the image file.
	 *
	 * @param integer $user_id	user id
	 *
	 * @return Bengine_Comm_Controller_Signature
	 */
	protected function _displayCacheFile($user_id)
	{
		header("Content-type: image/png");
		echo file_get_contents($this->getCachePath().DIRECTORY_SEPARATOR.$user_id.self::IMAGE_FILE_EXTENSION);
		return $this;
	}

	/**
	 * Checks if a cache file exists.
	 *
	 * @param integer $image
	 *
	 * @return boolean
	 */
	public function hasCacheFile($image)
	{
		$image = $this->getCachePath().DIRECTORY_SEPARATOR.$image.self::IMAGE_FILE_EXTENSION;
		if(file_exists($image))
		{
			$config = $this->getConfig();
			if(filemtime($image) < TIME - $config["cache"]["expires"])
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
		$config = $this->getConfig();
		$path = AD.$config["cache"]["path"];
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
	 * @return Bengine_Comm_Controller_Signature
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
	 * @return array
	 */
	public function getConfig()
	{
		if($this->_config === null)
		{
			$meta = Comm::getMeta();
			$this->_config = $meta["config"]["signature"];
		}
		return $this->_config;
	}
}
?>