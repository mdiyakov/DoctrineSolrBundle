<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Manager;

use Mdiyakov\DoctrineSolrBundle\Manager\FinderManager;

class FinderManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructorSuccess()
    {
        $entityClass = 'MyEntity';
        $schemaName = 'schema';
        $config = $this->getMockBuilder('Mdiyakov\DoctrineSolrBundle\Config\Config')->disableOriginalConstructor()->getMock();
        $queryBuilder = $this->getMockBuilder('Mdiyakov\DoctrineSolrBundle\Query\SelectQueryBuilder')->disableOriginalConstructor()->getMock();
        $classSelectQuery = $this->getMockBuilder('Mdiyakov\DoctrineSolrBundle\Query\Select\ClassSelectQuery')->disableOriginalConstructor()->getMock();
        $schema = $this->getMockBuilder('Mdiyakov\DoctrineSolrBundle\Schema\Schema')->disableOriginalConstructor()->getMock();
        $entityConfigs = [
            ['class' => $entityClass, 'schema' => $schemaName]
        ];

        $config->expects($this->at(0))
            ->method('getIndexedEntities')
            ->will($this->returnValue($entityConfigs));


        $config->expects($this->at(1))
            ->method('getSchemaByEntityClass')
            ->will($this->returnValue($schema));

        $config->expects($this->at(2))
            ->method('getIndexedEntities')
            ->will($this->returnValue($entityConfigs));

        $queryBuilder->expects($this->at(0))
            ->method('buildClassSelectQuery')
            ->with($entityClass)
            ->will($this->returnValue($classSelectQuery));

        $manager = new FinderManager($config, $queryBuilder);

        $schemaFinder = $manager->getSchemaFinder($schemaName);
        $classFinder = $manager->getClassFinder($entityClass);

        $this->assertInstanceOf('Mdiyakov\DoctrineSolrBundle\Finder\ClassFinder', $classFinder);
        $this->assertInstanceOf('Mdiyakov\DoctrineSolrBundle\Finder\SchemaFinder', $schemaFinder);
    }
}