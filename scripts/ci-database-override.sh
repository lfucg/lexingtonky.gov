#!/bin/sh

mkdir "./db_backup"
cd ./db_backup

terminus auth login --machine-token=$MACHINE_TOKEN 2> /dev/null
terminus site backups get --site=$SITE_NAME --env=live --element=database --to=. --latest
gunzip "./`ls | head -1`"
mysql -u ubuntu circle_test < "./`ls | head -1`"

