<?php
/**
 * This class handles Ajax requests.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Abstract.php 22 2011-06-03 15:14:04Z secretchampion $
 */

abstract class Bengine_Page_Ajax_Abstract extends Bengine_Page_Abstract
{
	/**
	 * If HTTP header has been sent.
	 *
	 * @var boolean
	 */
	protected $headersSent = false;

	/**
	 * If out stream is compressed.
	 *
	 * @var boolean
	 */
	protected $compressed = false;

	/**
	 * Turns off any output.
	 *
	 * @var boolean
	 */
	protected $silence = false;

	/**
	 * Displays the text for clear Ajax output.
	 *
	 * @param string	The text to output
	 *
	 * @return Bengine_Page_Ajax_Abstract
	 */
	protected function display($outstream)
	{
		Bengine::unlock();
		if(!$this->silence)
			terminate($outstream);
		return $this;
	}

	/**
	 * Set response header.
	 *
	 * @return Bengine_Page_Ajax_Abstract
	 */
	protected function sendHeader()
	{
		if(!headers_sent() || !$this->headersSent)
		{
			if(@extension_loaded('zlib') && !$this->compressed && GZIP_ACITVATED)
			{
				ob_start("ob_gzhandler");
				$this->compressed = true;
			}
			@header("Content-Type: text/html; charset=".Core::getLanguage()->getOpt("charset"));
			$this->headersSent = true;
		}
		return $this;
	}

	/**
	 * Turns off the output.
	 *
	 * @param boolean $silence
	 * @return Bengine_Page_Ajax_Abstract
	 */
	public function setSilence($silence = true)
	{
		$this->silence = $silence;
		return $this;
	}
}
?>