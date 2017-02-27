<?php

// Read configuration file. Update this file before running examples
$conf_file = fopen("conf.json", "r") or die("No configuration file found!");
$conf = json_decode(fread($conf_file, filesize("conf.json")));
fclose($conf_file);

define('USERNAME_FIELD', $conf->username_field_name);

// Load the lib/_autoload.php library from saml
require_once($conf->saml_location . 'lib/_autoload.php');

// Create an authentication source.
// In this example, the authentication source is called
// 'default-sp'. This is defined in config/authsources.php
$as = new SimpleSAML_Auth_Simple('default-sp');

// Read attributes of the authenticated user
$attributes = $as->getAttributes();


/**
 * Created a list of all attributes for overview
 * @param $attr 	authsource attribtues
 * @return 			html string
 */
function prettyAttributes($attr) {
	$str = "";
	if(is_array($attr)) {
		$str .= "<ul>";
		foreach($attr as $k => $v) {
			$str .= '<li><b>' . $k . '</b>: ';
			$str .= prettyAttributes($v);
			$str .= '</li>';
		}
		$str .= '</ul>';
	} else {
		try {
			$str .= htmlentities($attr);
		} catch(Exception $e) {
			print("Could not encode something. Unknown type " . typeof($e));
		}
	}
	return $str;
}