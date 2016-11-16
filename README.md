# Lexington, KY city site

The official site for the City of Lexington built around the needs of the public. The 2016 rebuild followed the discovery, alpha, and beta stages [as described by 18f](https://18f.gsa.gov/dashboard/stages/) before going live on August 1, 2016. The live stage is continued iteration, measurement, and improvement.

## Core technology

* [Drupal 8](https://www.drupal.org/8)
* [US Web Design Standards](https://playbook.cio.gov/designstandards/)

## Test suite
* Feature tests [![Circle CI](https://circleci.com/gh/lfucg/lexingtonky.gov/tree/master.svg?style=svg)](https://circleci.com/gh/lfucg/lexingtonky.gov/tree/master)
* Visual regression testing via Wraith housed in
[separate repo](https://github.com/eeeschwartz/lexky-wraith)

## Development

[See DEVELOPMENT.md](DEVELOPMENT.md)

## Upgrading and deploying

[See DEPLOYMENT.md](DEPLOYMENT.md)

## Google Translate

The Google Translation widget javascript is triggered by a menu item in the `header-quick-links`
block titled `Translation`. See lex.theme:lex_preprocess_menu__header_quick_links for details

## Lex theme

To add a custom banner image to a content type, give it field_lex_custom_banner_image. No
need to display the field, it gets added to a style tag in the document head.

## Admin theme (lex_admin)

To use the [chosen](https://www.drupal.org/project/chose) select widget, set the field to 'Select list' under content type > manage form display

## Analytics

* Using Google Tag Manager as described in the [Unified Analytics](https://github.com/laurenancona/unified-analytics) repo
* Check GA events for useful info like document downloads, outbound links
