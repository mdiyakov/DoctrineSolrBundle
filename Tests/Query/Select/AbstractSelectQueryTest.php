<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Query\Select;

use Mdiyakov\DoctrineSolrBundle\Query\Select\AbstractSelectQuery;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\ConfigEntityField;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\Field;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;

class AbstractSelectQueryTest extends \PHPUnit_Framework_TestCase
{

    public function testAddAndWhere()
    {
        $entityFieldName = 'title';
        $documentFieldName = 'd_title';
        $searchTerm = 'search';
        $client = $this->getMockBuilder(\Solarium\Client::class)->disableOriginalConstructor()->getMock();
        $schema = $this->getMockBuilder(Schema::class)->disableOriginalConstructor()->getMock();
        $field = $this->getMockBuilder(StringField::class)->disableOriginalConstructor()->getMock();
        $discriminatorField = $this->getMockBuilder(ConfigEntityField::class)->disableOriginalConstructor()->getMock();
        $primaryKeyField = $this->getMockBuilder(Field::class)->disableOriginalConstructor()->getMock();
        $arguments = [
            $client,
            $schema
        ];

        $schema->expects($this->at(0))->method('getDiscriminatorConfigField')
            ->will($this->returnValue($discriminatorField));

        $schema->expects($this->at(1))->method('getEntityPrimaryKeyField')
            ->will($this->returnValue($primaryKeyField));

        $client->expects($this->at(0))->method('createSelect')
            ->will($this->returnValue(
                $this->createMock('Solarium\QueryType\Select\Query\Query')
            ));

        /** @var AbstractSelectQuery|\PHPUnit_Framework_MockObject_MockObject $query */
        $query = $this->getMockForAbstractClass(AbstractSelectQuery::class, $arguments);

        $field->expects($this->at(0))
            ->method('getPriority')
            ->will($this->returnValue(null));

        $field->expects($this->at(1))
            ->method('getDocumentFieldName')
            ->will($this->returnValue($documentFieldName));

        $schema->expects($this->any())
            ->method('getFieldByEntityFieldName')
            ->with($entityFieldName)
            ->will($this->returnValue($field));
        ;

        $query->addAndWhere($entityFieldName, $searchTerm);
        $this->assertEquals(
            sprintf('(%s:"%s") AND ()', $documentFieldName, $searchTerm),
            $query->getQueryString()
        );
    }

}