<?php

namespace Mdiyakov\DoctrineSolrBundle\Filter\Field;

class EntityFieldLteFilter extends EntityFieldFilter
{

    /**
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        return $value <= $this->getEntityFieldValue();
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
        return '<=';
    }

}