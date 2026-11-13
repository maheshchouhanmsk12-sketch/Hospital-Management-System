<?php 

class HIV{
	public function __construct(){}

	public static function add($name, $age, $location, $MOD, $comments){
		$token = md5(uniqid().time().$name); 
		Db::insert("hiv", 
			array("name", "age", "location", "moc", "dComments", "cTime", "token"), 
			array($name, $age, $location, $MOD, $comments, time(), $token)
		);
		Messages::success("Data has been added, thanks");
	}
	
	public static function load(){
		$query = Db::fetch("hiv", "", "", "", "id DESC", "", "");

		if(!Db::count($query)){
			Messages::info("There is no HIV data at the moment.");
			return; 
		}
		echo "<div class='form-holder'>"; 
		Table::start();
		$header = array("Name", "Age", "Location", "Contracted Via", "Doctors Comments", "Recorded");
		Table::header($header); 
		while($data = Db::assoc($query) ){
			$date = date("d-m-Y", (int)$data['cTime']);
			Table::body(array(
				htmlspecialchars($data['name']), 
				htmlspecialchars($data['age']), 
				htmlspecialchars($data['location']), 
				htmlspecialchars($data['moc']), 
				htmlspecialchars($data['dComments']), 
				$date
			)); 
		}
		
		Table::close();
		echo "</div>";
	}
}
