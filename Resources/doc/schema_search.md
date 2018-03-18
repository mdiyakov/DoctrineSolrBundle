# Schema search across multiple entities classes

The **ClassFinder** implementation provides options to search over single entity class. **DoctrineSolrBundle** provides an ability to search across multiple entity classes. To do it you have to set the shared schema for entity classes you would like to search across.
For example:
```
mdiyakov_doctrine_solr:
    indexed_entities:
        article:
            class:  AppBundle\Entity\Page
            schema: page
            config:
                - { name: type, value: page }
                - { name: host, value: %solr_host% }
        news:
            class: AppBundle\Entity\News
            schema: page
            config:
                - { name: type, value: news }
                - { name: host, value: %solr_host% }
    schemes:
        page:
            client: page
            document_unique_field: { name: 'uid' }
            config_entity_fields:
                - {  config_field_name: 'type', document_field_name: 'type', discriminator: true, priority: 50 }
                - {  config_field_name: 'host', document_field_name: 'app_source'  }
            fields:
                - {  entity_field_name: 'id', document_field_name: 'article_id', field_type: int, entity_primary_key: true }
                - {  entity_field_name: 'category', document_field_name: 'category', suggester: 'category' }
                - {  entity_field_name: 'title', document_field_name: 'title' , priority: 100 , suggester: 'title' }
                - {  entity_field_name: 'text', document_field_name: 'page_body' }
                - {  entity_field_name: 'tags', document_field_name: 'tags' , field_type: string }
...
```

Here both "AppBundle\Entity\News" and "AppBundle\Entity\Page" entity classes use "page" schema:
```
article:
   ...
   schema: page
   ...
news:
   ...
   schema: page
   ...
```

In this case you can use schema finder for search across all entity classes:
```
$schemaFinder = $this->get('ds.finder')->getSchemaFinder('page');
$schemaFinder->addSelectClass(Page::class);
$schemaFinder->addSelectClass(News::class);

/** @var object[] **/
$result = $schemaFinder->findSearchTermByFields('notthing', ['title', 'category']);
```
So result will be an array consisting both "AppBundle\Entity\News" and "AppBundle\Entity\Page" instances ordered by score
For SchemeFinder "findByRange" and "findFuzzyTerm" methods are available. Also you can use wildcard and negative search as described for [ClassFinder](fuzzy_wildcard_range_negative_search.md)


## Pages
* [Getting started with DoctrineSolrBundle] (getting_started.md)
* [ Regular, fuzzy, wildcard, range and negative search](fuzzy_wildcard_range_negative_search.md) 
* [ Custom finder class ](custom_finder_class.md)
* [ Filters ](filters.md)
* [Suggestions](suggestions.md)
* [Query building](query_building.md)
* [Console command to index entities](console.md)