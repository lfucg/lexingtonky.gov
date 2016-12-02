# Services integrated into the site

## Traffic data repository

CSVs that are published on GitHub pages via the [Jekyll CMS](https://help.github.com/articles/using-jekyll-as-a-static-site-generator-with-github-pages/)

The CSV spreadsheets are managed via [Prose.io](prose.io/#lfucg/traffic-data). Saving changes via prose publishes
the updated info to the traffic-data repo.

[lex-traffic-ticker.js](https://github.com/lfucg/lexingtonky.gov/blob/master/themes/custom/lex/js/lex-traffic-ticker.js) pulls them in.

## Neighborhood association directory

[lex-neighborhood-associations.js](https://github.com/lfucg/lexingtonky.gov/blob/master/themes/custom/lex/js/lex-neighborhood-associations.js) pulls association info from the city ArcGIS API endpoint.

## Leaf collection map -> Citygram

## Find your council district

[lex-geocoder.js](https://github.com/lfucg/lexingtonky.gov/blob/master/themes/custom/lex/js/lex-geocoder.js) hits the city ArcGIS API endpoint to identify the district for a given address.

Todo: stop passing address lookup through heroku CORS proxy. Instead use ESRI address lookup and identify
lat/lon against our council district ArcGIS endpoint

## Various iframes

Todo: create Drupal view that links to pages with iframes

## Final order database

Additions to the final order RSS feed is automatically sent to subscribers using the [Mailchimp RSS-to-Email feature](https://mailchimp.com/features/rss-to-email/). The Mailchimp admin can set how often the feed is sent (e.g. every two weeks).
