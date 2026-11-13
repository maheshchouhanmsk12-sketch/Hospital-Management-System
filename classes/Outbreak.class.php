<?php 

class Outbreak{
	public function __construct(){}

	public static function add($token, $outbreakDisease, $comments, $location, $measures){
		if($token == ""){
			$time = time(); 
			$ujd = function_exists('unixtojd') ? @unixtojd(time()) : time();
			$token = md5(uniqid().time().$ujd); 
			Db::insert( "outbreaks",
				array("outBreak", "comments", "location", "cTime", "measures", "token"), 
				array($outbreakDisease, $comments, $location, $time,  $measures, $token)
			);
			
			Messages::success("Outbreak has been added. Doctors will be able to see it and take necessary measures");
		} else {
			self::edit($token, $outbreakDisease, $comments, $location, $measures);
			Messages::success("Outbreak has been edited. <strong><a href='outbreaks.php'>View edited record</a></strong>");
		}
	}
	
	public static function load(){
		$query = Db::fetch("outbreaks", "", "", "", "id DESC","", "");
		if(!Db::count($query)){
			Messages::info("There are no outbreaks recorded at the moment");
			return; 
		}
		
		echo "<div class='form-holder'>";
		Table::start();
		$header = array("Outbreak", "Comments", "Location", "Recorded", "Measures", "Action"); 
		Table::header($header); 
		
		while($data = Db::assoc($query) ){
			Table::body(array(
				htmlspecialchars($data['outBreak']),
				htmlspecialchars($data['comments']),
				htmlspecialchars($data['location']),
				SystemTime::getD($data['cTime']),
				htmlspecialchars($data['measures']),
				"<center><a href='add-outbreak.php?token=".$data['token']."'>Edit</a></center>"
			));
		}
		
		Table::close();
		echo "</div>";
	}
	
	public static function delete($token){
		Db::delete("outbreaks", " token = ? ", $token); 
	}
	
	public static function get($token, $field){
		$query = Db::fetch("outbreaks", "$field", "token = ? ", "$token", "id DESC","", "");
		$data = Db::num($query); 
		return $data ? $data[0] : "";
	}
	
	public static function edit($token, $outbreakDisease, $comments, $location, $measures){
		Db::update("outbreaks", 
			array("outBreak", "comments", "location",  "measures"), 
			array($outbreakDisease, $comments, $location, $measures),
			"token = ? ", $token);
	}
}
