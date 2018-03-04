<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;

class DoubleField extends Field
{
    /**
     * @param object $entity
     * @return float
     */
    public function getDocumentFieldValue($entity)
    {
        return doubleval($this->getEntityFieldValue($entity));
    }
}