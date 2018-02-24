<?php

namespace Mdiyakov\DoctrineSolrBundle\Manager;

use Mdiyakov\DoctrineSolrBundle\Config\Config;
use Mdiyakov\DoctrineSolrBundle\Exception\FinderException;
use Mdiyakov\DoctrineSolrBundle\Finder\ClassFinder;
use Mdiyakov\DoctrineSolrBundle\Finder\SchemaFinder;
use Mdiyakov\DoctrineSolrBundle\Query\SelectQueryBuilder;

class FinderManager
{
    /**
     * @var SchemaFinder[]
     */
    private $schemaFinders = [];

    /**
     * @var ClassFinder[]
     */
    private $classFinders = [];

    /**
     * @param Config $config
     * @param SelectQueryBuilder $queryBuilder
     */
    public function __construct(Config $config, SelectQueryBuilder $queryBuilder)
    {
        $entityConfigs = $config->getIndexedEntities();

        foreach ($entityConfigs as $entityConfig) {
            $entityClass = $entityConfig['class'];
            if (!array_key_exists($entityClass, $this->classFinders)) {

                $finderClass = (!empty($entityConfig['finder_class'])) ? $entityConfig['finder_class'] : ClassFinder::class;

                if ($finderClass != ClassFinder::class && !is_subclass_of($finderClass, ClassFinder::class)) {
                    throw new FinderException('Finder class of entity must be extended from ClassFinder');
                }

                $this->classFinders[$entityClass] = new $finderClass(
                    $entityClass,
                    $queryBuilder->buildClassSelectQuery($entityClass)
                );
            }

            $entitySchemaName = $entityConfig['schema'];
            if (!array_key_exists($entitySchemaName, $this->schemaFinders)) {
                $this->schemaFinders[$entitySchemaName] = new SchemaFinder(
                    $queryBuilder,
                    $config->getSchemaByEntityClass($entityClass),
                    $config
                );
            }
        }

    }

    /**
     * @param string $schemaName
     * @return SchemaFinder
     * @throws \InvalidArgumentException
     */
    public function getSchemaFinder($schemaName)
    {
        if (!array_key_exists($schemaName, $this->schemaFinders)) {
            throw new \InvalidArgumentException(
                sprintf('Schema finder %s is not found', $schemaName)
            );
        }

        return $this->schemaFinders[$schemaName];
    }

    /**
     * @param string $class
     * @return ClassFinder
     * @throws \InvalidArgumentException
     */
    public function getClassFinder($class)
    {
        if (!array_key_exists($class, $this->classFinders)) {
            throw new \InvalidArgumentException(
                sprintf('Class finder %s is not found', $class)
            );
        }

        return $this->classFinders[$class];
    }
}