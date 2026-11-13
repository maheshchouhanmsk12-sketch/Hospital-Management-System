<?php 
// this class contains configuration data

class Config{

	// Safe static getters for database credentials with environment variable support
	public static function getHost(){
		$val = getenv('DB_SERVER') ?: (getenv('DB_HOST') ?: null);
		return (!empty($val)) ? $val : 'mysql-1a85412b-maheshchouhanmsk12-c513.a.aivencloud.com';
	}

	public static function getPort(){
		$val = getenv('DB_PORT');
		return (!empty($val) && is_numeric($val)) ? (int)$val : 26284;
	}

	public static function getUser(){
		$val = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: null);
		return (!empty($val)) ? $val : 'avnadmin';
	}

	public static function getPass(){
		$val = getenv('DB_PASSWORD');
		if ($val !== false && $val !== null && $val !== '') {
			return $val;
		}
		$val2 = getenv('DB_PASS');
		if ($val2 !== false && $val2 !== null && $val2 !== '') {
			return $val2;
		}
		return base64_decode('QVZOU19MU0xEdDV6NGFkSzQ5R0xsVFQ4');
	}

	public static function getDbName(){
		$val = getenv('DB_NAME') ?: (getenv('DB_DATABASE') ?: null);
		// Prefer dedicated hospital database over Aiven shared defaultdb
		if (!empty($val) && strtolower((string)$val) !== 'defaultdb') {
			return $val;
		}
		return 'hospital';
	}

	public static function getSsl(){
		$val = getenv('DB_SSL');
		if ($val !== false && $val !== null && $val !== '') {
			return !in_array(strtolower((string)$val), ['0', 'false', 'no', 'off'], true);
		}
		return true;
	}

	public static function getBaseUrl(){
		$val = getenv('BASE_URL');
		if ($val) {
			return rtrim($val, '/');
		}
		return '';
	}

	// Constants for backwards compatibility
	const DB_HOST = "mysql-1a85412b-maheshchouhanmsk12-c513.a.aivencloud.com";
	const DB_NAME = "hospital";
	const DB_USER = "avnadmin";
	const DB_PASSWORD = "";
	const DB_PORT = 26284;

	// SYSTEM 
	const SYSTEM_NAME = "EMR - Hospital Management System";
	const SLOGAN = "EMR";

	// Properties initialized in constructor for PHP 8.x compatibility
	private $db_host;
	private $db_port;
	private $db_user;
	private $db_pass;
	private $db_name;

	public function __construct(){
		$this->db_host = self::getHost();
		$this->db_port = self::getPort();
		$this->db_user = self::getUser();
		$this->db_pass = self::getPass();
		$this->db_name = self::getDbName();
	}

	// METHODS
	public static function redir($page){
		if (!headers_sent()) {
			header("Location: $page"); 
			exit();
		} else {
			echo "<script>window.location.href=" . json_encode($page) . ";</script>";
			echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($page) . "'></noscript>";
			exit();
		}
	}

	public static function includeD(){
	}

	public static function getMonth(){
		return 2419200;
	}

	public static function getWeek(){
		return 604800;
	}
}
