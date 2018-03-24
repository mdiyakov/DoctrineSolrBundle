<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;

class StringField extends Field
{
    /**
     * @param object $entity
     * @return string
     */
    public function getDocumentFieldValue($entity)
    {
        $entityValue = $this->getEntityFieldValue($entity);
        $documentValue = '';
        if (is_scalar($entityValue)) {
            $documentValue = strval($entityValue);
        } elseif (is_array($entityValue) || $entityValue instanceof \Iterator || $entityValue instanceof \IteratorAggregate) {
            $documentValue = [];
            foreach ($entityValue as $value) {
                $documentValue[] = (string) $value;
            }

            $documentValue = join(',', $documentValue);
        } elseif (is_object($entityValue)) {
            $documentValue = (string) $entityValue;
        }

        return $documentValue;
    }
}