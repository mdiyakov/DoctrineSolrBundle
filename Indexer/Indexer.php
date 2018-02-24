<?php

namespace Mdiyakov\DoctrineSolrBundle\Indexer;

use Mdiyakov\DoctrineSolrBundle\Query\Update\UpdateQuery;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;

class Indexer
{
    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var UpdateQuery
     */
    private $updateQuery;

    /**
     * @var string[]
     */
    private $entityConfig;

    /**
     * @param UpdateQuery $updateQuery
     * @param Schema $schema
     * @param string[] $entityConfig
     */
    public function __construct(UpdateQuery $updateQuery, Schema $schema, $entityConfig)
    {
        $this->updateQuery = $updateQuery;
        $this->schema = $schema;
        $this->entityConfig = $entityConfig;
    }

    /**
     * @param mixed $entity
     */
    public function indexAllFields($entity)
    {
        $updateQuery = $this->getUpdateQuery();
        $updateQuery->beginEntity();
        $fields = $this->schema->getFields();
        $uniqueField = $this->schema->getDocumentUniqueField();
        $configEntityFields = $this->schema->getConfigEntityFields();

        foreach ($fields as $field) {
            $updateQuery->addField(
                $field->getEntityFieldName(),
                $field->getDocumentFieldValue($entity)
            );
        }

        foreach ($configEntityFields as $configField) {
            $updateQuery->addConfigField(
                $configField->getConfigFieldName(),
                $configField->getValue($this->entityConfig)
            );
        }

        $updateQuery->addUniqueFieldValue(
            $uniqueField->getValue($entity, $this->entityConfig)
        );


        $updateQuery->endEntity();
        $updateQuery->update();
    }

    /**
     * @param mixed $entity
     */
    public function removeByPrimaryKey($entity)
    {
        $uniqueField = $this->schema->getDocumentUniqueField();
        $updateQuery = $this->getUpdateQuery();
        $updateQuery->addDeleteCriteriaByUniqueFieldValue(
            $uniqueField->getValue($entity, $this->entityConfig)
        );

        $updateQuery->update();
    }

    /**
     * @return UpdateQuery
     */
    private function getUpdateQuery()
    {
        return $this->updateQuery->reset();
    }
}