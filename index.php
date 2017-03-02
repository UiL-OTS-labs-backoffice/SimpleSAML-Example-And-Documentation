<!DOCTYPE html> 
<?php
require_once('functions.inc');
?>
<html>
	<head>
		<title>How-to Saml</title>
	</head>
	<link rel="stylesheet" type="text/css" href="//npmcdn.com/bootstrap@4.0.0-alpha.5/dist/css/bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="lib/highlight/styles/default.css"/>

	<script
			  src="https://code.jquery.com/jquery-3.1.1.min.js"
			  integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
			  crossorigin="anonymous"></script>
	<script type="//npmcdn.com/tether@1.2.4/dist/js/utils.js"></script>
	<script src="//npmcdn.com/tether@1.2.4/dist/js/tether.min.js"></script>

	<script type="text/javascript" src="//npmcdn.com/bootstrap@4.0.0-alpha.5/dist/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="lib/highlight/highlight.pack.js"></script>
	<script type="text/javascript" src="lib/bootstrap-switch.js"></script>
	<script>
		hljs.initHighlightingOnLoad();
		$('document').ready(function(){
			$('#environmentSelector').bootstrapSwitch({
			    on: 'Production', // default 'On'
			    off: 'Acceptation', // default 'Off'
			    same: true, // default false. same text for on/off and onLabel/offLabel
			    size: 'sm', // xs/sm/md/lg, default 'md'
			    onClass: 'success', //success/primary/danger/warning/default, default 'primary'
			    offClass: 'success', //success/primary/danger/warning/default default 'default'
			});
		});

		function environmentChanged() {
			production = $('#environmentSelector').is(':checked');
			var namidpMetaProd = 'https://namidp.services.uu.nl/nidp/saml2/metadata';
			var namidpMetaAcc = 'https://anamidp.services.uu.nl/nidp/saml2/metadata'
			var logoutProd = 'http://logout.uu.nl';
			var logoutAcc = 'http://logout.acc.uu.nl';
			var generalIdpProd = 'namidp.services.uu.nl';
			var generalIdpAcc = 'anamidp.services.uu.nl';

			$namidpMeta = production ? namidpMetaProd : namidpMetaAcc;
			$logout = production ? logoutProd : logoutAcc;
			$general = production ? generalIdpProd : generalIdpAcc;

			$evn = production ? "On acceptation: " : "On production: ";
			$namIdpMetaTitle = $evn + (production ? namidpMetaAcc : namidpMetaProd);
			$logoutTitle = $evn + (production ? logoutAcc : logoutProd);
			$generalTitle = $evn + (production ? generalIdpAcc : generalIdpProd);

			$('.idp-base-link').html($general); $('.idp-base-link').prop("title", $generalTitle);
			$('.namidp-metadata-link').html($namidpMeta); $('.namidp-metadata-link').prop("title", $namIdpMetaTitle);
			$('.namidp-logout-link').html($logout); $('.namidp-logout-link').prop("title", $logoutTitle);

			addPopover('idp-base-link');
			addPopover('namidp-metadata-link');
			addPopover('namidp-logout-link');

			$('.PHP').each(function(i, block){
				hljs.highlightBlock(block);
			});
		}

		function addPopover(el) {
			$('.' + el).prop('data-toggle', 'tooltip');
			$('.' + el).prop('data-placement', 'top');
			$('.' + el).tooltip();
		}
	</script>
	<body style="padding-top: 20px; padding-bottom: 50px; position: relative" data-spy="scroll" data-target="#scrollSpy">
		<div class="container">
			<div class="row">
				<div class="col-md-3 hidden-sm-down" id="scrollSpy" style="position: relative;">
					<div style="position: fixed;">
					<ul class="nav nav-list about-list hidden-phone hidden-tablet affix fixed" >
						<li><a href="#setup">Set-up SimpleSaml library</a></li>
						<li><a href="#examples">Using SimpleSaml in your Project</a>
							<ul>
								<li><a href="#req-auth">Require Authentication</a></li>
								<li><a href="#login">Login function</a></li>
								<li><a href="#Loginurl">Login URL</a></li>
								<li><a href="#logout">Loging out</a></li>
								<li><a href="#force auth">Force authentication</a></li>
							</ul>
						</li>
						<li><a href="#deviate">Deviate from the Single Sign-On</a></li>
					</ul>
					<p>
					<br/>
					Environment:<br/>
					<input type="checkbox" id="environmentSelector" checked="checked" onchange="environmentChanged();"/>
					</p>
					</div>
				</div>
				<div class="col-md-9 col-sm-12">
						<h1 id="how-to-saml">how-to Saml</h1>
						<small style="padding-bottom: 100px; display: block; position: relative;">
							<div class="col-md-6 col-sm-12">
							Documentation written by <a href="http://uilots-labs.wp.hum.uu.nl/people/#AJM" target="_blank">A.J. de Mooij</a>.<br/>
							Lab Technician and Software Developar at <a href="https://uilots-labs.wp.hum.uu.nl/" target="_blank">Utrecht Institute of Linguistics OTS</a> (UiL OTS)<br/> 
							<a href="https://www.uu.nl/" target="_blank">Utrecht University</a>
							</div>
							<div class="col-md-6 col-sm-12">
							Special thanks to <a href="https://www.uu.nl/medewerkers/TPRdeHaas" target="_blank">T.P.R. de Haas</a>.<br/>Information and Technology Services.<br/><a href="https://www.uu.nl/" target="_blank">Utrecht University</a>
							</div>
						</small>
						<p>This how-to tries to describe the process of setting up SimpleSaml to talk to the Identity Provider of University Utrechts ITS, and subsequently setting up a PHP project to make use of the features offered by SimpleSaml.</p>
						<p>Saml (Security Assertion Markup Language) is an xml-based standard for allowing federated authentication. The Univerity Utrecht is slowly moving all its web applications from LDAP authentication to Saml. With SAML, the user is redirected to a login page of the university, so no passwords have to be sent over the server that hosts an application, while still allowing Solis-ID authentication.</p>
						<p>SAML consists of two parts: The Identity Provider (IdP) and the Service Provider (SP). The Identity Provider is hosted by the university. The Service Provider rests with the application, and communicates with the Identity Provider.</p>
						<p>This how-to tries to describe how to set up a SAML Service Provider to communicate with the universities Identity Provider using the PHP library <i>SimpleSaml</i>. This how-to focusses especially on PHP, but may be useful for other languages for which SAML libraries exist (such as 
							<a href="https://github.com/onelogin/python-saml" target="_blank">Python</a>,
							<a href="https://github.com/onelogin/java-saml" target="_blank">Java</a>,
							<a href="https://github.com/onelogin/ruby-saml" target="_blank">Ruby</a>,
							<a href="https://github.com/onelogin/wordpress-saml" target="_blank">Wordpress</a>, etc
							).</p>

						<p>In the left menu you can select the production or acceptation environment for this how-to (<a href="#environmentCollapse" data-toggle="collapse" aria-expaned="false">read more</a>)</p>
						<div class="collapse" id="environmentCollapse"><div class="card"><div class="card-block">
						<p>ITS provides two environments. The production environment contains all Solis-IDs available for employees and students of Utrecht University and is more strict in how it can be used. For development and acceptation, an acceptation invironment is available. This environment is slightly less strict, which works well for development, but does not contain default Solis-ID's. Instead, you have to work with test accounts, which ITS can create for you.</p>
						<p>In the side menu, you can choose to read this how-to for production or for acceptation. The required URLS are automatically adapted for the environment you chose. Except for in the code boxes, howevering a url will show the value for the other environment, if it differs from the selected environment.</p>
						<p>In the code examples which you can find in the repository, the acceptation environment is always used. To test the examples for production, you will have to adapt the code to production.</p>
						</div></div></div>
						<h2 id="setup">Set-up SimpleSaml library</h2>
						<p>ITS requires all service providers to send its authentication requests over SSL. This means SimpleSaml can only be used with servers that serve the web application over secure HTTP (HTTPS).</p>
						<p>The instructions in this chapter follow the <a href="https://simplesamlphp.org/docs/stable/simplesamlphp-install" target="_blank">SimpleSaml tutorial</a>, but are finetuned on how to work with the Identity Provider served by ITS.</p>
						<p>In this tutorial, the name <var>hostname</var> will be used for the base URL of the web application. The name <var>saml_location</var> will be used for the absolute path saml is installed in. The name <var>saml_url</var> will be used for the url path to the saml service provider (as <var>host/saml_url</var>). The name <var>deploy_directory</var> will be used to refer to a private location on your server, where deployment files are placed. A bash script is provided in the file <var>deploy_saml.sh</var> in this repository, which will take away some of the heavy load.</p>
						<ol>
							<li><p>Download the latest SimpleSaml release from <a href="https://simplesamlphp.org/download" target="_blank">https://simplesamlphp.org/download</a> to <var>deploy_dir</var> on your server.</p></li>
							<li><p>Make sure all PHP packages mentioned in <a href="https://simplesamlphp.org/docs/stable/simplesamlphp-install" target="_blank">section 3 of the SimpleSaml docs</a> are installed. Most of these are standard.</p></li>
							<li><p>Copy the bash script from <var>deploy_saml.sh</var> to your <var>deploy_dir</var>, or be prepared to perform the actions in the script manually once this how-to indicates to run the script. In the script:</p>
								<ol>
									<li><p>Replace the value of <var>SAMLVersion</var> with the version number of the downloaded .tar.gz file. The file should be called <var>simplesamlphp-&lt;version&gt;.tar.gz</var>. If this is not the case, the script will fail.</p></li>
									<li><p>Replace the value of <var>SAMLLocation</var> with the absolute path <var>saml_location</var>. This should be an absolute path to a directory that is not accessible from the web. On default ICT&amp;M servers, this should probably be <i>/hum/web/<var>hostname/</var></i> or <i>/hum/web/<var>hostname</var>/private/</i>. <strong>Make sure to end with a forward slash, or the directory will be overwritten.</strong></p></li>
									<li><p>Replace the value of <var>SAMLPubLocation</var> with an absolute path to where a simlink can be created, such that <var>host/saml_url</var> opens this directory in the browser. On default ICT&amp;M servers, this will most likely be <i>/hum/web/<var>hostname</var>/htdocs/<var>saml_url</var></i></p></li>
								</ol>
							</li>
							<li><p>Generate a private and public key, named <i>saml.pem</i> and <i>saml.crt</i> respectively.</p>
							<pre><code class="bash">$ openssl req -newkey rsa:2048 -new -x509 -days 3652 -nodes -out saml.crt -keyout saml.pem</code></pre>
							<p>Fill out the information that this command asks for as fits your application.</p></li>
							<li><p>From the file <i>simplesaml-<var>&lt;version&gt;</var>.tar.gz</i>, copy the directories <i>config</i> and <i>metadata</i> without changing anything to <var>deploy_directory</var>.</p><p>The following minimal changes need to be made, but further configuration is possible. For most purposes, the changes below will suffice, however.</p>
							<ul>
								<li><strong>config/config.php</strong>
								<ol>
								<li><p>Change the value of <strong>baseurlpath</strong> to <var>host/saml_url/</var> (make sure to end with a forward slash)</p></li>
								<li><p>Change <strong>debug</strong> and <strong>showerrors</strong> to <var>false</var>.</p></li>
								<li><p>Change the value of <strong>auth.adminpassword</strong> to a new password. This is the password used for the admin login on the SimpleSaml Service Provider, so make sure to pick a safe password.</p>
								<li><p>Set <strong>admin.protectedindexpage</strong> and <strong>admin.protectedmetadata</strong> to <var>true</var></p></li>
								<li><p>Change the value of <strong>secretsalt</strong> to a new string of any length. The best practice for generating such a salt is using the following command:</p>
								<pre><code class="bash">$ <?=htmlspecialchars("tr -c -d '0123456789abcdefghijklmnopqrstuvwxyz' </dev/urandom | dd bs=32 count=1 2>/dev/null;echo");?></code></pre></li>
								<li><p>Change the value of <strong>technicalcontact_email</strong> to an e-mail address the technical administrator can receive e-mails on. This e-mailaddress is used by SimpleSaml to send error reports and this e-mail address will be public.</p></li>
								<li><p>Change <strong>session.cookie.secure</strong> and <strong>session.phpsession.httponly</strong> to <var>true</var>. Optionally, update <strong>session.cookie.path</strong>, <strong>session.cookie.domain</strong> and <strong>session.cookie.lifetime</strong> as explained in the comments above those fields.</p></li>
								<li><p>Add <var>"<span class="idp-base-link">namidp.services.uu.nl</span>"</var> and <var>host</var> (stripped of any https or path specifications) to the <strong>trusted.url.domains</strong> array. Add other domains you wish to redirect to after a login or logout request as well.</p></li>
								</ol>
								</li>
								<li><strong>config/authsources.php</strong>
								<ol>
								<li><p>(Optional) Change the array key that is named <var>'default-sp'</var> to a name you want your Service Provider to have.</p></li>
								<li><p>In the array with the (former) key <var>'default-sp'</var>, add two key-value pairs:</p>
								<pre><code class="PHP">'privatekey' => 'saml.pem',
