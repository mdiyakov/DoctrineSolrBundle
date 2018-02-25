<?php

namespace Mdiyakov\DoctrineSolrBundle\Suggester;

use Mdiyakov\DoctrineSolrBundle\Query\Suggest\SchemaSuggestQuery;

class SchemaSuggester extends AbstractSuggester
{
    /**
     * @var SchemaSuggestQuery
     */
    private $query;

    /**
     * @param SchemaSuggestQuery $query
     */
    public function __construct(SchemaSuggestQuery $query)
    {
        $this->query = $query;
    }

    /**
     * @return SchemaSuggestQuery
     */
    protected function getQuery()
    {
        return $this->query->reset();
    }

}