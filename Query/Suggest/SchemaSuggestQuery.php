<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Suggest;

use Mdiyakov\DoctrineSolrBundle\Query\Suggest\Solarium\Query;

class SchemaSuggestQuery extends AbstractSuggestQuery
{

    /**
     * @param Query $solrQuery
     */
    protected function initDiscriminatorConditions(Query $solrQuery) {}
}