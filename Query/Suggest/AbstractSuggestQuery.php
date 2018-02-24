<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Suggest;

use Mdiyakov\DoctrineSolrBundle\Exception\SuggestQueryException;
use Mdiyakov\DoctrineSolrBundle\Query\Suggest\Solarium\Query;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;
use Solarium\Client;

abstract class AbstractSuggestQuery
{
    const COUNT_DEFAULT = 10;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var string
     */
    private $term;

    /**
     * @var string[]
     */
    private $suggesters = [];

    /**
     * @var int
     */
    private $count;

    /**
     * @param Client $client
     * @param Schema $schema
     */
    public function __construct(Client $client, Schema $schema)
    {
        $this->client = $client;
        $this->schema = $schema;
    }

    /**
     * @param string $term
     */
    public function setTerm($term)
    {
        $this->term = $term;
    }

    /**
     * @param string $entityFieldName
     * @throws SuggestQueryException
     */
    public function addField($entityFieldName)
    {
        $field = $this->schema->getFieldByEntityFieldName($entityFieldName);

        if (!$field->getSuggester()) {
            throw new SuggestQueryException(
                sprintf(
                    'Class "%s"  does not support a suggestion by field %s',
                    $this->entityConfig['class'],
                    $entityFieldName
                )
            );
        }

        $this->suggesters[] = $field->getSuggester();
    }

    /**
     * @return \Solarium\Core\Query\Result\ResultInterface
     * @throws SuggestQueryException
     */
    public function suggest()
    {
        if (!$this->term) {
            throw new SuggestQueryException('Search term is not specified');
        }

        if (!$this->suggesters) {
            throw new SuggestQueryException('No entity field is specified for suggestion');
        }

        $solrQuery = new Query();
        $solrQuery->setQuery($this->term);

        $this->initDiscriminatorConditions($solrQuery);

        foreach ($this->suggesters as $suggesterName) {
            $solrQuery->addDictionary($suggesterName);
        }

        $solrQuery->setCount($this->getCount());

        return $this->client->execute($solrQuery);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @param Query $solrQuery
     */
    abstract protected function initDiscriminatorConditions(Query $solrQuery);

    /**
     * @return Schema
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @return AbstractSuggestQuery
     */
    public function reset()
    {
        $this->suggesters = [];
        $this->term = null;
        $this->count = self::COUNT_DEFAULT;

        return $this;
    }
}