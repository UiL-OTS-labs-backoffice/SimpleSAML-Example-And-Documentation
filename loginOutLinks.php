<?php
require_once('functions.inc');

if($as->isAuthenticated()) {
	// $url = $as->getLogoutUrl('http://anamidp.services.uu.nl/nidp/logout');
	$url = $as->getLogoutUrl('http://logout.acc.uu.nl');
	echo "You are logged in.";
	echo "<br/>Your attributes are:";
	echo prettyAttributes($as->getAttributes());
	echo sprintf('<a href="%1$s">Click here to logout</a>', $url);
} else {
	$url = $as->getLoginUrl();
	echo sprintf('<a href="%1$s">login</a>', $url);
}