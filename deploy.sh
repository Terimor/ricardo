#!/bin/bash

BRANCH="development"

if [[ "$BRANCH" == "development" ]]; then
  echo 'Deploying Odin DEVELOPMENT to 18.195.203.164';
  SERVER=18.195.203.164
    read -p "Ready to continue with DEVELOPMENT deployment (Y/N)? " choice
    case "$choice" in
      y|Y ) echo "Deploying";;
      * ) echo "Aborting"; exit 0;;
    esac
fi

composer install

npm install
npm run prod

rsync -avz -e "ssh -i ~/.ssh/odin-dev.pem" --exclude-from=rsync_exclude.txt . ubuntu@$SERVER:/var/www/odin
