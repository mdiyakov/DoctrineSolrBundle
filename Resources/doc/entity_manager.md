# Entity Manager

**DoctrineSolrBundle** provides an entity manager (**\Mdiyakov\DoctrineSolrBundle\Manager\EntityManager**) to handle the case when an exception was triggered during flushing entity in database.

During flushing an entity in database there is probability the exception will be thrown after the changes were successfully saved in solr. It can be caused by other entity listeners or something issues with database. In this case consistency can be broken beetwen database and solr because in database the changes will be rollback but in solr will be kept.

To rollback the changes in solr in this case you can use the following approach:

```
// MyController
$dsEm = $this->get('ds.entity_manager');
$myEntity = new MyEntity();
 ...
 
$dsEm ->flush($myEntity);
```
In this case if any exception will be thrown (for example in entity listener chain) the solr changes also will be rollback.


> Pay attention 'ds.entity_manager' doesn't have interface like \Doctrine\ORM\EntityManager. It has only "flush"" method with mandatory argument. 
> Also you don't need to call 'persist' method


## Pages
* [Getting started with DoctrineSolrBundle](getting_started.md)
* [ Regular, fuzzy, wildcard, range and negative search](fuzzy_wildcard_range_negative_search.md) 
* [ Custom finder class ](custom_finder_class.md)
* [ Filters ](filters.md)
* [Schema search over multiple entities classes](schema_search.md)
* [Suggestions](suggestions.md)
* [Query building](query_building.md)
* [Console command to index entities](console.md)
* [Console command to delete entities ](Resources/doc/console_delete.md)