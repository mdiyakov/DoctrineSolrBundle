<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Suggest\Result;

use Mdiyakov\DoctrineSolrBundle\Exception\SuggestResultException;

class Result
{
    /**
     * @var FieldResult[]
     */
    private $data;

    /**
     * @param FieldResult $fieldResult
     */
    public function addFieldResult(FieldResult $fieldResult)
    {
        $this->data[$fieldResult->getEntityFieldName()] = $fieldResult;
    }

    /**
     * @param $entityFieldName
     * @return FieldResult
     * @throws SuggestResultException
     */
    public function getResultsByField($entityFieldName)
    {
        if (!array_key_exists($entityFieldName, $this->data)) {
            throw new SuggestResultException(
                sprintf('There is no result for %s entity field', $entityFieldName)
            );
        }

        return $this->data[$entityFieldName];
    }
}