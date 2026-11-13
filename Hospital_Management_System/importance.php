<?php 

// this is the important file in the system, 
// it initializes all the classes and objects of the system

ob_start();
if (session_status() === PHP_SESSION_NONE) {
	@session_start();
}

// classes autoload (PHP 8.x compatible)
spl_autoload_register(function($file){
	$file1 = __DIR__ . "/classes/{$file}.class.php";
	$file2 = __DIR__ . "/classes/{$file}.php";
	if (file_exists($file1)) {
		require_once $file1;
	} elseif (file_exists($file2)) {
		require_once $file2;
	}
});

// functions

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
