<?php 
	// load hybridauth base file, change the following paths if necessary 
	// note: in your application you probably have to include these only when required.
	$hybridauth_config = './hybridauth/config.php';
	require_once( "./hybridauth/Hybrid/Auth.php" );
	require_once("./classes/Database.class.php");
	
	
	function execute_query( $sql, $param ){
		$db = new Database(); 
		$results = $db->doQuery($sql, $param);
		return $results;
	}
