<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Filter\Field;

use Mdiyakov\DoctrineSolrBundle\Filter\Field\EntityFieldEqualFilter;


class StringObject
{
    private $value;

    public function __construct($value)
    {
        $this->value = strval($value);
    }

    public function __toString()
    {
        return $this->value;
    }
}


class EntityFieldEqualFilterTest extends \PHPUnit_Framework_TestCase
{

    public function testIsFilterValidForScalar()
    {
        $string = 'string';
        $int = 123;
        $bool = true;
        $null = null;

        $entity = new \stdClass();
        $filter = new EntityFieldEqualFilter();
        $filter->setEntityFieldName('title');

        $entity->title = $int;
        $filter->setEntityFieldValue($int);
        $this->assertEquals(true, $filter->isFilterValid($entity));

        $entity->title = $string;
        $filter->setEntityFieldValue($string);
        $this->assertEquals(true, $filter->isFilterValid($entity));

        $entity->title = $bool;
        $filter->setEntityFieldValue($bool);
        $this->assertEquals(true, $filter->isFilterValid($entity));


        $entity->title = $null;
        $filter->setEntityFieldValue($null);
        $this->assertEquals(true, $filter->isFilterValid($entity));
    }

    public function testIsFilterValidStringObject()
    {
        $object = new StringObject('test');
        $entity = new \stdClass();
        $filter = new EntityFieldEqualFilter();
        $filter->setEntityFieldName('title');

        $entity->title = $object;
        $filter->setEntityFieldValue('test');
        $this->assertEquals(true, $filter->isFilterValid($entity));
    }

    /**
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\FilterConfigException
     */
    public function testIsFilterValidNotStringObject()
    {
        $object = new \stdClass();
        $entity = new \stdClass();
        $filter = new EntityFieldEqualFilter();
        $filter->setEntityFieldName('title');

        $entity->title = $object;
        $filter->setEntityFieldValue('test');
        $filter->isFilterValid($entity);
    }
}