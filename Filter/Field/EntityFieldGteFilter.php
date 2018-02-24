<?php


namespace Mdiyakov\DoctrineSolrBundle\Filter\Field;

use Symfony\Component\PropertyAccess\PropertyAccess;

class EntityFieldGteFilter extends EntityFieldFilter
{

    /**
     * @param mixed $entity
     * @return bool
     */
    public function isFilterValid($entity)
    {
        $entityFieldValue = PropertyAccess::createPropertyAccessor()->getValue($entity, $this->getEntityFieldName());

        return $entityFieldValue >= $this->getEntityFieldValue();
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return sprintf('The value of %s have to be great than %s', $this->getEntityFieldName(),  (string) $this->getEntityFieldValue());
    }

    public function getSupportedOperator()
    {
        return '>=';
    }


}