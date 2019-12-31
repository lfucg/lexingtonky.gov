CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

The Taxonomy Menu transforms your taxonomy vocabularies into menus.

 * For a full description of the module visit:
   https://www.drupal.org/project/taxonomy_menu

 * To submit bug reports and feature suggestions, or to track changes visit:
   https://www.drupal.org/project/issues/taxonomy_menu

 * If you prefer GitHub or GitLab, feel free to send a pull/merge request:
   https://github.com/unn/taxonomy_menu https://gitlab.com/dstol/taxonomy_menu


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

 * Install the Taxonomy Menu module as you would normally install a contributed
   Drupal module. Visit https://www.drupal.org/node/1897420 for further
   information.


CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module.
    2. Navigate to Administration > Structure > Taxonomy menu to add a new
       taxonomy menu.
    3. From the appropriate dropdown, assign a vocabulary.
    4. From the appropriate dropdown, assign a menu.
    5. Save.
    6. Clear caches.

Modify the menu:
Please note - once the taxonomy menu is created, the menu items are decoupled
from the taxonomy.

You can adjust the weight/order of the menu items, the ability to expand, and if
the item is enabled or disabled.

We have built some constraints to ensure the menu items resemble it's associated
taxonomy. First, you cannot adjust the parents. This ensures the original
taxonomy tree stays somewhat in tact. Second, you cannot change the title or
description for taxonomy-generated menu items. This is rendered dynamically from
the original taxonomy.

Caching:
Menu items are heavily cached. Upon making changes to menus and/or taxonomy,
please clear the cache before submitting an issue.


MAINTAINERS
-----------

 * Adam Bergstein (nerdstein) - https://www.drupal.org/u/nerdstein
 * Andrey Troeglazov (andrey.troeglazov) -
   https://www.drupal.org/u/andreytroeglazov
 * David Stoline (dstol) - https://www.drupal.org/u/dstol
 * Nick Wilde (NickWilde) - https://www.drupal.org/u/nickwilde

Supporting organization

 * Hook 42 - https://www.drupal.org/hook-42

Development proudly supported through a PhpStorm license from JetBrains.
