Generating self-signed certificates
===================================

This guide describes how to generate certificates for use with SAML, conforming
to the `requirements from ITS <https://wiki.iam.uu.nl/books/saml-20/page/vereiste-instellingen>`_.
The generated certificate will be valid for 5 years.

.. warning:: This required OpenSSL to be installed on your computer. Windows users should be warned that this is most likely not the case.

.. note:: If you are here to replace an existing certificate, make sure you've contacted ITS to plan the switchover
   before actually changing the certificates.

In this guide ``<fqdn>`` refers to the hostname of your Service Provider
(which is also used as part of your SP's ``entityID``).
For example: ``example-sp.hum.uu.nl``.

**Step 1** Generate a keyfile:

.. code-block:: bash

   openssl genrsa -out <fqdn>.key 4096

**Step 2** Create an OpenSSL config file `<fqdn>.cfg`

Add the following content, replacing ``<fdqn>`` where applicable.

``DNS.2`` is optional. If you don't know this value, you probably don't need it.
(And you should remove that line).

.. code-block::

    [ req ]
    default_bits            = 4096
    default_keyfile         = <fqdn>.key
    distinguished_name      = req_distinguished_name
    attributes              = req_attributes
    prompt                  = no
    req_extensions          = v3_req

    [ req_distinguished_name ]
    C                       = NL
    O                       = Universiteit Utrecht
    OU                      = DH-IT
    ST                      = Utrecht
    L                       = Utrecht
    CN                      = <fqdn>

    [ req_attributes ]

    [ v3_req ]
    subjectAltName=@alt_names
    keyUsage = digitalSignature

    [alt_names]
    DNS.1 = <fqdn>
    DNS.2 = <alt fqdn>


.. note::
   This assumes you are a member of DH-IT. If you follow this guide as a member
   of a different faculty/organisation, please update the
   ``req_distinguished_name`` values accordingly.


**Step 3** Generate a CSR (certificate signing request) file:

.. code-block:: bash

   openssl req -new -sha256 -config <fqdn>.cfg -key <fqdn>.key -out <fqdn>.csr

**Step 4** Generate the certificate itself:

.. code-block:: bash

   openssl x509 -req -sha384 -days 1825 -in <fqdn>.csr -signkey <fqdn>.key -out <fqdn>.crt -extfile <fqdn>.cfg -extensions v3_req

**Step 5** Use your newly created certificate for SAML!

Please follow the guides applicable to your programming language/framework for
more detailed information on how to do so.

For reference, ``<fqdn>.crt`` is your certificate and ``<fdqn>.key`` is the
corresponding private key, you will need both for SAML.
**Keep the latter save and secret.**
