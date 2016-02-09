#!/bin/sh

terminus site deploy --env=test --updatedb --cc --sync-content
terminus drush cim -y --env=test
