# Taxonomy Menu

Drupal 8 port of the Taxonomy Menu module

[![Build Status](https://travis-ci.org/d8-contrib-modules/taxonomy_menu.svg?branch=master)](https://travis-ci.org/d8-contrib-modules/taxonomy_menu)

## Common usage

### Make a new taxonomy menu

1. Structure > Taxonomy Menu
2. Assign a taxonomy
3. Assign a menu
4. Save
5. Clear cache

You should see the taxonomy tree show up in the menu.

### Modify the menu

Please note - once the taxonomy menu is created, the menu items are decoupled from the taxonomy.

You can adjust the weight/order of the menu items, the ability to expand, and if the item is enabled or disabled. 

We have built some constraints to ensure the menu items resemble it's associated taxonomy. First, you cannot
adjust the parents. This ensures the original taxonomy tree stays somewhat in tact. Second, you cannot change
the title or description for taxonomy-generated menu items. This is rendered dynamically from the original 
taxonomy.

### Caching

Menu items are heavily cached. Upon making changes to menus and/or taxonomy, please clear the cache before
submitting an issue. 