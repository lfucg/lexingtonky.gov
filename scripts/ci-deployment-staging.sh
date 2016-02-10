#!/bin/bash

terminus site deploy --site=$SITE_NAME --env=test --updatedb --cc --sync-content
terminus drush "cim -y" --site=$SITE_NAME --env=test
