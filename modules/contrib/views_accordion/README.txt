CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

Views Accordion provides a display style plugin for the Views module.
It will take the results and display them as a JQuery accordion, using the first
field as the header for the accordion rows.

The module integrates the jQuery UI Accordion plugin as a views style plugin.
You can configure the options provided by the jQuery UI plugin.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/views_accordion

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/views_accordion


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

 * Install the Views Accordion module as you would normally install a
   contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
   further information.


CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module.
    2. Navigate to Administration > Structure > Views and create or edit a view.
    3. Choose jQuery UI accordion in the Style dialog within your view, which
       will prompt you to configure the jquery.ui.accorion settings.

Your view must meet the following requirements:
  * Row style must be set to Fields.
  * Provide at least two fields to show.

Please note:
The first field WILL be used as the header for each accordion section, all
others will be displayed when the header is clicked. The module creates an
accordion section per row of results from the view.


MAINTAINERS
-----------

 * Manuel Garcia - https://www.drupal.org/u/manuel-garcia
