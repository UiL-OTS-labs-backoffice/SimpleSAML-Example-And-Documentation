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
        (>&2 echo "Create a config directory first, by copying the config directory from a simplesamlphp archive, and modifying the configuration files for your purposes")
        exit 1
fi

if ! [ -e metadata ]; then
        (>&2 echo "Create a metadata directory first, by copying the metadata directory from a simplesamlphp archive, and modifying the files for your purposes")
        exit 1
fi

if ! [ -e saml.crt -a -e saml.pem ]; then
        (>&2 echo "Installation failed. No keys present. Please create keys first. You can use the following command:")
        (>&2 echo "openssl req -newkey rsa:2048 -new -x509 -days 3652 -nodes -out saml.crt -keyout saml.pem")
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