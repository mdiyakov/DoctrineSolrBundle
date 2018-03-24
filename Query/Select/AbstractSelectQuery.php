<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Select;

use Mdiyakov\DoctrineSolrBundle\Exception\SchemaConfigException;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\DocumentFieldInterface;
use Solarium\Client;
use Mdiyakov\DoctrineSolrBundle\Exception\QueryException;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\ConfigEntityField;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\Field;
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
    private $addAndCondition = [];

    /**
     * @var string[]
     */
    private $addOrCondition = [];

    /**
     * @var string[]
     */
    private $requiredDocumentFieldsNames = [];


    /**
     * @var \Solarium\QueryType\Select\Query\Query
     */
    private $solrQuery;

    /**
     * @var bool
     */
    private $isPhrase = false;

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

        $this->createSolrQuery();
        $this->initDiscriminatorConditions();
    }

    /**
     * @return object[]
     */
    public function getResult()
    {
        $query = $this->getSolrQuery()
            ->setQuery(
                $this->getQueryString()
            )
            ->setRows($this->getLimit())
            ->setStart($this->getOffset());

        if ($this->isPhrase) {
            $query->setQuery(
                $query->getHelper()->qparser('complexphrase') .
                $query->getQuery()
            );
        }

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
     * @param bool $isNegative
     * @param bool $wildcard
     * @return AbstractSelectQuery
     */
    public function addAllFieldOrWhere($searchTerm, $isNegative = false, $wildcard = false)
    {
        $this->reset();
        $searchTerm = $this->prepareSearchTerm($searchTerm, $wildcard);
        $fields = $this->getSchema()->getFields();
        foreach ($fields as $field) {
            $this->addOrCondition($field, $searchTerm, $wildcard, $isNegative);
        }

        return $this;
    }

    /**
     * @param string $entityFieldName
     * @param string $from
     * @param string $to
     * @param bool|false $exclusiveFrom
     * @param bool|false $exclusiveTo
     * @param bool $isNegative
     * @return $this
     */
    public function addRangeOrWhere($entityFieldName, $from = '*', $to = '*', $exclusiveFrom = false, $exclusiveTo = false, $isNegative = false)
    {
        $from = $this->prepareSearchTerm($from);
        $to = $this->prepareSearchTerm($to);
        if ($from && $to) {
            $field = $this->prepareField($entityFieldName, false);
            $this->addRangeOrCondition($field, $from, $to, $exclusiveFrom, $exclusiveTo, $isNegative);
        }

        return $this;
    }


    /**
     * @param string $entityFieldName
     * @param string $from
     * @param string $to
     * @param bool|false $exclusiveFrom
     * @param bool|false $exclusiveTo
     * @param bool $isNegative
     * @return $this
     */
    public function addRangeAndWhere($entityFieldName, $from = '*', $to = '*', $exclusiveFrom = false, $exclusiveTo = false, $isNegative = false)
    {
        $from = $this->prepareSearchTerm($from);
        $to = $this->prepareSearchTerm($to);
        if ($from && $to) {
            $field = $this->prepareField($entityFieldName, false);
            $this->addRangeAndCondition($field, $from, $to, $exclusiveFrom, $exclusiveTo, $isNegative);
        }

        return $this;
    }

    /**
     * @param string $entityFieldName
     * @param string $searchTerm
     * @param bool $isNegative
     * @param int $distance
     * @return AbstractSelectQuery
     */
    public function addFuzzyOrWhere($entityFieldName, $searchTerm, $isNegative = false, $distance = 1)
    {
        $searchTerm = $this->prepareSearchTerm($searchTerm);
        if ($searchTerm) {
            $field = $this->prepareField($entityFieldName, false);
            $this->addFuzzyOrCondition($field, $searchTerm, $isNegative, $distance);
        }

        return $this;
    }

    /**
     * @param string $entityFieldName
     * @param string|number $searchTerm
     * @param bool|false $isNegative
     * @param int $distance
     * @return AbstractSelectQuery
     */
    public function addFuzzyAndWhere($entityFieldName, $searchTerm, $isNegative = false, $distance = 1)
    {
        $searchTerm = $this->prepareSearchTerm($searchTerm);
        if ($searchTerm) {
            $field = $this->prepareField($entityFieldName, false);
            $this->addFuzzyAndCondition($field, $searchTerm, $isNegative, $distance);
        }

        return $this;
    }

    /**
     * @param string $entityFieldName
     * @param string $searchTerm
     * @param bool $isNegative
     * @param bool $wildcard
     * @return AbstractSelectQuery
     */
    public function addOrWhere($entityFieldName, $searchTerm, $isNegative = false, $wildcard = false)
    {
        $searchTerm = $this->prepareSearchTerm($searchTerm, $wildcard);
        if ($searchTerm) {
            $field = $this->prepareField($entityFieldName, false);
            $this->addOrCondition($field, $searchTerm, $wildcard, $isNegative);
        }

        return $this;
    }

    /**
     * @param $entityFieldName
     * @param $searchTerm
     * @param bool|false $isNegative
     * @param bool|false $wildcard
     * @return AbstractSelectQuery
     */
    public function addAndWhere($entityFieldName, $searchTerm, $isNegative = false, $wildcard = false)
    {
        $searchTerm = $this->prepareSearchTerm($searchTerm, $wildcard);
        if ($searchTerm) {
            $field = $this->prepareField($entityFieldName, false);
            $this->addAndCondition($field, $searchTerm, $wildcard, $isNegative);
        }

        return $this;
    }

    /**
     * @param string $configFieldName
     * @param string $searchTerm
     * @param bool $isNegative
     * @param bool|false $wildcard
     * @return AbstractSelectQuery
     */
    public function addConfigFieldOrWhere($configFieldName, $searchTerm, $isNegative = false, $wildcard = false)
    {
        $searchTerm = $this->prepareSearchTerm($searchTerm, $wildcard);
        if ($searchTerm) {
            $field = $this->prepareField($configFieldName, true);
            $this->addOrCondition($field, $searchTerm, $wildcard, $isNegative);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        $discriminatorConditions = join(' OR ', $this->discriminatorConditions);
        if (count(array_merge($this->addOrCondition, $this->addAndCondition)) === 0) {
            return $discriminatorConditions;
        }

        $orCondition = join(' OR ', $this->addOrCondition);
        $andCondition = join(' AND ', $this->addAndCondition);


        if ($orCondition && $andCondition) {
            $result = sprintf(
                '(%s AND %s) AND (%s)',
                $orCondition,
                $andCondition,
                $discriminatorConditions
            );
        } else {
            $conditions = $orCondition ?: $andCondition;
            $result = sprintf('(%s) AND (%s)', $conditions, $discriminatorConditions);
        }

        return $result;

    }

    /**
     * @return AbstractSelectQuery
     */
    public function groupConditionsAsOr()
    {
        $this->addOrCondition = [ $this->buildGroupCondition() ];
        $this->addAndCondition = [];

        return $this;
    }

    /**
     * @return AbstractSelectQuery
     */
    public function groupConditionsAsAnd()
    {
        $this->addAndCondition = [ $this->buildGroupCondition() ];
        $this->addOrCondition = [];

        return $this;
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

    /**
     * @return AbstractSelectQuery
     */
    public function reset()
    {
        $this->isPhrase = false;
        $this->addAndCondition = [];
        $this->addOrCondition = [];
        $this->offset = 0;
        $this->limit = 100;
        $this->createSolrQuery();

        return $this;
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
     * @param string $fieldName
     * @param bool|false $isConfigField
     * @return DocumentFieldInterface
     */
    private function prepareField($fieldName, $isConfigField = false)
    {
        if (!is_string($fieldName)) {
            throw new QueryException('FieldName argument must be a string');
        }

        if ($isConfigField) {
            $field = $this->schema->getConfigFieldName($fieldName);
        } else {
            $field = $this->getField($fieldName);
        }

        return $field;
    }

    /**
     * @param DocumentFieldInterface $field
     * @param string $searchTerm
     * @param bool $wildcard
     * @param bool $isNegative
     */
    private function addOrCondition(DocumentFieldInterface $field, $searchTerm, $wildcard, $isNegative)
    {
        $condition = $this->buildFieldCondition($field, $searchTerm, $wildcard, $isNegative);
        $this->addOrCondition[] = $condition;
    }

    /**
     * @param DocumentFieldInterface $field
     * @param string $searchTerm
     * @param bool $wildcard
     * @param bool $isNegative
     */
    private function addAndCondition(DocumentFieldInterface $field, $searchTerm, $wildcard, $isNegative)
    {
        $condition = $this->buildFieldCondition($field, $searchTerm, $wildcard, $isNegative);
        $this->addAndCondition[] = $condition;
    }

    /**
     * @param DocumentFieldInterface $field
     * @param string $searchTerm
     * @param bool $wildcard
     * @param bool $isNegative
     * @return string
     */
    private function buildFieldCondition(DocumentFieldInterface $field, $searchTerm, $wildcard, $isNegative)
    {
        $fieldPartFormat = '%s:';
        $valuePartFormat = '"%s"';
        $isPhrase = (strpos($searchTerm, ' ') > 0);
        $this->isPhrase = $isPhrase ?: $this->isPhrase;

        if ($wildcard && !$isPhrase) {
            $valuePartFormat = '%s';
        }

        if ($field->getPriority()) {
            $format = $fieldPartFormat . sprintf('(%s)^%%s', $valuePartFormat);
            $condition = sprintf($format, $field->getDocumentFieldName(), $searchTerm, $field->getPriority());
        } else {
            $condition = sprintf($fieldPartFormat . $valuePartFormat , $field->getDocumentFieldName(), $searchTerm);
        }

        $condition = $isNegative ? $this->buildNegativeCondition($condition) : $condition;

        return $condition;
    }

    /**
     * @param DocumentFieldInterface $field
     * @param string $from
     * @param string $to
     * @param bool $exclusiveFrom
     * @param bool  $exclusiveTo
     * @param bool $isNegative
     * @return string
     */
    private function buildRangeCondition(DocumentFieldInterface $field, $from, $to, $exclusiveFrom, $exclusiveTo, $isNegative)
    {
        $fieldPartFormat = '%s:';
        $valuePartFormat = $exclusiveFrom ? '{' : '[';
        $valuePartFormat .= '%s TO %s';
        $valuePartFormat .= $exclusiveTo ? '}' : ']';
        $condition = $fieldPartFormat . $valuePartFormat;


        $condition = $isNegative ?  $this->buildNegativeCondition($condition) : $condition;

        return sprintf(
            $condition,
            $field->getDocumentFieldName(),
            $from,
            $to
        );
    }

    /**
     * @param $condition
     * @return string
     */
    private function buildNegativeCondition($condition)
    {
        return sprintf('(*:* AND -%s)', $condition);
    }

    /**
     * @param DocumentFieldInterface $field
     * @param string $searchTerm
     * @param bool $isNegative
     * @param int $distance
     * @return string
     */
    private function buildFuzzyCondition(DocumentFieldInterface $field, $searchTerm, $isNegative, $distance)
    {
        $fieldPartFormat = '%s:';
        if (strpos($searchTerm, ' ') > 0) {
            $parts = explode(' ', $searchTerm);
            $formattedParts = [];
            foreach ($parts as $part)  {
                $formattedParts[] = sprintf('%s~%u', $part, $distance);
            }
            $valuePartFormat = '"%s"';
            $this->isPhrase = true;
            $searchTerm = join(' ', $formattedParts);
        } else {
            $valuePartFormat = '%s';
            $searchTerm = sprintf('%s~%u', $searchTerm, $distance);
        }

        $condition = $fieldPartFormat . $valuePartFormat;
        $condition = $isNegative ?  $this->buildNegativeCondition($condition) : $condition;

        return sprintf(
            $condition,
            $field->getDocumentFieldName(),
            $searchTerm
        );
    }

    private function buildGroupCondition()
    {
        $currentOrConditions = join(' OR ', $this->addOrCondition);
        $currentAndConditions = join(' AND ', $this->addAndCondition);

        if ($currentOrConditions && $currentAndConditions) {
            $groupedCondition = sprintf('(%s AND %s)', $currentOrConditions, $currentAndConditions);
        } else {
            $conditions =  $currentOrConditions ?: $currentAndConditions;
            $groupedCondition = sprintf('(%s)', $conditions);
        }

        return $groupedCondition;
    }

    /**
     * @param DocumentFieldInterface $field
     * @param string $from
     * @param string $to
     * @param bool $exclusiveFrom
     * @param bool $exclusiveTo
     * @param bool $isNegative
     */
    private function addRangeOrCondition(DocumentFieldInterface $field, $from, $to, $exclusiveFrom, $exclusiveTo, $isNegative)
    {
        $this->addOrCondition[] = $this->buildRangeCondition($field, $from, $to, $exclusiveFrom, $exclusiveTo, $isNegative);
    }

    /**
     * @param DocumentFieldInterface $field
     * @param string $from
     * @param string $to
     * @param bool $exclusiveFrom
     * @param bool $exclusiveTo
     * @param bool $isNegative
     */
    private function addRangeAndCondition(DocumentFieldInterface $field, $from, $to, $exclusiveFrom, $exclusiveTo, $isNegative)
    {
        $this->addAndCondition[] = $this->buildRangeCondition($field, $from, $to, $exclusiveFrom, $exclusiveTo, $isNegative);
    }

    /**
     * @param DocumentFieldInterface $field
     * @param string $searchTerm
     * @param bool $isNegative
     * @param int $distance
     */
    private function addFuzzyOrCondition(DocumentFieldInterface $field, $searchTerm, $isNegative, $distance)
    {
        $this->addOrCondition[] = $this->buildFuzzyCondition($field, $searchTerm, $isNegative, $distance);
    }

    /**
     * @param DocumentFieldInterface $field
     * @param string $searchTerm
     * @param bool $isNegative
     * @param int $distance
     */
    private function addFuzzyAndCondition(DocumentFieldInterface $field, $searchTerm, $isNegative, $distance)
    {
        $this->addAndCondition[] = $this->buildFuzzyCondition($field, $searchTerm, $isNegative, $distance);
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
     * @param $searchTerm
     * @param bool $wildcard
     * @return mixed
     * @throws QueryException
     */
    private function prepareSearchTerm($searchTerm, $wildcard = false)
    {
        if (!is_scalar($searchTerm)) {
            throw new QueryException('SearchTerm argument must be a scalar');
        }

        $specialSymbols = ['+','-','&&','||','!','(',')','{','}','[',']','^','"','~',':','/'];
        $escapedSpecialSymbols = ['\+','\-','\&&','\||','\!','\(','\)','\{','\}','\[','\]','\^','\"','\~','\:','\/'];

        if (!$wildcard) {
            $specialSymbols = array_merge($specialSymbols, ['*','?']);
            $escapedSpecialSymbols = array_merge($escapedSpecialSymbols, ['\*','\?']);
        }

        $searchTerm = preg_replace('/[^a-zA-Z\s0-9-_=+.?*!:)(\]\[ ]/', '', $searchTerm);

        return str_replace($specialSymbols, $escapedSpecialSymbols, $searchTerm);
    }


    private function createSolrQuery()
    {
        $this->solrQuery = $this->client->createSelect()
            ->setFields($this->requiredDocumentFieldsNames);
    }

}