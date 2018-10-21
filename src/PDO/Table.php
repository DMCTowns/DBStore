<?php
/**
 * PDO Table object
 * @author Diccon Towns <dtowns@reapit.com>
 */

namespace Reapit\Database\PDO;

/**
 * Class to handle tables in the PDO database environment
 * @package Reapit_Database_PDO
 */
class Table{

	/**
	 * @var object database
	 */
	protected $_database;

	/**
	 * @var string name
	 */
	public $name;

	/**
	 * @var string index
	 */
	protected $_primaryIndexField;

	/**
	 * @var array $columns
	 */
	protected $_columns;

	/**
	 * @param Database $database
	 * @param string $name
	 */
	public function __construct(Database $database, $name){
		$this->_database = $database;
		$this->name = preg_replace('/\W/', '', $name);
	}

	/**
	 * Sets the primary index field used in the table
	 * @param string $primaryIndex
	 */
	public function setPrimaryIndexField($primaryIndex){
		$this->_primaryIndexField = $primaryIndex;
	}

	/**
	 * Returns primary index field used in the table
	 * @return string
	 */
	public function getPrimaryIndexField(){
		return $this->_primaryIndexField;
	}

	/**
	 * Runs query
	 * @param string $query
	 * @param array $parameters
	 * @return \PDOStatement
	 */
	public function query($query, $parameters = array()){
		if($this->_database){
			return $this->_database->query($query, $parameters);
		}
		Throw new \Exception("Database must be defined in PDO Table object before running query");
	}

	/**
	 * Returns column object
	 * @param string $name
	 * @return ColumnInterface
	 */
	public function getColumn($name){
		if(($columns = $this->getColumns()) && isset($columns[$name])){
			return $columns[$name];
		}
		return null;
	}

	/**
	 * Returns database object
	 * @return Database
	 */
	public function getDatabase(){
		return $this->_database;
	}

	/**
	 * Returns table name
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Returns array of column objects
	 * @param boolean $reload
	 * @return array
	 */
	public function getColumns($reload=false){
		if(!$reload && is_array($this->_columns)){
			return $this->_columns;
		}
		return $this->_loadColumns();
	}

	/**
	 * Returns array of column objects
	 * @return array
	 */
	protected function _loadColumns(){
		// try getting table info

		switch($this->_database->getDriverType()){
			case 'sqlsrv':
			case 'dblib':
				$query = "SELECT COLUMNPROPERTY(object_id('" . $this->name . "'), COLUMN_NAME, 'IsIdentity') AS AUTO_INCREMENTS, * FROM (SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME=:table AND TABLE_CATALOG=:database) AS a";
				break;
			default:
				$query = "SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME=:table AND TABLE_SCHEMA=:database";
		}
		$parameters = array(':table' => $this->name, ':database' => $this->_database->getName());

		$columns = array();

		//$result = $this->_database->query($query, $parameters);

		if(($result = $this->_database->query($query, $parameters)) && ($column = $result->fetch(\PDO::FETCH_ASSOC))){
			do{
				if(isset($column["COLUMN_TYPE"])){
					preg_match("/^([A-Za-z]+)(\(([^\)]+)\))?( ([A-Za-z]+))?/",$column["COLUMN_TYPE"],$matches);
				}else{
					$matches = null;
				}

				$data = array(
					"name" => $column["COLUMN_NAME"],
					"type" => $column["DATA_TYPE"],
					"length" => ($matches && (strtolower($column['DATA_TYPE']) == 'enum') ? $matches[3] : $column["CHARACTER_MAXIMUM_LENGTH"]),
					"attributes" => ((isset($matches[5])) ? strtoupper($matches[5]) : null),
					"default" => $column["COLUMN_DEFAULT"]
				);

				$columnObj = new Column($data);
				$columnObj->allowNull(($column["IS_NULLABLE"] == "YES"));
				if(isset($column['AUTO_INCREMENTS']) && $column['AUTO_INCREMENTS']){
					$columnObj->autoIncrements(true);
				}else if(isset($column["EXTRA"])){
					$columnObj->autoIncrements(stristr($column["EXTRA"],"auto_increment"));
				}

				$columns[$column["COLUMN_NAME"]] = $columnObj;
			}while($column = $result->fetch(\PDO::FETCH_ASSOC));
			$result->closeCursor();
		}else{
			return null;
		}


		// get indexes
		switch($this->_database->getDriverType()){
			case 'mysql':
				// get indexes
				$query = 'SELECT COLUMN_NAME, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=:db AND TABLE_NAME=:table AND COLUMN_KEY<>\'\'';
				$params = array(':db'=>$this->_database->getName(), ':table'=>$this->getName());
				//$query = "SHOW INDEXES IN " . $this->name;
				if($result = $this->_database->query($query, $params)){
					while($row = $result->fetch(\PDO::FETCH_ASSOC)){
						if($row["COLUMN_KEY"] == "PRI"){
							$index = "PRIMARY";
						}else if($row["COLUMN_KEY"] == "UNI"){
							$index = "UNIQUE";
						}else{
							$index = "INDEX";
						}
						if(isset($columns[$row["COLUMN_NAME"]])){
							$columns[$row["COLUMN_NAME"]]->setIndexType($index);
						}
					}
					$result->closeCursor();
				}
				break;
			case 'sqlsrv':
			case 'dblib':
				$query = "EXEC sp_helpindex " . $this->name;
				if($result = $this->_database->query($query)){
					while($row = $result->fetch(\PDO::FETCH_ASSOC)){
						if(stristr($row["index_description"],"PRIMARY") !== false){
							$index = "PRIMARY";
						}else if(stristr($row["index_description"],"unique") ){
							$index = "UNIQUE";
						}else{
							$index = "INDEX";
						}
						if(isset($columns[$row["index_keys"]])){
							$columns[$row["index_keys"]]->setIndexType($index);
						}
					}
					$result->closeCursor();
				}
		}

		if(count($columns)){
			$this->_columns = $columns;
			return $this->_columns;
		}
		return null;
	}

