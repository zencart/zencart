#!/bin/bash

# update packagist reqs
cp composer.json composer.json1
sed --file testFramework/webtests/composer_dev.sed <composer.json1 >composer.json

# display the edits for visual verification
cat composer.json

# update packagist 
composer install --dev

# install sauce webdriver
cd testFramework/webtests
curl -s https://raw.githubusercontent.com/jlipps/sausage-bun/master/givememysausage.php | php
cd ../..

