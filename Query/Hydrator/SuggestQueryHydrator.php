<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Hydrator;

use Mdiyakov\DoctrineSolrBundle\Exception\HydratorException;
use Mdiyakov\DoctrineSolrBundle\Query\Suggest\Result\FieldResult;
use Mdiyakov\DoctrineSolrBundle\Query\Suggest\Solarium\Result\Result;
use Mdiyakov\DoctrineSolrBundle\Query\Suggest\Solarium\Result\Term;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;
use Mdiyakov\DoctrineSolrBundle\Query\Suggest\Result\Result as SuggestQueryResult;


class SuggestQueryHydrator
{
    /**
     * @var Schema
     */
    private $schema;

    /**
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @param Result $solrResult
     * @return SuggestQueryResult
     */
    public function hydrate(Result $solrResult)
    {
        if (!$solrResult->count()) {
            throw new HydratorException('Result dataset is empty');
        }

        $result = new SuggestQueryResult();

        foreach ($solrResult->getResults() as $suggester => $termData) {
            $field = $this->schema->getFieldBySuggester($suggester);

            /** @var Term $term */
            $term = current($termData);
            $result->addFieldResult(
                new FieldResult($field->getEntityFieldName(), key($termData), $term->getSuggestions())
            );
        }

        return $result;
    }
}