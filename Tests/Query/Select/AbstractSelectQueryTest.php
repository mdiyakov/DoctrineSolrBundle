<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Query\Select;

use Mdiyakov\DoctrineSolrBundle\Query\Select\AbstractSelectQuery;

class AbstractSelectQueryTest extends \PHPUnit_Framework_TestCase
{

    public function testAddConfigFieldOrWhere()
    {
        $schema = $this->getSchemaMock();
        $client = $this->getClientMock();
        $query = $this->getQueryMock($client, $schema);

        $fieldName = 'type';
        $documentFieldName = 'discriminator';
        $field = $this->getFieldMock($documentFieldName, 'Mdiyakov\DoctrineSolrBundle\Schema\Field\ConfigEntityField');
        $searchTerm = 'search';

        $schema->expects($this->any())
            ->method('getConfigFieldName')
            ->with($fieldName)
            ->will($this->returnValue($field));
        ;

        $query->addConfigFieldOrWhere($fieldName, $searchTerm);
        $query->addConfigFieldOrWhere($fieldName, $searchTerm);
        $query->addConfigFieldOrWhere($fieldName, $searchTerm, true);
        $query->addConfigFieldOrWhere($fieldName, $searchTerm, true, true);
        $query->addConfigFieldOrWhere($fieldName, $searchTerm, false, true);

        $this->assertEquals(
            sprintf('(%1$s:"%2$s" OR %1$s:"%2$s" OR (*:* AND -%1$s:"%2$s") OR (*:* AND -%1$s:%2$s) OR %1$s:%2$s) AND ()',
                $documentFieldName,
                $searchTerm
            ),
            $query->getQueryString()
        );
    }

