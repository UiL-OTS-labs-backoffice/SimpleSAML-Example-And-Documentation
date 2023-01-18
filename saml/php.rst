PHP Guide
=========

The instructions in this chapter follow the
`SimpleSaml tutorial <https://simplesamlphp.org/docs/stable/simplesamlphp-install>`_,
but are finetuned on how to work with the Identity Provider served by ITS.

.. contents:: **Table of Contents**
    :local:
    :depth: 3

SAML Setup
**********

In this guide, the name ``hostname`` will be used for the base URL of the web
application. The name ``saml_location`` will be used for the absolute path
SimpleSAMLPHP is installed in. The name ``saml_url`` will be used for the url
path to the saml service provider (as ``host/saml_url``). The name
``deploy_directory`` will be used to refer to a private location on your server,
where deployment files are placed.

A bash script is provided at the end of this page, which will take away some
of the heavy load.

1. Download the latest SimpleSaml release from
   https://simplesamlphp.org/download to ``deploy_dir`` on your server.

2. Make sure all PHP packages mentioned in
   `section 3 <https://simplesamlphp.org/docs/stable/simplesamlphp-install>`_
   of the SimpleSaml docs are installed. Most of these are standard.

3. Copy the bash script :ref:`deploy_saml.sh` to your ``deploy_dir``, or be
   prepared to perform the actions in the script manually once this how-to
   indicates to run the script. In the script:

   1. Replace the value of SAMLVersion with the version number of the
      downloaded .tar.gz file. The file should be called
      ``simplesamlphp-<version>.tar.gz``. If this is not the case, the script
      will fail.

   2. Replace the value of ``SAMLLocation`` with the absolute path
      ``saml_location``. This should be an absolute path to a directory that is
      not accessible from the web. On default DH-IT servers, this should
      probably be ``/hum/web/<hostname>/`` or ``/hum/web/<hostname>/private/``.
      Make sure to end with a forward slash, or the target directory will be
      overwritten.

   3. Replace the value of ``SAMLPubLocation`` with an absolute path to where a
      symlink can be created, such that ``host/saml_url`` opens this directory
      in the browser. On default DH-IT servers, this will most likely
      be ``/hum/web/<hostname>/htdocs/<saml_url>``

4. Connect a private and public key to SAML. You must use keys following the
   X.509 standard (e.g. your SSL certificate), provided by an UU approved CA.
   Make sure to call them saml.key and saml.crt respectively. TODO: rewrite this line

   * See also :doc:`certificates`

5. | From the file ``simplesaml-<version>.tar.gz``, copy the directories
     ``config`` and ``metadata`` without changing anything to
     ``deploy_directory``.
   |
   | The following minimal changes need to be made, but further configuration is
     possible. For most purposes, the changes below will suffice, however.

   * ``config/config.php``

     1. Change the value of ``baseurlpath`` to ``host/saml_url/`` (make sure to
        end with a forward slash)

     2. Change ``debug`` and ``showerrors`` to false.

     3. Change the value of ``auth.adminpassword`` to a new password. This is
        the password used for the admin login on the SimpleSaml Service
        Provider, so make sure to pick a safe password.

     4. Set ``admin.protectedindexpage`` to ``true``

     5. Change the value of ``secretsalt`` to a new string of sufficient length.
        The best practice for generating such a salt is using the following
        command:

        .. code-block::

            $ tr -c -d '0123456789abcdefghijklmnopqrstuvwxyz' < /dev/urandom | dd bs=32 count=1 2> /dev/null;echo

        If this command complains about illegal byte sequence, first run

        .. code-block::

            $ export LC_CTYPE=C

     6. Change the value of ``technicalcontact_email`` to an e-mail address the
        technical administrator can receive e-mails on. This e-mailaddress is
        used by SimpleSaml to send error reports and this e-mail address will
        be public.

     7. | Change ``session.cookie.secure`` and ``session.phpsession.httponly``
          to ``true``.
        | Optionally, update ``session.cookie.path``,
          ``session.cookie.domain`` and ``session.cookie.lifetime`` as explained
          in the comments above those fields.

     8. Add ``login.uu.nl`` and ``hostname`` (stripped of any https or path
        specifications) to the ``trusted.url.domains`` array. Add other
        domains you wish to redirect to after a login or logout request as well.

   * ``config/authsources.php``

     1. In the array with the key ``default-sp``, add two key-value pairs:
        .. code-block::

            'privatekey' => 'saml.key',
            'certificate' => 'saml.crt'

        These keys refer to the private and public keys mentioned earlier.

     2. Change the value of idp to the appropiate IdP:

        * For acceptation: ``https://login.acc.uu.nl/nidp/saml2/metadata``
        * For production: ``https://login.uu.nl/nidp/saml2/metadata``

     3. ITS requires that a few extra security settings be enabled. Add the
        following key-value pairs to the ``default-sp`` as above:

        .. code-block::

            'redirect.sign' => TRUE,
            'redirect.validate' => TRUE,
            'sign.authnrequest' => TRUE,
            'sign.logout' => TRUE,
            'validate.logout' => TRUE,
            'WantAssertionsSigned' => TRUE,

     See :ref:`Example authsources.php` for an example

