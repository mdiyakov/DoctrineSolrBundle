<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;

class IntField extends NumericField
{
    /**
     * @param $value
     * @return int
     */
    public function castValue($value)
    {
        return intval($value);
    }
}