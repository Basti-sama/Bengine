<?php
/**
 * Represents an universe.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Uni.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Comm_Uni
{
	/**
	 * Name of the universe.
	 *
	 * @var string
	 */
	protected $name = "";

	/**
	 * Subdomain of the universe.
	 *
	 * @var string
	 */
	protected $domain = "";

	/**
	 * The universe lays on another server.
	 *
	 * @var boolean
	 */
	protected $external = false;

	/**
	 * Creates a new Universe object.
	 *
	 * @param string	Name
	 * @param string	Subdomain
	 *
	 * @return void
	 */
	public function __construct($name, $domain, $external = false)
	{
		$this->name = $name;
		$this->domain = $domain;
		$this->external = (bool) $external;
		return;
	}

	/**
	 * Returns the name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the full domain with HTTP host.
	 *
	 * @return string
	 */
	public function getDomain()
	{
		if($this->domain == "")
		{
			return "";
		}
		if($this->isExternal())
		{
			return "http://".$this->domain."/";
		}
		$parsedUrl = parse_url(BASE_URL);
		return $parsedUrl["scheme"]."://".$this->domain.".".$parsedUrl["host"].$parsedUrl["path"];
	}

	/**
	 * Returns the external flag.
	 *
	 * @return boolean
	 */
	public function isExternal()
	{
		return $this->external;
	}

	/**
	 * Sets the name.
	 *
	 * @param string
	 *
	 * @return Bengine_Uni
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Sets the domain.
	 *
	 * @param string
	 *
	 * @return Bengine_Uni
	 */
	public function setDomain($domain)
	{
		$this->domain = $domain;
		return $this;
	}

	/**
	 * Sets the external flag.
	 *
	 * @param boolean
	 *
	 * @return Bengine_Uni
	 */
	public function setExternal($external)
	{
		$this->external = $external;
		return $this;
	}

	/**
	 * Returns whether the universe is selected as default.
	 *
	 * @return boolean
	 */
	public function isSelected()
	{
		return (Core::getRequest()->getCOOKIE("uni") == $this->getName()) ? true : false;
	}
}
?>