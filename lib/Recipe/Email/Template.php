<?php
/**
 * Email template.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 * @version $Id$
 */

class Recipe_Email_Template
{
	/**
	 * Template file name.
	 *
	 * @var string
	 */
	protected $template = null;

	/**
	 * Rendered messages.
	 *
	 * @var array
	 */
	protected $messages = array();

	/**
	 * Template engine.
	 *
	 * @var Recipe_Template_Adapter_Abstract
	 */
	protected $engine = null;

	/**
	 * Constructor.
	 *
	 * @param string $template
	 * @param Recipe_Template_Adapter_Abstract $engine
	 */
	public function __construct($template, Recipe_Template_Adapter_Abstract $engine = null)
	{
		$this->template = $template;
		if($engine === null)
		{
			$engine = Core::getTemplate();
		}
		$this->setEngine($engine);
	}

	/**
	 * Send the mail.
	 *
	 * @param Email $mail
	 * @param boolean $throwException
	 * @return Recipe_Email_Template
	 */
	public function send(Email $mail, $throwException = true)
	{
		$mail->setMessages($this->getMessages());
		$mail->sendMail($throwException);
		return $this;
	}

	/**
	 * Returns the rendered mail messages.
	 *
	 * @return array
	 */
	public function getMessages()
	{
		if(empty($this->messages))
		{
			try {
				$html = $this->getEngine()->render("html/".$this->template, "emails");
				if(!empty($html))
				{
					$this->messages["html"] = $html;
				}
			} catch(Exception $e) {}

			try {
				$plain = $this->getEngine()->render("plain/".$this->template, "emails");
				if(!empty($plain))
				{
					$this->messages["plain"] = $plain;
				}
			} catch(Exception $e) {}
		}
		return $this->messages;
	}

	/**
	 * Sets the template engine.
	 *
	 * @param Recipe_Template_Adapter_Abstract $engine
	 * @return Recipe_Email_Template
	 */
	public function setEngine(Recipe_Template_Adapter_Abstract $engine)
	{
		$this->engine = $engine;
		return $this;
	}

	/**
	 * Returns the template engine.
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public function getEngine()
	{
		return $this->engine;
	}

	/**
	 * Returns the template.
	 *
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template;
	}
}
?>