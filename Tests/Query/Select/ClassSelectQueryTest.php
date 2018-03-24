<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Query\Select;

use Mdiyakov\DoctrineSolrBundle\Query\Hydrator\SelectQueryHydrator;
use Mdiyakov\DoctrineSolrBundle\Query\Select\ClassSelectQuery;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\ConfigEntityField;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\Field;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;

class ClassSelectQueryTest extends \PHPUnit_Framework_TestCase
{

    public function testInitDiscriminatorConditionsSuccess()
    {
        $configFieldName = 'type';
        $documentFieldName = 'd_type';
        $configFieldValue = 'article';
        $entityConfig =  [
            'config' => [
                [ 'name' => $configFieldName, 'value' => $configFieldValue ]
            ]
        ];

        /** @var \Solarium\Client|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMockBuilder(\Solarium\Client::class)->disableOriginalConstructor()->getMock();
        $client->expects($this->at(0))->method('createSelect')
            ->will($this->returnValue(
                $this->createMock('Solarium\QueryType\Select\Query\Query')
            ));


        /** @var Schema|\PHPUnit_Framework_MockObject_MockObject $schema */
        $schema = $this->getMockBuilder(Schema::class)->disableOriginalConstructor()->getMock();
        $discriminatorField = new ConfigEntityField($configFieldName, $documentFieldName, true, 10);
        $primaryKeyField = $this->getMockBuilder(Field::class)->disableOriginalConstructor()->getMock();

        $schema->expects($this->any())->method('getDiscriminatorConfigField')
            ->will($this->returnValue($discriminatorField));

        $schema->expects($this->any())->method('getEntityPrimaryKeyField')
            ->will($this->returnValue($primaryKeyField));

        /** @var SelectQueryHydrator|\PHPUnit_Framework_MockObject_MockObject $hydrator */
        $hydrator = $this->getMockBuilder(SelectQueryHydrator::class)->disableOriginalConstructor()->getMock();
        $query = new ClassSelectQuery(
            $client,
            $schema,
            $entityConfig,
            $hydrator
        );

        $this->assertEquals( sprintf('%s:"%s"', $documentFieldName, $configFieldValue) ,$query->getQueryString());
    }
}