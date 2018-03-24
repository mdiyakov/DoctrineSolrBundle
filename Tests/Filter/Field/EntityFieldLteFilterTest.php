<?php


namespace Mdiyakov\DoctrineSolrBundle\Tests\Filter\Field;


use Mdiyakov\DoctrineSolrBundle\Filter\Field\EntityFieldLteFilter;

class EntityFieldLteFilterTest extends \PHPUnit_Framework_TestCase
{

    public function testIsFilterValidForScalar()
    {
        $string = 'abcd';
        $int = 123;
        $bool = true;
        $null = null;

        $entity = new \stdClass();
        $filter = new EntityFieldLteFilter();
        $filter->setEntityFieldName('title');

        $entity->title = $int;
        $filter->setEntityFieldValue(124);
        $this->assertEquals(true, $filter->isFilterValid($entity));
        $filter->setEntityFieldValue(122);
        $this->assertEquals(false, $filter->isFilterValid($entity));

        $entity->title = $string;
        $filter->setEntityFieldValue('abc');
        $this->assertEquals(false, $filter->isFilterValid($entity));

        $entity->title = $bool;
        $filter->setEntityFieldValue($bool);
        $this->assertEquals(true, $filter->isFilterValid($entity));


        $entity->title = $null;
        $filter->setEntityFieldValue(1);
        $this->assertEquals(true, $filter->isFilterValid($entity));
    }
}