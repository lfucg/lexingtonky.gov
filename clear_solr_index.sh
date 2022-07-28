#!/bin/bash

set -e

SOLR_URL=$(lando info --format json | lando jq -r '(.[] | select(.service=="solr")).urls[0]')

echo "Using SOLR url: $SOLR_URL"
echo

read -p "Are you sure you want clear the lando SOLR index (y/n)? " -n 1 -r
echo
[[ ! $REPLY =~ ^[Yy]$ ]] && exit

output=$(curl -X POST -H 'Content-Type: application/json' \
    "$SOLR_URL/solr/lexky/update?commit=true" \
    -d '{ "delete": {"query":"*:*"} }' 2>&1)

err_code=$?

echo

if [[ $err_code -ne 0 ]]; then
  echo "$output"
  exit $err_code
else
  echo "Index successfully cleared!"
fi