6. Run the bash ``deploy_saml.sh`` script from the ``deploy_dir``. This will
   put all the files in the correct locations.

7. Open your browser and go to ``host/saml_url`` and login with username
   admin and the password you set in ``config/config.php``.

8. Click the configuration tab and check if all required parts of the PHP
   installation are running. Click the sanity check link, to see if the basic
   setup is configured correctly.

9. | Go to the federation tab and click Show Metadata. If no errors are shown,
    click the get the metadata xml on a dedicated URL link. This will download
    a meta data XML file.
   |
   | Send this file to ITS along with the message that you want to register your
     application with their Identity Provider. Give the base URL of your
     application and say if you want to make use of their acceptation or
     production Identity Provider (depending on what URL you entered in the
     ``config/authsources.php`` file). Also indicate which fields you want
     the Identity Provider to pass back with a successful authentication
     redirect (such as solis-ID, full name, e-mail address, etc).

   .. warning:: Before you can connect a production SP, ITS will require you to
      connect an acceptation SP first to test if it works. They'll probably also
      want a SAML trace log from a successful login/logout using the acceptation
      IdP.

      This can be done by using the SAML-tracer extension.
      (`Firefox <https://addons.mozilla.org/en-US/firefox/addon/saml-tracer/>`_,
      `Chrome <https://chrome.google.com/webstore/detail/saml-tracer/mpdajninpobndbfcldcmbpnnbhibjmch?hl=en>`_)

10. Wait for a reply

11. | If ITS accepts the request, they will add your Service Provider to their
      Identity Provider. If you're lucky, they'll also send you back a file
      called ``saml20-idp-remote.php``. This is, essentially, a PHP version of
      the IdP metadata.
    |
    | If not, you can generate this file yourself. Log into your SAML admin, go
      to 'Federation' and click 'XML to SimpleSAMLphp metadata converter'. Copy
      the contents of their metadata
      ("https://login(.acc).uu.nl/nidp/saml2/metadata") into the box, and press
      parse. The contents of ``saml2-idp-remote.php`` should be printed on
      screen. **NOTE:** It will also print their metadata as a SP, above the IdP
      metadata. Make sure you use the configuration labeled as
      ``saml20-idp-remote``
    |
    | Place this file in the metadata directory inside your ``deploy_dir`` and
      rerun the bash script ``deploy_saml.sh`` from your ``deploy_dir``.
    |
    | This file is unique for the Identity Provider you register your service
      provider with and so does not change when you set op another service
      provider on another server. Once you have this file, you can use it on
      other servers as well, as long as you want to connect to the same
      Identity Provider on those servers (notice that the acceptation and
      production versions of the Identity Provider are two distinct Identity
      Providers, so switching between those requires switching between these
      files). You will, however, still need to contact ITS, as they will have
      to add your Service Provider to their Identity Provider.

12. | Go to ``host/saml_url`` and click the page authentication. Click test
      configured authentication sources. This should redirect you to the
      UU login page.
    |
    | Enter your Solis-ID and password (or test account credentials if you
      are on the acception Identity Provider and your own Solis-ID has not
      been added) and click login.
    |
    | If all works well, you are set up to implement SimpleSaml into your web
      application. If you see an error, the debugging begins, which this how-to
      cannot provide more than the most general pointers for. Please contact
      your local (or nearest-neighbor) SAML whisperer for help if that happens.


