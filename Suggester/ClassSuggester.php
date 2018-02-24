<?php

namespace Mdiyakov\DoctrineSolrBundle\Suggester;

use Mdiyakov\DoctrineSolrBundle\Query\Suggest\ClassSuggestQuery;

class ClassSuggester extends AbstractSuggester
{

    /**
     * @var ClassSuggestQuery
     */
    private $query;

    public function __construct(ClassSuggestQuery $query)
    {
        $this->query = $query;
    }

    /**
     * @return ClassSuggestQuery
     */
    protected function getQuery()
    {
        return $this->query->reset();
    }
}