	/**
	 * Adds column to table
	 * @param ColumnInterface $column
	 * @param array $options
	 */
	public function addColumn($column, array $options=null){

		// create query
		$query = "ALTER TABLE ".$this->_database->getName().".".$this->name." ADD COLUMN ".$column->getSQL();

		$this->_database->execute($query);
		// reload columns
		$this->_loadColumns();

	}

	/**
	 * Creates new table based on supplied column data and options - Abstract
	 * @param array $column_data
	 * @param array $options
	 */
	public function create(array $column_data, array $options=null){

		$query = "CREATE TABLE ".$this->_database->getName().".".$this->name." (\n";
		$indices = "";

		$i = 0;

		foreach($column_data as $key=>$column){

			$query .= ($i == 0) ? "" : ", ";

			$query .= $column->getSQL();

			if($index = $column->getIndexType()){

				switch($index){
					case "PRIMARY":
						switch($this->_database->getDriverType()){
							case 'mysql':
								$indices .= ", PRIMARY KEY(".$column->getName().")";
								break;
							case 'sqlsrv':
							case 'dblib':
								$indices .= 'CREATE UNIQUE NONCLUSTERED INDEX ' . $column->getName() . ' ON ' . $this->name . ' (' . $column->getName() . ');';
								break;
						}
						break;
					case "UNIQUE":
						switch($this->_database->getDriverType()){
							case 'mysql':
								$indices .= ", UNIQUE(".$column->getName().")";
								break;
							case 'sqlsrv':
							case 'dblib':
								$indices .= 'CREATE UNIQUE NONCLUSTERED INDEX ' . $column->getName() . ' ON ' . $this->name . ' (' . $column->getName() . ');';
								break;
						}
						break;
					case "INDEX":
						switch($this->_database->getDriverType()){
							case 'mysql':
								$indices .= ", INDEX(".$column->getName().")";
								break;
							case 'sqlsrv':
							case 'dblib':
								$indices .= 'CREATE NONCLUSTERED INDEX ' . $column["field"] . ' ON ' . $this->name . ' (' . $column->getName() . ');';
								break;
						}
						break;
				}

			}

			$i++;

		}

		switch($this->_database->getDriverType()){
			case 'mysql':
				if($indices){
					$query .= $indices;
				}
				$query .= ") CHARACTER SET " . $this->_database->getCharacterSet();
				break;
			case 'sqlsrv':
			case 'dblib':
				$query .= ");";
				if($indices){
					$query .= $indices;
				}
				break;
		}


		if(isset($options["drop"]) && $options["drop"]){
			$this->_database->execute("DROP TABLE IF EXISTS ".$this->name."");
		}
		$this->_database->execute($query);
	}

