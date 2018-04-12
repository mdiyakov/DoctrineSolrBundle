<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Update;

use Mdiyakov\DoctrineSolrBundle\Exception\SchemaConfigException;
use Mdiyakov\DoctrineSolrBundle\Exception\UpdateQueryException;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;
use Solarium\Client;

class UpdateQuery
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var \Solarium\QueryType\Update\Query\Query
     */
    private $solrQuery;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var array
     */
    private $addFieldsConditions = [];

    /**
     * @var array
     */
    private $deleteConditions = [];

    /**
     * @var bool
     */
    private $entityStarted = false;

    /**
     * @var bool
     */
    private $entityEnded = true;

    /**
     * @var array
     */
    private $entityConditions = [];

    /**
     * @var array
     */
    private $uniqueFieldCondition = [];

    /**
     * @param Client $client
     * @param Schema $schema
     */
    public function __construct(Client $client, Schema $schema)
    {
        $this->client = $client;
        $this->solrQuery = $client->createUpdate();
        $this->schema = $schema;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->entityConditions = [];
        $this->addFieldsConditions = [];
        $this->deleteConditions = [];
        $this->solrQuery = $this->client->createUpdate();
        $this->entityStarted = false;
        $this->entityEnded = true;
        $this->uniqueFieldCondition = [];

        return $this;
    }

    public function beginEntity()
    {
        if (!$this->entityEnded) {
            throw new UpdateQueryException('Entity is not ended. Call "endEntity" method before starting another one');
        }

        $this->addFieldsConditions = [];
        $this->uniqueFieldCondition = [];
        $this->entityStarted = true;
        $this->entityEnded = false;
    }


    public function endEntity()
    {
        if (!$this->entityStarted) {
            throw new UpdateQueryException('Entity is not started. Call "beginEntity" method before ending');
        }

        if (!$this->uniqueFieldCondition) {
            throw new UpdateQueryException('You have to specify unique field value');
        }

        $this->entityConditions[] = array_merge($this->addFieldsConditions, $this->uniqueFieldCondition);
        $this->entityStarted = false;
        $this->entityEnded = true;
    }

    /**
     * @param $configFieldName
     * @param $value
     */
    public function addConfigField($configFieldName, $value)
    {
        if (!$this->entityStarted) {
            throw new UpdateQueryException('Entity is not started. Call "beginEntity" method before add field value');
        }

        $configEntityField = $this->schema->getConfigFieldName($configFieldName);
        $this->addFieldsConditions[$configEntityField->getDocumentFieldName()] = $value;
    }

    /**
     * @param string $entityFieldName
     * @param $value
     */
    public function addField($entityFieldName, $value)
    {
        if (!$this->entityStarted) {
            throw new UpdateQueryException('Entity is not started. Call "beginEntity" method before add field value');
        }

        $field = $this->schema->getFieldByEntityFieldName($entityFieldName);
        $this->addFieldsConditions[$field->getDocumentFieldName()] = $value;
    }

    /**
     * @param $value
     */
    public function addUniqueFieldValue($value)
    {
        if (!$this->entityStarted) {
            throw new UpdateQueryException('Entity is not started. Call "beginEntity" method before add field value');
        }

        $documentUniqueField = $this->schema->getDocumentUniqueField();
        $this->uniqueFieldCondition[$documentUniqueField->getName()] = $value;
    }

    /**
     * @param string $entityFieldName
     * @param string $value
     * @throws SchemaConfigException
     */
    public function addDeleteCriteriaByField($entityFieldName, $value)
    {
        $field = $this->schema->getFieldByEntityFieldName($entityFieldName);
        $this->deleteConditions[$field->getDocumentFieldName()] = $value;
    }


    /**
     * @param $value
     */
    public function addDeleteCriteriaByUniqueFieldValue($value)
    {
        $field = $this->schema->getDocumentUniqueField();
        $this->deleteConditions[$field->getName()] = $value;
    }

    public function update()
    {
        if ($this->entityStarted || !$this->entityEnded) {
            throw new UpdateQueryException('It seems you did not end the entity');
        }

        foreach($this->deleteConditions as $documentFieldName => $value) {
            $this->solrQuery->addDeleteQuery(
                sprintf('%s:"%s"', $documentFieldName, $value)
            );
        }

        foreach ($this->entityConditions as $fieldsConditions) {
            /** @var \Solarium\QueryType\Update\Query\Document $document */
            $document = $this->solrQuery->createDocument();
            foreach ($fieldsConditions as $documentFieldName => $value) {
                $document->addField($documentFieldName, $value);
            }
            $this->solrQuery->addDocument($document);
        }

        $this->solrQuery->addCommit();
        $this->client->execute($this->solrQuery);

        $this->reset();
    }
}