<?php

namespace Mdiyakov\DoctrineSolrBundle\Manager;

use Mdiyakov\DoctrineSolrBundle\Config\Config;
use Mdiyakov\DoctrineSolrBundle\Query\SuggestQueryBuilder;
use Mdiyakov\DoctrineSolrBundle\Suggester\ClassSuggester;
use Mdiyakov\DoctrineSolrBundle\Suggester\SchemaSuggester;

class SuggesterManager
{
    /**
     * @var ClassSuggester[]
     */
    private $classSuggesters = [];

    /**
     * @var SchemaSuggester[]
     */
    private $schemaSuggesters = [];

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

            $entitySchemaName = $entityConfig['schema'];
            if (!array_key_exists($entitySchemaName, $this->schemaSuggesters)) {
                $this->schemaSuggesters[$entitySchemaName] = new SchemaSuggester(
                    $queryBuilder->buildSchemaSuggestQuery(
                        $config->getSchemaByEntityClass($entityConfig['class'])
                    )
                );
            }
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


    /**
     * @param string $schema
     * @return SchemaSuggester
     * @throws \InvalidArgumentException
     */
    public function getSchemaSuggester($schema)
    {
        if (!array_key_exists($schema, $this->schemaSuggesters)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '"%s" schema is not found. You have the following schemas: "%s"',
                    $schema,
                    join('","', array_keys($this->schemaSuggesters))
                )
            );
        }

        return $this->schemaSuggesters[$schema];
    }
}