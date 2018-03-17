<?php

namespace Mdiyakov\DoctrineSolrBundle\Query;

use Mdiyakov\DoctrineSolrBundle\Exception\SchemaNotFoundException;
use Mdiyakov\DoctrineSolrBundle\Query\Select\ClassSelectQuery;
use Mdiyakov\DoctrineSolrBundle\Query\Select\MultiClassSelectQuery;
use Nelmio\SolariumBundle\ClientRegistry;
use Mdiyakov\DoctrineSolrBundle\Config\Config;
use Mdiyakov\DoctrineSolrBundle\Exception\EntityNotIndexedException;
use Mdiyakov\DoctrineSolrBundle\Exception\SchemaConfigException;
use Mdiyakov\DoctrineSolrBundle\Query\Hydrator\HydratorBuilder;
use Mdiyakov\DoctrineSolrBundle\Query\Hydrator\SelectQueryHydrator;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;

class SelectQueryBuilder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var SelectQueryHydrator[]
     */
    private $hydrators;

    public function __construct(
        Config $config,
        ClientRegistry $clientRegistry,
        HydratorBuilder $hydratorBuilder
    )
    {
        $this->config = $config;
        $this->clientRegistry = $clientRegistry;

        foreach($config->getIndexedEntities() as $entityConfig) {
            $this->hydrators[$entityConfig['class']] = $hydratorBuilder->buildSelectQueryHydratorByClass($entityConfig['class']);
        }
    }


    /**
     * @param string $class
     * @return ClassSelectQuery
     */
    public function buildClassSelectQuery($class)
    {
        $entityConfig = $this->config->getEntityConfig($class);
        if (!$entityConfig) {
            throw new EntityNotIndexedException(
                sprintf('""%s" is not indexed. Check bundle config in config.yml', $class)
            );
        }
        $schema = $this->config->getSchemaByEntityClass($class);

        return new ClassSelectQuery(
            $this->clientRegistry->getClient(
                $this->config->getSolariumClient($schema->getClient())
            ),
            $schema,
            $entityConfig,
            $this->hydrators[$class]
        );
    }

    /**
     * @param string $schemaName
     * @param string[] $classes
     * @return MultiClassSelectQuery
     * @throws \InvalidArgumentException
     * @throws SchemaNotFoundException
     */
    public function buildMultiClassSelectQueryBySchemaName($schemaName, $classes)
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

        return $this->buildMultiClassSelectQuery($schema, $classes);
    }


    /**
     * @param Schema $schema
     * @param string[] $classes
     * @return MultiClassSelectQuery
     */
    public function buildMultiClassSelectQuery(Schema $schema, $classes)
    {
        if (!is_array($classes) || empty($classes)) {
            throw new \InvalidArgumentException(sprintf('Argument must be an array of classes'));
        }

        $multiClassQueryConfig = [
            'entityConfigs' => [],
            'hydrators' => []
        ];

        $discriminatorConfigField = $schema->getDiscriminatorConfigField();

        foreach ($classes as $class) {
            $entityConfig = $this->config->getEntityConfig($class);
            if (!$entityConfig) {
                throw new EntityNotIndexedException(
                    sprintf('""%s" is not indexed. Check bundle config in config.yml', $class)
                );
            }

            if ($schema->getName() != $entityConfig['schema'])
            {
                throw new SchemaConfigException(
                    'Entity class %s doesn\'t support scheme %s',
                    $entityConfig['class'],
                    $schema->getName()
                );
            }

            $multiClassQueryConfig['entityConfigs'][] = $entityConfig;
            $multiClassQueryConfig['hydrators'][$discriminatorConfigField->getValue($entityConfig)] = $this->hydrators[$class];
        }

        return new MultiClassSelectQuery(
            $schema,
            $this->clientRegistry->getClient(
                $this->config->getSolariumClient($schema->getClient())
            ),
            $multiClassQueryConfig['entityConfigs'],
            $multiClassQueryConfig['hydrators']
        );
    }

}