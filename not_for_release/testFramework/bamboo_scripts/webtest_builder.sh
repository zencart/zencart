#!/bin/bash

WORKING_DIRECTORY=`pwd`
EXPORT_DIRECTORY=/home/bamboo-test/public_html/v1-5
RELEASE_DIRECTORY=/home/bamboo-test/public_html/v1-5-release

#PHASE = CLEAN
rm -rf $WORKING_DIRECTORY/not_for_release/testFramework/build
rm -rf $EXPORT_DIRECTORY
rm -rf $RELEASE_DIRECTORY

#PHASE = PREPARE
mkdir $WORKING_DIRECTORY/not_for_release/testFramework/build


#PHASE = EXPORT
cp -ar $WORKING_DIRECTORY $EXPORT_DIRECTORY
touch $EXPORT_DIRECTORY/includes/configure.php
touch $EXPORT_DIRECTORY/admin/includes/configure.php
chmod 777 $EXPORT_DIRECTORY/includes/configure.php
chmod 777 $EXPORT_DIRECTORY/admin/includes/configure.php
mkdir $EXPORT_DIRECTORY/logs
mkdir $EXPORT_DIRECTORY/includes/local
chmod 777 $EXPORT_DIRECTORY/includes/local
chmod 777 $EXPORT_DIRECTORY/logs
chmod 777 $EXPORT_DIRECTORY/cache
chmod 777 $EXPORT_DIRECTORY/includes/modules/payment/paypal/logs
chmod 777 $EXPORT_DIRECTORY/images
chmod 777 $EXPORT_DIRECTORY/admin/includes/local
chmod 777 $EXPORT_DIRECTORY/not_for_release/testFramework/webtests
echo "<?php define('ADMIN_BLOCK_WARNING_OVERRIDE', 'on');" > $EXPORT_DIRECTORY/admin/includes/extra_configures/override_admin_block.php
cp $EXPORT_DIRECTORY/not_for_release/testFramework/extra_scripts/*.pem $EXPORT_DIRECTORY/includes/modules/payment/linkpoint_api/

#PHASE START xvfb
XVFB_PID=$WORKING_DIRECTORY/xvfb.pid
if [ -f $XVFB_PID ]
  then
  PID=`cat $XVFB_PID 2>/dev/null`
  kill $PID 2>/dev/null
  sleep 2
  kill -9 $PID 2>/dev/null
  rm -f $XVFB_PID
  sleep 2
fi
export DISPLAY=:99
/usr/bin/Xvfb -nolisten tcp -ac :99 >/dev/null 2>&1 &
PID=$!
echo $PID > $XVFB_PID
sleep 2
echo Xvfb Started on PID = $PID

#PHASE = START SELENIUM

SELENIUM_PID=$WORKING_DIRECTORY/selenium.pid
if [ -f $SELENIUM_PID ]
  then
  PID=`cat $SELENIUM_PID 2>/dev/null`
  kill $PID 2>/dev/null
  sleep 2
  kill -9 $PID 2>/dev/null
  rm -f $SELENIUM_PID
  sleep 2
fi
##/usr/bin/java -jar /usr/bin/selenium-server.jar -firefoxProfileTemplate /home/bamboo/ff_profile -log /home/bamboo/selenium.log &
/usr/bin/java -jar /usr/bin/selenium-server.jar -trustAllSSLCertificates -log /home/bamboo/selenium.log -debug &

PID=$!
echo $PID > $SELENIUM_PID
sleep 5

echo Selenium Started on PID = $PID





#PHASE = WEBTESTS
/usr/bin/phpunit --log-junit $WORKING_DIRECTORY/not_for_release/testFramework/build/logs/webtests.xml $WORKING_DIRECTORY/not_for_release/testFramework/webtests/allWebTests.php






#PHASE STOP SELENIUM
sleep 10
if [ -f $SELENIUM_PID ]
  then
  PID=`cat $SELENIUM_PID 2>/dev/null`
  kill $PID 2>/dev/null
  sleep 2
  kill -9 $PID 2>/dev/null
  rm -f $SELENIUM_PID
  sleep 2
fi


#PHASE = STOP XVFB
XVFB_PID=$WORKING_DIRECTORY/xvfb.pid
if [ -f $XVFB_PID ]
  then
  PID=`cat $XVFB_PID 2>/dev/null`
  kill $PID 2>/dev/null
  sleep 2
  kill -9 $PID 2>/dev/null
  rm -f $XVFB_PID
  sleep 2
fi

#PHASE = PREPARE_RELEASE

rm -rf /tmp/customProfileDir*

rm -rf $RELEASE_DIRECTORY
cp /home/bamboo/testing_additional/init_admin_auth.php $EXPORT_DIRECTORY/admin/includes/init_includes/
rm -rf $EXPORT_DIRECTORY/admin/includes/extra_configures/not_for_release1.php
