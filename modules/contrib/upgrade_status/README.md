CONTENTS OF THIS FILE
---------------------
 
 * Introduction
 * Installation
 * Configuration
 * Maintainers

INTRODUCTION
------------

This module scans the code of installed contributed and custom projects on the
site, and reports any deprecated code that must be replaced before the next
major version. Available project updates are also suggested to keep your site
up to date as projects will resolve deprecation errors over time.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/upgrade_status

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/upgrade_status

INSTALLATION
------------

You must use composer to install all the required third party dependencies,
for example composer require "drupal/upgrade_status:^2.0", then normally install
the module in Drupal.

While the module takes an effort to categorize projects properly, installing
Composer Deploy (https://www.drupal.org/project/composer_deploy) or
Git Deploy (https://www.drupal.org/project/git_deploy) as appropriate to your
Drupal setup is suggested to identify custom vs. contributed projects more
accurately and gather version information leading to useful available update
information.

CONFIGURATION
-------------

There are no configuration options. Go to Administration » Reports »
Upgrade status to use the module.

MAINTAINERS
-----------

 * Gábor Hojtsy - https://www.drupal.org/user/4166
