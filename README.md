# DoctrineSolrBundle

DoctrineSolrBundle is a Symfony bundle designed to mitigate Solr usage in symfony projects


# Features
* Auto-indexing doctrine entities in Solr
* Supports wildcard, fuzzy & negative searches by specific entity fields
* Supports Range searches by specific entity fields
* Supports Boosting a Term with ^ by specific entity fields
* Supports Solr SuggestComponent 
* Supports filters by entity fields or custom symfony service before indexing 
* Auto-resolving search results in Doctrine entities
* Supports implementation of separate finder(repository) class for entity class  
* Flexible query building interface
* Cross-search over different entity classes


# Installation

### Step 1 Download DoctrineSolrBundle using composer

```
$ composer require mdiyakov/doctrine-solr-bundle
```
Composer will install the bundle to your project's vendor/mdiyakov/doctrine-solr-bundle directory.


### Step 2

Enable the bundle in the kernel :
```
// app/AppKernel.php

public function registerBundles()
{
	$bundles = array(
		// ...
		new \Mdiyakov\DoctrineSolrBundle\MdiyakovDoctrineSolrBundle(),
		// ...
	);
}
```


### Step 3 : Quick start with DoctrineSolrBundle

#### Prerequisites
* Solr schema.yml created and solr core is initialized
* Solr schema.yml unique field is "uid"
* Solr schema.yml consists "document_id", "document_title" and "discriminator" fields
* AppBundle\Entity\MyEntity is created and has "id" and "title" fields

Init nelmio bundle configuration:

Init bundle configuration in config.yml:

```yml
 mdiyakov_doctrine_solr:
    indexed_entities:
        my_entity:
            class: AppBundle\Entity\MyEntity
            schema: my_entity_schema
            config:
                - { name: config_field_name, value: config_field_value }
    schemes:
        my_entity_schema:
            client: my_client
            document_unique_field: { name: 'uid' }
            config_entity_fields:
                - {  config_field_name: 'config_field_name', document_field_name: 'discriminator', discriminator: true }
            fields:
                - {  entity_field_name: 'id', document_field_name: 'document_id', field_type: int, entity_primary_key: true }
                - {  entity_field_name: 'title', document_field_name: 'document_title', suggester: 'title' }
    solarium_clients:
        my_client: "solr_client"
```

As a result "id" and "title" fields of "AppBundle\Entity\MyEntity" will be synced with Solr 
each time "AppBundle\Entity\MyEntity" is created, updated or removed.    
 
>If you use doctrine/orm < 2.5 then you have to add an annotation to "AppBundle\Entity\MyEntity" class:
```
@ORM\EntityListeners({"Mdiyakov\DoctrineSolrBundle\EventListener\DoctrineEntityListener"})
```


**To search "AppBundle\Entity\MyEntity" use the following code:**

```php
// MyController
//...
$finder = $this->get('ds.finder')->getClassFinder(MyEntity::class);

/** @var MyEntity[] $searchResults */
$searchResults = $finder->findSearchTermByFields($searchTerm, ['title']);
//...
```


## next steps
1. [Getting started with DoctrineSolrBundle] ()
2. [Fuzzy, wildcard, range and negative search]() 
3. [ Custom finder class ]()
4. [ Filters ]()
5. [Schema search]()
6. [Suggestions]()
7. [Query building]()
8. [Console command to index entities]()


