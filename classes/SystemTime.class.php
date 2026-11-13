<?php 

class SystemTime{
	public function __construct(){}

	public static function getD($timestamp){ // getDate
		return date("d-m-Y", (int)$timestamp);
	}
	
	public static function getT($timestamp){ // gets time
		return date("g:i a", (int)$timestamp); 
	}
}
