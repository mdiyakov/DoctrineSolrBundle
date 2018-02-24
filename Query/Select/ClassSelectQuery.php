<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Select;

use Solarium\Client;
use Solarium\QueryType\Select\Result\Result;
use Mdiyakov\DoctrineSolrBundle\Query\Hydrator\SelectQueryHydrator;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;

class ClassSelectQuery extends AbstractSelectQuery
{
    /**
     * @var array
     */
    private $entityConfig;

    /**
     * @var SelectQueryHydrator
     */
    private $hydrator;

    /**
     * @param Client $client
     * @param Schema $schema
     * @param string[] $entityConfig
     * @param SelectQueryHydrator $hydrator
     */
    public function __construct(
        Client $client,
        Schema $schema,
        $entityConfig,
        SelectQueryHydrator $hydrator
    )
    {
        $this->entityConfig = $entityConfig;
        $this->hydrator = $hydrator;
        parent::__construct($client, $schema);
    }

    /**
     * @param Result $result
     * @return \object[]
     */
    protected function transformResponse(Result $result)
    {
        $documentsArray = $result->getData()['response']['docs'];

        return $this->hydrator->hydrate($documentsArray);
    }

    protected function initDiscriminatorConditions()
    {
        $discriminatorConfigField = $this->getSchema()->getDiscriminatorConfigField();
        $this->discriminatorConditions[] = sprintf(
            '%s:"%s"',
            $discriminatorConfigField->getDocumentFieldName(),
            $discriminatorConfigField->getValue($this->entityConfig)
        );
    }

}