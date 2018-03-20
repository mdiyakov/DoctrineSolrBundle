<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;

use Mdiyakov\DoctrineSolrBundle\Exception\InvalidFieldValueException;

class DateField extends Field
{
    const FORMAT = 'Y-m-d\TH:i:s\Z';

    /**
     * @param object $entity
     * @return string
     */
    public function getDocumentFieldValue($entity)
    {
        $entityValue = $this->getEntityFieldValue($entity);

        if (!$entityValue instanceof \DateTime) {
            throw new InvalidFieldValueException(
                sprintf(
                    '"%s" field value of "%s" must be a DateTime instance',
                    $this->getEntityFieldName(),
                    get_class($entity)
                )
            );
        }

        return $entityValue->format(self::FORMAT);
    }
}