<?php
// this is a 2k micro mvc 'thing'.., what you looking for in inside ./application directory
// based on http://www.henriquebarroso.com/how-to-create-a-simple-mvc-framework-in-php/
error_reporting( E_ALL );
ini_set( "display_errors", 1 );

session_start();
require_once("./hybridauth/Hybrid/Auth.php");
$config_file_path = './hybridauth/config.php';

try{
	$hybridauth = new Hybrid_Auth( $config_file_path );
	if(!$hybridauth->isConnectedWith("Twitter")&&!$hybridauth->isConnectedWith("Facebook")){
		echo "<a href='auth.php?auth=facebook'>Login met Facebook!</a><br />";
		echo "<a href='auth.php?auth=twitter'>Login met Twitter!</a><br />";
		
		
	}else{
		echo "<a href='profile.php'>Profile</a>";
	}
}catch( Exception $e ){
     echo "Ooophs, we got an error: " . $e->getMessage();
     echo " Error code: " . $e->getCode();
}
