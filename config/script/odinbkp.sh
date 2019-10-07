#!/bin/bash
set -e
STORAGE_NAME=odin-prod-mongo-backup
BACKUP_NAME=$(date +%y%m%d_%H%M%S).gz
HOST=172.33.16.100
USER=odin_user
PWD=ZGAKRNZFWvEJPjvFX2w4R2WzeAXhK8gs9hx2QDKRQT6T
DB=odin
date
echo "Backing up MongoDB  $DB database to $STORAGE_NAME"
echo
echo
echo "Creating dump of $DB to compressed archive"
echo
mongodump --host $HOST --port 27017 --username $USER --password $PWD --authenticationDatabase $DB --db $DB --archive=$HOME/backup/tmp_dump.gz --gzip
echo
echo "Copying compressed archive to $STORAGE_NAME"
echo
s3cmd put $HOME/backup/tmp_dump.gz s3://$STORAGE_NAME/$BACKUP_NAME
echo
echo "Cleaning up..."
rm $HOME/backup/tmp_dump.gz
echo
echo 'Backup complete!'