	/**
	 * Alters table structure based on supplied column data and options - Abstract
	 * @param array $column_data
	 * @param array $options
	 */
	public function alter(array $column_data, array $options=null){

		$existing_columns = $this->getColumns();

		$prev_column = null;

		$query_stub = "ALTER TABLE ".$this->_database->getName().".".$this->name."";

		$index_queries = array();;

		if($this->_database){

			while($column = array_shift($column_data)){

				$query = $query_stub;

				if(isset($existing_columns[$column->getName()])){// column is already in table - move it rather than add it.

					$query .= " MODIFY ";

					if(($column->getIndexType() == 'UNIQUE' || $column->getIndexType() == "INDEX") && !$existing_columns[$column->getName()]->getIndexType()){// add index

						switch($this->_database->getDriverType()){
							case 'mysql':
								$index_queries[] = $query_stub." ADD ".(($column->getIndexType() == "UNIQUE") ? "UNIQUE " : "")." INDEX ".$column->getName()."(".$column->getName()."); ";
								break;
							case 'sqlsrv':
							case 'dblib':
								$index_queries[] .= 'CREATE '.(($column->getIndexType() == "UNIQUE") ? "UNIQUE " : "").' NONCLUSTERED INDEX ' . $column->getName() . ' ON ' . $this->name . ' (' . $column->getName() . ');';
								break;
						}

					}else if(!$column->getIndexType() && $existing_columns[$column->getName()]->getIndexType()){// drop index

						$index_queries[] = $query_stub." DROP INDEX ".$column->getName()."; ";

					}
					unset($existing_columns[$column->getName()]);

				}else{

					$query .= " ADD COLUMN ";

					if(($column->getIndexType() == 'UNIQUE' || $column->getIndexType() == "INDEX")){// add index

						switch($this->_database->getDriverType()){
							case 'mysql':
								$index_queries[] = $query_stub." ADD ".(($column->getIndexType() == "UNIQUE") ? "UNIQUE " : "")."INDEX ".$column->getName()."(".$column->getName()."); ";
								break;
							case 'sqlsrv':
							case 'dblib':
								$index_queries[] .= 'CREATE '.(($column->getIndexType() == "UNIQUE") ? "UNIQUE " : "").' NONCLUSTERED INDEX ' . $column->getName() . ' ON ' . $this->name . ' (' . $column->getName() . ');';
								break;
						}

					}

				}

				$query .=  $column->getSQL();

				$query .= ($prev_column) ? " AFTER ".$prev_column."" : " FIRST";

				$this->_database->execute($query);

				$prev_column = $column->getName();

			}

			if(count($index_queries)){

				foreach($index_queries as $key=>$index_query){

					$this->_database->execute($index_query);

				}
			}

			// drop any extra columns

			while($column = array_shift($existing_columns)){

				$query = $query_stub;

				$query .= " DROP COLUMN ".$column->getName()."";

				$this->_database->execute($query);

			}

			$this->_loadColumns();

		}
	}

	/**
	 * Returns array of record objects - Abstract
	 * @param string $query
	 * @param array $parameters
	 * @return array
	 */
	public function getRecords($query=null, $parameters = array()){
		$query = (!$query) ? 'SELECT * FROM ' . $this->name : $query;
		if(($result = $this->_database->query($query, $parameters)) && ($row = $result->fetch(\PDO::FETCH_ASSOC))){
			$arr = array();
			do{
				$record = $this->getNewRecord($row);
				$record->isSynched(true);
				$arr[] = $record;
			}while($row = $result->fetch(\PDO::FETCH_ASSOC));
			return $arr;
		}
		return null;
	}

	/**
	 * Gets new record based on table
	 * @param array $fieldData
	 * @return \Reapit\Database\RecordInterface
	 */
	public function getNewRecord($fieldData=null){
		return new Record($this, $fieldData);
	}

	/**
	 * Gets new column
	 * @return \Reapit\Database\ColumnInterface
	 */
	public function getNewColumn(){
		return new Column();
	}

	/**
	 * Returns true if table exists on database
	 * @return boolean
	 */
	public function exists(){
		return $this->_database->tableExists($this->name);
	}

	/**
	 * Get the enum definition for a field
	 * @param $enumName
	 */
	public function getEnum($name) {
		if (isset($this->$name))
			return $this->$name;

		return null;
	}
}
?>
