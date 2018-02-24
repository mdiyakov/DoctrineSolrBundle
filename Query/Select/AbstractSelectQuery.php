<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Select;

use Mdiyakov\DoctrineSolrBundle\Exception\SchemaConfigException;
use Solarium\Client;
use Mdiyakov\DoctrineSolrBundle\Exception\QueryException;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\ConfigEntityField;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\Field;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;
use Solarium\QueryType\Select\Result\Result;

abstract class AbstractSelectQuery
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var int
     */
    private $limit = 100;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var string[]
     */
    private $addOrCondition;

    /**
     * @var string[]
     */
    private $requiredDocumentFieldsNames = [];


    /**
     * @var \Solarium\QueryType\Select\Query\Query
     */
    private $solrQuery;

    /**
     * @var string[]
     */
    protected $discriminatorConditions = [];


    /**
     * @param Client $client
     * @param Schema $schema
     */
    public function __construct(Client $client, Schema $schema)
    {
        $this->client = $client;
        $this->schema = $schema;

        /** @var ConfigEntityField $discriminatorConfigField */
        $discriminatorConfigField = $this->getSchema()->getDiscriminatorConfigField();
        $entityPrimaryKeyField = $this->getSchema()->getEntityPrimaryKeyField();

        $this->requiredDocumentFieldsNames = [
            $discriminatorConfigField->getDocumentFieldName(),
            $entityPrimaryKeyField->getDocumentFieldName()
        ];

        $this->solrQuery = $this->client->createSelect()
            ->setFields($this->requiredDocumentFieldsNames);

        $this->initDiscriminatorConditions();
    }

    /**
     * @return object[]
     */
    public function getResult()
    {

        if (empty($this->addOrCondition)) {
            return [];
        }

        $query = $this->getSolrQuery()
            ->setQuery(
                $this->getQueryString()
            )
            ->setRows($this->getLimit())
            ->setStart($this->getOffset());

        /** @var Result $response */
        $response = $this->getClient()->execute($query);

        if (!$response->getNumFound()) {
            return [];
        }

        return $this->transformResponse($response);
    }

    /**
     * @param Result $result
     * @return object[]
     */
    abstract protected function transformResponse(Result $result);

    abstract protected function initDiscriminatorConditions();

    /**
     * @return \Solarium\QueryType\Select\Query\Query
     */
    protected function getSolrQuery()
    {
        return $this->solrQuery;
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * @return Schema
     */
    protected function getSchema()
    {
        return $this->schema;
    }

    /**
     * @param array|string $entityFieldNames
     */
    public function select($entityFieldNames)
    {
        if (!is_array($entityFieldNames)) {
            $entityFieldNames = [$entityFieldNames];
        }

        foreach($entityFieldNames as $entityFieldName) {
            $this->getField($entityFieldName);
        }

        $this->getSolrQuery()->setFields(array_merge($entityFieldNames, $this->requiredDocumentFieldsNames));
    }

    /**
     * @param string $searchTerm
     * @param bool $wildcardPostfix
     * @param bool $wildcardPrefix
     */
    public function addAllFieldWhere($searchTerm, $wildcardPostfix = false, $wildcardPrefix = false)
    {
        $searchTerm = $this->prepareSearchTerm($searchTerm);
        $this->addOrCondition = [];
        $fields = $this->getSchema()->getFields();
        foreach ($fields as $field) {
            $this->addOrCondition($field, $searchTerm, $wildcardPostfix, $wildcardPrefix );
        }
    }

    /**
     * @param string $entityFieldName
     * @param string $searchTerm
     * @param bool $wildcardPostfix
     * @param bool $wildcardPrefix
     * @return AbstractSelectQuery
     */
    public function addOrWhere($entityFieldName, $searchTerm, $wildcardPostfix = false, $wildcardPrefix = false)
    {
        if (!is_string($entityFieldName)) {
            throw new QueryException('EntityFieldName argument must be a string');
        }
        $searchTerm = $this->prepareSearchTerm($searchTerm);
        if ($searchTerm) {
            $field = $this->getField($entityFieldName);
            $this->addOrCondition($field, $searchTerm, $wildcardPostfix, $wildcardPrefix );
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        $discriminatorConditions = join(' OR ', $this->discriminatorConditions);

        if (empty($this->addOrCondition)) {
            return $discriminatorConditions;
        }

        return sprintf(
            '(%s) AND (%s)',
            join(' OR ', $this->addOrCondition),
            $discriminatorConditions
        );
    }

    /**
     * @param Field $field
     * @param string $searchTerm
     * @param bool $wildcardPostfix
     * @param bool $wildcardPrefix
     */
    private function addOrCondition(Field $field, $searchTerm, $wildcardPostfix , $wildcardPrefix)
    {
        $fieldPartFormat = '%s:';
        $valuePartFormat = '';
        if (!$wildcardPrefix && !$wildcardPostfix) {
            $valuePartFormat .= '"%s"';
        } else {
            $valuePartFormat .= $wildcardPrefix ? '*' : '';
            $valuePartFormat .= '%s';
            $valuePartFormat .= $wildcardPostfix ?'*' : '';
        }

        if ($field->getPriority()) {
            $format = $fieldPartFormat . sprintf('(%s)^%%s', $valuePartFormat);
            $condition = sprintf($format, $field->getDocumentFieldName(), $searchTerm, $field->getPriority());
        } else {
            $condition = sprintf($fieldPartFormat . $valuePartFormat , $field->getDocumentFieldName(), $searchTerm);
        }

        $this->addOrCondition[] = $condition;
    }

    /**
     * @param string $entityFieldName
     * @return Field
     * @throws SchemaConfigException
     */
    private function getField($entityFieldName)
    {
        return $this->getSchema()->getFieldByEntityFieldName($entityFieldName);
    }

    /**
     * @param string $searchTerm
     * @throws QueryException
     * @return string
     */
    private function prepareSearchTerm($searchTerm)
    {
        if (!is_string($searchTerm)) {
            throw new QueryException('SearchTerm argument must be a string');
        }

        return preg_replace('/[^a-zA-Z0-9-_=+?! ]/','',$searchTerm);
    }


    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return AbstractSelectQuery
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return AbstractSelectQuery
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    public function reset()
    {
        $this->addOrCondition = [];

        return $this;
    }
}