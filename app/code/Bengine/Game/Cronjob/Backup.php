<?php
/**
 * Backup routine for temporary data.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Backup.php 26 2011-06-17 13:09:26Z secretchampion $
 */

class Bengine_Game_Cronjob_Backup extends Recipe_CronjobAbstract
{
	/**
	 * Contains the target directory to save backups.
	 *
	 * @var string
	 */
	protected $backupDir = "";

	/**
	 * Holds the tables to backup.
	 *
	 * @var array
	 */
	protected $tables = array();

	/**
	 * Sets some basic variables.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->backupDir = AD."var/backups/backup_".date("Y-m-d_H.i", TIME).".xml";
		$this->tables = array(
			PREFIX."user", PREFIX."password", PREFIX."user2group", PREFIX."buddylist",
			PREFIX."alliance", PREFIX."user2ally", PREFIX."ally_relationships", PREFIX."allyrank",
			PREFIX."research2user", PREFIX."planet", PREFIX."galaxy", PREFIX."building2planet", PREFIX."unit2shipyard", PREFIX."events"
		);
	}

	/**
	 * Creates a database backup for all required tables.
	 *
	 * @param array		Tables for backup
	 * @param string	Backup file name and path
	 *
	 * @return Bengine_Game_Cronjob_Backup
	 */
	protected function createBackup($tables, $path)
	{
		$exporter = new Recipe_Database_Export_XML($tables);
		$exporter->setShowStructure(false);
		$exporter->setTruncate($tables);
		$exporter->save($path);
		return $this;
	}

	/**
	 * Executes this cronjob.
	 *
	 * @return Bengine_Game_Cronjob_Backup
	 */
	protected function _execute()
	{
		$this->createBackup($this->tables, $this->backupDir);
		return $this;
	}
}
?>