'certificate' => 'saml.crt'</code></pre><p>These keys refer to the private and public keys that were generated earlier.</li>
								<li><p>Change the value of <strong>idp</strong> to <i>"<span class="namidp-metadata-link">https://namidp.services.uu.nl/nidp/saml2/metadata</span>"</i></p></li>
								</ol>
								<p>Example minimal contents configuration:</p>
								<pre><code class="PHP">$config = array(

  // This is a authentication source which handles admin authentication.
  'admin' => array(
    // The default is to use core:AdminPassword, but it can be replaced with
    // any authentication source.

    'core:AdminPassword',
  ),


  // An authentication source which can authenticate against both SAML 2.0
  // and Shibboleth 1.3 IdPs.
  'default-sp' => array(
    'saml:SP',
    'privatekey' => 'saml.pem',
    'certificate' => 'saml.crt',

    // The entity ID of this SP.
    // Can be NULL/unset, in which case an entity ID is generated based on the metadata URL.
    'entityID' => null,

    // The entity ID of the IdP this should SP should contact.
    // Can be NULL/unset, in which case the user will be shown a list of available IdPs.
    'idp' => '<span class="namidp-metadata-link">https://namidp.services.uu.nl/nidp/saml2/metadata</span>',

    // The URL to the discovery service.
    // Can be NULL/unset, in which case a builtin discovery service will be used.
    'discoURL' => null
  )
);</code></pre>
								</li>
							</ul>
							</li>
							<li><p>Run the bash <var>deploy_saml.sh</var> script from the <var>deploy_dir</var>. This will put all the files in the correct locations.</p></li>
							<li><p>Open your browser and go to <var>host/saml_url</var> and login with username <var>admin</var> and the password you set in <var>config/config.php</var>.</p></li>
							<li><p>Click the <var>configuration</var> tab and check if all required parts of the PHP installation are running. Click the <var>sanity check</var> link, to see if the basic setup is configured correctly.</p></li>
							<li><p>Go to the <var>federation</var> tab and click <var>Show Metadata</var>. If no errors are shown, click the <var>get the metadata xml on a dedicated URL</var> link. This will download a meta data XML file.</p>
							<p>Send this file to ITS along with the message that you want to register your application with their Identity Provider. Give the base URL of your application and say if you want to make use of their acceptation or production Identity Provider (depending on what URL you entered in the <var>config/authsources.php</var> file). Also indicate which fields you want the Identity Provider to pass back with a successful authentication redirect (such as solis-ID, full name, e-mail address, etc).</p></li>
							<li><p>Wait for a reply</p></li>
							<li><p>If ITS accepts the request, they will add your Service Provider to their Identity Provider and send you back a file called <var>saml20-idp-remote.php</var>.</p>
							<p>Place this file in the <var>metadata</var> directory inside your <var>deploy_dir</var> and rerun the bash script <var>deploy_saml.sh</var> from your <var>deploy_dir</var>.</p></li>
							<li><p>Go to <var>host/saml_url</var> and click the page <var>authentication</var>. Click <var>test configured authentication sources</var>. This should redirect you to the UU login page.</p><p>Enter your Solis-ID and password (or test account credentials if you are on the acception Identity Provider and your own Solis-ID has not been added) and click <var>login</var>.</p><p>If all works well, you are set up to implement SimpleSaml into your web application. If you see an error, the debugging begins, which this how-to does not provide any answers for.</p></li>
						</ol>

						<h2 id="examples">Using SimpleSaml in your PHP Project</h2>
						<p>Once the library is installed, SimpleSaml is relatively easy to load into your project. The following paragraphs describe how to use functionality from SimpleSaml in a PHP project. These functions are also documented in <a href="//simplesamlphp.org/docs/1.8/simplesamlphp-sp-api" target="_blank">the SimpleSaml docs</a></p>

						<p>A few files with example code are provided in the <a href="https://github.com/UiL-OTS-labs-backoffice/SimpleSAML-Example-And-Documentation" target="_blank">Github repository</a>. These can be used if the instructions in the previous chapter have been completed. Edit the <var>conf.json</var> file and change the value of <var>saml_location</var> to the absolute base path of the SAML installation on your server.</p>

						<p>First, you have to load the library into your project, and then you have to create a SimpleSaml authentication object for your service provider. In this example, it is assumed the service provider is called <var>default-sp</var>. This depends on how you defined your service provider in <var>simplesaml/config/authsources.php</var>.</p>
						<pre><code class="PHP">// Load SimpleSaml library
