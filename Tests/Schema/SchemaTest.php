<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Schema;

use Mdiyakov\DoctrineSolrBundle\Schema\Field\ConfigEntityField;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\DocumentUniqueField;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\Field;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;
use Symfony\Component\Yaml\Yaml;

class SchemaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string[][][][]
     */
    private $schemaFixtures;

    public function setUp()
    {
        $this->schemaFixtures = Yaml::parse(file_get_contents(__DIR__ . '/schema_fixtures.yml'));
    }

    public function testSuccessInitialization()
    {
        $name = 'valid_schema';
        $schemaConfig = $this->schemaFixtures[$name];

        $schema = new Schema(
            $name,
            $schemaConfig['client'],
            $schemaConfig['document_unique_field'],
            $schemaConfig['fields'],
            $schemaConfig['config_entity_fields']
        );

        $fieldNames = array_flip(
            array_map(
                function($fieldConfig) { return $fieldConfig['entity_field_name']; },
                $schemaConfig['fields']
            )
        );

        $documentFieldNames = array_flip(
            array_map(
                function($fieldConfig) { return $fieldConfig['document_field_name']; },
                $schemaConfig['fields']
            )
        );

        $configFieldNames = array_flip(
            array_map(
                function($configFieldConfig) { return $configFieldConfig['config_field_name']; },
                $schemaConfig['config_entity_fields'])
        );

        $documentConfigFieldNames = array_flip(
            array_map(
                function($configFieldConfig) { return $configFieldConfig['document_field_name']; },
                $schemaConfig['config_entity_fields'])
        );

        $this->assertEquals($name, $schema->getName());
        $this->assertEquals($schemaConfig['client'], $schema->getClient());
        $configFields = $schema->getConfigEntityFields();

        $discriminator = false;
        foreach($configFields as $configField) {
            $this->assertInstanceOf(ConfigEntityField::class, $configField);
            $discriminator = $discriminator ? $discriminator : $configField->isDiscriminator();
            $this->assertArrayHasKey($configField->getConfigFieldName(), $configFieldNames);
            $this->assertArrayHasKey($configField->getDocumentFieldName(), $documentConfigFieldNames);
        }

        $this->assertEquals(true, $discriminator);

        $uniqueDocumentField = $schema->getDocumentUniqueField();
        $this->assertInstanceOf(DocumentUniqueField::class, $uniqueDocumentField);
        $this->assertEquals($schemaConfig['document_unique_field']['name'], $uniqueDocumentField->getName());

        $primaryKeyField = $schema->getEntityPrimaryKeyField();
        $this->assertInstanceOf(Field::class, $primaryKeyField);
        $this->assertEquals('id', $primaryKeyField->getEntityFieldName());
        $this->assertEquals('d_id', $primaryKeyField->getDocumentFieldName());

        $fields = $schema->getFields();
        foreach($fields as $field) {
            $this->assertInstanceOf(Field::class, $field);
            $this->assertArrayHasKey($field->getEntityFieldName(), $fieldNames);
            $this->assertArrayHasKey($field->getDocumentFieldName(), $documentFieldNames);
        }

        $titleField = $schema->getFieldByEntityFieldName('title');
        $this->assertInstanceOf(Field::class, $titleField);
        $this->assertEquals('title', $titleField->getEntityFieldName());
        $this->assertEquals('d_title', $titleField->getDocumentFieldName());
    }
}