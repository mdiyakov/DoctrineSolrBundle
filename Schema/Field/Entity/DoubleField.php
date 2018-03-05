<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;

class DoubleField extends NumericField
{
    /**
     * @param mixed $value
     * @return float
     */
    protected function castValue($value)
    {
        return doubleval($value);
    }
}