require_once($samlloc . "lib/_autoload.php");

// Get service provider
$as = new SimpleSAML_Auth_Simple('default-sp');

// Only get attributes if user is authenticated
if($as->isAuthenticated()) {
  $attributes = $as->getAttributes();
}</code></pre>
						<h3 id="req-auth">Require Authentication</h3>
						<p>In most usecases ITS envisions, a website is only accessible for logged in users. In this scenario, a user who visits the website, but is not logged in at any of the other applications using the same Identity Provider, get redirected to the UU Login page. After authenticating with their Solis-ID, the user gets redirected back to the original page. As long as a user is logged in on any of the applications using the same Identity Provider, they do not have to reauthenticate again. This is called Single Sign-On (SSO).</p>
						<p>Enabling Saml authentication on a website where no access is allowed to unauthenticated users is very simple. On all pages where access is denied to unauthenticated users, simply add the line</p>
						<pre><code class="PHP">$as->requireAuth();</code></pre>

						<p>This function can take two arguments:</p>
						
						<ul>
							<li>
								<var>ReturnTo</var> A url to which the user is redirected after successfully authenticating
							</li>
							<li>
								<var>KeepPost</var> A boolean indicating SimpleSaml should send the post data currently available on the page back with the redirect after a successful authentication
							</li>
						</ul>
						
						<pre><code class="PHP">$as->requireAuth(
  array(
    'ReturnTo' => 'https://sp.example.org/login-success.php',
    'KeepPost' => true
  )
);</code></pre>
						<h3 id="login">Login function</h3>
						<p>While <var>requireAuth()</var> works well for webpages that are completely hidden behind an authentication wall, in some cases, it is required to only perform a login request if the user has indicated to want to login. For this, the function <var>login</var> works best. This function can be called on any page. As soon as the function is called, SimpleSaml tries authenticating the user. If the user is already signed in on another application using the same SSO, the user is automatically signed in. Otherwise, the user is redirected to the SimpleSaml login page, and redirected back to the current page after a successful authentication.</p>
						<p>The function takes the same optional parameters as <var>requireAuth()</var>, plus a few more:</p>
						<ul>
							<li><var>isPassive</var> If this is enabled, the service provider tries loging in the user automatically. This succeeds if the user is already logged in to the Identity Provider using SSO. If not, the user is redirected to the <var>ErrorURL</var>. This will not work if <var>ForceAuthn</var> is enabled in either the SP configuration, or in the arguments of this function.</li>
							<li><var>ErrorUrl</var> The URL the user is redirected to if an error occurs (such as not being able to login)</li>
							<li><var>ReturnCallback</var> A callback function that is called by SimpleSaml after the login has been performed. This has the form of a size 2 array, where the first item is an object name that is accessible from the <var>SimpleSAML_Auth_Simple</var> object (<var>saml/lib/SimpleSaml/Auth/Simple.php</var>) and the second item is a public function on that object.</li>
							<li><var>ForceAuthn</var> The opposite of <var>isPassive</var>; If this is enabled, users are always asked for their username and password when this function is called. Therefor, do not call this function with this argument set to true on the page the user is redirected to after login, unless they are not yet authenticated.</li>
						</ul>
						<pre><code class="PHP">$as->login(
  array(
    'ForceAuthn' => true,
    'KeepPost' => true,
    'isPassive' => false,
    'ReturnTo' => 'https://sp.example.org/login-success.php',
    'ErrorURL' => 'https://.../error_handler.php',
    'ReturnCallback' => array('SimpleSAML_Auth_Simple', 'callbackFunction')
  )
);</code></pre>
			<p>In addition, certain items from the service provider configuration can also be passed as an argument to the login function. See <a href="//simplesamlphp.org/docs/1.9/saml:sp" target="_blank">the SimpleSaml docs</a> for a list of configuration parameters</p><p>Some of these parameters may be used on the <var>requireAuth()</var> function as well, but this is largely a process of trial and error</p>
						<h3 id="Loginurl">Login URL</h3>
						<p>A third option is to create a link that redirects the user to the Saml login page. This function takes one optional argument, which is the return url. If no return url is set, the user is redirected to the same page as they started on after authentication.</p>
						<pre><code class="PHP">$url = $as->getLoginURL();
