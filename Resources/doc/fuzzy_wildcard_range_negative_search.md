
# Regular, fuzzy, wildcard, range and negative search

The Solr standard query parser provides a different types of search. DoctrineSolrBundle supports it through "ClassFinder" and "SchemaFinder" (see [Schema search](schema_search.md) page ) implementation. Also you can use "ClassSelectQuery" and "MultiClassSelectQuery" to implement as complex search criteria as you want (see [Query building](query_building.md))   

### Content

1.[Regular search](#regular-search)

2.[Wildcard search](#wildcard-search)

3.[Negative search](#negative-search)

4.[Range search](#range-search)

5.[Fuzzy search](#fuzzy-search)

6.[Phrase treatment](#phrase-treatment)

### Regular search

To start search you need to do the following:
```php
$finder = $this->get('ds.finder')->getClassFinder(MyEntity::class);
$myEntities = $finder->findSearchTermByFields($searchTerm, ['title',...]);
```
As result an array of "MyEntity" objects will be returned or empty array if nothing is found

##### Field specification
The second argument of "findSearchTermByFields" method is array of entity fields where search will be implemented. And as a third argument you can specify config fields as source of search term:
```
$myEntities = $finder->findSearchTermByFields($searchTerm, ['title','category',...], ['config_field_name',..]);
```

If a set of fields is defined the solr query will be build using OR condition.So the result solr query will be look like:
```
title:"searchTerm" OR category:"searchTerm" ...
```      

If a field specified in second arguments is not defined inside an entity then "SchemaConfigException" exception will be thrown
The same behavior is for config field as a third argument.

### Wildcard search
In solr you can use special symbols "\*" and "?" in search term (see [solr doc](https://lucene.apache.org/solr/guide/6_6/the-standard-query-parser.html#TheStandardQueryParser-WildcardSearches) for details). To use it for search you can set a "wildcard" argument of method "findSearchTermByFields" to true (by default it's false). "wildcard" argument is a fifth argument of "findSearchTermByFields" method. In this case both "\*" and "?" will be treated as special wildcard symbols. In other case if you don't set "wildcard" argument to "true" these symbols will be escaped and treated as regular text. For example:
   
```
$myEntities = $finder->findSearchTermByFields('ho?se', ['title','category',...], [], false, true);
```
In this case the "ho?se" search term  will be treated as a wildcard statement. But in the following example:
```
$myEntities = $finder->findSearchTermByFields('ho?se', ['title','category',...], []);
``` 

the "?" symbol inside search term  will be escaped and in result query there will be no wildcard statement.

### Negative search

There is an availability to use negative condition for search. The "$isNegative" (forth argument) argument of "findSearchTermByFields" method is responsible for this. For example:

```
$myEntities = $finder->findSearchTermByFields('house', ['title','category',...], [], true);
```
In this case the search result will exclude entities having "house" inside "title" **or** "category" fields. See 
[solr doc](https://lucene.apache.org/solr/guide/6_6/the-standard-query-parser.html#TheStandardQueryParser-TheBooleanOperator-) for details   

### Range search

Default ClassFinder implementation provides a special method for Range search ([solr doc](https://lucene.apache.org/solr/guide/6_6/the-standard-query-parser.html#TheStandardQueryParser-RangeSearches)):
```
$myEntities = $finder->findByRange(1, 23, ['popularity',..]);
```

To search by date you can use the following approach:
```
$myEntities = $finder->findByRange($startDate->format(DateField::FORMAT), $endDate->format(DateField::FORMAT), ['date']);
```

The search result will consist entities having "popularity" from 1 to 23 inclusive.

The following arguments are available:
*   **$from** -  where the range starts from
*   **$to** - where the range ends 
*   **$fields** - entity fields where search will be done
*   **$isNegative** - if "true" the search result will exclude entities matched with range by specified fields 
*   **$exclusiveFrom** - if "true" then the search result will exclude entities having exactly same value specified in $from argument
*   **$exclusiveTo** - if "true" then the search result will exclude entities having exactly same value specified in $to argument
*   **$limit** - the maximum documents in search result set (100 by default)


### Fuzzy search

The fuzzy search is another search option provided by Solr ([solr doc](https://lucene.apache.org/solr/guide/6_6/the-standard-query-parser.html#TheStandardQueryParser-FuzzySearches))
DoctrineSolrBundle provides a special method for fuzzy search in default ClassFinder implementation:
```
$myEntities = $finder->findFuzzyTerm($searchTerm, ['title','category',...]);
```
The following arguments are available:
 *    **$searchTerm** - query term
 *   **$fields** - entity fields where search will be done
 *   **$isNegative** - if "true" the search result will exclude entities matched with search term by specified fields
 *   **$splitPhrase** - see section ["Phrase treatment"](#phrase-treatment) above
 *   **$distance** - for example if $distance is 1 then the "roam" search term will match terms like roams, foam, & foams. See [solr doc]((https://lucene.apache.org/solr/guide/6_6/the-standard-query-parser.html#TheStandardQueryParser-FuzzySearches)) for more details. 1 by default
 *   **$limit** - the maximum documents in search result set. 100 by default
    
### Phrase treatment

For methods:
```
... $finder->findSearchTermByFields(..)
... $finder->findFuzzyTerm(..)
```
the special **"$splitPhrase"** argument are available.  If it's set to "true" value then the search term consisting a few words is splitted by "space" symbols. And each word will be used as a search term. For example:
```
$splitPhrase = true;
... $finder->findSearchTermByFields('house appartment', ['title', 'category',..], .., $splitPhrase);
```
in this case the result query will be look like:
```
title:"house" OR title:"appartment" OR category:"house" OR category:"appartment"
```
in another case if:
```
$splitPhrase = false;
... $finder->findSearchTermByFields('house appartment', ['title', 'category',..], .., $splitPhrase);
```
the result query will be look like:
```
title:"house appartment" OR category:"house appartment"
```
For wildcard and fuzzy searches you also can use **"$splitPhrase = false"** in this case the ["Complex Phrase Query Parser"](https://lucene.apache.org/solr/guide/6_6/other-parsers.html#OtherParsers-ComplexPhraseQueryParser) will be used for phrase treatment

## Pages
* [Getting started with DoctrineSolrBundle](getting_started.md) 
* [ Custom finder class ](custom_finder_class.md)
* [ Filters ](filters.md)
* [Schema search over multiple entities classes](schema_search.md)
* [Suggestions](suggestions.md)
* [Query building](query_building.md)
* [Console command to index entities](console.md)
