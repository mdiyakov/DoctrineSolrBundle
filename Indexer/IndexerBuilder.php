<?php

namespace Mdiyakov\DoctrineSolrBundle\Indexer;

use Mdiyakov\DoctrineSolrBundle\Config\Config;
use Mdiyakov\DoctrineSolrBundle\Exception\EntityNotIndexedException;
use Mdiyakov\DoctrineSolrBundle\Query\UpdateQueryBuilder;

class IndexerBuilder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var UpdateQueryBuilder
     */
    private $queryBuilder;

    /**
     * @param $config
     * @param UpdateQueryBuilder $queryBuilder
     */
    public function __construct($config, UpdateQueryBuilder $queryBuilder)
    {
        $this->config = $config;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param string $entityClass
     * @return Indexer
     * @throws EntityNotIndexedException
     */
    public function createByEntityClass($entityClass)
    {
        $result = null;
        $classes = class_parents($entityClass);
        array_unshift($classes, $entityClass);

        foreach ($classes as $entityClass) {
            $entityConfig = $this->config->getEntityConfig($entityClass);
            if (!$entityConfig) {
                continue;
            }

            $schema = $this->config->getSchemaByEntityClass($entityClass);
            $updateQuery = $this->queryBuilder->buildUpdateQuery($schema);
            $result = new Indexer($updateQuery, $schema, $entityConfig);
            break;
        }

        if (!$result) {
            throw new EntityNotIndexedException(
                sprintf('"%s" or parents is not indexed. Check config.yml', $entityClass)
            );
        }

        return $result;
    }
}