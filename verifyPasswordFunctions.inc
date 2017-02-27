<?php
require_once('functions.inc');

/**
 * This function checks if the last attempted saml authentication
 * was performed succesfully. This function can be used to verify
 * a user that is already logged in.
 *
 * This method also checks if the last redirect id, i.e. 
 * the id that was passed to the current page by the 
 * redirector, is also the last authentication redirect 
 * that SimpleSaml sent for the service provider. 
 * This check ensures the current redirect was sent by 
 * SimpleSaml and prohibits cross site attacks
 *
 * @param as 	Authentication source
 * @return bool True iff the last login is valid for the user
 * 				that was already logged in
 */
function lastLoginValid($as) {
	$redirReceivedId = _getRedirId();
	$redirVerifyId = _getRedirValidId($as);
	$lastUsernameValid = _checkLastLoginUserIsValid($as);

	$lastValid = 
		$as->isAuthenticated() && 
		$redirReceivedId !== false && 
		$redirVerifyId !== false && 
		$redirReceivedId === $redirVerifyId && 
		$lastUsernameValid;

	return $lastValid;
}

/**
 * Extracts the redirect id passed by the referrer from the url.
 *
 * @return string 	Redirect Id provided by referer
 */
function _getRedirId() {
	$redirId = false;

	if(isset($_SERVER['HTTP_REFERER'])) {
		
		// TODO: check if referer is correct address. Otherwise, return false
		// This should be done based on the base url set in defs.

		parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $queries);
		if(isset($queries['RedirId'])) {
			$redirId = $queries['RedirId'];
		} else if(isset($_SERVER['QUERY_STRING'])) {
			parse_str($_SERVER['QUERY_STRING'], $queries);
			if(isset($queries['RedirId'])) {
				$redirId = $queries['RedirId'];
			}
		} else if(isset($_REQUEST['RedirId'])) {
			$redirId = $_REQUEST['RedirId'];
		}
	}

	return $redirId;
}

/**
 * Extracts the last redirect id that was provided by 
 * SimpleSaml
 * 
 * @param as 		Authentication source
 * @param string 	Last redirect id of SimpleSaml
 */
function _getRedirValidId($as) {
	$prevAuth = $as->getAuthDataArray();
	$redirUrl = false;
	$redirId = false;
	if(isset($prevAuth["saml:sp:prevAuth"]) && isset($prevAuth["saml:sp:prevAuth"]["redirect"])) {
		$redirUrl = $prevAuth["saml:sp:prevAuth"]["redirect"];
		if($redirUrl) {
			parse_str(parse_url($redirUrl, PHP_URL_QUERY), $queries);
			if(isset($queries['RedirId'])) {
				$redirId = $queries['RedirId'];
			}
		}
	} 

	return $redirId;
}

/**
 * Check if the currently authenticated user is also
 * the user that attempted to authenticate with the last
 * authentication request. This works, because if a user
 * is already signed in with the IdP, no other user can
 * login without first logging out, after which password
 * verification is no longer available
 * 
 * @param as 	Authentication source
 * @return bool True iff last logged in user is valid
 */
function _checkLastLoginUserIsValid($as) {
	$attributes = $as->getAttributes();
	$curUser = false;
	$lastUser = _getLastLoginUser($as);
	if(isset($attributes[USERNAME_FIELD])) {
		$curUser = $attributes[USERNAME_FIELD];
	}

	$lastLoginUserValid = $curUser !== false && $lastUser !== false && $curUser === $lastUser;

	return $lastLoginUserValid;
}

/**
 * Method to search auth data array for the user that
 * authenticated on the last attempt
 * @param $as 		Auth Source
 * @return string 	Username
 */
function _getLastLoginUser($as) {
	$username = false;
	$prevAuth = $as->getAuthDataArray();
	if(isset($prevAuth['Attributes']) && isset($prevAuth['Attributes'][USERNAME_FIELD])) {
		$username = $prevAuth['Attributes'][USERNAME_FIELD];
	}
	
	return $username;
}
?>