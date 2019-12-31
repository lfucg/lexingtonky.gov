CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended Modules
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

The File Entity Browser module provides a default Entity Browser for files,
using Masonry and Imagesloaded.

The purpose of this module is to bring back the Media Library experience from
Drupal 7, without requiring users to standardize on contributed projects like
File Entity and Media Entity.

 * For a full description of the module visit:
   https://www.drupal.org/project/file_browser

 * To submit bug reports and feature suggestions, or to track changes visit:
   https://www.drupal.org/project/issues/file_browser


REQUIREMENTS
------------

This module requires the following modules:

 * Entity Browser - https://www.drupal.org/project/entity_browser
 * Entity Embed - https://www.drupal.org/project/entity_embed
 * Dropzonejs - https://www.drupal.org/project/dropzonejs
 * Embed - https://www.drupal.org/project/embed

This module requires the following libraries:

 * imagesLoaded library - https://github.com/desandro/imagesloaded
 * Masonry library - https://github.com/desandro/masonry/
 * dropzone.min.js library - https://github.com/enyo/dropzone


RECOMMENDED MODULES
-------------------

This module introduces a common repository for libraries in sites/all/libraries
resp. sites/<domain>/libraries for contributed modules.

 * Libraries API - https://www.drupal.org/project/libraries


INSTALLATION
------------

Install the File Entity Browser module as you would normally install a
contributed Drupal module. Visit https://www.drupal.org/node/1897420 for further
information.

If you maintain a composer.json file for your Drupal project, you can require
this module and its dependencies! A fully complete example can be found here:

 * example composer.json file -
   https://gist.github.com/mortenson/a5390d99013b5b8c0254081e89bb4d47


CONFIGURATION
-------------

    1. Download the required libraries in the libraries directory.
       a. Download https://github.com/desandro/imagesloaded/archive/v3.2.0.zip
          and extract the download to /libraries/imagesloaded (or any libraries
          directory if you're using the Libraries module).
       b. Download https://github.com/desandro/masonry/archive/v3.3.2.zip and
          extract the download to /libraries/masonry (or any libraries directory
          if you're using the Libraries module).
       c. Download https://github.com/enyo/dropzone/archive/v4.3.0.zip and
          extract the download to /libraries/dropzone (or any libraries
          directory if you're using the Libraries module).
    2. Navigate to Adminstration > Extend and enabled the File Entity Browser
       and its dependencies.

Please note:
The example sub-module "File Browser Example" includes a Custom Block/Content
Type that uses File Browser components for File, Image, and File Entity
Reference fields. Enable the example module if you'd like to quickly see how
Entity Browser integration with File Browser can work.


MAINTAINERS
-----------

 * Samuel Mortenson (samuel.mortenson) -
   https://www.drupal.org/u/samuelmortenson

Supporting organization:
 * Acquia - https://www.drupal.org/acquia
