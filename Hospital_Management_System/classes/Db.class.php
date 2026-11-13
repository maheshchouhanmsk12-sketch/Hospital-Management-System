<?php 

/*
  This database class contains various db operations in the system .
  Development of this class was inspired by EMR project (presented by JOYCE NASHA)
  it contains complex structures that ease database operations in the whole system
  
  Copy Righted to MURANGA UNIVERSITY OF TECHNOLOGY
  Credits to: 
	TOTONA JOYCE NASHA - MUT STUDENT
					(BACHELOR OF BUSINESS INFORMATION TECHNOLOGY)
	
	GLORY BE TO GOD
*/
class Db{

	// Constructor for PHP 8.x compatibility
	public function __construct(){}

	public static function returnedSQlError($e){
		$msg = is_object($e) && method_exists($e, 'getMessage') ? $e->getMessage() : (string)$e;
		die("<div style='background:#f8d7da;color:#721c24;padding:20px;border-radius:5px;font-family:sans-serif;'><h3>Database Connection Error</h3><p>" . htmlspecialchars($msg) . "</p><p><a href='db_setup.php'>Click here to open Database Setup & Diagnostic Tool</a></p></div>");
	}

	public static function connect(){
		static $db = null;
		if ($db !== null) {
			return $db;
		}

		$host = Config::getHost();
		$port = Config::getPort();
		$dbname = Config::getDbName();
		$user = Config::getUser();
		$pass = Config::getPass();
		$ssl = Config::getSsl();

		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_TIMEOUT => 15
		];
		if ($ssl) {
			$options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
		}

