About
-----

The Search API Solr Defaults module is mainly for demonstration purposes.
It contains configuration files that should work with a single-language English
installation of the Drupal installation profile 'standard'.
It provides a simple view for a node search.


Installation
------------

The search_api_solr_defaults module can be enabled in the GUI (Manage -> Extend) or via a drush command

```
cd $DRUPAL/htdocs
drush en search_api_solr_defaults
```

For the search to work you also need a Solr collection with the proper configuration.
The default collection name used by this module is 'drupal' and the default host is 'localhost'.
Adapt the settings to your needs.

Then create a Solr configuration archive by clicking "Get config.zip" on
admin/config/search/search-api/server/default_solr_server or by
running the drush command
```
cd $DRUPAL/htdocs
drush search-api-solr-get-server-config default_solr_server config-drupal.zip $SOLR_VERSION
```
where $SOLR_VERSION is the minimum Solr version you target, e.g. 7.4.0.

You need to set the files in this archive as configuration of the collection (default 'drupal').
How this can be achieved depends on your setup (which access you have to the Solr server,
and whether you run a traditional setup or Solr cloud).  Please refer to the Solr
documentation or the support of your Solr provider.


What you can see
----------------

Create some articles or basic pages, then go to the path solr-search/content
and type your search keyword into the search box.
Hit the "Search" button and you should see matching results.
