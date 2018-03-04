<?php

namespace Mdiyakov\DoctrineSolrBundle\Query;

use Mdiyakov\DoctrineSolrBundle\Config\Config;
use Mdiyakov\DoctrineSolrBundle\Query\Update\UpdateQuery;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;
use Nelmio\SolariumBundle\ClientRegistry;

class UpdateQueryBuilder
{
    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     * @param ClientRegistry $clientRegistry
     */
    public function __construct(Config $config, ClientRegistry $clientRegistry)
    {
        $this->config = $config;
        $this->clientRegistry = $clientRegistry;
    }

    /**
     * @param Schema $schema
     * @return UpdateQuery
     */
    public function buildUpdateQuery(Schema $schema)
    {
        return new UpdateQuery(
            $this->clientRegistry->getClient(
                $this->config->getSolariumClient($schema->getClient())
            ),
            $schema
        );
    }
}