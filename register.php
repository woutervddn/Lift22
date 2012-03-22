<?php
// this is a 2k micro mvc 'thing'.., what you looking for in inside ./application directory
// based on http://www.henriquebarroso.com/how-to-create-a-simple-mvc-framework-in-php/
error_reporting( E_ALL );
ini_set( "display_errors", 1 );

session_start();
require_once("./hybridauth/Hybrid/Auth.php");
require_once("./classes/Database.class.php");
$db = new Database();
$config_file_path = './hybridauth/config.php';
$hybridauth = new Hybrid_Auth( $config_file_path );
if(isset($_GET['callback'])){
	echo "Authentication with ".$_GET['callback'];
	echo "<a href='?reg'>Registreer</a>";
}elseif(isset($_GET['auth'])){
	if(!$hybridauth->isConnectedWith($_GET['auth'])){
		$hybridauth->authenticate($_GET['auth'], array("hauth_return_to" => "http://dump.visionsandviews.net/lift/register.php?callback=twitter"));
	}
	echo "Already authenticated";
	echo "<a href='?reg'>Registreer</a>";
}elseif(isset($_GET['reg'])){
	try{
		
		if($hybridauth->isConnectedWith("Twitter") || $hybridauth->isConnectedWith("Facebook")){
			$connectedProviders = $hybridauth->getConnectedProviders();
			$firstRun = true;
			if(!empty($connectedProviders)){
				foreach($connectedProviders as $connectedProvider){
					$insertUserSQL = "INSERT INTO `users` (email, first_name, last_name, created_at) VALUES (:email, :firstname, :lastname, NOW())";
					$insertAuthSQL = "INSERT INTO `authentications` (user_id, provider, provider_uid, display_name, profile_url, website_url, email, first_name, last_name, created_at) VALUES 
					(:userid, :provider, :provider_uid, :displayname, :profileurl, :websiteurl, :email, :firstname, :lastname, NOW())";
					
					
					$adapter = $hybridauth->authenticate($connectedProvider);
					$user_data = $adapter->getUserProfile();
					$sql = "SELECT `id` FROM `users` WHERE `id` = (SELECT `user_id` FROM `authentications` WHERE LOWER(provider) = LOWER(:provider) AND `provider_uid` = :provider_uid)";
					$param = array(":provider" => $connectedProvider, ":provider_uid" => $user_data->identifier);
					$usersInDatabase = $db->rowCount($sql, $param);
					
					if(($usersInDatabase == 0) && ($firstRun)){
						$param = array(":email" => $user_data->email, ":firstname" => $user_data->firstName, ":lastname" => $user_data->lastName);
						$userID = $db->execQuery($insertUserSQL, $param);
						echo "User inserted into Database<br />";
					
					}
					if($usersInDatabase == 0){
						$selectUserIDSQL = "SELECT `id` FROM `users` WHERE `email` = :email AND `first_name` = :firstname";
						$param = array(":email" => $user_data->email, ":firstname" => $user_data->firstName);
						$userIDs = $db->doQuery($selectUserIDSQL, $param);
						foreach($userIDs as $userID){
							$userIdent = $userID;
						}
						$param = array(":userid" => $userIdent['id'], ":provider" => $connectedProvider, ":provider_uid" => $user_data->identifier, ":displayname" => $user_data->displayName, ":profileurl" => $user_data->profileURL, ":websiteurl" => $user_data->webSiteURL, ":email"=>$user_data->email, ":firstname" => $user_data->firstName, ":lastname" => $user_data->lastName );
						$db->execQuery($insertAuthSQL, $param);
						echo "User authentication data in database<br />";
					}
					
					$firstRun = false;	
				}
			}
		}
	}catch( Exception $e ){
	    echo "Ooophs, we got an error: " . $e->getMessage();
	}
}else{
	echo "Authenticate with one of the following profiles<br />";
	?>
	<a href="?auth=google">Sign-in with Google</a><br /> 
	<a href="?auth=facebook">Sign-in with Facebook</a><br />
	<a href="?auth=twitter">Sign-in with Twitter</a><br />
	<?php	
}
?>
