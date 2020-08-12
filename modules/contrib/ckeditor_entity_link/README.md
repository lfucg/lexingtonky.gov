CONTENTS OF THIS FILE
---------------------

 * INTRODUCTION
 * REQUIREMENTS
 * INSTALLATION
 * CONFIGURATION


INTRODUCTION
------------

This module allows to insert links to Drupal
entities (content, files, tags, etc...) when using CKEditor. 

 * For a full description of the module visit:
   https://www.drupal.org/project/ckeditor_entity_link

 * To submit bug reports and feature suggestions, or to track changes visit:
   https://www.drupal.org/project/issues/ckeditor_entity_link


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

 * Install the module as you would normally install a contributed
   Drupal module. Visit https://www.drupal.org/node/1897420 for further
   information.
 * Recommended: Install with Composer: 
   composer require 'drupal/ckeditor_entity_link'

CONFIGURATION
-------------

  1. Add a button to a format in Text formats and 
     editors(admin/config/content/formats).
  2. Configure available entity types and bundles in 
     CKEditor Entity Link settings (admin/config/content/ckeditor_entity_link)
  3. Provides new button  for CKEditor to insert entity link.
  4. Save the entity.
