<?php
class Admin_Controller_Language extends Admin_Controller_Abstract
{
	protected function init()
	{
		Core::getLanguage()->load("AI_LanguageManager");
		return parent::init();
	}

	protected function indexAction()
	{
		if($this->getParam("add_phrase"))
		{
			$this->addPhrase($this->getParam("languageid"), $this->getParam("phrasegroupid"), $this->getParam("title"), $this->getParam("content"));
		}
		else if($this->getParam("save_languages"))
		{
			$this->saveLanguages($this->getParam("delete"), $this->getParam("langs"));
		}
		$result = Core::getQuery()->select("languages", array("languageid", "title"), "", "", "title ASC");
		Core::getTPL()->addLoop("langselection", $result);
		$result = Core::getQuery()->select("languages l", array("l.languageid", "l.title", "l.langcode", "l.charset", "COUNT(p.phraseid) AS phrases"), "LEFT JOIN ".PREFIX."phrases p ON (p.languageid = l.languageid) GROUP BY l.languageid");
		Core::getTPL()->addLoop("languages", $result);
		$result = Core::getQuery()->select("phrasesgroups", array("phrasegroupid", "title"), "", "", "title ASC");
		Core::getTPL()->addLoop("groupselection", $result);
		return $this;
	}

	protected function phrasesgroupsAction()
	{
		if($this->getParam("add_group"))
		{
			$this->addGroup($this->getParam("title"));
		}
		else if($this->getParam("save_groups"))
		{
			$this->saveGroups($this->getParam("delete"), $this->getParam("groups"));
		}
		$langs = array();
		$result = Core::getQuery()->select("languages", array("languageid", "title"), "ORDER BY title ASC");
		while($row = Core::getDB()->fetch($result))
		{
			$id = $row["languageid"];
			$langs[$id]["title"] = $row["title"];
		}
		$groups = array();
		$result = Core::getQuery()->select("phrasesgroups", array("phrasegroupid", "title"), "ORDER BY title ASC");
		while($row = Core::getDB()->fetch($result))
		{
			$id = $row["phrasegroupid"];
			$groups[$id]["phrasesgroupid"] = $id;
			$groups[$id]["title"] = $row["title"];
			$groups[$id]["variables"] = "";
			foreach($langs as $key => $value)
			{
				$groups[$id]["variables"] .= Link::get("language/showfromgroup/".$key."/".$row["phrasegroupid"], $value["title"])."<br />";
			}
		}
		Core::getTPL()->addLoop("groups", $groups);
		return $this;
	}

	protected function saveGroups($delete, $groups)
	{
		foreach($delete as $id)
		{
			Core::getQuery()->delete("phrasesgroups", "phrasegroupid = '".$id."'");
		}
		foreach($groups as $id)
		{
			Core::getQuery()->update("groupid", "title", Core::getRequest()->getPOST("title_".$id), "phrasegroupid = '".$id."'");
		}
		return $this;
	}

	protected function addGroup($title)
	{
		Core::getQuery()->insert("phrasesgroups", "title", $title);
		return $this;
	}

	protected function addPhrase($langid, $groupid, $title, $content)
	{
		Core::getQuery()->insert("phrases", array("languageid", "phrasegroupid", "title", "content"), array($langid, $groupid, $title, $content));
		Admin::rebuildCache("lang", array(
			"language" => $langid,
			"group" => $groupid,
		));
		return $this;
	}

	protected function saveLanguages($delete, $langs)
	{
		foreach($delete as $id)
		{
			Core::getQuery()->delete("languages", "languageid = '".$id."'");
		}
		foreach($langs as $id)
		{
			$atts = array("title", "charset");
			$vals = array(Core::getRequest()->getPOST("title_".$id), Core::getRequest()->getPOST("charset_".$id),);
			Core::getQuery()->update("languages", $atts, $vals, "languageid = '".$id."'");
		}
		return $this;
	}

	protected function addLanguage($langcode, $language, $charset)
	{
		Core::getQuery()->insert("languages", array("langcode", "title", "charset"), array($langcode, $language, $charset));
		return $this;
	}

