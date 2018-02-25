<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema;

use Mdiyakov\DoctrineSolrBundle\Exception\SchemaConfigException;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\ConfigEntityField;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\DocumentUniqueField;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\Field;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\StringField;

class Schema
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var
     */
    private $configEntityFields;

    /**
     * @var Field
     */
    private $entityPrimaryKeyField;

    /**
     * @var ConfigEntityField
     */
    private $discriminatorConfigField;

    /**
     * @var DocumentUniqueField
     */
    private $documentUniqueField;

    /**
     * @var Field[]
     */
    private $fields = [];

    /**
     * @var string
     */
    private $client;

    /**
     * @var Field[]
     */
    private $suggesterFieldMap = [];

    /**
     * @param string $name
     * @param string $client
     * @param string[] $documentUniqueFieldConfig
     * @param string[][] $fieldsConfig
     * @param string[][] $configEntityFields
     * @throws \Exception
     */
    public function __construct($name, $client, $documentUniqueFieldConfig, $fieldsConfig, $configEntityFields)
    {
        $this->name = $name;
        $this->client = $client;
        $this->documentUniqueField = new DocumentUniqueField($documentUniqueFieldConfig, $this);
        foreach ($fieldsConfig as $fieldConfig) {
            /** todo implement something not so hardcoded */
            if ($fieldConfig['index_type'] == 'string') {
                $field = new StringField(
                    $fieldConfig['entity_field_name'],
                    $fieldConfig['document_field_name'],
                    $fieldConfig['entity_primary_key'],
                    $fieldConfig['priority'],
                    $fieldConfig['suggester']
                );
                $this->fields[$field->getEntityFieldName()] = $field;

                if (!empty($fieldConfig['suggester'])) {
                    $this->suggesterFieldMap[$fieldConfig['suggester']] = $field;
                }
            } else {
                throw new \Exception(
                    sprintf('The index type %s is not implemented yet', $fieldConfig['index_type'])
                );
            }

            if ($field->isPrimaryKey()) {
                if ($this->getEntityPrimaryKeyField()) {
                    throw new SchemaConfigException('You have already defined one field as "primary key". It can be only one primary field in schema');
                }
                $this->entityPrimaryKeyField = $field;
            }
        }

        if (!$this->getEntityPrimaryKeyField()) {
            throw new SchemaConfigException('You have to define one field as "entity_primary_key" with true value');
        }

        foreach ($configEntityFields as $configField) {
            $configField = new ConfigEntityField(
                $configField['config_field_name'],
                $configField['document_field_name'],
                $configField['discriminator']
            );
            $this->configEntityFields[$configField->getConfigFieldName()] = $configField;

            if ($configField->isDiscriminator()) {
                if ($this->getDiscriminatorConfigField()) {
                    throw new SchemaConfigException('You have already defined one config field as "discriminator". It can be only one discriminator config field in schema');
                }
                $this->discriminatorConfigField = $configField;
            }
        }

        if (!$this->getDiscriminatorConfigField()) {
            throw new SchemaConfigException(
                'You have to define one config field with flag "discriminator" having true value'
            );
        }
    }

    /**
     * return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ConfigEntityField[]
     */
    public function getConfigEntityFields()
    {
        return $this->configEntityFields;
    }

    /**
     * @return Field
     */
    public function getEntityPrimaryKeyField()
    {
        return $this->entityPrimaryKeyField;
    }

    /**
     * @return ConfigEntityField
     */
    public function getDiscriminatorConfigField()
    {
        return $this->discriminatorConfigField;
    }

    /**
     * @param string $entityFieldName
     * @return Field
     * @throws SchemaConfigException
     */
    public function getFieldByEntityFieldName($entityFieldName)
    {
        if (!array_key_exists($entityFieldName, $this->fields)) {
            throw new SchemaConfigException(
                sprintf('Schema %s does not contain "%s" entity_field_name', $this->getName(), $entityFieldName)
            );
        }

        return $this->fields[$entityFieldName];
    }

    /**
     * @return DocumentUniqueField
     */
    public function getDocumentUniqueField()
    {
        return $this->documentUniqueField;
    }

    /**
     * @return string
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param $configFieldName
     * @return mixed
     */
    public function getConfigFieldName($configFieldName)
    {
        if (!array_key_exists($configFieldName, $this->configEntityFields)) {
            throw new SchemaConfigException(
                sprintf('Schema %s does not contain "%s" config field', $this->getName(), $configFieldName)
            );
        }

        return $this->configEntityFields[$configFieldName];
    }

    /**
     * @param string $suggester
     * @return Field
     */
    public function getFieldBySuggester($suggester)
    {
        if (!array_key_exists($suggester, $this->suggesterFieldMap)) {
            throw new SchemaConfigException(
                sprintf('Schema %s does not support suggestion "%s"', $this->getName(), $suggester)
            );
        }

        return $this->suggesterFieldMap[$suggester];
    }
}