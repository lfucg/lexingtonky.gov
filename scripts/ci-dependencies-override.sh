#!/bin/sh

composer global require pantheon-systems/cli --prefer-source --no-interaction
composer require drush/drush:8 --prefer-source --no-interaction
composer install --prefer-source --no-interaction

mkdir "./files_backup"
cd ./files_backup

terminus auth login --machine-token=$MACHINE_TOKEN 2> /dev/null
terminus site backups get --site=$SITE_NAME --env=live --element=files --to=. --latest
tar -xzf "./`ls | head -1`"
mv files_live/ ../sites/default/files/
chmod 775 ../sites/defualt/files

