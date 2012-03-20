<?php
class user extends model {
	function create( $email, $password, $first_name, $last_name){ 
		$sql = "INSERT INTO users ( email, password, first_name, last_name, created_at ) VALUES ( :emil, :password, :first_name, :last_name, NOW() ) ";
		$param = array(":email" => $email, ":password" => $password, ":first_name" => $first_name, ":last_name" => $last_name);
		return execute_query($sql, $param);
	}

	function update( $user_id, $email, $password, $first_name, $last_name){ 
		$sql = "UPDATE users SET email = :email, password = :password, first_name = :first_name, last_name = :last_name WHERE id = :userid LIMIT 1";
		$param = array(':email' => $email, ':password' => $password, ':first_name' => $first_name, ':last_name' => $last_name, ':userid' => $user_id);
		return execute_query($sql, $param);
	}

	function find_by_id( $id ){
		$sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
		$param = array(":id" => $id);
		$results = execute_query($sql, $param);
		foreach($results as $result){
			return $result;
		} 
	}

	function find_by_email( $email ){
		$sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
		$param = array(":email" => $email);
		$results = execute_query($sql, $param);
		foreach($results as $result){
			return $result;
		} 
	}

	function find_by_email_and_password( $email, $password ){
		$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' LIMIT 1";
		$param = array(":email" => $email, ":password" => $password);
		$results = execute_query($sql, $param);
		foreach($results as $result){
			return $result;
		} 
	}
}
