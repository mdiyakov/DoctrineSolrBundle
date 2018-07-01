# Getting started

The configuration of DoctrineSolrBundle consists of 4 main configuration sections.
So the full configuration can look like:
```yml
mdiyakov_doctrine_solr:
    indexed_entities:
        ...
    schemes:
        ...
    filters:
        ...
    solarium_clients:
        ...
```

Here we'll take a look on each configuration section closer.

### Content

1.[Indexed entities section](#indexed-entities-section)

2.[ Schemes section ](#schemes-section)

3.[Filters section](#filters-section)

4.[Solarium clients section](#solarium-clients-section)


### Indexed entities section
In this section you can declare a configuration of entities you would like to be indexed. 
Each entity configuration can be consisted from the set of settings grouped by string key. For example:
```yml
indexed_entities:
    page:
        class:  AppBundle\Entity\Page
        schema: page
        finder_class: AppBundle\Finder\PageFinder
        filters: [ enabled, ... ]
        config: 
            - { name: config_field_name_1, value: config_field_value_1  }
            - { name: config_field_name_2, value: config_field_value_2  }
            ...
```
>If you use doctrine/orm < 2.5 then you have to add an annotation to "AppBundle\Entity\Page" class:
```
@ORM\EntityListeners({"Mdiyakov\DoctrineSolrBundle\EventListener\DoctrineEntityListener"})
```


##### class (required)
Here we define a class of entity we would like to index.

##### schema (required)
Here we defined a schema string key from "Schemes" section (see ["Schemes section"](#schemes-section) below). 
 
#### finder_class (optional)
We specifies a custom finder class for this entity class (see page "[ Custom finder class ](custom_finder_class.md)")

#### filters (optional)
In "filters" parameter we can define a set of filters we want to apply during indexing process of entity (see ["Filters section"](#filters-section) below). An each filter is applied to entity during indexing process at runtime and if filter returns false then entity will not be indexed or will be removed from the solr.   

##### config (required)
In "config" parameter we can add a key-value pairs we want to be indexed with this entity. Such fields will have the same value for all entities of particular class. It can be considered something similar static class field. It can be used for example to index any parameter from parameter.yml:
```yml
...
config: 
            - { name: "app_version", value: %app_version%  }
...            
```
You can add such config fields as much as you want. 



Also you can declare as much entities as you want. For example:
```yml
indexed_entities:
    page:
        class:  AppBundle\Entity\Page
        schema: page
        ...         
    news:                    
        class:  AppBundle\Entity\News
        schema: page
        ...
    article:
        class:  AppBundle\Entity\Article
        schema: article
        ...
```

>The minimum required set of parameters must include "class", "schema" and at least one config field with unique value across all indexed entities to be used as discriminator(see ["discriminator"](#discriminator) for details)

### Schemes section

In this section you have to declare set of schemes defining how your entities will be stored and pushed to solr document. Here you can specify a few schemes. For example:
```yml
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
            - {  entity_field_name: 'tags', document_field_name: 'tags' , field_type: array }
    article:
        client: ...
        ...    
```

>The important thing here is the field value of "schema" parameter from "indexed_entities" section must point out at a string key of one of schemes defined in this section.  

##### client (optional)
In this example we defined **"client"** parameter pointing out at configured solarium client name (see ["Solarium clients section"](#solarium-clients-section) below). If it's not specified the default solarium client will be used.

##### document_unique_field (required)
The **"document_unique_field"** specifies a name of unique field in solr schema.yml. It's used to store a unique key of entity in solr. The value of this field is compound. It's the concatenation of discriminator config field (see "discriminator" for details) and entity primary key field joined by "-". 

##### config_entity_fields (required)
In the **"config_entity_fields"** each row reflects how config field specified in "indexed_entities" section ("config" parameter) is stored in solr. Also here we have to mark one field as "discriminator" (see "discriminator" below for details). The possible attributes here are:
```
        {  config_field_name: 'type', document_field_name: 'type', discriminator: true, priority: 50 }
```
  *  **config_field_name** (required) - reflects the config field name in "indexed_entities" sections. For example if you have in "indexed_entities":
```     
        config: 
            - { name: config_field_name_1, value: config_field_value_1  }
```       
     then **config_field_name** should be "config_field_name_1"
  * **document_field_name** (required) - reflects what name the config field has in solr schema.xml.
  * **discriminator** - reflects the config field is discriminator or not (see ["discriminator"](#discriminator) ).
  * **priority** (optional) -  boosting a search results by this field accordingly with value. 

##### fields (required)
The **"fields"** each row reflects how particular entity field is stored in solr. The possible attributes here are:
```
 - {  entity_field_name: 'id', document_field_name: 'article_id', field_type: int, entity_primary_key: true , suggester: 'dictionary_name', priority: 50}
```
  
  * **entity_field_name** (required) - reflects the field name of the entity what to be indexed
  * **document_field_name** (required) - reflects what name the entity field has in solr schema.xml
  * **field_type** (optional, by default "string") -  Reflect how the entity field value will be treated. It can be the following: "array", "string", "double", "int", "boolean".
    * **"array"** - you can use this field type if corresponding field in solr has "multiValued" attribute equals "true". In this case if entity field value is scalar it will be converted to array. If it's an array then there will be call "strval" method for each element. If the entity field is not an array or \IteratorAggregate or\Iterator the exception will be thrown     
    * **"string"** - In this case the attempt of casting to string will be executed. If an entity field value is an array then there will be call "strval" method for each element and results will be joined with "join(',', $array)". If an entity field value is an object then the attempt of "__toString" will be performed 
    * **"double"** - In this case "doubleval" method will be run to an entity field value . If an entity field value  is not a number the exception will be thrown 
    * **"int"** - In this case "intval" method will be run to an entity field value. If an entity field value  is not a number the exception will be thrown
    * **"boolean"** - In this case "boolval" method will be run to an entity field value
    * **"date"** - In this case an entity field value must be an \DateTime instance otherwise an exception will be thrown
  * **entity_primary_key** (optional, by default "false") - has the boolean value. If it set as true then the value of the entity field will be used for unique solr document field. Only one field can have this attribute as true.   
  * **suggester** (optional, by default "null")  -  reflects the field supports suggestions solr component or not. See ["Suggestions"](suggestions.md) page for details
  * **priority** (optional) -  boosting a search results by this field accordingly with value.



##### \*discriminator\*
One of config fields must be a discriminator and in this case it has a special meaning. It's needed because a single scheme (reflecting a single solr core) can be used to store different entity classes. So there may be entities of different classes with same primary key value. For example you can index "AppBundle\Entity\Page" and "AppBundle\Entity\News". Therefore there can be page entity with "id" value equals 1 and the news entity having "id" value equal 1. To avoid ambiguous in solr document  the discriminator field value is used to build an value for unique field . So the value of unique field (defined by "document_unique_field" parameter ) is value of config field defined as discriminator and entity field defined as "entity_primary_key" joined by "-".  
    
    
    
### Filters section
In this section you can specify a set of named filters. A filter can be applied for a particular entity field or it can be a symfony service implementing **"\Mdiyakov\DoctrineSolrBundle\Filter\EntityFilterInterface"** (see ["Filters"](filters.md) page for more details) getting whole entity. 
Each filter will be named by its string configuration key. A filter name should be used in "indexed_entities" section in "filters" parameter of particular entity configuration. For example you have the following "filters" configuration:
```yml
filters:    
    fields:
        big_id: { entity_field_name: "id", entity_field_value: 3, operator: ">=" }
        published: { entity_field_name: "published", entity_field_value: true, operator: "=" }
        price_less_ten: { entity_field_name: "price", entity_field_value: 10, operator: "<=" }
        category_is_like_get: { entity_field_name: "category", entity_field_value: '/^Get [a-z]*$/', operator: "regexp" }
        ....
    services:
        service_filter: { service: "app_bundle.service.complex_filter" }

```
So you can apply each of these filters for indexed entities:
```yml
indexed_entities:
    page:
        class:  AppBundle\Entity\Page
        ...
        filters: [ big_id, published, category_is_like_get ... ]
    news:
        class:  AppBundle\Entity\News
        ...
        filters: [ published, price_less_ten, service_filter... ]        
        
``` 
In case if entity doesn't have a field configured in filter the exception will be thrown. So during the indexing process if entity fullfils all filters it will be indexed|reindexed. Otherwise it will not be indexed or removed and no exception will be thrown. 
 
 You also can define your own operator for field filters (see ["Filters"](filters.md) page )
    
### Solarium clients section
DoctrineSolrBundle is using "NelmioSolariumBundle" to configure solarium clients. So in this section you can create a map between solarium clients defined inside "nelmio_solarium" and schemes. You can specify which particular solarium client should be used for each schema. 

For example if you have defined for schema:
```yml
schemes:
    page:
        client: client_page
        ...
```

then you need to add the following:
```yml
solarium_clients:
    client_page: "solarium_client_name"
```

where "solarium_client_name" is the string configuration key of client inside "nelmio_solarium" config:
```yml
nelmio_solarium:
   ...
    endpoints:
        page_endpoint:
            dsn: http://%solr_host%:%solr_port%%solr_path%
            timeout: 5
    clients:
        solarium_client_name:
            endpoints: [page_endpoint]
            client_class: Solarium\Client
            adapter_class: Solarium\Core\Client\Adapter\Curl
```

Beside this you can use default client defined in "nelmio_solarium" without any additional configuration. Just remove "client" parameter in schemes config.

## Pages
* [ Regular, fuzzy, wildcard, range and negative search](fuzzy_wildcard_range_negative_search.md) 
* [ Custom finder class ](custom_finder_class.md)
* [ Filters ](filters.md)
* [Schema search over multiple entities classes](schema_search.md)
* [Suggestions](suggestions.md)
* [Query building](query_building.md)
* [Console command to index entities](console.md)
* [Console command to delete entities ](Resources/doc/console_delete.md)
* [EntityManager. How to flush an entity safe ](Resources/doc/entity_manager.md)