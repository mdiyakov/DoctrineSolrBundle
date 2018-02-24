<?php

namespace Mdiyakov\DoctrineSolrBundle\Config;

use Mdiyakov\DoctrineSolrBundle\Schema\Schema;

class Config
{

    /**
     * @var \string[][]
     */
    private $indexedEntities;

    /**
     * @var Schema[]
     */
    private $entitySchemaMap = [];

    /**
     * @var string[]
     */
    private $entitiesClasses = [];

    /**
     * @var Schema[]
     */
    private $schemes = [];

    /**
     * @var array|\string[][]
     */
    private $filters = [];

    /**
     * @var array
     */
    private $entitiesConfigMap = [];

    /**
     * @param string[][] $indexedEntities
     * @param string[][] $schemes
     * @param string[][] $filters
     * @param string[] $solariumClients
     */
    public function __construct($indexedEntities, $schemes, $filters, $solariumClients)
    {
        $configValidator = new ConfigValidator();
        foreach ($indexedEntities as $entityConfig) {
            $schemaName = $entityConfig['schema'];
            $configValidator->validate($entityConfig, $schemes, $filters, $solariumClients);

            /** todo should be moved to some SchemaProvider class */
            if (!isset($this->schemes[$schemaName])) {
                $schema = new Schema(
                    $schemaName,
                    $schemes[$schemaName]['client'],
                    $schemes[$schemaName]['document_unique_field'],
                    $schemes[$schemaName]['fields'],
                    $schemes[$schemaName]['config_entity_fields']
                );
                $this->schemes[$schemaName] = $schema;
            }

            $this->entitySchemaMap[$entityConfig['class']] = $schema;
            $this->entitiesClasses[$entityConfig['class']] = true;
            $this->entitiesConfigMap[$entityConfig['class']] = $entityConfig;
        }

        $this->filters = $filters;
        $this->indexedEntities = $indexedEntities;
    }

    /**
     * @param string $class
     * @return Schema|null
     */
    public function getSchemaByEntityClass($class)
    {
        if (array_key_exists($class, $this->entitySchemaMap)) {
            return $this->entitySchemaMap[$class];
        }

        return null;
    }

    /**
     * @param string $class
     * @return string|null
     *
     */
    public function getClientByEntityClass($class)
    {
        return array_key_exists($class, $this->entityClientMap) ? $this->entityClientMap[$class] : null;
    }

    /**
     * @param $class
     * @return null|\string[][]
     */
    public function getEntityConfig($class)
    {
        return array_key_exists($class, $this->entitiesConfigMap) ? $this->entitiesConfigMap[$class] : null;
    }

    /**
     * @return string[]
     */
    public function getIndexedClasses()
    {
        return array_keys($this->entitiesClasses);
    }

    /**
     * @return array
     */
    public function getIndexedEntities()
    {
        return $this->indexedEntities;
    }

    /**
     * @return array|\string[][]
     */
    public function getFilters()
    {
        return $this->filters;
    }
}