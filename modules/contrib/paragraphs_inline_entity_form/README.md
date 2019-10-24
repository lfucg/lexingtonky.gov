# Paragraphs Inline Entity Form
This module extends [Inline Entity Form](https://www.drupal.org/project/inline_entity_form)
to add support for Paragraphs.
It allows paragraphs to work with entity embed via Wysiwyg plugin.
It provides an entity browser plugin to allow the user to select the paragraph
type, create and embed it.

It works in a similar way of [Paragraphs Entity Embed](https://www.drupal.org/project/paragraphs_entity_embed)
The main differences are:
- It uses the native content type provided by []Paragraphs](https://www.drupal.org/project/paragraphs)
  This makes it seamlessly work with existing paragraphs.
- It uses [Entity Browser](https://www.drupal.org/project/entity_browser)
  There is no code, only configuration.
- It uses [Entity Embed](https://www.drupal.org/project/entity_embed)
  There is no code, only configuration.
- It extends [Inline Entity Form](https://www.drupal.org/project/inline_entity_form)
  This makes the amount of code significantly small.

## Required modules
- [Paragraphs](https://www.drupal.org/project/paragraphs)
- [Entity Browser](https://www.drupal.org/project/entity_browser)
- [Entity Embed](https://www.drupal.org/project/entity_embed)
- [Inline Entity Form](https://www.drupal.org/project/inline_entity_form)

## Installation
1) Use composer to require the module and enable it as usual
1) Edit the 'Paragraphs' Embed type in /admin/config/content/embed and select the bundles
1) Configure the Wysiwyg editor in /admin/config/content/formats and add the button to embed `Paragraphs`
   Select `Display embedded entities`
   Make sure you have `<drupal-entity data-entity-type data-entity-uuid data-entity-embed-display data-entity-embed-display-settings data-align data-caption data-embed-button>` in Allowed HTML tags
 
## Todo
- Support for nested paragraphs. It doesn't play well with Bootstrap Paragraphs
  module. There is some error to do with language field on the database when we
  try to add a Column paragraph with children.
  See https://www.drupal.org/node/2564327
  https://www.drupal.org/project/paragraphs/issues/2877484
