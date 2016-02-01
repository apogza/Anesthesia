<?php
	require_once "Db.php";	

	abstract class Model
	{
		public $id;
		
		public function getTableName()
		{
			return get_class($this) . "s";
		}
		
		protected function getTableColumnsFromDb()
		{
			$query = "	SELECT	COLUMN_NAME 
						FROM 	INFORMATION_SCHEMA.COLUMNS 
						WHERE 	TABLE_SCHEMA = :db 
								AND TABLE_NAME = :tableName";
			
			$stmt = Db::getDb()->prepare($query);
			
			$stmt->bindParam(":db", Connection::$dbname);
			$stmt->bindValue(":tableName", $this->getTableName());
			
			$arr_columns = array();
			$stmt->execute();
			
			while($row = $stmt->fetch())
			{
				$col = $row["COLUMN_NAME"];
				$arrColumns[] = $row["COLUMN_NAME"];
			}
			
			return $arrColumns;
		}
		
		/*
		 * 
		 * The method is to be implemented in the child class
		 * Use a private static variable for caching to limit calls to the DB when not necessary
		 * 
		 */
		
		abstract protected function getTableColumns($arrExceptions);
		
		/*
		 * 
		 * Model methods to manage insert, update and delete
		 * 
		 */
		
		protected function insert()
		{
			$arrColumns = $this->getTableColumns(array("id"));
			$query = $this->getInsertQuery($arrColumns);
			$stmt = $this->prepareStatement($query, $arrColumns);
			
			return $stmt->execute();
		}
		
		protected function getInsertQuery($arrColumns)
		{
			$query = "INSERT INTO " . $this->getTableName();
			$colLabels = "";
			$colValues = "";
			
			foreach($arrColumns as $var)
			{
				$colLabels .= ($colLabels == "") ? $var : ", " . $var;
				$colValues .= ($colValues == "") ? ":". $var : ", :" . $var;
			}
			
			$query .= "(" . $colLabels . ") VALUES(". $colValues . ")";
			
			return $query;
		}
		
		protected function update()
		{
			$arrColumns = $this->getTableColumns(array("id"));
			
			//we do not want the id in this list since it is in the where clause
			$query = $this->getUpdateQuery($arrColumns);
			
			//add the id column in the list to prepare the db statement
			$arrColumns[] = "id";
			$stmt = $this->prepareStatement($query, $arrColumns);
			
			return $stmt->execute();
		}
		
		protected function getUpdateQuery($arrColumns)
		{
			$colLen = count($arrColumns);
			$query = " UPDATE " . $this->getTableName() . " SET ";
			
			$count= count($arrColumn);
			
			$i = 0;
			foreach($arrColumns as $var)
			{
				$query .= $col . "=" . ":" . $col;
				if(++$i < $count - 1) $query = ", ";
			}
			
			$query .= " WHERE id = :id";
			
			return $query;
		}
		
		public function save()
		{
			if($this->isOkForSave())
			{
				if($this->exists())
					return $this->update();
				
				return $this->insert();
			}
			
			return null;
		}
		
		protected function exists()
		{
			if(!isset($this->id))
				return false;
					
			$query = "SELECT id FROM " . $this->getTableName() . " WHERE id = :id";
			$stmt = Db::getDb()->prepare($query);
				
			$stmt->bindParam(":id", $this->id);
			return $stmt->execute() && $stmt->rowCount() > 0;
		}
		
		protected function isOkForSave()
		{
			$arrColumns = $this->getTableColumns(array("id"));
			$ok = true;
				
			foreach($arrColumns as $var)
				$ok = $ok && isset($this->$var);
					
				return $ok;
		}		
		
		public function delete()
		{
			if(isset($this->id))
			{
				$query = $this->getDeleteQuery();
				$arrColumns = array("id");
				$stmt = $this->prepareStatement($query, $arrColumns);
				
				return $stmt->execute();
			}
			
			return null;
		}
		
		protected function getDeleteQuery()
		{
			$query = "DELETE FROM " . $this->getTableName() . " WHERE id = :id";
			return $query;
		}
		
		protected function prepareStatement($query, $arrColumns)
		{
			$stmt = Db::getDb()->prepare($query);
			
			foreach($arrColumns as $var)
				$stmt->bindParam(":$var", $this->$var);
			
			return $stmt;
		}
		
		/* 
		 * 
		 * Initialization functions
		 * 
		 */
		
		public function initFromDb($row)
		{
			$arrColumns = $this->getTableColumns();
			foreach($arrColumns as $var)
				$this->$var = $row[$var];
		}
		
		public function initFromJson($json)
		{
			$arrColumns = $this->getTableColumns();
			$objJson = json_decode($json);
			
			foreach($arrColumns as $var)
			{
				if(isset($objJson->{$var}))
					$this->$var = $objJson->{$var};
			}
		}
			
		/*
		 * 
		 * Factory Static Methods
		 * 
		 */
		
		public static function get()
		{
			$modelClass = get_called_class();
			$tableName = $modelClass . "s"; 
			
			$query = "	SELECT	*
						FROM	$tableName";
			
			$model = null;
			$arrModels = array();
			
			$stmt = Db::getDb()->query($query);
			
			while($row = $stmt->fetch())
			{
				$model = new $modelClass();
				$model->initFromDb($row);
				$arrModels[] = $model;
			}
			
			return $arrModels;
		}
		
		public static function getById($id)
		{
			$modelClass = get_called_class();
			$tableName = $modelClass . "s";
			
			$query = "	SELECT	*
						FROM	$tableName
						WHERE	id = :id";
			
			$stmt = Db::getDb()->prepare($query);
			$stmt->bindParam(":id", $id);
			
			if($stmt->execute() && $row = $stmt->fetch())
			{
				$model = new $modelClass();
				$model->initFromDb($row);
				
				return $model;
			}
			
			return null;
		}
	}
?>
