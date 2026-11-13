<?php 

class Doctor{
	public function __construct(){}

	public static function add($token, $firstName, $secondName, $email, $phone, $gender, $role){
		if($token == ""){
			$ujd = function_exists('unixtojd') ? @unixtojd(time()) : time();
			$token = md5(time().uniqid().$ujd.$role.$email.$phone);
			$password = "hospital"; 
			
			Db::insert(
				"users", 
				array("firstName", "secondName", "email", "password", "token", "status", "phone", "gender", "role"), 
				array($firstName, $secondName, $email, $password, $token, 2, $phone, $gender, $role)
			);
			
			Messages::success("Doctor has been added successfully");
		} else {
			self::edit($token, $firstName, $secondName, $email, $phone, $role);
		}
	}
	
	public static function load(){
		$query = Db::fetch("users", "", "status = ? ", "2", "id DESC", "", "");
		if(Db::count($query)){
			echo"<div class='form-holder'>
					<table class='table table-bordered table-stripped'> 
					<tr>
						<td><strong>First Name</strong></td> 
						<td><strong>Second Name</strong></td> 
						<td><strong>Email</strong></td> 
						<td><strong>Phone</strong></td> 
						<td><strong>Gender</strong></td> 
						<td><strong>Role</strong></td>
						<td><strong>Action</strong></td>
					</tr>
			"; 
			
			while($data = Db::assoc($query)){
				$firstName = htmlspecialchars($data['firstName']); 
				$secondName = htmlspecialchars($data['secondName']); 
				$email = htmlspecialchars($data['email']); 
				$phone = htmlspecialchars($data['phone']); 
				$gender = htmlspecialchars($data['gender']); 
				$role = htmlspecialchars($data['role']); 
				$token = htmlspecialchars($data['token']); 
				
				echo "<tr>
						<td>$firstName</td> 
						<td>$secondName</td> 
						<td>$email</td> 
						<td>$phone</td> 
						<td>$gender</td> 
						<td>$role</td> 
						<td><center><a href='actions.php?action=remove-doc&token=$token'>Delete</a> | <a href='add-doctors.php?token=$token'>Edit</a></center></td>
					</tr>";
			}
			
			echo "</table></div>";
			return; 
		}
		
		Messages::info("No doctors found in the records");
	}
	
	public static function getArray($name, $labelDistance){
		$nextLabel = 12 - (int) $labelDistance; 
		$query = Db::fetch("users", "", "status = ? ", "2", "id DESC", "", "");
		$array = array(); 
		echo "<div class='form-group'>
				<label class='col-md-".$labelDistance."' >Doctors</label>
				<div class='col-md-".$nextLabel."'>
				<select name='$name' class='form-control'>
					<option value='' >--Select a Doctor--</option>
				";
				
		while($data = Db::assoc($query)){
			$token = $data['token']; 
			$firstName = User::get($token,"firstName"); 
			$secondName = User::get($token, "secondName"); 
			
			echo "<option value='$token'>$firstName $secondName</option> ";
		}
		echo "</select></div></div> ";
	}
	
	public static function delete($token){
		Db::delete("users", "token = ? ", $token);
	}
	
	public static function edit($token, $firstName, $secondName, $email, $phone, $role){
		Db::update("users",
		array("firstName", "secondName", "email", "phone", "role"), 
		array($firstName, $secondName, $email, $phone, $role), 
		"status = ? AND token = ? ", array(2, $token)); 
		
		Messages::success("You have edited this doctor <strong><a href='doctors-record.php'>View Edits</a></strong> ");
	}
}