Using SimpleSaml in your PHP Project
************************************

Once the library is installed, SimpleSaml is relatively easy to load into your
project. The following paragraphs describe how to use functionality from
SimpleSaml in a PHP project. These functions are also documented in the
SimpleSaml docs

First, you have to load the library into your project, and then you have to
create a SimpleSaml authentication object for your service provider. In
this example, it is assumed the service provider is called default-sp.
This depends on how you defined your service provider in
``/config/authsources.php``.

.. code-block:: php

    <?php

    // Load SimpleSaml library
    require_once($samlloc . "lib/_autoload.php");

    // Get service provider
    $as = new SimpleSAML_Auth_Simple('default-sp');

    // Only get attributes if user is authenticated
    if($as->isAuthenticated()) {
      $attributes = $as->getAttributes();
    }

Require Authentication
----------------------

In most usecases ITS envisions, a website is only accessible for logged in
users. In this scenario, a user who visits the website, but is not logged in at
any of the other applications using the same Identity Provider, get redirected
to the UU Login page. After authenticating with their Solis-ID, the user gets
redirected back to the original page. As long as a user is logged in on any of
the applications using the same Identity Provider, they do not have to
reauthenticate again. This is called Single Sign-On (SSO).

Enabling Saml authentication on a website where no access is allowed to
unauthenticated users is very simple. On all pages where access is denied
to unauthenticated users, simply add the line

.. code-block:: php

    $as->requireAuth();

This function can take two arguments:

* *ReturnTo* A url to which the user is redirected after successfully
  authenticating
* *KeepPost* A boolean indicating SimpleSaml should send the post data currently
  available on the page back with the redirect after a successful authentication

.. code-block:: php

    $as->requireAuth(
        array(
            'ReturnTo' => 'https://sp.example.org/login-success.php',
            'KeepPost' => true
        )
    );

Login function
--------------

While ``requireAuth()`` works well for webpages that are completely hidden
behind an authentication wall, in some cases, it is required to only perform a
login request if the user has indicated to want to login. For this, the function
``login()`` works best. This function can be called on any page. As soon as the
function is called, SimpleSaml tries authenticating the user. If the user is
already signed in on another application using the same SSO, the user is
automatically signed in. Otherwise, the user is redirected to the SimpleSaml
login page, and redirected back to the current page after a successful
authentication.

The function takes the same optional parameters as requireAuth(), plus a few
more:

* ``ErrorUrl`` The URL the user is redirected to if an error occurs (such as not being able to login)

* ``isPassive`` If this is enabled, the service provider tries loging in the
  user automatically. This succeeds if the user is already logged in to the
  Identity Provider using SSO. If not, the user is redirected to the
  ``ErrorURL``. This will not work if ``ForceAuthn`` is enabled in either the SP
  configuration, or in the arguments of this function.

* ``ReturnCallback`` A callback function that is called by SimpleSaml after the
  login has been performed. This has the form of a size 2 array, where the first
  item is an object name that is accessible from the ``SimpleSAML_Auth_Simple``
  object and the second item is a public function on that object.

* ``ForceAuthn`` The opposite of isPassive; If this is enabled, users are
  always asked for their username and password when this function is called.
  Therefore, do not call this function with this argument set to true on the
  page the user is redirected to after login, unless they are not yet
  authenticated.

.. code-block:: php

    $as->login(
      array(
        'ForceAuthn' => true,
        'KeepPost' => true,
        'isPassive' => false,
        'ReturnTo' => 'https://sp.example.org/login-success.php',
        'ErrorURL' => 'https://.../error_handler.php',
        'ReturnCallback' => array('SimpleSAML_Auth_Simple', 'callbackFunction')
      )
    );

In addition, certain items from the service provider configuration can also be
passed as an argument to the login function. See the SimpleSaml docs for a
list of configuration parameters.

Some of these parameters may be used on the requireAuth() function as well, but
this is largely a process of trial and error.

Login URL
---------

A third option is to create a link that redirects the user to the Saml login
page. This function takes one optional argument, which is the return url.
If no return url is set, the user is redirected to the same page as they started
on after authentication.

