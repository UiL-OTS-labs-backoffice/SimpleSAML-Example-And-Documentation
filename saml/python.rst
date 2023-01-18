Python guide
============

These instructions try to follow the installation guide of Python3-SAML provided
in `the readme of the package <https://github.com/SAML-Toolkits/python3-saml>`_,
but tries to help configuring the service provider exactly to the needs of ITS'
Identity Provider

Do note that even for Python various implementations of SAML exist. E.g. PySAML2.
A library worth especially worth mentioning is Django SAML2 Auth, a library
integrating the Service Provider into Django with minimal configuration
required. Whether this implementation works with the IdP configuration
maintained by ITS is, however, not verified, but if your current application
uses Django authentication, this library might be worthwhile to explore.

The reason OneLogin's Python-SAML library is elaborated here is that it is a
Python-general library that can be used in any Python framework, it is well
documented and OneLogin has implementations for many other languages that follow
roughly the same procedures. The last of these arguments means that if this
how-to is successful, it can more easily be generalized to implementations for
other languages than is the case with other libraries.

.. warning::
   The CDH is currently migrating to our own :doc:`Django auth app <django>`
   that uses PySAML2 for SAML. As a result, this guide might be out-of-date.


.. contents:: **Table of Contents**
    :local:
    :depth: 3

Library Setup
*************

In this tutorial, the name ``hostname`` will be used for the base URL of the web
application. The name ``saml_location`` will be used for the absolute path saml
is installed in. The name ``saml_url`` will be used for the url path to the saml
service provider (as ``hostname/saml_url``).

1. Depending on which configuration you use, any of these packages may be
   required:

   * Linux packages:

     * libxml2
     * libxml2-dev
     * libxlst1-dev
     * python-dev
     * pkg-config
     * libxmlsec1-dev

   * Pip packages:

     * xmlsec
     * isodate
     * defusedxml

   If you already use Django or a similar framework, you will most likely
   already have these packages installed

2. Installing OneLogin Saml-3 is as simple as running a pip command:

   .. code-block:: bash

        $ pip install python3-saml

   You may also simply add python3-saml to your dependencies if you are using
   dependancy management.

3. Inside your project, create a folder named ``saml``. Within this folder, you
   need to create two files, ``settings.json`` and ``advanced_settings.json``,
   and one directory: ``certs``.

