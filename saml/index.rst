SAML
====

SAML2 (Security Assertion Markup Language) is an xml-based standard for allowing
federated authentication. The Univerity Utrecht is slowly moving all its web
applications from LDAP authentication to SAML. With SAML, the user is
redirected to a login page of the university, so no passwords have to be sent
over the server that hosts an application, while still allowing Solis-ID
authentication.

SAML consists of two parts: The Identity Provider (IdP) and the Service
Provider (SP). The Identity Provider is hosted by the university. The Service
Provider rests with the application, and communicates with the Identity Provider.

.. note::
    A slightly more in-depth explaination on SAML can be found on
    `this page <https://communities.surf.nl/trust-en-identity/artikel/saml-for-dummies>`_
    from Surf.

This documentation tries to describe how to set up a SAML Service Provider to
communicate with the universities Identity Provider using various libraries in
various programming languages. Currently we have guides for PHP (SimpleSamlPHP),
Python (generic) and Django.

.. toctree::
   :maxdepth: 1
   :caption: Guides:

   php
   python
   django
   certificates

General SAML info
-----------------

Certificates
************

SAML uses X.509 certificates to sign the communication between the IdP and the
SP. This means your Service Provider will need its own set of certificates.

ITS has some requirements on the certificates that are used, which can be
viewed on `their documentation <https://wiki.iam.uu.nl/books/saml-20/page/vereiste-instellingen>`_.

You can use the SSL certificates used by your webhost for SSL, but these tend
to expire quite quickly. We recommend you generate your own certificates, which
can have a maximum lifetime of 5 years, which is decribed in the
:doc:`certificates guide <certificates>`.

Environments
************

ITS provides two environments. The production environment contains all
Solis-IDs available for employees and students of Utrecht University and is more
strict in how it can be used. For acceptation, an acceptation environment is
available. This environment is slightly less strict, but does not contain
default Solis-ID's. Instead, you have to work with test accounts, which
ITS can create for you.

Before you can use the production, ITS will require you to demonstrate your SP
working with the acceptation environment. This will require test accounts, so
make sure you have (active) test accounts!

SAML Trace
**********

Before you can connect a production SP, ITS will require you to connect an
acceptation SP first to test if it works. They'll probably also want a SAML
trace log from a successful login/logout using the acceptation IdP.

This can be done by using the SAML-tracer extension:
`Firefox <https://addons.mozilla.org/en-US/firefox/addon/saml-tracer/>`_,
`Chrome <https://chrome.google.com/webstore/detail/saml-tracer/mpdajninpobndbfcldcmbpnnbhibjmch?hl=en>`_

Simply open your app in a private window, open the tracer plugin and do a login
followed by a logout. After that, search for the download button and sent the
resulting file along with your request to connect your production SP.


HTTPS
*****

ITS requires all service providers to send its authentication requests over SSL.
This means SAML2 can only be used with servers that serve the web
application over secure HTTP (HTTPS).