.. code-block:: php

    $url = $as->getLoginURL();
    echo sprintf('<a href="%1$s">login</a>', htmlspecialchars($url));

Logging out
-----------

There are two ways of logging out. One is to call the ``logout()`` function, the
other is to request the logout url from SimpleSaml, so you can provide a logout
link to the user. In both cases, the ``ReturnTo`` should be set to the UU logout
page, which handles logging out from the Identity Provider. The logout function
which you can call in your project only logs the user out from the service
provider. This url is ``http://logout.uu.nl``.

.. code-block:: php

    // Logout as soon as the page is loaded
    $as->logout('http://logout.uu.nl');

    // Or create a logout URL
    $url = $as->getLogoutURL('http://logout.uu.nl');
    echo sprintf('<a href="%1$s">logout</a>', htmlspecialchars($url));

Force authentication
--------------------

The ``ForceAuthn`` parameter can be especially useful to secure certain
operations behind a verify login function. This can be the case if you want to
make sure the user performing the action is the user that is still logged in,
and not someone who noticed a web page is still left open and wants to abuse
that.

.. warning::
   Notice: The provided code depends on the HTTP_REFERER key in the $_SERVER
   array. This key may be absent for all kinds of reasons, so this approach may
   not work in all cases, e.g. when HTTP Referers are disabled in the browser.
   See `this thread from Stack Overflow <http://stackoverflow.com/questions/6880659/in-what-cases-will-http-referer-be-empty>`_
   for a discussion.

Files
*****

deploy_saml.sh
--------------

.. code-block:: bash

    #!/bin/bash

    SAMLVersion="1.14.11"
    SAMLLocation="/var/www/"
    SAMLPubLocation="/var/www/html/saml"
    webuser="www-data"

    #### NO FURTHER CHANGES FROM HERE ######
    if ! [ "$USER" = $webuser ]; then
            echo "This script must be run as www-data"
            exit 1
    fi

    if ! [ -e config ]; then
            (>&2 echo "Create a config directory first")
            exit 1
    fi

    if ! [ -e metadata ]; then
            (>&2 echo "Create a metadata directory first")
            exit 1
    fi

    if ! [ -e saml.crt -a -e saml.pem ]; then
            (>&2 echo "Installation failed. No keys present. Please create keys first")
            exit 1
    fi

    SAMLName=simplesamlphp-$SAMLVersion

    tar xzf "${SAMLName}.tar.gz" || exit 1

    cd $SAMLName
    rm -r config metadata
    cd ../

    if [ -e $SAMLPubLocation ]; then
            rm $SAMLPubLocation || exit 1
            echo "Previous simlink removed"
    fi
    if [ -e $SAMLLocation$SAMLName ]; then
            rm -r $SAMLLocation$SAMLName || exit 1
            echo "Previous installation removed"
    fi

    mv $SAMLName $SAMLLocation || exit 1
    cp -r config $SAMLLocation/$SAMLName/
    cp -r metadata $SAMLLocation/$SAMLName/
    cp saml.crt ${SAMLLocation}/$SAMLName/cert/
    cp saml.pem ${SAMLLocation}/$SAMLName/cert/

    ln -sv "${SAMLLocation}${SAMLName}/www" "$SAMLPubLocation" || exit 1

    echo "Copied SAML to location: ${SAMLLocation}${SAMLName}"

Example authsources.php
-----------------------

.. code-block:: php

    <?php

    $config = array(

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
        'privatekey' => 'saml.key',
        'certificate' => 'saml.crt',

        // The entity ID of this SP.
        // Can be NULL/unset, in which case an entity ID is generated based on the metadata URL.
        'entityID' => null,

        'redirect.sign' => TRUE,
        'redirect.validate' => TRUE,
        'sign.authnrequest' => TRUE,
        'sign.logout' => TRUE,
        'validate.logout' => TRUE,
        'WantAssertionsSigned' => TRUE,

        'signature.algorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',

        // The entity ID of the IdP this should SP should contact.
        // Can be NULL/unset, in which case the user will be shown a list of available IdPs.
        'idp' => 'https://login.uu.nl/nidp/saml2/metadata',

        // The URL to the discovery service.
        // Can be NULL/unset, in which case a builtin discovery service will be used.
        'discoURL' => null
      )
    );
