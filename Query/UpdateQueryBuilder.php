<?php

namespace Mdiyakov\DoctrineSolrBundle\Query;

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
     * @param ClientRegistry $clientRegistry
     */
    public function __construct(ClientRegistry $clientRegistry)
    {
        $this->clientRegistry = $clientRegistry;
    }

    /**
     * @param Schema $schema
     * @return UpdateQuery
     */
    public function buildUpdateQuery(Schema $schema)
    {
        return new UpdateQuery(
            $this->clientRegistry->getClient($schema->getClient()),
            $schema
        );
    }
}