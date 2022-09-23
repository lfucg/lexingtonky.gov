#!/bin/bash

# Run through all the scripts.
./create-fresh-d8-site.sh
./setup-d8-repo.sh
./enable-modules.sh
./verify-solr.sh
