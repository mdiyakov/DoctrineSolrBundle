<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;

use Mdiyakov\DoctrineSolrBundle\Exception\InvalidFieldValueException;

abstract class NumericField extends Field
{

    /**
     * @param object $entity
     * @return mixed
     * @throws InvalidFieldValueException
     */
    public function getDocumentFieldValue($entity)
    {
        $value = $this->getEntityFieldValue($entity);
        if (!empty($value) && !is_numeric($value)) {
            throw new InvalidFieldValueException(
                sprintf(
                    '"%s" field value of "%s" must be a numeric',
                    $this->getEntityFieldName(),
                    get_class($entity)
                )
            );
        }

        return $this->castValue($value);
    }

    abstract protected function castValue($value);
}