<?php

echo "Running update hooks...";
passthru('drush updatedb -y');
echo "DONE\n";

echo "Importing configuration from yml files...";
passthru('drush config-import -y');
echo "DONE\n";

// Clear all cache
echo "Rebuilding cache...";
passthru('drush cr');
echo "DONE\n";
