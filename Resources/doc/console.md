# Console command to index entities

**DoctrineSolrBundle** provides a console command for flexible indexing of entities. It can be useful if you need to index a bunch entities of particular entity class.
To run use the following command:
```
app/console doctrine-solr:index
```

By default if no any arguments specified the command will index all instances of entity classes specified in config.yml at "indexed_entities" section.

You can specify which entity class exactly you want to be indexed. For example if you have the following config:
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
 app/console doctrine-solr:index article
```
or
```
 app/console doctrine-solr:index news
```
So in first case the all "AppBundle\Entity\Page" instances will be indexed (fulfilled "filters" section). In the second example all "AppBundle\Entity\News" instances will be indexed

Also you can take a look at  "help" of this command to see all possible entity options:
```
app/console doctrine-solr:index -h

Usage:
  doctrine-solr:index [<entity-type>] [<id>]

Arguments:
  entity-type              Specify type of entity to be indexed. Possible values are "all", "article","news [default: "all"]
  id                       Specify id of entity to be indexed. Value must be integer. Also entity-type must be specify
```

Beside this you can specify the "id" of particular enitity:
```
 app/console doctrine-solr:index news 2
```


## Pages
* [Getting started with DoctrineSolrBundle](getting_started.md)
* [ Regular, fuzzy, wildcard, range and negative search](fuzzy_wildcard_range_negative_search.md) 
* [ Custom finder class ](custom_finder_class.md)
* [ Filters ](filters.md)
* [Schema search over multiple entities classes](schema_search.md)
* [Suggestions](suggestions.md)
* [Query building](query_building.md)
* [Console command to delete entities ](Resources/doc/console_delete.md)
* [EntityManager. How to flush an entity safe ](Resources/doc/entity_manager.md)