<?php

namespace Mdiyakov\DoctrineSolrBundle\Query;

use Mdiyakov\DoctrineSolrBundle\Exception\SchemaNotFoundException;
use Mdiyakov\DoctrineSolrBundle\Query\Suggest\ClassSuggestQuery;
use Mdiyakov\DoctrineSolrBundle\Query\Suggest\SchemaSuggestQuery;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;
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
            $this->clientRegistry->getClient(
                $this->config->getSolariumClient($schema->getClient())
            ),
            $schema,
            $entityConfig
        );
    }

    /**
     * @param string $schemaName
     * @return SchemaSuggestQuery
     * @throws \InvalidArgumentException
     * @throws SchemaNotFoundException
     */
    public function buildSchemaSuggestQueryBySchemaName($schemaName)
    {
        if (!is_string($schemaName)) {
            throw new \InvalidArgumentException('Argument $schemaName must be a string');
        }

        $schema = $this->config->getSchemaByName($schemaName);
        if (!$schema) {
            throw new SchemaNotFoundException(
                sprintf('Schema "%s" is not found', $schemaName)
            );
        }

        return $this->buildSchemaSuggestQuery($schema);
    }

    /**
     * @param Schema $schema
     * @return SchemaSuggestQuery
     */
    public function buildSchemaSuggestQuery(Schema $schema)
    {
        return new SchemaSuggestQuery(
            $this->clientRegistry->getClient(
                $this->config->getSolariumClient($schema->getClient())
            ),
            $schema
        );
    }
}