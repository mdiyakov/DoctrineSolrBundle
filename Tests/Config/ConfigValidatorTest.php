<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Config;

use Mdiyakov\DoctrineSolrBundle\Config\ConfigValidator;
use Mdiyakov\DoctrineSolrBundle\Finder\ClassFinder;
use Symfony\Component\Yaml\Yaml;

class MyEntityFinder extends ClassFinder {}


class ConfigValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigValidator
     */
    private $validator;

    /**
     * @var array[]][]
     */
    private $configFixtures;

    public function setUp()
    {
        $this->validator = new ConfigValidator();
        $this->configFixtures = Yaml::parse(file_get_contents(__DIR__ . '/config_fixtures.yml'));
    }

    public function testSuccessValidation()
    {
        $this->runConfigTest('success_config');
    }

    /**
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\ConfigFieldException
     */
    public function testConfigFieldMissed()
    {
        $this->runConfigTest('config_field_missed');
    }

    /**
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\SchemaConfigException
     * @expectedExceptionMessage You have more than one fields with the same  document field name "d_id"
     */
    public function testDocumentNamesAreUnique()
    {
        $this->runConfigTest('document_field_names_unique');
    }

    /**
     * @expectedException  \Mdiyakov\DoctrineSolrBundle\Exception\RequiredFieldException
     * @expectedExceptionMessage Either getter method "getExtraField" or isser method "isExtraField" is not found in Mdiyakov\DoctrineSolrBundle\Tests\Config\Entity\MyEntity.
     */
    public function testEntityFieldNotExist()
    {
        $this->runConfigTest('entity_field_not_exist');
    }

    /**
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\FilterConfigException
     * @expectedExceptionMessage Filter "filter_1" is not defined in "filters" section
     */
    public function testFilterNotDefined()
    {
        $this->runConfigTest('filter_not_defined');
    }

    /**
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\ClientConfigException
     * @expectedExceptionMessage Solarium client "my_client" is not defined in "solarium_clients" section
     */
    public function testClientNotDefined()
    {
        $this->runConfigTest('client_not_defined');
    }

    /**
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\SchemaNotFoundException
     * @expectedExceptionMessage Schema "my_schema" is not found in "schema" config section. Check config.yml
     */
    public function testSchemaNotDefined()
    {
        $this->runConfigTest('schema_not_defined');
    }

    /**
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\EntityClassConfigException
     * @expectedExceptionMessage It seems entity class "Mdiyakov\DoctrineSolrBundle\Tests\Config\Entity\MyEntity" has been configured more than once inside "indexed_entities" section. You can not have different config for the single entity class
     */
    public function testEntityClassDoubled()
    {
        $this->runConfigTest('entity_class_doubled');
    }


    /**
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\ConfigFieldException
     * @expectedExceptionMessage "my_enitity_type" discriminator value has already been used.
    It seems there are two entity classes inside "indexed_entities" with identical discriminator config field value
     */
    public function testDiscriminatorConfigNotUnique()
    {
        $config = $this->configFixtures['discriminator_config_not_unique'];

        $config['indexed_entities']['my_entity_1']['class']  = 'Mdiyakov\DoctrineSolrBundle\Tests\Config\Entity\MyEntity';
        $config['indexed_entities']['my_entity_2']['class']  = 'Mdiyakov\DoctrineSolrBundle\Tests\Config\Entity\MyEntity2';

        foreach ($config['indexed_entities'] as $entityConfig) {
            $this->validator->validate($entityConfig, $config['schemes'], $config['filters'], $config['solarium_clients']);
        }
    }

    /**
     * @param string $configName
     * @return array
     */
    private function getConfig($configName)
    {
        $config = $this->configFixtures[$configName];

        $config['indexed_entities']['my_entity']['class']  = 'Mdiyakov\DoctrineSolrBundle\Tests\Config\Entity\MyEntity';
        $config['indexed_entities']['my_entity']['finder_class']  = 'Mdiyakov\DoctrineSolrBundle\Tests\Config\MyEntityFinder';

        return $config;
    }

    /**
     * @param string $configName
     */
    private function runConfigTest($configName)
    {
        $config = $this->getConfig($configName);
        foreach ($config['indexed_entities'] as $entityConfig) {
            $this->validator->validate($entityConfig, $config['schemes'], $config['filters'], $config['solarium_clients']);
        }
    }
}