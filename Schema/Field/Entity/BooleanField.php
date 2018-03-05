<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;

class BooleanField extends Field
{
    /**
     * @param object $entity
     * @return bool
     */
    public function getDocumentFieldValue($entity)
    {
        return boolval($this->getEntityFieldValue($entity));
    }


}