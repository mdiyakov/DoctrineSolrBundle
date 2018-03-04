<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;

class ArrayField extends Field
{
    /**
     * @param object $entity
     * @return string[]
     */
    public function getDocumentFieldValue($entity)
    {
        $entityValue = $this->getEntityFieldValue($entity);
        if (!is_array($entityValue)) {
            $entityValue = (array) $entityValue;
        }
        $result = [];
        foreach ($entityValue as $value) {
            $result[] = strval($value);
        }

        return $entityValue;
    }
}