# Entity Manager

**DoctrineSolrBundle** provides an entity manager (**\Mdiyakov\DoctrineSolrBundle\Manager\EntityManager**) to handle the case when an exception was triggered during flushing entity in database.

During flushing an entity in database there is probability the exception will be thrown after the changes were successfully saved in solr. It can be caused by other entity listeners or something issues with database. In this case inconsistency can be broken beetwen database and solr because in database the changes will be rollback but in solr will be kept.

To rollback the changes in solr in this case you can use the following approach:

```
// MyController
$dsEm = $this->get('ds.entity_manager');
$myEntity = new MyEntity();
 ...
 
$dsEm ->flush($myEntity);
```
In this case if any exception will be thrown in entity listener chain for example the solr changes also will be rollback.

You can use it with a few entities:
```
$dsEm = $this->get('ds.entity_manager');
$myEntity = new MyEntity();
$mySecondEntity = new MySecondEntity();
 ...
 
$dsEm ->flush([$myEntity,$mySecondEntity]);

```

> Pay attention 'ds.entity_manager' doesn't have interface like \Doctrine\ORM\EntityManager. It has only "flush"" method with mandatory argument
> Also you don't need to call 'persist' method