<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Schema\Field\Entity;

use Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\ArrayField;


class ArrayFieldTest extends \PHPUnit_Framework_TestCase
{

    public function testGetDocumentValueForScalar()
    {
        $string = 'string';
        $int = 123;
        $bool = false;
        $null = null;

        $entity = new \stdClass();
        $field = new ArrayField('tags', 'd_tags', false, 10, false);

        $entity->tags = $string;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals([$string] ,$fieldValue);

        $entity->tags = $int;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals([$int] ,$fieldValue);

        $entity->tags = $bool;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals([$bool] ,$fieldValue);

        $entity->tags = $null;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals([] ,$fieldValue);
    }


    public function testGetDocumentValueForArray()
    {
        $array = [1,'string', false];

        $entity = new \stdClass();
        $field = new ArrayField('tags', 'd_tags', false, 10, false);

        $entity->tags = $array;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals($array ,$fieldValue);
    }


    public function testGetDocumentValueForIterator()
    {
        $array = [1,3,4,4,5,6,9];
        $iterator = new \ArrayIterator($array);

        $entity = new \stdClass();
        $field = new ArrayField('tags', 'd_tags', false, 10, false);

        $entity->tags = $iterator;
        $fieldValue = $field->getDocumentFieldValue($entity);

        $this->assertEquals($array ,$fieldValue);
    }

    public function testGetDocumentValueForIteratorAggregate()
    {
        $array = [1,3,4,4,5,6,9];
        $arrayObject = new \ArrayObject($array);

        $entity = new \stdClass();
        $field = new ArrayField('tags', 'd_tags', false, 10, false);

        $entity->tags = $arrayObject;
        $fieldValue = $field->getDocumentFieldValue($entity);

        $this->assertEquals($array ,$fieldValue);
    }
}