<?php 
class DatabaseManager
{
	private $host = DB_HOST;
	private $user = DB_USER;
	private $pass = DB_PASS;
	private $dbname = DB_NAME;

	protected $pdo;
	protected $error;
	protected $sth;

	public function __construct($pdo = null){
		if (!empty($pdo)) {
			$this->pdo = $pdo;
		} else {
	        // Set DSN
			$dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
	        // Set options
			$options = array(
				PDO::ATTR_PERSISTENT    => true,
				PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
				);
	        // Create a new PDO instanace
			try{
				$this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
			}
	        // Catch any errors
			catch(PDOException $e){
				$this->error = $e->getMessage();
			}
		}
	}	

	public function query($query) {
		$this->sth = $this->pdo->prepare($query);
	}

	public function bind($param, $value, $type = null) {
		if (is_null($type)) {
			switch (true) {
				case is_int($value):
				$type = PDO::PARAM_INT;
				break;
				case is_bool($value):
				$type = PDO::PARAM_BOOL;
				break;
				case is_null($value):
				$type = PDO::PARAM_NULL;
				break;
				default:
				$type = PDO::PARAM_STR;
			}
			$this->sth->bindValue($param, $value, $type);
		}
	}
	// Execute
	public function execute($param = null) {
		return $this->sth->execute($param);
	}
	// Get all results
	public function resultset($param = null) {
		$this->sth->execute($param);
		return $this->sth->fetchAll(PDO::FETCH_ASSOC);
	}
	// Get single result
	public function single($param = null){
		$this->execute($param);
		return $this->sth->fetch(PDO::FETCH_ASSOC);
	}
	// Count rows
	public function rowCount() {
		return $this->sth->rowCount();
	}
	// Get last insert id
	public function lastInsertId() {
		return $this->sth->lastInsertId();
	}
	// begin database transaction
	public function beginTransaction() {
		$this->pdo->beginTransaction();
	}
	// cancel database Transaction
	public function cancelTransaction() {
		$this->pdo->rollBack();
	}
	// end database transaction and commit
	public function endTransaction() {
		$this->pdo->commit();
	}

	public function debugDumpParams(){
		return $this->sth->debugDumpParams();
	}
	/**
	 * Insert into table
	 *
	 * @param      string  $TableName  The table name
	 * @param      array   $Values     The values ( 0 - ColumnName, 1 - Condition, 2 - Value)
	 */
	public function InsertIntoTable($TableName,$Values)
	{
		$query = "INSERT INTO ".$TableName."(";

		$Parameters = array();
		$LastItem = count($Values)-1;

		for ($i=0; $i < count($Values) ; $i++) 
		{ 
			$Parameters = array_merge($Parameters, array(':'.$Values[$i][0] => $Values[$i][2]));

			if($i != $LastItem) 
				$query .= $Values[$i][0] . ", ";
			else
				$query .= $Values[$i][0];
		}	
		
		$query .= ") VALUES(";

		for ($i=0; $i < count($Values) ; $i++) 
		{ 
			if($i != $LastItem)
				$query .= ":".$Values[$i][0].',';
			else
				$query .= ":".$Values[$i][0];
		}	
		$query .= ")";

		$query;

		$this->sth = $this->pdo->prepare($query);
		$this->sth->execute($Parameters);
	}

	/**
	 * Gets all from table.
	 *
	 * @param      string   $TableName   The table name
	 * @param      array   	$Values      The values (0 - columnname, 1 - Condition, 2 - Value)
	 * @param      array   	$Conditions  The conditions
	 * @param      array  	$OrderBy     The order by (0 - columnname, 1 - Condition)
	 * @param      integer  $FetchAssoc  The fetch associated
	 *
	 * @return     object   All from table.
	 */
	public function getAllFromTable($TableName, $Values = NULL, $Conditions = NULL, $OrderBy = NULL, $FetchAssoc = null) {
		// Optional Condition e.g SELECT Level, Name FROM $TableName
		if (!empty($Condition)) {
			$query = "SELECT ";
			foreach ($Conditions as $Condition) {
				$query .= (sizeof($Conditions)) ? $Condition.", " : $Condition;
			}
			$query .= " FROM ".$TableName;

		}else {
			$query = "SELECT * FROM ".$TableName;
		}
		// Optional Parameters (WHERE + AND)
		$Parameters = array();
		if (!empty($Values)) {

			$query .= " WHERE ";
			for ($i=0; $i < sizeof($Values) ; $i++) { 
				if ($i >= 1) {
					$query .= " AND ";
				}
				$Parameters = array_merge($Parameters, array(':'.$Values[$i][0] => $Values[$i][2]));

				$query .= $Values[$i][0]. " ".$Values[$i][1]." :".$Values[$i][0];
			}
		}
		// Optional Order By parameter e.g Name DESCENDING
		if (!empty($OrderBy)) {
			for ($i=0; $i < $OrderBy; $i++) { 
				$query .= " ORDER BY ".$OrderBy[0]." ".$OrderBy[1];
			}
		}	
		$this->query($query);
		$this->execute($Parameters);

		if (!empty($FetchAssoc)) {
			return $this->sth->fetchAll(PDO::FETCH_ASSOC);
		}else {
			return $this->sth->fetchAll(PDO::FETCH_CLASS, $TableName, array($this->pdo));
		}

	}
	/**
	 * Update Table
	 *
	 * @param      string $TableName  The table name
	 * @param      array  $Values      The values (0 - columnname, 1 - Condition, 2 - Value)
	 * @param      array  $Conditions  The conditions
	 */
	public function updateAllFromTable($TableName, $Values, $Conditions) {
		$query = "UPDATE ".$TableName." SET ";

		$Parameters = array();

		for ($i=0; $i < sizeof($Values) ; $i++) { 
			$Parameters = array_merge($Parameters, array(':'.$Values[$i][0] => $Values[$i][2]));
			if ($i >= 1) {
				$query .= ", ";
			}
				$query .= $Values[$i][0]. " ".$Values[$i][1]." :".$Values[$i][0];
		}
		// Optional specific Conditions e.g WHERE ID = 1
		if (!empty($Conditions)) {
			$query .= " WHERE ";
			//$query .= (count($Conditions)) ? $Condition." = " : $Condition;
			for ($i=0; $i < count($Conditions) ; $i++) { 
				if ($i >= 1) {
					$query .= " AND ";
				}
				$Parameters = array_merge($Parameters, array(':'.$Conditions[$i][0] => $Conditions[$i][2]));

				$query .= $Conditions[$i][0]. " ".$Conditions[$i][1]." :".$Conditions[$i][0];
			}
		}

		$this->query($query);
		$this->execute($Parameters);
	}

	public function deleteAllFromTable($TableName, $Conditions) {
		$query = "DELETE FROM ".$TableName." WHERE ";

		$query .= (sizeof($Conditions)) ? $Condition." = " : $Condition;
		for ($i=0; $i < sizeof($Conditions) ; $i++) { 
			$Parameters = array_merge($Parameters, array(':'.$Conditions[$i][0] => $Conditions[$i][2]));

			$query .= $Conditions[$i][0]. " ".$Conditions[$i][1]." :".$Conditions[$i][0];
		}

		$this->query($query);
		$this->execute($Parameters);
	}

	public function GetPDO()
	{
		return $this->pdo;
	}
}
?>