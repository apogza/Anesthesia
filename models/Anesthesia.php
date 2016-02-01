<?php
	require_once "Model.php";
	require_once "Db.php";
	
	class Anesthesia extends Model 
	{
		public $label;
		private static $tableColumns;
		
		/*
		 * Local cache of the database table columns
		 */
		protected function getTableColumns($arrExceptions = null)
		{
			if(!isset($tableColumns))
				self::$tableColumns = $this->getTableColumnsFromDb();
			
			return (isset($arrExceptions)) ? array_diff(self::$tableColumns, $arrExceptions) : self::$tableColumns; 
		}
		
	}
	
?>