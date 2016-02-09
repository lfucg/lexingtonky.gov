#!/bin/sh

mkdir "./db_backup"

terminus auth login --machine-token=$MACHINE_TOKEN 2> /dev/null
terminus site backups get --site=$SITE_NAME --env=live --element=database --to=./db_backup --latest
mysql -u ubuntu circle_test < "./db_backup/`ls | head -1`"

