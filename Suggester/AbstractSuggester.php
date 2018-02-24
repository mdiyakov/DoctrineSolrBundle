<?php

namespace Mdiyakov\DoctrineSolrBundle\Suggester;

use Mdiyakov\DoctrineSolrBundle\Query\Suggest\AbstractSuggestQuery;

abstract class AbstractSuggester
{

    /**
     * @return AbstractSuggestQuery
     */
    abstract protected function getQuery();


    /**
     * @param string $searchTerm
     * @param string[] $fields
     * @param int $limit
     * @return \Solarium\Core\Query\Result\ResultInterface
     */
    public function suggestByFields($searchTerm, $fields, $limit = 10)
    {
        $query = $this->getQuery();
        $query->setTerm($searchTerm);
        $query->setCount($limit);

        if (is_array($fields)) {
            foreach ($fields as $field) {
                $query->addField($field);
            }
        }

        return $query->suggest();
    }
}