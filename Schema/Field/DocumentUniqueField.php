<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field;

use Mdiyakov\DoctrineSolrBundle\Schema\Schema;

class DocumentUniqueField
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var
     */
    private $schema;

    /**
     * @param $documentUniqueFieldConfig
     * @param Schema $schema
     */
    public function __construct($documentUniqueFieldConfig, Schema $schema)
    {
        $this->name = $documentUniqueFieldConfig['name'];
        $this->schema = $schema;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param object $entity
     * @param array $entityConfig
     * @return mixed
     */
    public function getValue($entity, $entityConfig)
    {
        $entityPrimaryKeyField = $this->schema->getEntityPrimaryKeyField();
        $discriminatorConfigField = $this->schema->getDiscriminatorConfigField();

        return sprintf(
            '%s-%s',
            $discriminatorConfigField->getValue($entityConfig),
            $entityPrimaryKeyField->getEntityFieldValue($entity)
        );
    }
}