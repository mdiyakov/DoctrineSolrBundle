<?php

namespace Mdiyakov\DoctrineSolrBundle\Query;

use Mdiyakov\DoctrineSolrBundle\Query\Suggest\ClassSuggestQuery;
use Nelmio\SolariumBundle\ClientRegistry;
use Mdiyakov\DoctrineSolrBundle\Config\Config;
use Mdiyakov\DoctrineSolrBundle\Exception\EntityNotIndexedException;

class SuggestQueryBuilder
{
    /**
     * @var
     */
    private $config;

    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    public function __construct(
        Config $config,
        ClientRegistry $clientRegistry
    )
    {
        $this->config = $config;
        $this->clientRegistry = $clientRegistry;

    }

    /**
     * @param $class
     * @return ClassSuggestQuery
     */
    public function buildClassSuggestQuery($class)
    {
        $entityConfig = $this->config->getEntityConfig($class);
        if (!$entityConfig) {
            throw new EntityNotIndexedException(
                sprintf('""%s" is not indexed. Check bundle config in config.yml', $class)
            );
        }
        $schema = $this->config->getSchemaByEntityClass($class);

        return new ClassSuggestQuery(
            $this->clientRegistry->getClient($schema->getClient()),
            $schema,
            $entityConfig
        );
    }
}