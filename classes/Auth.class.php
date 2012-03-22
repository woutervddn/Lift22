<?php
class Authentication{
	private $db;
	function __construct(){
		$this->db = new Database();
	}
	function find_by_provider_uid( $provider, $provider_uid ){
		$sql = "SELECT * FROM authentications WHERE LOWER(provider) = LOWER(:provider) AND provider_uid = :provider_uid LIMIT 1";
		$param = array(":provider" => $provider, ":provider_uid" => $provider_uid);
		foreach($this->db->doQuery($sql, $param) as $result){
			return $result;
		}
		
	}

	function create( $user_id, $provider, $provider_uid, $display_name, $profile_url){ 
		$sql = "INSERT INTO authentications ( user_id, provider, provider_uid, display_name, profile_url, created_at ) VALUES ( :user_id, :provider, :provider_uid, :display_name, :profile_url, NOW() ) ";
		$param = array(":user_id" => $user_id, ":provider" => $provider, ":provider_uid" => $provider_uid, ":display_name" => $display_name, ":profile_url" => $profile_url); 
		if($lastID = $this->db->execQuery($sql, $param)){
			if(is_numeric($lastID)){
				return $lastID;
			}else{
				return false;
			}
		}
	} 
	
	function find_by_user_id( $user_id ){ 
		$sql = "SELECT * FROM authentications WHERE user_id = :userid";
		$param = array(":userid" => $user_id);
		$results = $this->db->doQuery($sql, $param);
		return $results;
	}
	
	function provider_authenticated_by_userid($userid, $provider){
		$sql = "SELECT * FROM authentications WHERE user_id = :userid AND LOWER(provider) = LOWER(:provider) LIMIT 1";
		$param = array(":userid" => $userid, ":provider" => $provider);
		$rows = $this->db->rowCount($sql, $param);
		if($rows == 1){
			return true;
		}else{
			return false;
		}
	}
}
