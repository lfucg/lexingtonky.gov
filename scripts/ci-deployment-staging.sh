#!/bin/bash

terminus site deploy --site=$SITE_NAME --env=test --updatedb --cc --sync-content -note="Deployed from CirceCI"
terminus drush "cim -y" --site=$SITE_NAME --env=test
