<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;

use Mdiyakov\DoctrineSolrBundle\Exception\InvalidFieldValueException;

class DateField extends Field
{
    const FORMAT = 'Y-m-d\TH:i:s\Z';

    /**
     * @param object $entity
     * @return string|null
     */
    public function getDocumentFieldValue($entity)
    {
        $result = null;
        $entityValue = $this->getEntityFieldValue($entity);
        if (!is_null($entityValue)) {
            if (!$entityValue instanceof \DateTime) {
                throw new InvalidFieldValueException(
                    sprintf(
                        '"%s" field value of "%s" must be a DateTime instance',
                        $this->getEntityFieldName(),
                        get_class($entity)
                    )
                );
            }
            $result = $entityValue->format(self::FORMAT);
        }

        return $result;
    }
}