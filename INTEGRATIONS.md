# Services integrated into the site

## Traffic data repository

CSVs that are published on GitHub pages via the [Jekyll CMS](https://help.github.com/articles/using-jekyll-as-a-static-site-generator-with-github-pages/)

The CSV spreadsheets are managed via [Prose.io](prose.io/#lfucg/traffic-data). Saving changes via prose publishes
the updated info to the traffic-data repo.

[lex-traffic-ticker.js](https://github.com/lfucg/lexingtonky.gov/blob/master/themes/custom/lex/js/lex-traffic-ticker.js) pulls them in.

## Neighborhood association directory

[lex-neighborhood-associations.js](https://github.com/lfucg/lexingtonky.gov/blob/master/themes/custom/lex/js/lex-neighborhood-associations.js) pulls association info from the city ArcGIS API endpoint.

## Leaf collection map -> Citygram

Data flow

* The Citygram [leaf collection connector](https://github.com/citygram/citygram-services/blob/master/lib/spy_glass/registry/lexington-leaf-collection.rb) pulls data from our [GIS leaf collection endpoint](http://maps.lexingtonky.gov/lfucggis/rest/services/leafcollection/MapServer).
* Citygram polls the connector looking for new events as described in the [Citygram wiki](https://github.com/codeforamerica/citygram/wiki/Getting-Started-with-Citygram).

Integration from lexingtonky.gov

* [The map](https://lfucg.github.io/leaf-collection-map/) is hosted on GitHub pages and embeded on lexky.gov with an iframe.
* The map makes an Ajax [PUT request (through a CORS proxy)](https://github.com/lfucg/leaf-collection-map/blob/gh-pages/index.html#L202) to Citygram to sign-up a user for their area.
* The CORS proxy is currently hosted https://lexington-geocode-proxy.herokuapp.com. But you can easily set up another one on heroku [as described here](https://github.com/lfucg/lexington-cors-proxy). If you do, pay for the $7/hobby account so that it runs 24 hours a day without going to sleep.

What determines the message that subscribers see?

* The message is the ['Title' field](https://github.com/citygram/citygram-services/blob/master/lib/spy_glass/registry/lexington-leaf-collection.rb#L89) in the Citygram event.
* The users are notified when a new event appears in an area that intersects theirs. 
* An event is considered 'new' when it has an event id that Citygram has never seen before. 
* You can allow the city to send custom messages at any time. Set the event id to a hash of the message along with any namespacing [as seen here](https://github.com/citygram/citygram-services/blob/master/lib/spy_glass/registry/lexington-leaf-collection.rb#L33). Whenever the message (or namespacing) changes, Citygram will create a new event and send a message. But be careful since _any_ change will trigger a message, for example an extra space.
* For custom messages, give the GIS endpoint a 'message override' field that the connector hashes into the event id. Then users at a given division can edit that message through a GIS CRUD interface and trigger a send whenever they want.

## Find your council district

[lex-geocoder.js](https://github.com/lfucg/lexingtonky.gov/blob/master/themes/custom/lex/js/lex-geocoder.js) hits the city ArcGIS API endpoint to identify the district for a given address.

Todo: stop passing address lookup through heroku CORS proxy. Instead use ESRI address lookup and identify
lat/lon against our council district ArcGIS endpoint

## Various iframes

Todo: create Drupal view that links to pages with iframes

## Final order database

Additions to the final order RSS feed is automatically sent to subscribers using the [Mailchimp RSS-to-Email feature](https://mailchimp.com/features/rss-to-email/). The Mailchimp admin can set how often the feed is sent (e.g. every two weeks).

The mailchimp account is under webmasterlexington (webmaster@lexingtonky.gov). Nick Brock has access.
