<?php

namespace Mdiyakov\DoctrineSolrBundle\Filter\Field;

class EntityFieldRegexpFilter extends EntityFieldFilter
{

    /**
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        return (bool) preg_match($this->getEntityFieldValue(), $value);
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