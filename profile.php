<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
require_once ("./hybridauth/Hybrid/Auth.php");
require_once ("./classes/Database.class.php");
require_once ("./classes/User.class.php");
require_once ("./classes/Auth.class.php");
$db = new Database();
$user = new User();
$auth = new Authentication();
$config_file_path = './hybridauth/config.php';
$userDataInDatabase = array();
$availableProviders = array("twitter", "facebook");
function lower(&$string) {
						$string = strtolower($string);
					}
try {
	$hybridauth = new Hybrid_Auth($config_file_path);
	if (isset($_GET['callback'])) {
		$callbackProvider = $_GET['callback'];
		$connectedProviders = $hybridauth -> getConnectedProviders();
		if (!empty($connectedProviders)) {
			foreach ($connectedProviders as $connectedProvider) {
				$adapter = $hybridauth -> authenticate($connectedProvider);
				$user_data = $adapter -> getUserProfile();
				$userInfo = $auth -> find_by_provider_uid($connectedProvider, $user_data -> identifier);
				if (!empty($userInfo)) {
					$userDataInDatabase = $user -> find_by_id($userInfo['user_id']);
				}
			}
		} else {
			header("Location: index.php");
		}
	} elseif (isset($_GET['reg'])) {
		if (isset($_POST['register'])) {
			$firstname = $_POST['firstName'];
			$lastname = $_POST['lastName'];
			$birthdate = $_POST['birthDate'];
			$email = $_POST['email'];
			$location = $_POST['location'];
			$userID = $user -> create($firstname, $lastname, $birthdate, $email, $location);
			$connectedProviders = $hybridauth -> getConnectedProviders();
			foreach ($connectedProviders as $connectedProvider) {
				$adapter = $hybridauth -> getAdapter($connectedProvider);
				$user_data = $adapter -> getUserProfile();
				$auth -> create($userID, $connectedProvider, $user_data -> identifier, $user_data -> displayName, $user_data -> profileURL);
			}
			header("Location: profile.php");
		}elseif(isset($_POST['update'])){
			$connectedProviders = $hybridauth -> getConnectedProviders();
			$selectedProviders  = array();
			$userID = NULL;
			foreach ($connectedProviders as $connectedProvider) {
				$adapter = $hybridauth -> getAdapter($connectedProvider);
				$user_data = $adapter -> getUserProfile();
				$authUser = $auth->find_by_provider_uid($connectedProvider, $user_data->identifier);
				if($authUser != NULL){
					array_push($selectedProviders, $authUser['provider']);
					$userID = $authUser['user_id'];
				}
			}
			array_walk($selectedProviders, "lower");
			array_walk($connectedProviders, "lower");
			$notLinkedProviders = array_diff($connectedProviders, $selectedProviders);
			foreach($notLinkedProviders as $notLinkedProvider){
				$adapter = $hybridauth -> getAdapter($notLinkedProvider);
				$user_data = $adapter -> getUserProfile();
				$auth -> create($userID, $notLinkedProvider, $user_data -> identifier, $user_data -> displayName, $user_data -> profileURL);
			}
			header("Location: profile.php");
		}
			
			
		
	} else {
		$connectedProviders = $hybridauth -> getConnectedProviders();
		if (!empty($connectedProviders)) {
			foreach ($connectedProviders as $connectedProvider) {
				$adapter = $hybridauth -> authenticate($connectedProvider);
				$user_data = $adapter -> getUserProfile();
				$userInfo = $auth -> find_by_provider_uid($connectedProvider, $user_data -> identifier);
				if (!empty($userInfo)) {
					$userDataInDatabase = $user -> find_by_id($userInfo['user_id']);

				}
			}
		} else {
			header("Location: index.php");
		}

	}
} catch( Exception $e ) {
	echo "Ooophs, we got an error: " . $e -> getMessage();
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en">
	<head>
		<link rel="stylesheet" href="application/public/css.css" type="text/css" >
		<style type="text/css">
			.userInfo {
				width: 400px;
			}
			.userInfo label {
				float: left;
				width: 150px;
			}
			.userInfo input {
				float: right;
				width: 150px
			}
		</style>
	</head>
	<body>
		<h1>Welcome back</h1>
		<br />
		<a href="index.php">Home</a>
		<form method="POST" action="profile.php?reg">
			<div class="userInfo">
				<?php
				if(!empty($userDataInDatabase)){
					?>
					<h2>Gegevens in de database</h2>
					<label for="firstName">Voornaam</label>
					<input type="text" disabled name='firstName' value='<?php echo $userDataInDatabase['first_name'];?>' />
					<label for="lastName">Achternaam</label>
					<input type="text" disabled name='lastName' value='<?php echo $userDataInDatabase['last_name'] ?>' />
					<label for="birthDate">Verjaardag</label>
					<input type="text" disabled name='birthDate' value='<?php echo $userDataInDatabase['birthdate'];?>' />
					<label for="email">Email</label>
					<input type="text" name='email' value='<?php echo $userDataInDatabase['email'];?>' />
					<label for="location">Huidige locatie</label>
					<input type="text" name='location' value='<?php echo $userDataInDatabase['location'];?>' />
					<?php
				}elseif(empty($userDataInDatabase)){
					?>
					<h2>Gegevens van <?php echo $connectedProvider;?></h2>
					<label for="firstName">Voornaam</label>
					<input type="text" name='firstName' value='<?php echo $user_data -> firstName;?>' />
					<label for="lastName">Achternaam</label>
					<input type="text" name='lastName' value='<?php echo $user_data -> lastName;?>' />
					<label for="birthDate">Verjaardag</label>
					<input type="text" name='birthDate' value='<?php echo $user_data -> birthYear . "-" . $user_data -> birthMonth . "-" . $user_data -> birthDay;?>' />
					<label for="email">Email</label>
					<input type="text" name='email' value='<?php echo $user_data -> email;?>' />
					<label for="location">Huidige locatie</label>
					<input type="text" name='location' value='' />
					<?php
				}
				//User exists in Database, we can now list the associated providers for this account.
				if(!empty($userDataInDatabase)){
					?>
					<table width="100%" border="0" cellpadding="2" cellspacing="2">
						<?php
						// Looping through all the connected providers
						// And show if they are stored in the database
						foreach($connectedProviders as $connectedProvider){
							$adapter = $hybridauth->getAdapter($connectedProvider);
							$user_data = $adapter -> getUserProfile();
							$accountLinkedToThisUserInDatabase = $auth->find_by_provider_uid($connectedProvider, $user_data -> identifier);
							?>
							<tr>
								<td valign="top">
								<fieldset>
									<legend>
										Associated authentications
									</legend>
									<table width="100%" cellspacing="0" cellpadding="3" border="0">
										<tbody>
											<tr>
												<td width="35%"><b>Provider</b></td>
												<td width="65%">&nbsp; <?php echo $connectedProvider;?></td>
											</tr>
											<tr>
												<td><b>Provider UID</b></td>
												<td>&nbsp; <?php echo $accountLinkedToThisUserInDatabase['provider_uid'];?></td>
											</tr>
											<tr>
												<td><b>Display name</b></td>
												<td>&nbsp; <?php echo $accountLinkedToThisUserInDatabase['display_name'];?></td>
											</tr>
											<tr>
												<td><b>User profile URL</b></td>
												<td>&nbsp; <?php echo $accountLinkedToThisUserInDatabase['profile_url'];?></td>
											</tr>
											<tr>
												<td><b>Link this account to your Lift22-account</b></td>
												<td> <input type="checkbox" name="providerSelect[]"
												<?php
												
												if (in_array(ucfirst($accountLinkedToThisUserInDatabase['provider']), $connectedProviders)) {
													echo "checked ";
												}
												?>
												value=<?php echo $connectedProvider;?>"/></td>
											</tr>
										</tbody>
									</table>
								</fieldset>
								</td>
							</tr>
							<?php
							}
						}
						?>
					</table>
					<?php
					
					array_walk($availableProviders, "lower");
					array_walk($connectedProviders, "lower");
					$notLinkedProviders = array_diff($availableProviders, $connectedProviders);
					foreach ($notLinkedProviders as $notLinkedProvider) {
						echo "<a href='auth.php?auth=" . $notLinkedProvider . "'>Link {$notLinkedProvider}";
					}
					if(!empty($userDataInDatabase)){
					?>
					<input type="submit" value="Update" name="update"/>
					<?php
					}else{
					?>
					<input type="submit" value="Registreer" name="register"/>
					<?php
					}
					?>
					</div>
					</form>
					</body>
					</html>
