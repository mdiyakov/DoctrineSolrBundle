<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Suggest;

use Mdiyakov\DoctrineSolrBundle\Query\Suggest\Solarium\Query;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;
use Solarium\Client;

class ClassSuggestQuery extends AbstractSuggestQuery
{

    /**
     * @var string[][]
     */
    private $entityConfig;

    /**
     * @param Client $client
     * @param Schema $schema
     * @param string[][] $entityConfig
     */
    public function __construct(Client $client, Schema $schema, $entityConfig)
    {
        parent::__construct($client, $schema);
        $this->entityConfig = $entityConfig;
    }

    /**
     * @param Query $solrQuery
     */
    protected function initDiscriminatorConditions(Query $solrQuery)
    {
        $discriminatorConfigField = $this->getSchema()->getDiscriminatorConfigField();
        $solrQuery->setContextFieldQuery(
            $discriminatorConfigField->getValue($this->entityConfig)
        );
    }
}