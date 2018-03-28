# DoctrineSolrBundle
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/18962102-252f-4e26-a990-37072d3061b7/small.png)](https://insight.sensiolabs.com/projects/18962102-252f-4e26-a990-37072d3061b7)
[![Build Status](https://travis-ci.org/mdiyakov/DoctrineSolrBundle.svg?branch=master)](https://travis-ci.org/mdiyakov/DoctrineSolrBundle)
[![Latest Stable Version](https://poser.pugx.org/mdiyakov/doctrine-solr-bundle/v/stable)](https://packagist.org/packages/mdiyakov/doctrine-solr-bundle)
[![License](https://poser.pugx.org/mdiyakov/doctrine-solr-bundle/license)](https://packagist.org/packages/mdiyakov/doctrine-solr-bundle)

DoctrineSolrBundle is a Symfony bundle designed to mitigate Solr usage in symfony projects


# Features
* Auto-indexing doctrine entities in Solr
* Supports wildcard, fuzzy & negative searches by specific entity fields
* Supports Range searches by specific entity fields
* Supports Boosting a Term by specific entity fields
* Supports Solr SuggestComponent 
* Supports filters by entity fields or custom symfony service before indexing 
* Auto-resolving search results in Doctrine entities
* Supports implementation of separate finder class for particular entity class  
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
		new Nelmio\SolariumBundle\NelmioSolariumBundle(),
		new Mdiyakov\DoctrineSolrBundle\MdiyakovDoctrineSolrBundle(),
		// ...
	);
}
```
> You have to install "NelmioSolariumBundle" also because it's used by MdiyakovDoctrineSolrBundle

### Step 3 : Quick start with DoctrineSolrBundle

#### Prerequisites
* Solr schema.yml created and solr core is initialized
* Solr schema.yml unique field is "uid"
* Solr schema.yml consists "document_id", "document_title" and "discriminator" fields
* AppBundle\Entity\MyEntity is created and has "id" and "title" fields

DoctrineSolrBundle is using ["NelmioSolariumBundle"](https://github.com/nelmio/NelmioSolariumBundle) for solarium integration. So you need to set a configuration to use it. Here is minimum config:
```yml
nelmio_solarium: ~
```
The default solr endpoint will be used in this case (http://localhost:8983/solr)

Init bundle configuration in config.yml. Quick example:

```yml
 mdiyakov_doctrine_solr:
    indexed_entities:
        my_entity:
            class: AppBundle\Entity\MyEntity
            schema: my_schema
            config:
                - { name: config_field_name, value: config_field_value }
    schemes:
        my_schema:
            document_unique_field: { name: 'uid' }
            config_entity_fields:
                - {  config_field_name: 'config_field_name', document_field_name: 'discriminator', discriminator: true }
            fields:
                - {  entity_field_name: 'id', document_field_name: 'document_id', field_type: int, entity_primary_key: true }
                - {  entity_field_name: 'title', document_field_name: 'document_title', suggester: 'title' }
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
// @var \Mdiyakov\DoctrineSolrBundle\Finder\ClassFinder $finder 
$finder = $this->get('ds.finder')->getClassFinder(MyEntity::class);

/** @var MyEntity[] $searchResults */
$searchResults = $finder->findSearchTermByFields($searchTerm, ['title']);
//...
```


## Next steps
1. [Getting started with DoctrineSolrBundle](Resources/doc/getting_started.md)
2. [ Regular, fuzzy, wildcard, range and negative search](Resources/doc/fuzzy_wildcard_range_negative_search.md) 
3. [ Custom finder class ](Resources/doc/custom_finder_class.md)
4. [ Filters ](Resources/doc/filters.md)
5. [Schema search across multiple entities classes](Resources/doc/schema_search.md)
6. [Suggestions](Resources/doc/suggestions.md)
7. [Query building](Resources/doc/query_building.md)
8. [Console command to index entities](Resources/doc/console.md)
