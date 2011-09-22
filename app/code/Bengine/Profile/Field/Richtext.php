<?php
/**
 * Profile richtext field.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Richtext.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Profile_Field_Richtext extends Bengine_Profile_Field_Abstract
{
	/**
	 * Validate the data.
	 *
	 * @param string	Data
	 *
	 * @return boolean
	 */
	public function validate()
	{
		$text = $this->getData();
		if(Str::length(trim(str_replace("&nbsp;", "", strip_tags($text)))) > 0)
		{
			if($this->getSetup("max_length", 10000) < Str::length($text))
			{
				$this->addError("TEXT_TOO_LARGE");
				return false;
			}
			$this->setData(richText($text));
		}
		else
		{
			$this->setData("");
		}
		return true;
	}

	/**
	 * Returns the form html for the field.
	 *
	 * @return string
	 */
	public function getHtml()
	{
		$field = $this->getModel();
		return '<textarea id="'.strtolower($field->getName()).'" name="'.$field->getName().'">'.$this->getData().'</textarea>
<script type="text/javascript">
//<![CDATA[
tinyMCE_GZ.init({
language: "'.Core::getLang()->getOpt("langcode").'",theme: "advanced",disk_cache : true,debug : false,
plugins: "style,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,fullscreen,noneditable,xhtmlxtras"
});
//]]>
</script>
<script type="text/javascript">
//<![CDATA[
tinyMCE.init({
language: "'.Core::getLang()->getOpt("langcode").'",forced_root_block: "div",theme: "advanced",skin : "alliancetext",mode: "exact",elements: "'.strtolower($field->getName()).'",theme_advanced_toolbar_location: "top",theme_advanced_toolbar_align : "left",theme_advanced_disable: "anchor,styleselect",theme_advanced_statusbar_location: "bottom",theme_advanced_resizing: true,theme_advanced_resize_horizontal: false,width: 505,height: 370,relative_urls: false,remove_script_host: false,
theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,insertdate,inserttime,preview",
theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,forecolor,backcolor",
theme_advanced_buttons4 : "styleprops,cite,abbr,acronym,|,link,unlink,anchor,image,cleanup,code,fullscreen,|,charmap,emotions,iespell,media,advhr,|,sub,sup",
plugins: "style,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,fullscreen,noneditable,xhtmlxtras"
});
//]]>
</script>';
	}
}
?>