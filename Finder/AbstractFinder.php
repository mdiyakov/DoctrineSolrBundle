<?php

namespace Mdiyakov\DoctrineSolrBundle\Finder;

use Mdiyakov\DoctrineSolrBundle\Query\Select\AbstractSelectQuery;

abstract class AbstractFinder
{

    /**
     * @return AbstractSelectQuery
     */
    abstract protected function getQuery();

    /**
     * @param string $searchTerm
     * @param string[] $fields
     * @param bool $wildcardPostfix
     * @param bool $wildcardPrefix
     * @param bool $splitPhrase
     * @param int $limit
     * @return object[]
     */
    public function findByFields(
        $searchTerm,
        $fields = null,
        $configFields = null,
        $wildcardPostfix = false,
        $wildcardPrefix = false,
        $splitPhrase = true,
        $limit = 100
    )
    {
        if ($splitPhrase) {
            $words = explode(' ', $searchTerm);
        } else {
            $words = [$searchTerm];
        }

        $query = $this->getQuery();
        $query->setLimit($limit);
        if (is_array($fields)) {
            foreach ($fields as $field) {
                foreach ($words as $word) {
                    $query->addOrWhere($field, $word, $wildcardPostfix, $wildcardPrefix);
                }
            }
        } else {
            foreach ($words as $word) {
                $query->addAllFieldWhere($word);
            }
        }

        return $query->getResult();
    }
}