<?php
class authentication extends model { 
	function find_by_provider_uid( $provider, $provider_uid ){
		$sql = "SELECT * FROM authentications WHERE provider = :provider AND provider_uid = :provider_uid LIMIT 1";
		$param = array(":provider" => $provider, ":provider_uid" => $provider_uid);
		$results = execute_query($sql, $param);
		foreach($results as $result){
			return $result;
		} 
	}

	function create( $user_id, $provider, $provider_uid, $email, $display_name, $first_name, $last_name, $profile_url, $website_url ){ 
		$sql = "INSERT INTO authentications ( user_id, provider, provider_uid, email, display_name, first_name, last_name, profile_url, website_url, created_at ) VALUES ( :userid, :provider, :provider_uid, :email, :display_name, :first_name, :last_name, :profile_url, :website_url, NOW() ) ";
		$param = array(":user_id" => $user_id, ":provider" => $provider, ":provider_uid" => $provider_uid, ":email" => $email, ":display_name" => $display_name, ":first_name" => $first_name, "last_name" => $last_name, ":profile_url" => $profile_url, ":website_url" => $website_url); 
		return execute_query($sql, $param);
	} 
	
	function find_by_user_id( $user_id ){ 
		$sql = "SELECT * FROM authentications WHERE user_id = :userid LIMIT 1";
		$param = array(":userid" => $user_id);
		$results = execute_query($sql, $param);
		foreach($results as $result){
			return $result;
		} 
		
	} 
}
