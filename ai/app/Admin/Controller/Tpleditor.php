<?php
class Admin_Controller_Tpleditor extends Admin_Controller_Abstract
{
	protected $mode = "", $package = "", $template = "";
	const TEMPLATE_EXTENSION = ".tpl";

	protected function init()
	{
		Core::getLanguage()->load("AI_Templates");
		switch(Core::getRequest()->getGET("1"))
		{
			case 1:
				$this->mode = APP_ROOT_DIR."ai/templates/";
			break;
			case 2:
				$this->mode = APP_ROOT_DIR."app/templates/";
			break;
			default: $this->mode = null; break;
		}
		$this->package = Core::getRequest()->getGET("2");
		$this->template = Core::getRequest()->getGET("3");
		return parent::init();
	}

	protected function editAction()
	{
		if($this->getParam("save_template"))
		{
			$this->save($this->getParam("title"), $this->getParam("content"));
		}
		Core::getTPL()->assign("package", $this->package);
		$contents = file_get_contents($this->mode.$this->package."/".$this->template.self::TEMPLATE_EXTENSION);
		$contents = Str::validateXHTML($contents);
		Core::getTPL()->assign("backlink", Link::get("tpleditor/show/".Core::getRequest()->getGET("1")."/".$this->package, Core::getLanguage()->getItem("Back")));
		Core::getTPL()->assign("contents", $contents);
		Core::getTPL()->assign("filename", $this->template);
		Core::getTPL()->assign("extension", self::TEMPLATE_EXTENSION);
		return $this;
	}

	protected function showAction()
	{
		Core::getTPL()->assign("package", $this->package);
		$files = array();
		if($directory = new DirectoryIterator($this->mode.$this->package))
		{
			foreach($directory as $file)
			{
				if(strpos($file->getFilename(), ".tpl") > 0 && !$file->isDot() && $file->getFilename() != "Thumbs.db" && !$file->isDir())
				{
					$files[]["template"] = Link::get("tpleditor/edit/".Core::getRequest()->getGET("1")."/".$this->package."/".Str::replace(self::TEMPLATE_EXTENSION, "", $file->getFilename()), $file->getFilename());
				}
			}
		}
		Core::getTPL()->addLoop("templates", $files);
		return $this;
	}

	protected function indexAction()
	{
		$aiTemplates = array();
		if($directory = new DirectoryIterator(APP_ROOT_DIR."ai/templates/"))
		{
			foreach($directory as $file)
			{
				if($file->isDir() && !$file->isDot() && $file->getFilename() != "Thumbs.db" && $file->getFilename() != ".svn")
				{
					$aiTemplates[]["package"] = Link::get("tpleditor/show/1/".$file->getFilename(), $file->getFilename());
				}
			}
		}
		$appTemplates = array();
		if($directory = new DirectoryIterator(APP_ROOT_DIR."app/templates/"))
		{
			foreach($directory as $file)
			{
				if($file->isDir() && !$file->isDot() && $file->getFilename() != "Thumbs.db" && $file->getFilename() != ".svn")
				{
					$appTemplates[]["package"] = Link::get("tpleditor/show/2/".$file->getFilename(), $file->getFilename());
				}
			}
		}
		Core::getTPL()->addLoop("ai", $aiTemplates);
		Core::getTPL()->addLoop("app", $appTemplates);
		return $this;
	}

	protected function save($title, $content)
	{
		$file = $this->mode.$this->package."/".$this->template.$this->templateExtension;
		file_put_contents($file, stripslashes($content));
		if($this->template != $title)
		{
			rename($file, $this->mode.$this->package."/".$title.$this->templateExtension);
			$this->template = $title;
		}
		return $this;
	}
}
?>