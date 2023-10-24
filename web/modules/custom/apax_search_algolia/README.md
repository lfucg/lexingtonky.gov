# ALGOLIA INDEXING THROUGH DRUPAL

### Helpful resources for implmentation
- [Algolia Dashboard](https://dashboard.algolia.com)
- [React Algolia Documentation](https://www.algolia.com/doc/guides/building-search-ui/what-is-instantsearch/react/)
- [Algolia Module for Search API](https://www.drupal.org/project/search_api_algolia)

### API Keys
- The API Key to push to algolia in the server settings for search_api needs to be hidden.
- The two in the React code (appid/apikey) for the searchClient settings are public.

### Different Indexes
- Each environment has it's own index in algolia and is set with settings.php overrides. The one committed to config files is local.
    - local: local_lexky
    - dev: dev_lexky
    - test: test_lexky
    - live: prod_lexky


#### NOTE: there's a setting in constants.jsx that connects the frontend to the right index. For now, it's hardcoded. 