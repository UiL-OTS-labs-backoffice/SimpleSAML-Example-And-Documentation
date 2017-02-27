<?php
require_once('functions.inc');

// The code for testing a reauthentication request
require_once('verifyPasswordFunctions.inc');

// Reauthentication only works if the user is already logged in
$as->requireAuth();

// Test if user performed action requiring reauthentication
if(isset($_POST['testReAuthenticate']) && $_POST['testReAuthenticate'] === "testNow") {

	if(lastLoginValid($as)) {
		// If last authentication attempt succesful, execute code
		echo "You have succesfully verified your identity.";
		echo "<br/>Your attributes are:";
		echo prettyAttributes($as->getAttributes());
	} else {
		// Else, show login box
		$as->login(array(
			'ForceAuthn' => true,
			'KeepPost' => true
		));
	}
}

?>
<h1>Reauthenticate to see your SAML attributes</h1>
<p>Click the button below to initiate verify login</p>
<form method="POST">
	<input type="submit" name="testReAuthenticate" value="testNow"/>
</form>

Or <a href="<?=$as->getLogoutUrl();?>">logout now</a>