echo sprintf('&lt;a href="%1$s"&gt;login&lt;/a&gt;', htmlspecialchars($url));</code></pre>
						<p>An example using links for logging in and out can be found in <a href="loginOutLinks.php" target="_blank">loginOutLinks.php</a> in this repository</p>
						<h3 id="logout">Loging out</h3>
						<p>There are two ways of logging out. One is to call the <var>logout()</var> function, the other is to request the logout url from SimpleSaml, so you can provide a logout link to the user. In both cases, the <var>ReturnTo</var> should be set to the UU logout page, which handles logging out from the Identity Provider. The logout function which you can call in your project only logs the user out from the service provider. This url is <i><span class="namidp-logout-link">http://logout.uu.nl</span></i>.</p>
						<pre><code class="PHP">// Logout as soon as the page is loaded
$as->logout('<span class="namidp-logout-link">http://logout.uu.nl</span>');

// Or create a logout URL
$url = $as->getLogoutURL('<span class="namidp-logout-link">http://logout.uu.nl</span>');
echo sprintf('&lt;a href="%1$s"&gt;logout&lt;/a&gt;', htmlspecialchars($url));</code></pre>	<p>An example using links for logging in and out can be found in <a href="loginOutLinks.php" target="_blank">loginOutLinks.php</a> in this repository</p>		
						<h3 id="force auth">Force authentication</h3>
						<p>The <var>ForceAuthn</var> parameter can be especially useful to secure certain operations behind a verify login function. This can be the case if you want to make sure the user performing the action is the user that is still logged in, and not someone who noticed a web page is still left open and wants to abuse that.</p>
						<p>Example code is provided in the file <var>verifyPasswordFunctions</var>. Comments are provided to clearify the code, but in general, what happens is this:</p>
						<ol>
							<li>Check the redirect ID passed as a get parameter with the last redirect against the last generated redirect ID from SimpleSaml. If these do not match, this suggests the user is redirected from another page with some valid post data, so the action should not be performed</li>
							<li>Check if the user that was last authenticated with SimpleSaml is the same user as the one that is currently logged in</li>
						</ol>
						<p>The main function is <var>lastLoginValid()</var>, which is passed the authentication source as an argument. The following code is an example of how this can be used in a project.</p>
						<pre><code class="PHP">if(isset($_POST['delete'])) {
 if(!$lastLoginValid($as) {
   $as->login(
     array(
       'ForceAuthn' => true,
       'KeepPost' => true,
     )
   );
 } else {
  if(isAdmin($as->getAttributes['cn'][0])) {
    // perform delete
  } else {
    // User does not have the correct rights. Abort
  }
 } else {
  // Show form where user can delete stuff
 }</code></pre>
 						<p>An example for reauthentication using SimpleSaml can be found in <a href="reAuthenticate.php" target="_blank">reAuthenticate.php</a> in this repository</p>
 						<h2 id="deviate">Deviate from the Single Sign-On</h3>
						<p>ITS envisions most applications to be only accessible for authenticated users. This works by <a href="#req-auth">requiring users to be authenticated</a> before the website loads. If a user is not yet authenticated with the Identity Provider, the user is redirected to the login page and redirected back after succesful authentication. If the user already is authenticated with the Identity Provider, but not with the Service Provider, a <i>passive authentication request</i> is sent out, unless <var>ForceAuthn</var> is enabled either in the request, or in the authentication source configuration. Similarily, in order to logout, a user should be directed to <i><span class="namidp-logout-link">http://logout.uu.nl</span></i> after logging out of the Service Provider, which means the user is not redirected back to the original application</p>
						<p>This makes sense in the scenario envisioned above, as users would otherwise be redirected back directly to a login box after logging out, but in other scenarios, it may be more useful to be redirected directly to the application. By adding the following configuration to your <var>default-sp</var> configuration in <var>config/authsources.php</var>, this can be achieved:</p>
						<pre><code class="PHP">'redirect.sign' => TRUE,
'redirect.validate' => TRUE,
'sign.authnrequest' => TRUE,
'sign.logout' => TRUE,
'validate.logout' => TRUE,</code></pre>
						<p>It is also possible to simply add<pre><code class="PHP">'ForceAuthn' => TRUE</code></pre> to this configuration, but the configuration above is more suited to deal with this. In order to explain this, it is important to understand how the SAML Single Sign-Out strategy works</p>
						<p>The Single Sign-Out strategy is an extension of the Single Sign-On strategy, which reverses the process. With a Single Sign-On request, a user is both authenticated with the Service Provider (which rests with the web application) and the Identity Provider (which actually matches login requests against a user database). In order to be logged in with the Service Provider, one has to be logged in with the Identity Provider, but this does not hold the other way around.</p>
						<p>When a logout request is generated, e.g. by means of <pre><code class="PHP">$as->logout();</code></pre> a function is called that signs the user out of the Service Provider, but not of the Identity Provider. The webpage <i><span class="namidp-logout-link">http://logout.uu.nl</span></i> handles the signout of the user from the Identity Provider, which is why this is where a user should be redirected to after a logout request from the Service Provider.</p> <p>Now imagine a user <var>A</var> signing in on a web application. An authentication request is sent to the Service Provider. With a passive request, the Service Provider requests the Identity Provider if someone is already logged in and if so, logs that user in automatically. With an <i>active</i> request, however, the Service Provider always asks the user to login manually. If user A subsequently logs out from the Service Provider, but not from the Identity Provider, and then a person B tries to authenticate, the Service Provider asks the Identity Provider to check the credentials of user B, but the Identity Provider still has a person A logged in and thus the authentication fails.</p>

						<p>The <var>ForceAuthn</var> flag ensures users have to log in, even if they are already logged in with the Identity Provider. This is therefor a good feature for verifying the identity of the currently logged in user, but not so much for disabling the Single Sign-On and Out services. The configuration given above ensures two things:</p>
						<ol>
						<li>If a user logs out, the logout request is automatically deferred to the Identity Provider as well, so if another user then logs in, no error occurs</li>
						<li>If a user is already authenticated, but is asked to verify his or her identity and the credentials entered in that box do not match those of the user that was already signed in, that original user is signed out, rather than that the new user is signed in</li>
						</ol>
					</div>
				</div>
			</div>
	</body>
</html>