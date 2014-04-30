#!/bin/bash

# install apache, with mod-fastcgi (because Travis doesn't support Apache mod PHP)
sudo apt-get install apache2 libapache2-mod-fastcgi

#Set up PHP-FPM (Travis requires PHP-FPM)
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
sudo a2enmod rewrite actions fastcgi alias
echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm

# configure apache virtual hosts
sudo cp -f testFramework/config/travis-ci-apache-default /etc/apache2/sites-available/default
sudo cp -f testFramework/config/travis-ci-apache-default-ssl /etc/apache2/sites-available/default-ssl
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/default
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/default-ssl

sudo service apache2 restart

