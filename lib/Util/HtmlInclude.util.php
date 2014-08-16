<?php
/**
 * Creates include tags for CSS or JS files.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2013, Sebastian Noll
 * @license Proprietary
 */

class HtmlInclude
{
	/**
	 * @param string|array $files
	 * @param string $type
	 * @throws Exception
	 * @return string
	 */
	public static function getInclude($files, $type)
	{
		if(!is_array($files))
		{
			$files = array($files);
		}
		$html = "";
		switch($type)
		{
			case "css":
				foreach($files as $file)
				{
					$html .= '<link rel="stylesheet" type="text/css" href="'.BASE_URL.'css/'.$file.'"/>';
				}
				break;
			case "js":
				$files = implode(",", $files);
				$checksum = md5($files);
				$cachePath = "var/cache/html-inc/".$checksum.".".$type;
				if(!file_exists(APP_ROOT_DIR.$cachePath))
				{
					$files = explode(",", $files);
					$cacheContent = "";
					foreach($files as $file)
					{
						$path = APP_ROOT_DIR.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$file;
						if(file_exists($path))
						{
							$cacheContent .= file_get_contents($path);
						}
					}
					file_put_contents(APP_ROOT_DIR.$cachePath, $cacheContent);
				}
				$html = '<script type="text/javascript" src="'.BASE_URL.$cachePath.'"></script>';
				break;
			default:
				throw new Exception("'$type' is no valid include type.");
				break;
		}
		return $html;
	}
}
?>