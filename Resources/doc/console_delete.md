# Console command to delete entities

**DoctrineSolrBundle** provides a console command for removing of entities. It can be useful if you need to remove a bunch entities of particular entity class.
To run use the following command:
```
app/console doctrine-solr:clear-index
```

By default if no any arguments specified the command will remove all instances of entity classes specified in config.yml at "indexed_entities" section.

You can specify which entity class exactly you want to be removed. For example if you have the following config:
```
mdiyakov_doctrine_solr:
    indexed_entities:
        article:
            class:  AppBundle\Entity\Page
            ...
        news:
            class: AppBundle\Entity\News
            ...
```
You can run:
```
 app/console doctrine-solr:clear-index article
```
or
```
 app/console doctrine-solr:clear-index news
```
So in first case the all "AppBundle\Entity\Page" instances will be removed. In the second example all "AppBundle\Entity\News" instances will be removed

Also you can take a look at  "help" of this command to see all possible entity options.

Beside this you can specify the "id" of particular enitity:
```
 app/console doctrine-solr:clear-index news 2
```


## Pages
* [Getting started with DoctrineSolrBundle](getting_started.md)
* [ Regular, fuzzy, wildcard, range and negative search](fuzzy_wildcard_range_negative_search.md) 
* [ Custom finder class ](custom_finder_class.md)
* [ Filters ](filters.md)
* [Schema search over multiple entities classes](schema_search.md)
* [Suggestions](suggestions.md)
* [Query building](query_building.md)
* [Console command to index entities](console.md)
* [EntityManager. How to flush an entity safe](entity_manager.md)
