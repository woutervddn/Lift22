<?php
class User {
	private $db;
	function __construct(){
        $this->db = new Database();
	}
	function create( $first_name, $last_name, $birthdate, $email, $location){ 
		$sql = "INSERT INTO users ( first_name, last_name, birthdate, email, location, created_at ) VALUES ( :first_name, :last_name, DATE(:birthdate), :email, :location, NOW() ) ";
		$param = array(":first_name" => $first_name, ":last_name" => $last_name, ":birthdate" => $birthdate, ":email" => $email, ":location" => $location);
		if($lastID = $this->db->execQuery($sql, $param)){
			if(is_numeric($lastID)){
				return $lastID;
			}else{
				return false;
			}
		}
	}

	function update( $user_id, $email, $password, $first_name, $last_name){ 
		$sql = "UPDATE users SET email = :email, password = :password, first_name = :first_name, last_name = :last_name WHERE id = :userid LIMIT 1";
		$param = array(':email' => $email, ':password' => $password, ':first_name' => $first_name, ':last_name' => $last_name, ':userid' => $user_id);
		return $this->db->doQuery($sql, $param);
	}

	function find_by_id( $id ){
		$sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
		$param = array(":id" => $id);
		foreach($this->db->doQuery($sql, $param) as $user){
			return $user;
		}
	}

	function find_by_email( $email ){
		$sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
		$param = array(":email" => $email);
		foreach($this->db->doQuery($sql, $param) as $user){
			return $user;
		}
	}

	function find_by_email_and_password( $email, $password ){
		$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' LIMIT 1";
		$param = array(":email" => $email, ":password" => $password);
		foreach($this->db->doQuery($sql, $param) as $user){
			return $user;
		}
	}
}