4. Open the file ``settings.json`` you just created for editing. In the readme
   of the python3-saml repository you can find complete specifications for all
   options in this file. However, you can use the following template as a
   minimal configuration. These options are at the very least required:

   TODO: this is not the entire file?

   .. code-block:: json

        {
            "strict": true,
            "debug": false,
            "sp": {
                "entityId": "https://<hostname>/saml/metadata/",
                "assertionConsumerService": {
                    "url": "https://<hostname>/saml/acs/",
                    "binding": "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                },
                "singleLogoutService": {
                    "url": "https://<hostname>/saml/sls/",
                    "binding": "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                },
                "NameIDFormat": "urn:oasis:names:tc:SAML:2.0:nameid-format:transient"
            },
            "attributeConsumingService": { },
            "idp": {
                "entityId": "https://login.uu.nl/nidp/saml2/metadata",
                "singleSignOnService": {
                    "url": "https://login.uu.nl/nidp/saml2/sso",
                    "binding": "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                },
                "singleLogoutService": {
                    "url": "https://login.uu.nl/nidp/saml2/slo",
                    "binding": "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                },
                "x509cert": ""
            }
        }

   1. Replace all occurences of ``<hostname>`` with the hostname of your web
      application

   2. These settings are for the production IdP. To use the acceptation IdP,
      change ``login.uu.nl`` to ``login.acc.uu.nl``.

   3. Optionally, change the value of ``serviceName`` and ``serviceDescription``
      to a name and description of your liking. The name should not contain any
      spaces and should be as simple as possible. After you have requested
      access to the IdP from ITS, changing this value will cause SAML to stop
      working.

   4. | If you already have the public key of the IdP you register on (either
        their acceptation IdP or their production IdP) you can add the contents
        to the value of ``idp.x509cert``.
      |
      | If you do not have this yet, contact ITS to ask for it, or go to
        https://login.uu.nl/nidp/saml2/metadata and use the value of
        ``ds:X509Certificate`` in the node ``ds:KeyInfo``. You will have to
        remove the line endings before adding the contents of this key to your
        json file
      |
      | If that is unclear, you can always e-mail ITS and ask for the public key
        for their IdP (mention if you want the acceptation IdP or the production
        IdP)

   5. | Optionally, you might want to use persistent ``NameID``'s instead of
        transient ones. To do this, chance ``sp.NameIDFormat`` to
        ``urn:oasis:names:tc:SAML:2.0:nameid-format:persistant``
      | If you don't know what this means, leave it on transient for now. You
        can always change this later

5. Open the file ``advanced_settings.json`` you just created. You can insert
   the following template:

   .. code-block:: json

        {
            "security": {
                "nameIdEncrypted": false,
                "authnRequestsSigned": true,
                "logoutRequestSigned": true,
                "logoutResponseSigned": false,
                "signMetadata": false,
                "wantMessagesSigned": false,
                "wantAssertionsSigned": true,
                "wantNameId" : true,
                "wantNameIdEncrypted": false,
                "wantAssertionsEncrypted": false,
                "signatureAlgorithm": "http://www.w3.org/2000/09/xmldsig#rsa-sha256",
                "metadataValidUntil" : "2027-03-06T09:00:30Z",
                "requestedAuthnContext" : false
            },
            "contactPerson": {
                "technical": {
                    "givenName": "technical_name",
                    "emailAddress": "technical@example.com"
                },
                "support": {
                    "givenName": "support_name",
                    "emailAddress": "support@example.com"
                }
            },
            "organization": {
                "en-US": {
                    "name": "default-sp",
                    "displayname": "default-sp",
                    "url": "https://<hostname>/saml"
                }
            }
        }

   1. Replace all occurences of ``<hostname>`` with the host name of your web
      application
   2. Replace all values in ``ContactPerson`` and ``organization`` to your own
      needs

6. | Inside the ``saml/certs`` folder you created earlier, there should the
     public and private components of a key that SAML can use to sign requests.
   |
   | You must use keys following the X.509 standard (e.g. your SSL certificate),
     provided by an UU approved CA. Make sure to cal them ``sp.key`` and
     ``sp.cert`` respectively.

   * See also :doc:`certificates`


Preparing your application
**************************

To start using SAML in your application, you have to load the various classes.
Where you do this depends on the framework you use. Minimal demos are provided
by OneLogin for the Django, Flask and Pyramid frameworks. The following section
largely uses examples from the django demo, but tries to elaborate a bit more on
what to implement and why. The code may have been adapted to fully work with
the ITS IdP.

Setting up the auth object
--------------------------

Python-SAML works largely from a singly object that you need in your code: the
auth object. This object is constructed from a request (from Django, Flask,
Pyramid, etc) and can be used to process the response sent by the IdP,
authenticate users and extract attributes of the signed in user.

In order to use Python-SAML in your project, you need to load the appropriate
libraries:

.. code-block:: python

    from onelogin.saml2.auth import OneLogin_Saml2_Auth
    from onelogin.saml2.settings import OneLogin_Saml2_Settings
    from onelogin.saml2.utils import OneLogin_Saml2_Utils

Once these libraries are loaded, the auth object can be constructed. This
object takes two parameters: A dictionary containing request information and
the full path to your saml settings directory. The latter is the location
where you placed your certs directory and your .json files. The former has
the following general form:

.. code-block:: python

    req = {
        "http_host": "",
        "script_name": "",
        "server_port": "",
        "get_data": "",
        "post_data": ""
    }

All these parameters are about your server. So ``http_host`` is your server
address (in previous section indicated as ``hostname``), ``script_name`` is the
path to the specific script being executed (or page being loaded) and
``server_port`` is the port through which your server can be accessed. If you
are using an SSL connection, this will most likely be 443.

In most frameworks, this dictionary can be extracted from the framework itself.

The auth object can be created like this:

.. code-block:: python

    auth = OneLogin_Saml2_Auth(req, custom_base_path='/path/to/saml/configuration/')

You will need this auth object on any page you want to use SAML features on,
so you might want to create a function that will generate this object
automatically from the request object of your framework. The rest of this
documentation will assume a function called ``init_saml_auth(req)``, which
creates the auth object from the req dictionary as indicated above.

Creating a Metadata page
------------------------

In order to have ITS add your Service Provider (SP) to their Identity Provider
(IdP), they will need an overview of your metadata. This metadata is
automatically generated by Python-SAML using the following code
(although there are various other ways of doing this as well):

.. code-block:: python

    auth = init_saml_auth(request)
    saml_settings = auth.get_settings()
    metadata = saml_settings.get_sp_metadata()
    errors = saml_settings.validate_metadata(metadata)
    if len(errors) == 0:
        print(metadata)
    else:
        print("Error found on Metadata: %s" % (', '.join(errors)))

An XML version of this output should be located at the address set for the
``entityID`` which you set in the ``settings.json`` file. In the example,
this was ``https://hostname/saml/metadata/``

Contact ITS
***********

You should now contact ITS and ask them to add your Service Provider to their
Identity Provider. Save the metadata as an XML file and send this file to ITS,
along with the message that you want to register your application with their
Identity Provider. Give the base URL of your application and say if you want to
make use of their acceptation or production Identity Provider
(depending on what URL you entered in ``settings.json`` file).

Also indicate which fields you want the Identity Provider to pass back with a
successful authentication redirect (such as solis-ID, full name, e-mail address,
etc).

Once they have added you, you should be able to use SAML for authenticating your
users.

Using SAML auth in your project
*******************************

.. note:: We are loosely basing the following examples on Django,
   but you should take note of your framework's auth tools/code/backend on how
   to actually implement this in your app. (Note: loosely means loosely, it's
   not valid Django code either)

Authenticate users in your Python application
---------------------------------------------

To authenticate users, you have to send an *authentication request* to the
single sign on (SSO) service of the IdP. You have already configured everything
Python-SAML needs in the ``settings.json`` file, so the URL to send this request
to can be generated from the ``login()`` function of the auth object. Lets say
the user should be authenticated right away when they visit the index page of
your site. The code could look like this:

.. code-block:: python

    def index(request):
        auth = init_saml_auth(request)
        url = auth.login()

        # Redirect the user to this url (exact method depends on framework)
        return HttpResponseRedirect(url)

This code will redirect the user to the SAML login page configured in the IdP.
If the user logs in succesfully, she will be redirected back to the
Attribute Customer Service (ACS) of your service provider, where the
authentication can be processed. This last redirect makes use of the
POST method. The important information is encoded as POST data.

If you want the user to end up on a different page than your ACS page after they
have authenticated, you can add the return_to parameter to the login function
(in this example in your index function):

.. code-block:: python

    target_url = 'where-you-want-to-send-the-user.example.org'
    auth.login(return_to=target_url)

Along with the return_to parameter, the login method accepts three other names
parameters:

* ``force_authn``: If set to True, the user will be forced to enter their
  credentials. Usually this is not required if the user is already signed in to
  the IdP, either on this application or on another application in the same
  browser.
* ``is_passive``: This is the opposite of force_authn; if the user is already
  logged in to the IdP, the user will not have to enter their credentials, even
  if they did not yet log in to this specific application (Possibly not supported
  by the ITS IdP)
* ``set_nameid_policy``: If set to true, the name ID policy will be added to the
  login request sent to the IdP. For the current configuration of the ITS IdP
  this does not add anything useful.

Process the authentication response
-----------------------------------
The location of your ACS is configured in the settings.json file, but it still
has to be implemented. In the example above, the location of the ACS
is ``https://hostname/saml/acs/``, so in this case, the ACS needs to be
implemented on the acs endpoint.

The ACS will process the information sent back by the IdP. Lets create the acs
endpoint:

.. code-block:: python

    def acs(request):
        auth = init_saml_auth(request)
        auth.process_response() # This is the magic of checking the IdP response
        errors = auth.get_errors() # If something went wrong, we will know
        status = "Not authenticated"
        if len(errors) == 0:
            if auth.is_authenticated(): #this will only work on a response object
                # So we have to remember if the user already authenticated
                request.session['samlUserdata'] = auth.get_attributes()
                # We also need the NameID provided by the IdP in case we want to send a followup request
                request.session['samlNameId'] = auth.get_nameid()
                # And for good measure, let's save the session index as well
                request.session['samlSessionIndex'] = auth.get_session_index()

                if 'RelayState' in req['post_data'] and
                  OneLogin_Saml2_Utils.get_self_url(req) != req['post_data']['RelayState']:
                    # If the authentication request was accompanied by a relay state, i.e. an
                    # url to send the user to after authentication, redirect there
                    auth.redirect_to(req['post_data']['RelayState'])
            else:
                status = "Authentication failed"
        else:
            status = str(len(errors)) + " errors: " + str(errors)

        return HttpResponse(status) # Unless a relay state was given

Notice the function call ``is_authenticated()`` on the auth object. This call
will only work after ``process_response()`` is called and an actual response is
available. Because this response is not stateless (i.e. no longer exists after
the user navigates to a different page) this call can not always be made.
To verify the authentication status of the user on later pages, the required
information is stored in the session data.

Notice also that we extract the ``NameID`` from the response object. This is
because the configuration ITS has set for its IdP requires the ``NameID`` to be
sent back with every following request. This is especially important if you
want to send a logout request.

Now that the data is stored in the session, you can use it anywhere. The various
demos provided by OneLogin provide a separate page where the authenticated user
can check her own attribute values.

Checking if the user is logged in
---------------------------------
On any page that does not have direct access to the login response, you can
check if the user is logged in by checking if the key samlUserdata is in your
session data. If you want to verify authentication on other pages in a different
manner, make sure to set this up in the acs.

Logout the user
---------------

The process for a user to logout of your application consists of two parts,
similar to the login: A logout request is sent to the IdP, and the IdP
response is then processed on your application.

To send a logout request:

.. code-block:: python

    def logout(request):
        auth = init_saml_auth(request)

        # Start building the logout request
        name_id = None
        session_index = None

        # Both these parameters are required by the ITS IdP!
        if 'samlNameId' in request.session:
            name_id = request.session['samlNameId']
        if 'samlSessionIndex' in request.session:
            session_index = request.session['samlSessionIndex']

        logouturl = auth.logout(
            name_id=name_id,
            session_index=session_index,
            return_to='http://logout.uu.nl'
        )

        return HttpResponseRedirect(logouturl)

When the user navigates to this endpoint, she will be redirected to the IdP
with a logout request. The IdP will process this response and, if successful,
return the user to the Single Logout Service (SLS) endpoint of your SP. In the
``settings.json`` file we defined this as ``https://hostname/saml/sls/`` so in
this case our SLS should be located at the sls endpoint:

.. code-block:: python

    def sls(request):
        auth = init_saml_auth(request)

        # create a passable function that flushes the session data.
        # At least make sure the session values you use to check if
        # your user is still authenticated are deleted, so when the
        # user goes back to your page, she has to login again
        dscb = lambda: request.session.flush()

        url = auth.process_slo(delete_session_cb=dscb)
        if url is None:
            # If the SSO is initiated by the Service Provider, rather
            # than by the IdP, the process_slo function does not return
            # a url. In that case, we can extract it automatically from
            # the auth object (if the login function was called with
            # a 'return_to' parameter)
            url = auth.redirect_to()

        errors = auth.get_errors()
        if len(errors) == 0:
            if url is not None:
                return HttpResponseRedirect(url)
            else:
                return HttpResponse("Succesfully logged out!")
        else:
            # Construct a useful error message
            msg = '<p>Logout failed</p>'
            msg += '<ul>'
            for e in errors:
                msg += '<li>{0}</li>'.format(e)
            msg += '</ul>'
            msg += 'Reason: {0}'.format(auth.get_last_error_reason())
            return HttpResponse(msg)
