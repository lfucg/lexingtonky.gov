#!/bin/bash
kbox terminus site backups get --site=lexky-d8 --element=database --env=live --to=./db.sql.gz --latest
kbox drush sql-drop -y
kbox drush sql-cli < gunzip ./db.sql.gz
rm ./db.sql.gz
kbox drush cim -y
kbox drush cr