<?php


namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;


class IntField extends Field
{
    /**
     * @param object $entity
     * @return float
     */
    public function getDocumentFieldValue($entity)
    {
        return intval($this->getEntityFieldValue($entity));
    }
}