	protected function newlanguageAction()
	{
		if($this->getParam("add_language"))
		{
			$this->addLanguage($this->getParam("langcode"), $this->getParam("language"), $this->getParam("charset"));
		}
		return $this;
	}

	protected function portingAction()
	{
		if($this->getParam("export"))
		{
			$this->export($this->getParam("languageid"), $this->getParam("destination"));
		}
		else if($this->getParam("import"))
		{
			$this->import();
		}
		$langs = array();
		$result = Core::getQuery()->select("languages", array("langcode", "title"));
		while($row = Core::getDB()->fetch($result))
		{
			$langs[] = $row;
		}
		Core::getTPL()->addLoop("languages", $langs);
		return $this;
	}

	protected function export($langId, $destination)
	{
		$file = "language_export_".$langId.".xml";
		if(empty($langId) || $langId == "all")
		{
			$langId = null;
		}
		$exporter = new Recipe_Language_Exporter(APP_ROOT_DIR.$destination, $file);
		$exporter->setLanguageId($langId);
		$exporter->startExport();
		return $this;
	}

	protected function import()
	{
		if($file = Core::getRequest()->getFILES("import_file"))
		{
			$xml = file_get_contents($file["tmp_name"]);
			$importer = new Recipe_Language_XML_Importer($xml);
			$importer->proceed();
		}
		return $this;
	}

	protected function showfromgroupAction($langid, $groupid)
	{
		if($this->getParam("save_phrases"))
		{
			$this->savePhrases($this->getParam("phraseid"));
		}
		$langs = array();
		$result = Core::getQuery()->select("languages", array("languageid", "title"), "ORDER BY title ASC");
		while($row = Core::getDB()->fetch($result))
		{
			$id = $row["languageid"];
			$langs[$id]["title"] = $row["title"];
		}
		$groups = array();
		$result = Core::getQuery()->select("phrasesgroups", array("phrasegroupid", "title"), "ORDER BY title ASC");
		while($row = Core::getDB()->fetch($result))
		{
			$id = $row["phrasegroupid"];
			$groups[$id]["title"] = $row["title"];
		}

		$phrases = array();
		$result = Core::getQuery()->select("phrases p", array("p.phraseid", "p.languageid", "p.phrasegroupid", "p.title", "p.content"), "", "p.languageid = '".$langid."' AND p.phrasegroupid = '".$groupid."'");
		while($row = Core::getDB()->fetch($result))
		{
			$id = $row["phraseid"];
			$phrases[$id]["phraseid"] = $id;
			$phrases[$id]["title"] = $row["title"];
			$phrases[$id]["content"] = $row["content"];
			$phrases[$id]["delete"] = Link::get("language/deletephrase/".$langid."/".$groupid."/".$id."/", Core::getLanguage()->getItem("Delete"));
			$phrases[$id]["lang"] = "";
			foreach($langs as $key => $value)
			{
				$phrases[$id]["lang"] .= createOption($key, $value["title"], ($key == $row["languageid"]) ? 1 : 0);
			}
			$phrases[$id]["groups"] = "";
			foreach($groups as $key => $value)
			{
				$phrases[$id]["groups"] .= createOption($key, $value["title"], ($key == $row["phrasegroupid"]) ? 1 : 0);
			}
		}
		Core::getTPL()->addLoop("vars", $phrases);
		return $this;
	}

	protected function deletephraseAction($langid, $phraseid, $id)
	{
		Core::getQuery()->delete("phrases", "phraseid = '".$id."'");
		$this->redirect("language/showfromgroup/".$langid."/".$phraseid."/".$id);
		return $this;
	}

	protected function savePhrases($phraseids)
	{
		foreach($phraseids as $id)
		{
			$atts = array("languageid", "phrasegroupid", "title", "content");
			$vals = array(Core::getRequest()->getPOST("language_".$id), Core::getRequest()->getPOST("phrasegroup_".$id), Core::getRequest()->getPOST("title_".$id), Core::getRequest()->getPOST("content_".$id));
			Core::getQuery()->update("phrases", $atts, $vals, "phraseid = '".$id."'");
		}
		Admin::rebuildCache("lang", array(
			"language" => Core::getRequest()->getGET("1")
		));
		return $this;
	}
}
?>