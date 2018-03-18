# Query building
**DoctrineSolrBundle** provides a flexible interface to build as complex query as you need. The query can be a "select" query to find indexed entities or "update" query to index entity in solr or "suggest" query to get a suggestions by particular entity fields. 
   
### Content
  
[Select query](#select-query)
[Update query](#update-query)
[Suggest query](#suggest-query)   
   
   
### Select query   

As described in [Custom finder class](custom_finder_class.md) you can implement you own "ClassFinder" class with different methods related to particular search cases. Inside such methods you have to use an    "\Mdiyakov\DoctrineSolrBundle\Query\Select\ClassSelectQuery" instance to build a query:
```
use Mdiyakov\DoctrineSolrBundle\Finder\ClassFinder;

class PageFinder extends ClassFinder
{        
        public function findByTitle($searchTerm)
        {
            /** @var  Mdiyakov\DoctrineSolrBundle\Query\Select\ClassSelectQuery  **/
            $query =  $this->getQuery()
                ...
        }
}
```

Also you can get "ClassSelectQuery" instance without any implementation of "ClassFinder" using query_builder:    
```
/** Mdiyakov\DoctrineSolrBundle\Query\Select\ClassSelectQuery */
$query = $this->get('mdiyakov_doctrine_solr.query.select_builder')->buildClassSelectQuery(Page::class);
```

Beside this you can get "MultiClassSelectQuery":
```
/** @var Mdiyakov\DoctrineSolrBundle\Query\Select\MultiClassSelectQuery */
$schemaQuery = $this->get('mdiyakov_doctrine_solr.query.select_builder')->buildMultiClassSelectQueryBySchemaName('page', [Page::class, News::class]);
```

Both "ClassSelectQuery" and "MultiClassSelectQuery" have the same interface to build a query. But in case of "MultiClassSelectQuery" the search will be run across all classes defined for "MultiClassSelectQuery".

You can use the following methods to build a select query:

* search by all entity fields defined in config.yml with "OR" union:
```
  $query->addAllFieldOrWhere($searchTerm, $isNegative, $wildcard)
```
>**in this case all conditions defined for this query instance earlier will be removed** 
* search by range with "OR", "AND" union:
```
  $query->addRangeOrWhere($entityFieldName, $from, $to, $exclusiveFrom, $exclusiveTo, $isNegative)
  $query->addRangeAndWhere($entityFieldName, $from, $to, $exclusiveFrom, $exclusiveTo, $isNegative)
```

* fuzzy search with "OR", "AND" union:
```
  $query->addFuzzyOrWhere($entityFieldName, $searchTerm, $isNegative, $distance)
  $query->addFuzzyAndWhere($entityFieldName, $searchTerm, $isNegative, $distance)
```

* search by particular entity field with "OR", "AND" union:
```
    $query->addOrWhere($entityFieldName, $searchTerm, $isNegative, $wildcard)
    $query->addAndWhere($entityFieldName, $searchTerm, $isNegative, $wildcard)
```

* search by config field with "OR" union:
```
   $query->addConfigFieldOrWhere($configFieldName, $searchTerm, $isNegative, $wildcard)
```

* group conditions with "OR", "AND" union:
```
    $query->groupConditionsAsOr()
    $query->groupConditionsAsAnd()
```
with these methods you can group explicitly any amount of conditions. For example:
```
$result = $classQuery
            ->addOrWhere('title', 'pension')
            ->addOrWhere('category', 'fat')
            ->groupConditionsAsAnd()
            ->addOrWhere('text', 'some text')
            ->getResult();
```
you will get a result query like:
```
page_body:"some text" AND (title:("pension")^100 OR category:"fat")
```

* remove all previously defined conditions:
```
$query->reset()
```

* set offset and limit:
```
$query->setOffset(10)
$query->setLimit(10)
```

* To check result query string for debugging you can use method:
```
$query->getQueryString();
```

### Update query

You can use update query to implement you own logic of storing of your entities in solr:
```
    $updateQuery = $this->get('mdiyakov_doctrine_solr.query.update_builder')->buildUpdateQueryBySchemaName('page');
    $updateQuery->beginEntity();
    $updateQuery->addField('title', 'some value');
    $updateQuery->addField('category', 'category value');
    $updateQuery->addConfigField('type', 'page_entity');
    $updateQuery->addUniqueFieldValue('page-1');
    $updateQuery->endEntity();

    $updateQuery->beginEntity();
    ...
    $updateQuery->endEntity();

    $updateQuery->update();
            
```
In this case two solr documents will be created. 

Beside this there are methods to delete documents:

* to delete by entity field criteria:
```
$updateQuery->addDeleteCriteriaByField($entityFieldName, $value)
```
* to delete by unqiue field document value:
```
$updateQuery->addDeleteCriteriaByUniqueFieldValue($value)
```

### Suggest query

You can use suggest query to get suggestions. Basically it's better and more simple to use SuggestManager (see [Suggestions](suggestions.md) page) instead to buld suggest query manually. 

You can build suggest query across single entity class (keep in mind there is something to set up before using of suggest query (see [Suggestions](suggestions.md) page): 
```
$suggestQuery = $this->get('mdiyakov_doctrine_solr.query.suggester_builder')->buildClassSuggestQuery(Page::class);
$suggestQuery->addField('title');
$suggestQuery->addField('category');
$suggestQuery->setTerm('pension');
$result = $suggestQuery->suggest();
```

Also you can use SchemaSuggester:
```
$suggestQuery = $this->get('mdiyakov_doctrine_solr.query.suggester_builder')->buildSchemaSuggestQueryBySchemaName('page');
```

## Pages
* [Getting started with DoctrineSolrBundle] (getting_started.md)
* [ Regular, fuzzy, wildcard, range and negative search](fuzzy_wildcard_range_negative_search.md) 
* [ Custom finder class ](custom_finder_class.md)
* [ Filters ](filters.md)
* [Schema search over multiple entities classes](schema_search.md)
* [Suggestions](suggestions.md)
* [Console command to index entities](console.md)