# Filters

### Content

1.[Field filter](#field-filter)

2.[Service filter](#service-filter)


### Field filter

As discussed on ["Getting started"](getting_started.md) page the filters configuration section allows to define a set of filters applicable to entity during indexing process. The indexing process starts every time an entity is persisted, updated or removed. Before an entity is saved in solr storage the each filter defined for entity  is applied to entity and if all filters are fulfilled then an entity will be saved in solr storage otherwise an entity will be removed from solr storage or skipped for indexing.   
 
For example you have configuration in config.yml:  
```
indexed_entities:
    page:
        class:  AppBundle\Entity\Page
        schema: page
        filters: [ published, big_id, category_is_like_get ]
        ...
filters:    
     fields:
         big_id: { entity_field_name: "id", entity_field_value: 3, operator: ">=" }
         published: { entity_field_name: "published", entity_field_value: true, operator: "=" }
         price_less_ten: { entity_field_name: "price", entity_field_value: 10, operator: "<=" }
         category_is_like_get: { entity_field_name: "category", entity_field_value: '/^Get [a-z]*$/', operator: "regexp" }
```
In this case when an instance of "AppBundle\Entity\Page" is updated the following actions will take a place:
  - check an instance of "AppBundle\Entity\Page" has a "published" field value equals to "true". The identical "===" operator will be used for comparison
  - check an instance of "AppBundle\Entity\Page" has a "id" field value great than 3 or equal 
  - check an instance of "AppBundle\Entity\Page" has a "category" field value matched with regexp "/^Get [a-z]*$/"
If all above conditions are fulfilled then an instance of "AppBundle\Entity\Page" will be stored or updated in solr.
   
> Pay attention if the entity field value is not a scalar then the attempt to call "__toString" method will be executed to get the value of entity field for comparison. If "__toString" method  is not implemented then "FilterConfigException" will be thrown
   
#### Field filter operator
   
You can use the following operators for field filters as "operator" attribute value:
    - ">="
    - "<="
    - "="
    - "regexp" 

Also you can implement your own operator and use it for field filters. To do it you have to do the following:

  - implement a class extending "Mdiyakov\DoctrineSolrBundle\Filter\Field\EntityFieldFilter":
  
    ```
        class TestFilterOperator extends EntityFieldFilter
        {
        
            public function getSupportedOperator()
            {
                return ')(';
            }
        
            protected function validate($value)
            {
                return strpos($value, $this->getEntityFieldValue()) !== false;
            }
        
            public function getErrorMessage()
            {
                return 'field error operator';
            }
        }    
    ```
    
  - implement a symfony service config:
    
    ```
        test_test.filter_field.operator.user_operator:
            class: Test\TestBundle\Service\TestFilterOperator
            public: false
            tags:
               - { name: doctrine_solr.field_filter }
    ```

  - create the filter in DoctrineSolrBundle config:
    ``` 
       filters:
            fields:
                test_field_filter: { entity_field_name: "title", entity_field_value: "(", operator: ")(" }
    ```
    
    
  - apply the filter to an entity:
```
    indexed_entities:
        page:
            class:  AppBundle\Entity\Page
            filters: [ test_field_filter,... ]
            ...
```

### Service filter

You also can implement your own symfony service as a filter applicable for whole entity. To do it you have to do the following:

 - implement an interface "\Mdiyakov\DoctrineSolrBundle\Filter\EntityFilterInterface"
 - in service.yml tag your service:
```
    app_bundle.service.complex_filter:
        class: AppBundle\Service\TestServiceFilter
        ...
        tags:
            - { name: doctrine_solr.service_filter }
```

 - add the filter in DoctrineSolrBundle config:
```
    filters:
        services:
           service_filter: { service: "app_bundle.service.complex_filter" }    
```
 - apply the filter to an entity:
```
    indexed_entities:
        page:
            class:  AppBundle\Entity\Page
            filters: [ service_filter,... ]
            ...
```


## Pages
* [Getting started with DoctrineSolrBundle](getting_started.md)
* [ Regular, fuzzy, wildcard, range and negative search](fuzzy_wildcard_range_negative_search.md) 
* [ Custom finder class ](custom_finder_class.md)
* [Schema search over multiple entities classes](schema_search.md)
* [Suggestions](suggestions.md)
* [Query building](query_building.md)
* [Console command to index entities](console.md)
* [Console command to delete entities ](console_delete.md)
* [EntityManager. How to flush an entity safe ](entity_manager.md)