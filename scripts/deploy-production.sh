#!/bin/sh

terminus site backups create --env=live --element=all
terminus site deploy --env=live --updatedb --cc
terminus drush cim -y --env=live`

