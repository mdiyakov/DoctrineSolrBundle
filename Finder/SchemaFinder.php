<?php

namespace Mdiyakov\DoctrineSolrBundle\Finder;

use Mdiyakov\DoctrineSolrBundle\Config\Config;
use Mdiyakov\DoctrineSolrBundle\Query\Select\MultiClassSelectQuery;
use Mdiyakov\DoctrineSolrBundle\Query\SelectQueryBuilder;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;

class SchemaFinder extends AbstractFinder
{
    /**
     * @var SelectQueryBuilder
     */
    private $queryBuilder;

    /**
     * @var MultiClassSelectQuery
     */
    private $selectQuery;

    /**
     * @var array[][]
     */
    private $entityConfigs;

    /**
     * @var string[]
     */
    private $selectedClasses = [];

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @param SelectQueryBuilder $queryBuilder
     * @param Schema $schema
     * @param Config $config
     */
    public function __construct(SelectQueryBuilder $queryBuilder, Schema $schema, Config $config)
    {
        $this->queryBuilder = $queryBuilder;
        $this->schema = $schema;

        foreach ($config->getIndexedEntities() as $entityConfig) {
            if ($schema->getName() != $entityConfig['schema']) {
                continue;
            }
            $this->entityConfigs[$entityConfig['class']] = $entityConfig;
        }
    }

    /**
     * @param string $class
     */
    public function addSelectClass($class)
    {
        if (!array_key_exists($class, $this->entityConfigs)) {
            throw new \InvalidArgumentException(
                sprintf('Class "%s" is not configured to be used with schema "%s"', $class, $this->getSchema()->getName())
            );
        }

        if (array_search($class, $this->selectedClasses) === false) {
            $this->selectedClasses[] = $class;
            $this->selectQuery = null;
        }
    }

    /**
     * @param string $class
     */
    public function removeClass($class)
    {
        $key = array_search($class, $this->selectedClasses);
        if ($key !== false) {
            unset($this->selectedClasses[$key]);
            $this->selectQuery = null;
        }
    }

    /**
     * @return Schema
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @return MultiClassSelectQuery
     */
    protected function getQuery()
    {
        if (!$this->selectQuery) {
            $this->selectQuery = $this->queryBuilder
                ->buildMultiClassSelectQuery(
                    $this->schema,
                    $this->selectedClasses
                );
        } else {
            $this->selectQuery->reset();
        }

        return $this->selectQuery;
    }
}