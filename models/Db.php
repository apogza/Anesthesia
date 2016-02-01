<?php
	require_once "Connection.php";

	class Db
	{
		private static $pdo;
		private function __construct(){}

		public static function getDb()
		{
			if(!isset(self::$pdo))
				self::$pdo = new Pdo(Connection::$connectionString, Connection::$username, Connection::$password);
			
			return self::$pdo;
		}
	}
?>
