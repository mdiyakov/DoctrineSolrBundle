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
     * @param string[] $configFields
     * @param bool $isNegative
     * @param bool $wildcard
     * @param bool $splitPhrase
     * @param int $limit
     * @return object[]
     */
    public function findSearchTermByFields(
        $searchTerm,
        $fields = null,
        $configFields = null,
        $isNegative = false,
        $wildcard = false,
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
                    $query->addOrWhere($field, $word, $isNegative, $wildcard);
                }
            }
        }

        if (is_array($configFields)) {
            foreach ($configFields as $field) {
                foreach ($words as $word) {
                    $query->addConfigFieldOrWhere($field, $word, $isNegative, $wildcard);
                }
            }
        }

        return $query->getResult();
    }

    /**
     * @param string $from
     * @param string $to
     * @param array $fields
     * @param bool $isNegative
     * @param bool|false $exclusiveFrom
     * @param bool|false $exclusiveTo
     * @param int $limit
     * @return \object[]
     */
    public function findByRange(
        $from,
        $to,
        $fields = [],
        $isNegative = false,
        $exclusiveFrom = false,
        $exclusiveTo = false,
        $limit = 100
    )
    {
        $query = $this->getQuery();
        $query->setLimit($limit);
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $query->addRangeOrWhere($field, $from, $to, $exclusiveFrom, $exclusiveTo, $isNegative);
            }
        }

        return $query->getResult();
    }

    /**
     * @param string $searchTerm
     * @param string[] $fields
     * @param bool $isNegative
     * @param bool|false $splitPhrase
     * @param int $distance
     * @param int $limit
     * @return \object[]
     */
    public function findFuzzyTerm($searchTerm, $fields, $isNegative = false, $splitPhrase = false, $distance = 1, $limit = 100)
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
                    $query->addFuzzyOrWhere($field, $word, $isNegative, $distance);
                }
            }
        }

        return $query->getResult();
    }
}