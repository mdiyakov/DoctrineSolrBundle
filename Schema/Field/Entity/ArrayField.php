<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;

use Mdiyakov\DoctrineSolrBundle\Exception\InvalidFieldValueException;

class ArrayField extends Field
{
    /**
     * @param object $entity
     * @return string[]
     */
    public function getDocumentFieldValue($entity)
    {
        $result = [];
        $entityValue = $this->getEntityFieldValue($entity);
        if (!is_null($entityValue)) {
            if (is_scalar($entityValue)) {
                $entityValue =  [ $entityValue ];
            } elseif (
                !is_array($entityValue) &&
                ((!$entityValue instanceof \Iterator) && (!$entityValue instanceof \IteratorAggregate))
            ) {
                throw new InvalidFieldValueException('Field value must be \Iterator or \IteratorAggregate instance');
            }

            foreach ($entityValue as $value) {
                $result[] = strval($value);
            }
        }

        return $result;
    }
}