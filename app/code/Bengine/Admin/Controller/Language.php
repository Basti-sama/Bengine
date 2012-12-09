<?php
/**
 * Language controller.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Bengine_Admin_Controller_Language extends Bengine_Admin_Controller_Abstract
{
	/**
	 * @return Bengine_Admin_Controller_Abstract
	 */
	protected function init()
	{
		Core::getLanguage()->load("AI_LanguageManager");
		return parent::init();
	}

	/**
	 * @return Bengine_Admin_Controller_Language
	 */
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
		Core::getTPL()->addLoop("langselection", $result->fetchAll());
		$result = Core::getQuery()->select("languages l", array("l.languageid", "l.title", "l.langcode", "l.charset", "COUNT(p.phraseid) AS phrases"), "LEFT JOIN ".PREFIX."phrases p ON (p.languageid = l.languageid) GROUP BY l.languageid");
		Core::getTPL()->addLoop("languages", $result->fetchAll());
		$result = Core::getQuery()->select("phrasesgroups", array("phrasegroupid", "title"), "", "", "title ASC");
		Core::getTPL()->addLoop("groupselection", $result->fetchAll());
		return $this;
	}

	/**
	 * @return Bengine_Admin_Controller_Language
	 */
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
		foreach($result->fetchAll() as $row)
		{
			$id = $row["languageid"];
			$langs[$id]["title"] = $row["title"];
		}
		$groups = array();
		$result = Core::getQuery()->select("phrasesgroups", array("phrasegroupid", "title"), "ORDER BY title ASC");
		foreach($result->fetchAll() as $row)
		{
			$id = $row["phrasegroupid"];
			$groups[$id]["phrasesgroupid"] = $id;
			$groups[$id]["title"] = $row["title"];
			$groups[$id]["variables"] = "";
			foreach($langs as $key => $value)
			{
				$groups[$id]["variables"] .= Link::get("admin/language/showfromgroup/".$key."/".$row["phrasegroupid"], $value["title"])."<br />";
			}
		}
		Core::getTPL()->addLoop("groups", $groups);
		return $this;
	}

	/**
	 * @param array $delete
	 * @param array $groups
	 * @return Bengine_Admin_Controller_Language
	 */
	protected function saveGroups(array $delete, array $groups)
	{
		foreach($delete as $id)
		{
			Core::getQuery()->delete("phrasesgroups", "phrasegroupid = ?", null, null, array($id));
		}
		foreach($groups as $id)
		{
			Core::getQuery()->update("groupid", array("title" => Core::getRequest()->getPOST("title_".$id)), "phrasegroupid = ?", array($id));
		}
		return $this;
	}

	/**
	 * @param string $title
	 * @return Bengine_Admin_Controller_Language
	 */
	protected function addGroup($title)
	{
		Core::getQuery()->insert("phrasesgroups", array("title" => $title));
		return $this;
	}

	/**
	 * @param integer $langid
	 * @param integer $groupid
	 * @param string $title
	 * @param string $content
	 * @return Bengine_Admin_Controller_Language
	 */
	protected function addPhrase($langid, $groupid, $title, $content)
	{
		Core::getQuery()->insert("phrases", array("languageid" => $langid, "phrasegroupid" => $groupid, "title" => $title, "content" => $content));
		Admin::rebuildCache("lang", array(
			"language" => $langid,
			"group" => $groupid,
		));
		return $this;
	}

	/**
	 * @param array $delete
	 * @param array $langs
	 * @return Bengine_Admin_Controller_Language
	 */
	protected function saveLanguages(array $delete, array $langs)
	{
		foreach($delete as $id)
		{
			Core::getQuery()->delete("languages", "languageid = ?", null, null, array($id));
		}
		foreach($langs as $id)
		{
			$spec = array("title" => Core::getRequest()->getPOST("title_".$id), "charset" => Core::getRequest()->getPOST("charset_".$id));
			Core::getQuery()->update("languages", $spec, "languageid = ?", array($id));
		}
		return $this;
	}

	/**
	 * @param string $langcode
	 * @param string $language
	 * @param string $charset
	 * @return Bengine_Admin_Controller_Language
	 */
	protected function addLanguage($langcode, $language, $charset)
	{
		Core::getQuery()->insert("languages", array("langcode" => $langcode, "title" => $language, "charset" => $charset));
		return $this;
	}

	/**
	 * @return Bengine_Admin_Controller_Language
	 */
	protected function newlanguageAction()
	{
		if($this->getParam("add_language"))
		{
			$this->addLanguage($this->getParam("langcode"), $this->getParam("language"), $this->getParam("charset"));
		}
		return $this;
	}

	/**
	 * @return Bengine_Admin_Controller_Language
	 */
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
		foreach($result->fetchAll() as $row)
		{
			$langs[] = $row;
		}
		Core::getTPL()->addLoop("languages", $langs);
		return $this;
	}

	/**
	 * @param integer $langId
	 * @param string $destination
	 * @return Bengine_Admin_Controller_Language
	 */
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

	/**
	 * @return Bengine_Admin_Controller_Language
	 */
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

	/**
	 * @param integer $langid
	 * @param integer $groupid
	 * @return Bengine_Admin_Controller_Language
	 */
	protected function showfromgroupAction($langid, $groupid)
	{
		if($this->getParam("save_phrases"))
		{
			$this->savePhrases($this->getParam("phraseid"));
		}
		$langs = array();
		$result = Core::getQuery()->select("languages", array("languageid", "title"), "ORDER BY title ASC");
		foreach($result->fetchAll() as $row)
		{
			$id = $row["languageid"];
			$langs[$id]["title"] = $row["title"];
		}
		$groups = array();
		$result = Core::getQuery()->select("phrasesgroups", array("phrasegroupid", "title"), "ORDER BY title ASC");
		foreach($result->fetchAll() as $row)
		{
			$id = $row["phrasegroupid"];
			$groups[$id]["title"] = $row["title"];
		}

		$phrases = array();
		$result = Core::getQuery()->select("phrases p", array("p.phraseid", "p.languageid", "p.phrasegroupid", "p.title", "p.content"), "", "p.languageid = '".$langid."' AND p.phrasegroupid = '".$groupid."'");
		foreach($result->fetchAll() as $row)
		{
			$id = $row["phraseid"];
			$phrases[$id]["phraseid"] = $id;
			$phrases[$id]["title"] = $row["title"];
			$phrases[$id]["content"] = $row["content"];
			$phrases[$id]["delete"] = Link::get("admin/language/deletephrase/".$langid."/".$groupid."/".$id."/", Core::getLanguage()->getItem("Delete"));
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

	/**
	 * @param integer $langid
	 * @param integer $phraseid
	 * @param integer $id
	 * @return Bengine_Admin_Controller_Language
	 */
	protected function deletephraseAction($langid, $phraseid, $id)
	{
		Core::getQuery()->delete("phrases", "phraseid = ?", null, null, array($id));
		$this->redirect("admin/language/showfromgroup/".$langid."/".$phraseid."/".$id);
		return $this;
	}

	/**
	 * @param array $phraseids
	 * @return Bengine_Admin_Controller_Language
	 */
	protected function savePhrases(array $phraseids)
	{
		foreach($phraseids as $id)
		{
			$spec = array("languageid" => Core::getRequest()->getPOST("language_".$id), "phrasegroupid" => Core::getRequest()->getPOST("phrasegroup_".$id), "title" => Core::getRequest()->getPOST("title_".$id), "content" => Core::getRequest()->getPOST("content_".$id));
			Core::getQuery()->update("phrases", $spec, "phraseid = ?", array($id));
		}
		Admin::rebuildCache("lang", array(
			"language" => Core::getRequest()->getGET("1")
		));
		return $this;
	}
}
?>