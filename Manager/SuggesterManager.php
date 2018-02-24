<?php

namespace Mdiyakov\DoctrineSolrBundle\Manager;

use Mdiyakov\DoctrineSolrBundle\Config\Config;
use Mdiyakov\DoctrineSolrBundle\Query\SuggestQueryBuilder;
use Mdiyakov\DoctrineSolrBundle\Suggester\ClassSuggester;

class SuggesterManager
{
    /**
     * @var ClassSuggester[]
     */
    private $classSuggesters = [];

    /**
     * @param Config $config
     * @param SuggestQueryBuilder $queryBuilder
     */
    public function __construct(Config $config, SuggestQueryBuilder $queryBuilder)
    {
        $entityConfigs = $config->getIndexedEntities();

        foreach ($entityConfigs as $entityConfig) {
            $entityClass = $entityConfig['class'];
            if (!array_key_exists($entityClass, $this->classSuggesters)) {

                $this->classSuggesters[$entityClass] = new ClassSuggester(
                    $queryBuilder->buildClassSuggestQuery($entityClass)
                );
            }
/*
            $entitySchemaName = $entityConfig['schema'];
            if (!array_key_exists($entitySchemaName, $this->schemaFinders)) {
                $this->schemaFinders[$entitySchemaName] = new SchemaFinder(
                    $queryBuilder,
                    $config->getSchemaByEntityClass($entityClass),
                    $config
                );
            }*/
        }
    }

    /**
     * @param string $class
     * @return ClassSuggester
     * @throws \InvalidArgumentException
     */
    public function getClassSuggester($class)
    {
        if (!array_key_exists($class, $this->classSuggesters)) {
            throw new \InvalidArgumentException(
                sprintf('Class suggester %s is not found', $class)
            );
        }

        return $this->classSuggesters[$class];
    }
}