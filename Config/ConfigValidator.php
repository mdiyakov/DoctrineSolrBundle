<?php

namespace Mdiyakov\DoctrineSolrBundle\Config;

use Mdiyakov\DoctrineSolrBundle\Exception\ClientConfigException;
use Mdiyakov\DoctrineSolrBundle\Exception\ConfigFieldException;
use Mdiyakov\DoctrineSolrBundle\Exception\FilterConfigException;
use Mdiyakov\DoctrineSolrBundle\Exception\RequiredFieldException;
use Mdiyakov\DoctrineSolrBundle\Exception\SchemaConfigException;
use Mdiyakov\DoctrineSolrBundle\Exception\SchemaNotFoundException;

class ConfigValidator
{

    /**
     * @param string[][] $entityConfig
     * @param string[][] $schemes
     * @param string[][] $filters
     * @param string[] $clients
     * @throws SchemaNotFoundException
     */
    public function validate($entityConfig, $schemes, $filters, $clients)
    {
        if (!array_key_exists($entityConfig['schema'], $schemes)) {
            throw new SchemaNotFoundException(
                sprintf('Schema "%s" is not found in "schema" config section. Check config.yml', $entityConfig['schema'])
            );
        }

        $schemaConfig = $schemes[$entityConfig['schema']];
        $this->checkEntityContainRequiredFields($entityConfig['class'], $schemaConfig);
        $this->checkConfigFields($entityConfig, $schemaConfig);
        $this->checkFilters($entityConfig, $filters);
        $this->checkClients($schemaConfig, $clients);
        $this->checkDocumentFieldNamesAreUnique($schemaConfig);
    }

    /**
     * @param string $entityClass
     * @param string[][] $schemaConfig
     * @throws RequiredFieldException
     */
    private function checkEntityContainRequiredFields($entityClass, $schemaConfig)
    {
        foreach ($schemaConfig['fields'] as $fieldConfig) {
            $this->checkEntityHasField($entityClass, $fieldConfig['entity_field_name']);
        }
    }

    /**
     * @param string[][] $entityConfig
     * @param string[][] $schemaConfig
     * @throws ConfigFieldException
     */
    private function checkConfigFields($entityConfig, $schemaConfig)
    {
        $schemaConfigEntityFields = $schemaConfig['config_entity_fields'];
        if (!is_array($schemaConfigEntityFields)) {
            return;
        }

        $configFields = $entityConfig['config'];
        if (!is_array($configFields)) {
            throw new ConfigFieldException(
                sprintf('Config fields for "%s" entity are not defined. Check entity config', $entityConfig['class'])
            );
        }
        $configFieldsNames = [];
        foreach ($configFields as $configField) {
            $configFieldsNames[$configField['name']] = true;
        }

        foreach ($schemaConfigEntityFields as $fieldConfig) {
            if (!array_key_exists($fieldConfig['config_field_name'], $configFieldsNames)) {
                throw new ConfigFieldException(
                    sprintf(
                        '"%s" config field is not defined in indexed_entities for entity %s',
                        $fieldConfig['config_field_name'],
                        $entityConfig['class']
                    )
                );
            }
        }
    }

    /**
     * @param $schemaConfig
     * @param $clients
     * @throws ClientConfigException
     */
    private function checkClients($schemaConfig, $clients)
    {
        if (!array_key_exists($schemaConfig['client'], $clients)) {
            throw new ClientConfigException(
                sprintf('Solarium client "%s" is not defined in "solarium_clients" section', $schemaConfig['client'])
            );
        }
    }

    /**
     * @param string[][] $entityConfig
     * @param string[][] $filters
     */
    private function checkFilters($entityConfig, $filters)
    {
        if (array_key_exists('filters', $entityConfig)) {

            $filtersKeys = array_fill_keys(
                array_merge(
                    array_keys($filters['fields']),
                    array_keys($filters['services'])
                ),
                true
            );

            foreach($entityConfig['filters'] as $filterName) {
                if (!array_key_exists($filterName, $filtersKeys)) {
                    throw new FilterConfigException(
                        sprintf('Filter "%s" is not defined in "filters" section', $filterName)
                    );
                }
            }

            if (array_key_exists('fields', $filters) && is_array($filters['fields'])) {
                foreach ($filters['fields'] as $filterName => $filterConfig) {
                    try {
                        $this->checkEntityHasField($entityConfig['class'], $filterConfig['entity_field_name']);
                    } catch (RequiredFieldException $e) {
                        $message = $e->getMessage() . ' Error occurred for "filters" section. Please check the filters configuration in config.yml';
                        throw new RequiredFieldException($message);
                    }

                }
            }
        }
    }

    /**
     * @param string[][] $schemaConfig
     * @throws SchemaConfigException
     */
    private function checkDocumentFieldNamesAreUnique($schemaConfig)
    {
        $schemaFields = [ $schemaConfig['config_entity_fields'], $schemaConfig['fields'] ];
        $documentFieldsNames = [];

        while ($fieldsConfig = array_shift($schemaFields)) {
            foreach ($fieldsConfig as $fieldConfig) {
                $documentFieldName = $fieldConfig['document_field_name'];
                if (array_key_exists($documentFieldName, $documentFieldsNames)) {
                    throw new SchemaConfigException(
                        sprintf('You have more than one fields with the same  document field name "%s"', $documentFieldName)
                    );
                }
                $documentFieldsNames[$documentFieldName] = true;
            }
        }
    }

    /**
     * @param string $entityClass
     * @param string $entityFieldName
     * @throws RequiredFieldException
     */
    private function checkEntityHasField($entityClass, $entityFieldName)
    {
        $reflection = new \ReflectionClass($entityClass);
        $methodName = 'get' . ucfirst($entityFieldName);

        if (!$reflection->hasMethod($methodName)) {
            throw new RequiredFieldException(
                sprintf('Mandatory field getter method "%s" is not found in %s.', $methodName, $entityClass)
            );
        }

        $reflectionMethod = new \ReflectionMethod($entityClass, $methodName);
        if (!$reflectionMethod->isPublic()) {
            throw new RequiredFieldException(
                sprintf('Mandatory field getter method "%s" is not public in "%s".', $methodName, $entityClass)
            );
        }
    }
}