		try{
			$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
			$db = new PDO($dsn, $user, $pass, $options);
		} catch(Exception $e){
			try {
				$dsnFallback = "mysql:host={$host};port={$port};charset=utf8mb4";
				$tmpDb = new PDO($dsnFallback, $user, $pass, $options);
				$tmpDb->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}`");
				$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
				$db = new PDO($dsn, $user, $pass, $options);
			} catch(Exception $e2) {
				self::returnedSQlError($e); 
			}
		}

		return $db;
	}

	public static function getMysqli(){
		mysqli_report(MYSQLI_REPORT_OFF);
		$conn = mysqli_init();
		$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 15);

		$host = Config::getHost();
		$port = Config::getPort();
		$dbname = Config::getDbName();
		$user = Config::getUser();
		$pass = Config::getPass();
		$ssl = Config::getSsl();

		$flags = 0;
		if ($ssl) {
			$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
			$flags = MYSQLI_CLIENT_SSL;
		}

		$connected = @$conn->real_connect($host, $user, $pass, $dbname, $port, NULL, $flags);
		if (!$connected) {
			$connInit = mysqli_init();
			if ($ssl) {
				$connInit->ssl_set(NULL, NULL, NULL, NULL, NULL);
			}
			if (@$connInit->real_connect($host, $user, $pass, NULL, $port, NULL, $flags)) {
				$connInit->query("CREATE DATABASE IF NOT EXISTS `{$dbname}`");
				$connInit->close();
				$conn->real_connect($host, $user, $pass, $dbname, $port, NULL, $flags);
			}
		}
		return $conn;
	}

	public static function insert($table, array $columns, array $colValues ){
		$con = self::connect(); 
		try{
			$test = "INSERT INTO $table("; 
				foreach ($columns as $key => $value) {
					if($key == (count($columns) - 1)){
						$test.= $value; 
					} else {
						$test.= $value.", "; 
					}
				}
			$test.=	") VALUES("; 
			    foreach ($columns as $key => $value) {
			    	if($key == (count($columns) - 1)){
						$test.= "?"; 
					} else {
						$test.= "?, "; 
					}
			    }
			$test.= ") ";

            $query = $con->prepare($test);
            $paramsCount = count($colValues); 

            $start = 0; 
			while($start < $paramsCount){
				$keyData = $start +1; 
				$lastKey = count($colValues) + 1; 

				if($start == count($colValues)){
					$query->bindParam($lastKey, $colValues[$start]);
				} else {
					$query->bindParam($keyData, $colValues[$start]);
				}

				$start ++; 
			} 

			$query->execute(); 
			return 1;
		} catch(Exception $e){
			die($e->getMessage()); 
		}
	}

	public static function update($table, array $columns,  array $colValues, $where, $whereValue){
        $con = Db::connect(); 
        try{
			$sql = "UPDATE $table SET "; 
			     foreach ($columns as $key => $data) {
			     	if($key == (count($columns)- 1)){
			     		$sql.= " $data = ? "; 
			     	} else {
			     		$sql.= " $data = ?, "; 
			     	}
			     } 
			$sql.= "WHERE ";
			$sql.= $where;

			$query = $con->prepare($sql); 

			if(!is_array($whereValue)){
				$paramsCount = count($columns) + 1; 
				$start = 0; 
				while($start < $paramsCount){
					$keyData = $start +1; 
					$lastKey = count($colValues) + 1; 

					if($start == count($colValues)){
						$query->bindParam($lastKey, $whereValue);
					} else {
						$query->bindParam($keyData, $colValues[$start]);
					}

					$start ++; 
				} 
			} else {
				$start = 0; 
				$paramsCount = count($whereValue) + count($colValues); 

				while($start < $paramsCount){
					$keyData = $start + 1;
					if($keyData <= count($colValues)){
						$query->bindParam($keyData, $colValues[$start]);
					} else {
						$newKey = $start - count($colValues);
						$query->bindParam($keyData, $whereValue[$newKey]);
					}

					$start ++;
				}
			}

			$query->execute(); 
			return 1;
        } catch(Exception $e){
        	die($e->getMessage());
        } 
	}

	public static function delete($table, $where, $whereValue){
		$con = self::connect();
		try{
			if($where != ""){
				$query = $con->prepare("DELETE FROM $table WHERE $where ");
				if(is_array($whereValue)){
					$start = 0; 
					while($start < count($whereValue)){
						$paramKey = $start + 1; 
						$query->bindParam($paramKey, $whereValue[$start]); 
						$start ++;
					}
				} else {
					$query->bindParam(1, $whereValue);
				}
			} else {
				$query = $con->prepare("DELETE FROM $table ");
			}
			$query->execute(); 
		} catch(Exception $e){
			die($e->getMessage()); 
		}
	}

	public static function fetchAll($table, $where, $whereValue, $order,$limit){
		$con = self::connect();
		if($limit == ""){
			$limit = ""; 
		} else {
			$limit = " LIMIT $limit "; 
		}

		if($order == ""){
			$order = "";
		} else {
			$order = "ORDER BY $order"; 
		}
		try{
			if($where != ""){
				$query = $con->prepare("SELECT * FROM $table WHERE $where  $order  $limit ");
				if(!is_array($whereValue)){
					$query->bindParam(1, $whereValue);
				} else {
					$count = count($whereValue); 
					$start = 0; 
					while($start < $count){
						$paramKey = $start + 1; 
						$query->bindParam($paramKey, $whereValue[$start]);
						$start ++;
					}
				}
			}  else {
				$query = $con->prepare("SELECT * FROM $table  $order  $limit");
			}
			$query->execute(); 

			return $query; 
		} catch(Exception $e){
			return false; 
		}
	}

	public static function fetchSpecial($table, $where, $whereValue, $groupBy, $order,$limit){
		if($limit == ""){
			$limit = ""; 
		} else {
			$limit = " LIMIT $limit "; 
		}
		if($order == ""){
			$order = "";
		} else {
			$order = "ORDER BY $order"; 
		}
		if($groupBy == ""){
			$groupBy = "";
		} else {
			$groupBy = "GROUP BY $groupBy"; 
		}
		$con = self::connect();
		try{
			if($where != ""){
				$query = $con->prepare("SELECT * FROM $table WHERE $where $groupBy  $order $limit ");
				if(!is_array($whereValue)){
					$query->bindParam(1, $whereValue);
				} else {
					$count = count($whereValue); 
					$start = 0; 
					while($start < $count){
						$paramKey = $start + 1; 
						$query->bindParam($paramKey, $whereValue[$start]);
						$start ++;
					}
				}
			}  else {
				$query = $con->prepare("SELECT * FROM $table $groupBy  $order $limit");
			}
			$query->execute(); 
			return $query; 
		} catch(Exception $e){
			return false; 
		}
	}

	public static function fetchCols($table, array $cols,  $where, $whereValue, $order,$limit){
		$con = self::connect();
		if($limit == ""){
			$limit = ""; 
		} else {
			$limit = " LIMIT $limit "; 
		}
		if($order == ""){
			$order = "";
		} else {
			$order = "ORDER BY $order"; 
		}
		try{ 
			if($where != ""){
				$sql ="SELECT "; 
				foreach ($cols as $key => $value) {
					$lastCol = count($cols) - 1;  
					if($key == $lastCol){
						$sql .= $value;
					} else {
						$sql .= $value . ", "; 
					}
				}
				 
				$sql .=  " FROM $table WHERE $where  $order  $limit "; 
				$query = $con->prepare($sql);
				if(!is_array($whereValue)){
					$query->bindParam(1, $whereValue);
				} else {
					$count = count($whereValue); 
					$start = 0; 
					while($start < $count){
						$paramKey = $start + 1; 
						$query->bindParam($paramKey, $whereValue[$start]);
						$start ++;
					}
				}
			}  else {
				$sql ="SELECT "; 
				foreach ($cols as $key => $value) {
					$lastCol = count($cols) - 1;  
					if($key == $lastCol){
						$sql .= $value;
					} else {
						$sql .= $value . ", "; 
					}
				}
				 
				$sql .=  " FROM $table  $order $limit "; 
				$query = $con->prepare($sql);
			}
			$query->execute(); 

			return $query; 
		} catch(Exception $e){
			return false; 
		}
	}

	public static function fetch($table, $columns,  $whereClause, $whereValue, $orderBy, $limit, $groupBy){
		if($limit == ""){
			$limit = ""; 
		} else {
			$limit = " LIMIT $limit "; 
		}
		if($orderBy == ""){
			$orderBy = ""; 
		} else {
			$orderBy = " ORDER BY $orderBy "; 
		}
		if($groupBy == ""){
			$groupBy = ""; 
		} else {
			$groupBy = "GROUP BY $groupBy"; 
		}
		$con = self::connect(); 
		try {
			if($columns == ""){
				if($whereValue != ""){
					$query = $con->prepare("SELECT * FROM $table WHERE $whereClause $groupBy $orderBy $limit");
					if(is_array($whereValue)){
						$n = 0; 
						$countWhereValue = count($whereValue);
						while($n < $countWhereValue){
							$paramsCount = $n + 1; 
							$query->bindParam($paramsCount, $whereValue[$n]);
							$n++;
						}
					} else {
						$query->bindParam(1, $whereValue); 
					}
				} else {
					$query = $con->prepare("SELECT * FROM $table  $groupBy $orderBy  $limit");
				}
			} else {
				$sql = "SELECT "; 
				if(is_array($columns)){
					$colsCount = count($columns); 
					$start = 0; 
					while($start < $colsCount){
						$commas = $start + 1; 
						if($commas == $colsCount){
							$sql .= $columns[$start]." ";
						} else {
							$sql .= $columns[$start].", "; 
						}
						$start ++; 
					}
				} else {
					$sql .= "$columns ";
				}
				
				if($whereValue != ""){
					$sql .= "FROM $table WHERE $whereClause $groupBy $orderBy $limit"; 
					$query = $con->prepare($sql); 
					if(is_array($whereValue)){
						$n = 0; 
						$countWhereValue = count($whereValue);
						while($n < $countWhereValue){
							$paramsCount = $n + 1; 
							$query->bindParam($paramsCount, $whereValue[$n]);
							$n++;
						}
					} else {
						$query->bindParam(1, $whereValue); 
					}	
				} else {
					$sql .= "FROM $table $groupBy $orderBy $limit"; 
					$query = $con->prepare($sql); 
				}
			}
			$query->execute(); 
			return $query;
		} catch(Exception $e) {
			return false;
		}
	}
	
	public static function table($table, array $cols){
		// placeholder
	}

	public static function count($query){
		if (!$query) {
			return 0;
		}
		if ($query instanceof PDOStatement) {
			return $query->rowCount();
		}
		if (is_object($query) && method_exists($query, 'rowCount')) {
			return $query->rowCount();
		}
		if ($query instanceof mysqli_result) {
			return $query->num_rows;
		}
		return 0;
	}

	public static function assoc($query){
		if (!$query) {
			return null;
		}
		if ($query instanceof PDOStatement) {
			return $query->fetch(PDO::FETCH_ASSOC);
		}
		if ($query instanceof mysqli_result) {
			return $query->fetch_assoc();
		}
		return null;
	}

	public static function num($query){
		if (!$query) {
			return null;
		}
		if ($query instanceof PDOStatement) {
			return $query->fetch(PDO::FETCH_NUM);
		}
		if ($query instanceof mysqli_result) {
			return $query->fetch_row();
		}
		return null;
	}

	public static function getCount($table, $where, $whereValue, $order, $limit){
		$q = self::fetchAll($table, $where, $whereValue, $order, $limit);
		return self::count($q);
	}

	public static function get($table, $colToken, $tokenValue, $field){
		$con = Db::connect(); 
		try{
			if (strpos($colToken, '=') === false && strpos($colToken, '?') === false) {
				$query = $con->prepare("SELECT $field FROM $table WHERE $colToken = ? ");
			} else {
				$query = $con->prepare("SELECT $field FROM $table WHERE $colToken ");
			}
			$query->bindParam(1, $tokenValue);
			$query->execute();
			if($query && $query->rowCount()){
				$data = $query->fetch(PDO::FETCH_NUM);
				return $data ? $data[0] : "";
			}
		} catch(Exception $e){
			return "";
		}
		return "";
	}

	public static function form(array $label, $labelDistance, array $name, array $type, $submit){
		$form = "<form method='post' class='form-horizontal' action='' style='margin-top: 20px;'>";
		$startForm = 0; 
		$totalFields = count($name); 
		while($startForm < $totalFields){
			$inputDistance = 12 - (int)$labelDistance; 
			$form .="<div class='form-group'>";
			$form .="<label class='col-md-$labelDistance'>".$label[$startForm]."</label>";
			$form .="<div class='col-md-$inputDistance'><input type='".$type[$startForm]."' name='".$name[$startForm]."' class='form-control'/></div>";
			$form .="</div>";

			$startForm ++;
		}

		$form .= "<div class='form-group'>
					<div class='col-md-$labelDistance'></div> 
					<div class='col-md-$inputDistance'> 
						<input type='submit' value='$submit' class='btn btn-primary' />
					</div> 
		          </div> "; 
		$form .= "</form>"; 

		echo $form; 
	}
	
	public static function formSpecial($label, $labelDistance,  $name, $type,  $textAreaLabel, $textAreaName,  $selectLabel, $select, $selectOptions,  $submit){
		$form = "<form method='post' class='form-horizontal' action='' style='margin-top: 20px;'>";
		$startForm = 0; 
		$totalFields = is_array($name) ? count($name) : 0; 
		while($startForm < $totalFields){
			$inputDistance = 12 - (int)$labelDistance; 
			$form .="<div class='form-group'>";
			$form .="<label class='col-md-$labelDistance'>".$label[$startForm]."</label>";
			$form .="<div class='col-md-$inputDistance'><input type='".$type[$startForm]."' name='".$name[$startForm]."' class='form-control'/></div>";
			$form .="</div>";

			$startForm ++;
		}
		
		if(!empty($textAreaLabel) && is_array($textAreaLabel)){
			$startTextArea = 0; 
			while($startTextArea < count($textAreaLabel)){
				$inputDistance = 12 - (int)$labelDistance; 
				$form .="<div class='form-group'>";
				$form .="<label class='col-md-$labelDistance'>".$textAreaLabel[$startTextArea]."</label>";
				$form .="<div class='col-md-$inputDistance'><textarea name='".$textAreaName[$startTextArea]."' class='form-control'></textarea></div>";
				$form .="</div>";
				
				$startTextArea ++; 
			}
		}
		
		if(!empty($select) && is_array($select)){
			$startSelect = 0; 
			while($startSelect < count($selectLabel)){
				$inputDistance = 12 - (int)$labelDistance; 
				$form .="<div class='form-group'>";
				$form .="<label class='col-md-$labelDistance'>".$selectLabel[$startSelect]."</label>";
				$form .="<div class='col-md-$inputDistance'><select name='".$select[$startSelect]."' class='form-control'><option value=''>--Select--</option>";
				$startOptions = 0; 
				if (is_array($selectOptions)) {
					while($startOptions < count($selectOptions)){
						$form .= "<option value='".$selectOptions[$startOptions]."'>".$selectOptions[$startOptions]."</option>";
						$startOptions ++; 
					}
				}
				$form .="</select></div>";
				$form .="</div>";
				
				$startSelect ++; 
			}
		}

		$form .= "<div class='form-group'><div class='col-md-$labelDistance'></div><div class='col-md-$inputDistance'><input type='submit' value='$submit' class='btn btn-primary' /></div></div> "; 
		$form .= "</form>"; 

		echo $form; 
	}
}
