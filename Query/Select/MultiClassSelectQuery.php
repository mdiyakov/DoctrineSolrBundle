<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Select;

use Solarium\Client;
use Mdiyakov\DoctrineSolrBundle\Query\Hydrator\SelectQueryHydrator;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;
use Solarium\QueryType\Select\Result\Result;

class MultiClassSelectQuery extends AbstractSelectQuery
{
    /**
     * @var string[][]
     */
    private $entityConfigs;

    /**
     * @var SelectQueryHydrator[]
     */
    private $typeMappedHydrators;


    /**
     * @param Schema $schema
     * @param Client $client
     * @param string[][] $entityConfigs
     * @param SelectQueryHydrator[] $typeMappedHydrators
     */
    public function __construct(Schema $schema, Client $client, $entityConfigs, $typeMappedHydrators)
    {
        $this->entityConfigs = $entityConfigs;
        $this->typeMappedHydrators = $typeMappedHydrators;

        parent::__construct($client, $schema);
    }


    /**
     * @param Result $result
     * @return \object[]
     */
    protected function transformResponse(Result $result)
    {
        $documentsArray = $result->getData()['response']['docs'];
        $sortedByType = [];
        $discriminatorField = $this->getSchema()->getDiscriminatorConfigField();
        $entityPrimaryKeyField = $this->getSchema()->getEntityPrimaryKeyField();
        $discriminatorFieldName = $discriminatorField->getDocumentFieldName();

        foreach ($documentsArray as $row) {
            $entityIdValue = $row[$entityPrimaryKeyField->getDocumentFieldName()];
            $discriminatorValue = $row[$discriminatorFieldName];
            $sortedByType[$discriminatorValue][$entityIdValue] = $row;
        }

        foreach ($sortedByType as $discriminatorValue => $rows) {
            $hydrator = $this->typeMappedHydrators[$discriminatorValue];
            $ids = array_keys($rows);
            $entities = $hydrator->hydrate($sortedByType[$discriminatorValue]);
            $sortedByType[$discriminatorValue] = array_combine($ids, $entities);
        }

        $result = [];

        foreach ($documentsArray as $row) {
            $discriminatorValue = $row[$discriminatorFieldName];
            $entityIdValue = $row[$entityPrimaryKeyField->getDocumentFieldName()];
            $result[] = $sortedByType[$discriminatorValue][$entityIdValue];
        }

        return $result;
    }

    protected function initDiscriminatorConditions()
    {
        $discriminatorConfigField = $this->getSchema()->getDiscriminatorConfigField();
        foreach($this->entityConfigs as $entityConfig) {
            $this->discriminatorConditions[] = sprintf(
                '%s:"%s"',
                $discriminatorConfigField->getDocumentFieldName(),
                $discriminatorConfigField->getValue($entityConfig)
            );
        }
    }

}