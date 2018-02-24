<?php

namespace Mdiyakov\DoctrineSolrBundle\Filter\Field;

use Symfony\Component\PropertyAccess\PropertyAccess;

class EntityFieldRegexpFilter extends EntityFieldFilter
{

    /**
     * @param mixed $entity
     * @return bool
     */
    public function isFilterValid($entity)
    {
        $entityFieldValue = PropertyAccess::createPropertyAccessor()->getValue($entity, $this->getEntityFieldName());

        return (bool) preg_match($this->getEntityFieldValue(), $entityFieldValue);
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return sprintf('The value of %s have to be matched with regexp %s', $this->getEntityFieldName(),  (string) $this->getEntityFieldValue());
    }

    /**
     * @return string
     */
    public function getSupportedOperator()
    {
        return 'regexp';
    }
}