<?php

namespace Mdiyakov\DoctrineSolrBundle\Filter\Field;

class EntityFieldGteFilter extends EntityFieldFilter
{

    /**
     * @param mixed $value
     * @return bool
     */
    protected function validate($value)
    {
        return $value >= $this->getEntityFieldValue();
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