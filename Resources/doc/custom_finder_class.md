
# Custom finder class

The default **"\Mdiyakov\DoctrineSolrBundle\Finder\ClassFinder"** provides the default implementations for search. If you need supporting of more advanced search cases you can implement your own ClassFinder for particular entity class. To do it you have to specify your own "ClassFinder" implementation class in "indexed_entities" of config.yml:
```
indexed_entities:
    page:        
        ...
        finder_class: AppBundle\Finder\PageFinder
        ...
```
and inside "AppBundle\Finder\PageFinder":
```
namespace AppBundle\Finder;

use Mdiyakov\DoctrineSolrBundle\Finder\ClassFinder;

class PageFinder extends ClassFinder
{
        /**
         * @param string $searchTerm
         * @return Page[]
         */
        public function findByTitle($searchTerm)
        {
            return $this->getQuery()
                ->setLimit(10)
                ->addOrWhere('title', $searchTerm)
                ->getResult();
        }
}
```
PageFinder must extend the "Mdiyakov\DoctrineSolrBundle\Finder\ClassFinder". You can specify your domain model related methods. Each method have to use parent "getQuery()" method to get a special object for query building (see [Query building](query_building.md) page). 

So PageFinder can be a collection of entity-specific searching methods like Doctrine repo. 

## Pages
* [Getting started with DoctrineSolrBundle] (getting_started.md)
* [ Regular, fuzzy, wildcard, range and negative search](fuzzy_wildcard_range_negative_search.md) 
* [ Filters ](filters.md)
* [Schema search over multiple entities classes](schema_search.md)
* [Suggestions](suggestions.md)
* [Query building](query_building.md)
* [Console command to index entities](console.md)