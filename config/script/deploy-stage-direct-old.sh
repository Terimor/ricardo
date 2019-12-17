#!/bin/bash

DEPRECATED

echo
echo 'Deploying ODIN on odin.saga-be.host';
echo
cd /var/www/odin
git checkout -- ./package-lock.json
git checkout -- ./composer.lock
git checkout -- ./public/index.php

echo
echo 'Updating from repository:';
echo '---------------------------';
git pull

echo
echo 'Executing composer install:';
echo '---------------------------';
composer install
if [ $? != 0 ]; then
	echo
	echo '____________________________________';
	echo 'Error on executing composer install!';
	exit 1
fi

echo
echo 'Executing npm install:';
echo '---------------------------';
npm install
if [ $? != 0 ]; then
        echo
        echo '_______________________________';
        echo 'Error on executing npm install!';
        exit 1
fi

echo
echo 'Executing npm run prod:';
echo '---------------------------';
npm run prod
if [ $? != 0 ]; then
        echo
        echo '________________________________';
        echo 'Error on executing npm run prod!';
        exit 1
fi

echo
echo 'Do you need restart php-fpm? (y/n): ';
echo
read -p 'Yes/No?' reload
case "$reload" in
        y|Y ) echo  'Restarting php-fpm';
        sudo systemctl restart php7.3-fpm && systemctl status php7.3-fpm
        ;;
        n|N )  echo 'OK';
        ;;
        * )  echo 'OK';
        ;;
esac

echo
echo 'Done.';
echo