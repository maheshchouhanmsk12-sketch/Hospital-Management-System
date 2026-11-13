<?php 

// this is the important file in the system, 
// it initializes all the classes and objects of the system

ob_start();
if (session_status() === PHP_SESSION_NONE) {
	@session_start();
}

// Case-insensitive autoloader for Linux compatibility
spl_autoload_register(function($file){
	$dir = __DIR__ . "/classes";
	$candidates = [
		"$dir/{$file}.class.php",
		"$dir/" . ucfirst(strtolower($file)) . ".class.php",
		"$dir/" . strtolower($file) . ".class.php",
		"$dir/" . strtoupper($file) . ".class.php",
		"$dir/{$file}.php",
		"$dir/" . ucfirst(strtolower($file)) . ".php",
	];

	foreach ($candidates as $cand) {
		if (file_exists($cand)) {
			require_once $cand;
			return;
		}
	}

	// Dynamic case-insensitive scanning of classes directory
	if (is_dir($dir)) {
		$files = scandir($dir);
		foreach ($files as $f) {
			if (strcasecmp($f, "{$file}.class.php") === 0 || strcasecmp($f, "{$file}.php") === 0) {
				require_once "$dir/$f";
				return;
			}
		}
	}
});

// Pre-load core classes to eliminate any case-sensitivity delays
require_once __DIR__ . "/classes/Config.class.php";
require_once __DIR__ . "/classes/Db.class.php";
require_once __DIR__ . "/classes/User.class.php";
require_once __DIR__ . "/classes/Messages.class.php";

if (!class_exists('CONFIG', false)) {
	class_alias('Config', 'CONFIG');
}
if (!class_exists('DB', false)) {
	class_alias('Db', 'DB');
}

// initialize user variables safely
$userFirstName = "";
$userSecondName = "";
$userEmail = "";
$userPassword = "";
$userToken = "";
$userStatus = "";
$userPhone = "";
$userProfile = "";
$userGender = "";
$userRole = "";

if(User::loggedIn()){
	$token = isset($_COOKIE['emr-user']) ? $_COOKIE['emr-user'] : ''; 
	$userFirstName = User::get($token, "firstName");
	$userSecondName = User::get($token, "secondName");
	$userEmail = User::get($token, "email");
	$userPassword = User::get($token, "password");
	$userToken = User::get($token, "token");
	$userStatus = User::get($token, "status");
	$userPhone = User::get($token, "phone");
	$userProfile = User::get($token, "profile");
	$userGender = User::get($token, "gender");
	$userRole = User::get($token, "role");
	
	if($userStatus == 1){
		$userRole = "Admin";
	} else {
		$userRole = !empty($userRole) ? $userRole : "Doctor";
	}
}
