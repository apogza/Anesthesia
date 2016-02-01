<?php
	require_once "Model.php";
	

	class User extends Model
	{
		public $name, $username, $accessToken;
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
		
		public static function getByUsername($username)
		{
			$query = "SELECT * FROM Users WHERE username = :username";
			
			$stmt = Db::getDb()->prepare($query);
			$stmt->bindParam(":username", $username);
			
			if($stmt->execute() && $row = $stmt->fetch())
			{
				$user = new User();
				$user->initFromDb($row);
				
				return $user;
			}
			
			return null;
		}
	}
?>