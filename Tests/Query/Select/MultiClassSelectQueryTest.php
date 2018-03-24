<?php


namespace Mdiyakov\DoctrineSolrBundle\Tests\Query\Select;


use Mdiyakov\DoctrineSolrBundle\Query\Hydrator\SelectQueryHydrator;
use Mdiyakov\DoctrineSolrBundle\Query\Select\MultiClassSelectQuery;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\ConfigEntityField;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\Field;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;

class MultiClassSelectQueryTest extends \PHPUnit_Framework_TestCase
{

    public function testInitDiscriminatorConditionsSuccess()
    {
        $configFieldName = 'type';
        $documentFieldName = 'd_type';
        $configFieldValue1 = 'article';
        $configFieldValue2 = 'news';
        $entityConfig1 =  [
            'config' => [
                [ 'name' => $configFieldName, 'value' => $configFieldValue1 ]
            ]
        ];

        $entityConfig2 =  [
            'config' => [
                [ 'name' => $configFieldName, 'value' => $configFieldValue2 ]
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
        $query = new MultiClassSelectQuery(
            $schema,
            $client,
            [$entityConfig1, $entityConfig2],
            [$hydrator]
        );

        $this->assertEquals(
            sprintf(
                '%s:"%s" OR %s:"%s"',
                $documentFieldName,
                $configFieldValue1,
                $documentFieldName,
                $configFieldValue2
            ),
            $query->getQueryString()
        );
    }

}