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

The Iframe module creates a custom field, which lets you add a complete iframe
to your content types; including src-URL, setting width and height, optionally
a title above, and optionally a target attribute.

* For a full description of the module, visit this page:
https://www.drupal.org/project/iframe

* To submit bug reports and feature suggestions, or to track changes:
https://www.drupal.org/project/issues/iframe


REQUIREMENTS
------------

This module requires no other modules outside of Drupal core.


RECOMMENDED MODULES
-------------------

Token - https://www.drupal.org/project/token


INSTALLATION
------------

Install the Iframe module as you would normally install a contributed Drupal
module.
Visit: https://www.drupal.org/docs/8/extending-drupal-8/installing-modules
for further information.


CONFIGURATION
-------------

1. Navigate to Administration > Structure > Content types
   > [Content to edit] > Manage fields.
2. Add a new field and select "Iframe" as the Field type. 
3. There are three choices for Widget types: URL only, URL with height,
   and URL with width and height.

Field Settings
1. Navigate to Administration > Structure > Content types
   > [Content to edit] > Manage fields > Field to edit > Field Settings.
2. The width and height of an iframe can be set.
   They can be set in either fixed pixels (numbers only without "px" suffix) or
   in percentages with the percentage symbol following the number (%).
   ie. "50%" or for 500 pixels just "500".
3. Additional CSS classes can be defined.
   Multiple classes should be separated by spaces.
   Check the "Expose Additional CSS Class" box to allow authors to specify an
   additional class attribute.
4. A frameborder can be set. The default is set to zero (0), or no border.
5. Scrolling can be set to Automatic, Disabled, or Enabled.
   Scrollbars help the user to reach all iframe content despite the real height
   of the iframe content.
6. Transparency can be set to on or off to allow transparency per CSS in the
   outer iframe tag.
7. Token support can be set to "no tokens allowed",
   "tokens only in title field", or "tokens for title and URL field".
   The Token module must be enabled for some of this functionality.
8. Header level - for accessibily:
   The iframe title defaults to an h3. Depending on where this appears in the site,
   this might be the incorrect heading level to maintain proper accessible header
   navigation (if its the first header fo the site it should be a h1).


MAINTAINERS
-----------

* neffets - https://www.drupal.org/u/neffets
