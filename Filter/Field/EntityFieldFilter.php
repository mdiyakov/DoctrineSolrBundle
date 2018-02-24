<?php

namespace Mdiyakov\DoctrineSolrBundle\Filter\Field;

use Mdiyakov\DoctrineSolrBundle\Filter\EntityFilterInterface;

abstract class EntityFieldFilter implements EntityFilterInterface
{
    /**
     * @var string
     */
    private $entityFieldName;

    /**
     * @var mixed
     */
    private $entityFieldValue;

    /**
     * @param string $entityFieldName
     */
    public function setEntityFieldName($entityFieldName)
    {
        $this->entityFieldName = $entityFieldName;
    }

    /**
     * @param mixed $entityFieldValue
     */
    public function setEntityFieldValue($entityFieldValue)
    {
        $this->entityFieldValue = $entityFieldValue;
    }

    /**
     * @return string
     */
    public function getEntityFieldName()
    {
        return $this->entityFieldName;
    }

    /**
     * @return mixed
     */
    public function getEntityFieldValue()
    {
        return $this->entityFieldValue;
    }

    /**
     * @return string
     */
    abstract public function getSupportedOperator();


}