    public function testAddAndWhere()
    {
        $schema = $this->getSchemaMock();
        $client = $this->getClientMock();
        $query = $this->getQueryMock($client, $schema);

        $entityFieldName = 'title';
        $documentFieldName = 'd_title';
        $field = $this->getFieldMock($documentFieldName, 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField');
        $searchTerm = 'search+-?*';
        $searchTermFiltered = 'search\+\-\?\*';
        $searchTermWildcardFiltered = 'search\+\-?*';

        $schema->expects($this->any())
            ->method('getFieldByEntityFieldName')
            ->with($entityFieldName)
            ->will($this->returnValue($field));
        ;

        $query->addAndWhere($entityFieldName, $searchTerm);
        $query->addAndWhere($entityFieldName, $searchTerm);
        $query->addAndWhere($entityFieldName, $searchTerm, true);
        $query->addAndWhere($entityFieldName, $searchTerm, false, true);
        $query->addAndWhere($entityFieldName, $searchTerm, true, true);
        $this->assertEquals(
            sprintf('(%1$s:"%2$s" AND %1$s:"%2$s" AND (*:* AND -%1$s:"%2$s") AND %1$s:%3$s AND (*:* AND -%1$s:%3$s)) AND ()',
                $documentFieldName,
                $searchTermFiltered,
                $searchTermWildcardFiltered
            ),
            $query->getQueryString()
        );
    }

    public function testReset()
    {
        $schema = $this->getSchemaMock();
        $client = $this->getClientMock();
        $query = $this->getQueryMock($client, $schema);

        $entityFieldName = 'title';
        $documentFieldName = 'd_title';
        $field = $this->getFieldMock($documentFieldName, 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField');
        $searchTerm = 'search';

        $schema->expects($this->any())
            ->method('getFieldByEntityFieldName')
            ->with($entityFieldName)
            ->will($this->returnValue($field));
        ;

        $query->addAndWhere($entityFieldName, $searchTerm);
        $query->addAndWhere($entityFieldName, $searchTerm);
        $query->addAndWhere($entityFieldName, $searchTerm, true);
        $query->addOrWhere($entityFieldName, $searchTerm, false, true);
        $query->addAndWhere($entityFieldName, $searchTerm, true, true);

        $client->expects($this->once())->method('createSelect')
            ->will($this->returnValue(
                $this->createMock('Solarium\QueryType\Select\Query\Query')
            ));

        $query->reset();
        $this->assertEquals( '', $query->getQueryString());
    }

    public function testAddOrWhere()
    {
        $schema = $this->getSchemaMock();
        $client = $this->getClientMock();
        $query = $this->getQueryMock($client, $schema);

        $entityFieldName = 'title';
        $documentFieldName = 'd_title';
        $searchTerm = 'search';
        $field = $this->getFieldMock($documentFieldName, 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField');

        $schema->expects($this->any())
            ->method('getFieldByEntityFieldName')
            ->with($entityFieldName)
            ->will($this->returnValue($field));
        ;

        $query->addOrWhere($entityFieldName, $searchTerm);
        $query->addOrWhere($entityFieldName, $searchTerm);
        $query->addOrWhere($entityFieldName, $searchTerm, true);
        $query->addOrWhere($entityFieldName, $searchTerm, false, true);
        $query->addOrWhere($entityFieldName, $searchTerm, true, true);
        $this->assertEquals(
            sprintf('(%1$s:"%2$s" OR %1$s:"%2$s" OR (*:* AND -%1$s:"%2$s") OR %1$s:%2$s OR (*:* AND -%1$s:%2$s)) AND ()',
                $documentFieldName,
                $searchTerm
            ),
            $query->getQueryString()
        );
    }

    public function testAddOrWhereWithPriority()
    {
        $schema = $this->getSchemaMock();
        $client = $this->getClientMock();
        $query = $this->getQueryMock($client, $schema);

        $entityFieldName = 'title';
        $documentFieldName = 'd_title';
        $searchTerm = 'search';
        $field = $this->getFieldMock($documentFieldName, 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField', 10);

        $schema->expects($this->any())
            ->method('getFieldByEntityFieldName')
            ->with($entityFieldName)
            ->will($this->returnValue($field));
        ;

        $query->addOrWhere($entityFieldName, $searchTerm);
        $query->addOrWhere($entityFieldName, $searchTerm);
        $query->addOrWhere($entityFieldName, $searchTerm, true);
        $query->addOrWhere($entityFieldName, $searchTerm, false, true);
        $query->addOrWhere($entityFieldName, $searchTerm, true, true);

        $this->assertEquals(
            sprintf('(%1$s:("%2$s")^10 OR %1$s:("%2$s")^10 OR (*:* AND -%1$s:("%2$s")^10) OR %1$s:(%2$s)^10 OR (*:* AND -%1$s:(%2$s)^10)) AND ()',
                $documentFieldName,
                $searchTerm
            ),
            $query->getQueryString()
        );
    }


    public function testAddRangeOrWhere()
    {
        $schema = $this->getSchemaMock();
        $client = $this->getClientMock();
        $query = $this->getQueryMock($client, $schema);

        $entityFieldName = 'amount';
        $documentFieldName = 'd_amount';
        $from = 3;
        $to = 89;

        $field = $this->getFieldMock($documentFieldName, 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField');

        $schema->expects($this->any())
            ->method('getFieldByEntityFieldName')
            ->with($entityFieldName)
            ->will($this->returnValue($field));
        ;

        $query->addRangeOrWhere($entityFieldName, $from, $to);
        $query->addRangeOrWhere($entityFieldName, $from, $to, true);
        $query->addRangeOrWhere($entityFieldName, $from, $to, true, true);
        $query->addRangeOrWhere($entityFieldName, $from, $to, false, true);
        $query->addRangeOrWhere($entityFieldName, $from, $to, false, false , true);
        $query->addRangeOrWhere($entityFieldName, $from, $to, false, true, true);
        $this->assertEquals(
            sprintf('(%3$s:[%1$s TO %2$s] OR %3$s:{%1$s TO %2$s] OR %3$s:{%1$s TO %2$s} OR %3$s:[%1$s TO %2$s} OR (*:* AND -%3$s:[%1$s TO %2$s]) OR (*:* AND -%3$s:[%1$s TO %2$s})) AND ()',
                $from,
                $to,
                $documentFieldName
            ),
            $query->getQueryString()
        );
    }

    public function testAddRangeAndWhere()
    {
        $schema = $this->getSchemaMock();
        $client = $this->getClientMock();
        $query = $this->getQueryMock($client, $schema);

        $entityFieldName = 'amount';
        $documentFieldName = 'd_amount';
        $from = 3;
        $to = 89;

        $field = $this->getFieldMock($documentFieldName, 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField');
        $schema->expects($this->any())
            ->method('getFieldByEntityFieldName')
            ->with($entityFieldName)
            ->will($this->returnValue($field));
        ;

        $query->addRangeAndWhere($entityFieldName, $from, $to);
        $query->addRangeAndWhere($entityFieldName, $from, $to, true);
        $query->addRangeAndWhere($entityFieldName, $from, $to, true, true);
        $query->addRangeAndWhere($entityFieldName, $from, $to, false, true);
        $query->addRangeAndWhere($entityFieldName, $from, $to, false, false, true);
        $query->addRangeAndWhere($entityFieldName, $from, $to, false, true, true);
        $this->assertEquals(
            sprintf('(%3$s:[%1$s TO %2$s] AND %3$s:{%1$s TO %2$s] AND %3$s:{%1$s TO %2$s} AND %3$s:[%1$s TO %2$s} AND (*:* AND -%3$s:[%1$s TO %2$s]) AND (*:* AND -%3$s:[%1$s TO %2$s})) AND ()',
                $from,
                $to,
                $documentFieldName
            ),
            $query->getQueryString()
        );
    }


    public function testAddFuzzyAndWhere()
    {
        $schema = $this->getSchemaMock();
        $client = $this->getClientMock();
        $query = $this->getQueryMock($client, $schema);

        $entityFieldName = 'category';
        $documentFieldName = 'd_category';
        $searchTerm = 'search';
        $distance = 19;

        $field = $this->getFieldMock($documentFieldName, 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField');
        $schema->expects($this->any())
            ->method('getFieldByEntityFieldName')
            ->with($entityFieldName)
            ->will($this->returnValue($field));
        ;

        $query->addFuzzyAndWhere($entityFieldName, $searchTerm);
        $query->addFuzzyAndWhere($entityFieldName, $searchTerm);
        $query->addFuzzyAndWhere($entityFieldName, $searchTerm, true);
        $query->addFuzzyAndWhere($entityFieldName, $searchTerm, true, $distance);
        $this->assertEquals(
            sprintf('(%1$s:%2$s~1 AND %1$s:%2$s~1 AND (*:* AND -%1$s:%2$s~1) AND (*:* AND -%1$s:%2$s~19)) AND ()',
                $documentFieldName,
                $searchTerm,
                $distance
            ),
            $query->getQueryString()
        );
    }


    public function testAddFuzzyOrWhere()
    {
        $schema = $this->getSchemaMock();
        $client = $this->getClientMock();
        $query = $this->getQueryMock($client, $schema);

        $entityFieldName = 'category';
        $documentFieldName = 'd_category';
        $searchTerm = 'search';
        $distance = 19;

        $field = $this->getFieldMock($documentFieldName, 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField');
        $schema->expects($this->any())
            ->method('getFieldByEntityFieldName')
            ->with($entityFieldName)
            ->will($this->returnValue($field));
        ;

        $query->addFuzzyOrWhere($entityFieldName, $searchTerm);
        $query->addFuzzyOrWhere($entityFieldName, $searchTerm);
        $query->addFuzzyOrWhere($entityFieldName, $searchTerm, true);
        $query->addFuzzyOrWhere($entityFieldName, $searchTerm, true, $distance);
        $this->assertEquals(
            sprintf('(%1$s:%2$s~1 OR %1$s:%2$s~1 OR (*:* AND -%1$s:%2$s~1) OR (*:* AND -%1$s:%2$s~19)) AND ()',
                $documentFieldName,
                $searchTerm,
                $distance
            ),
            $query->getQueryString()
        );
    }

    public function testGroupConditionsAsOr()
    {
        $schema = $this->getSchemaMock();
        $client = $this->getClientMock();
        $query = $this->getQueryMock($client, $schema);

        $entityFieldName = 'category';
        $documentFieldName = 'd_category';
        $searchTerm = 'search';

        $field = $this->getFieldMock($documentFieldName, 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField');
        $schema->expects($this->any())
            ->method('getFieldByEntityFieldName')
            ->with($entityFieldName)
            ->will($this->returnValue($field));
        ;

        $query->addFuzzyOrWhere($entityFieldName, $searchTerm);
        $query->addAndWhere($entityFieldName, $searchTerm);
        $query->groupConditionsAsOr();
        $query->addOrWhere($entityFieldName, $searchTerm);

        $this->assertEquals(
            sprintf('((%1$s:%2$s~1 AND %1$s:"search") OR %1$s:"%2$s") AND ()',
                $documentFieldName,
                $searchTerm
            ),
            $query->getQueryString()
        );
    }


    public function testGroupConditionsAsAnd()
    {
        $schema = $this->getSchemaMock();
        $client = $this->getClientMock();
        $query = $this->getQueryMock($client, $schema);

        $entityFieldName = 'category';
        $documentFieldName = 'd_category';
        $searchTerm = 'search';

        $field = $this->getFieldMock($documentFieldName, 'Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField');
        $schema->expects($this->any())
            ->method('getFieldByEntityFieldName')
            ->with($entityFieldName)
            ->will($this->returnValue($field));
        ;

        $query->addFuzzyOrWhere($entityFieldName, $searchTerm);
        $query->addAndWhere($entityFieldName, $searchTerm);
        $query->groupConditionsAsAnd();
        $query->addOrWhere($entityFieldName, $searchTerm);

        $this->assertEquals(
            sprintf('(%1$s:"%2$s" AND (%1$s:%2$s~1 AND %1$s:"search")) AND ()',
                $documentFieldName,
                $searchTerm
            ),
            $query->getQueryString()
        );
    }


    private function getSchemaMock()
    {
        $schema = $this->getMockBuilder('Mdiyakov\DoctrineSolrBundle\Schema\Schema')->disableOriginalConstructor()->getMock();
        $discriminatorField = $this->getMockBuilder('Mdiyakov\DoctrineSolrBundle\Schema\Field\ConfigEntityField')->disableOriginalConstructor()->getMock();
        $primaryKeyField = $this->getMockBuilder('Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\Field')->disableOriginalConstructor()->getMock();

        $schema->expects($this->at(0))->method('getDiscriminatorConfigField')
            ->will($this->returnValue($discriminatorField));

        $schema->expects($this->at(1))->method('getEntityPrimaryKeyField')
            ->will($this->returnValue($primaryKeyField));

        return $schema;
    }

    /**
     * @param $client
     * @param $schema
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractSelectQuery
     */
    private function getQueryMock($client, $schema)
    {
        /** @var AbstractSelectQuery|\PHPUnit_Framework_MockObject_MockObject $query */
        return $this->getMockForAbstractClass('Mdiyakov\DoctrineSolrBundle\Query\Select\AbstractSelectQuery', [$client, $schema]);
    }

    private function getClientMock()
    {
        $client = $this->getMockBuilder('Solarium\Client')->disableOriginalConstructor()->getMock();

        $client->expects($this->at(0))->method('createSelect')
            ->will($this->returnValue(
                $this->createMock('Solarium\QueryType\Select\Query\Query')
            ));

        return $client;
    }

    /**
     * @param string $documentFieldName
     * @param string $fieldClass
     * @param int $priorityValue
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getFieldMock($documentFieldName, $fieldClass, $priorityValue = null)
    {
        $field = $this->getMockBuilder($fieldClass)->disableOriginalConstructor()->getMock();
        $field->expects($this->any())
            ->method('getPriority')
            ->will($this->returnValue($priorityValue));

        $field->expects($this->any())
            ->method('getDocumentFieldName')
            ->will($this->returnValue($documentFieldName));

        return $field;
    }
}