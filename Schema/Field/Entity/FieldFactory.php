<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;

class FieldFactory
{
    private $map = [
        'string' => 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField',
        'int' => 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\IntField',
        'double' => 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\DoubleField',
        'array' => 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\ArrayField',
        'boolean' => 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\BooleanField',
        'date' => 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\DateField',
    ];

    /**
     * @param string[] $fieldConfig
     * @return Field
     * @throws \Exception
     */
    public function buildField($fieldConfig)
    {
        if (!array_key_exists($fieldConfig['field_type'], $this->map)) {
            throw new \Exception(
                sprintf('The index field type "%s" is not implemented yet', $fieldConfig['field_type'])
            );
        }

        $fieldClass = $this->map[$fieldConfig['field_type']];
        /** @var Field $field */
        $field = new $fieldClass(
            $fieldConfig['entity_field_name'],
            $fieldConfig['document_field_name'],
            $fieldConfig['entity_primary_key'],
            $fieldConfig['priority'],
            $fieldConfig['suggester']
        );

        return $field;
    }


}