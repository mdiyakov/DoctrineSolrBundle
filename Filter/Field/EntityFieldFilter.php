<?php

namespace Mdiyakov\DoctrineSolrBundle\Filter\Field;

use Mdiyakov\DoctrineSolrBundle\Exception\FilterConfigException;
use Mdiyakov\DoctrineSolrBundle\Filter\EntityFilterInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

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
     * @param object $entity
     * @return bool
     * @throws FilterConfigException
     */
    public function isFilterValid($entity)
    {
        if (!is_object($entity)) {
            throw new FilterConfigException('Entity must be an object to be filtered');
        }

        $value = PropertyAccess::createPropertyAccessor()->getValue($entity, $this->getEntityFieldName());
        if (!is_scalar($value)) {
            if (method_exists($value, '__toString')) {
                $value = call_user_func([$value, '__toString']);
            } else {
                throw new FilterConfigException('Entity field must have scalar value to be filtered');
            }
        }

        return $this->validate($value);
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

    /**
     * @param $value
     * @return bool
     */
    abstract protected function validate($value);

}