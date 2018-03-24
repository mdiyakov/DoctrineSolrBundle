<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Schema\Field\Entity;

use Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\StringField;

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

class StringFieldTest extends \PHPUnit_Framework_TestCase
{

    public function testGetDocumentValueForScalar()
    {
        $string = 'string';
        $int = 123;
        $bool = false;
        $null = null;

        $entity = new \stdClass();
        $field = new StringField('title', 'd_title', false, 10, false);

        $entity->title = $string;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals($string, $fieldValue);

        $entity->title = $int;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals(strval($int) ,$fieldValue);

        $entity->title = $bool;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals(strval($bool) ,$fieldValue);

        $entity->title = $null;
        $fieldValue = $field->getDocumentFieldValue($entity);

        $this->assertEquals($null ,$fieldValue);
    }

    public function testGetDocumentValueForObject()
    {
        $value = 'value';
        $object = new StringObject($value);

        $entity = new \stdClass();
        $field = new StringField('title', 'd_title', false, 10, false);

        $entity->title = $object;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals($value, $fieldValue);
    }

    public function testGetDocumentValueForArrayOfObjects()
    {
        $value1 = 'value';
        $value2 = 'value2';

        $array = [new StringObject($value1), new StringObject($value2)];

        $entity = new \stdClass();
        $field = new StringField('title', 'd_title', false, 10, false);

        $entity->title = $array;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals(join(',', [$value1, $value2]), $fieldValue);
    }


    public function testGetDocumentValueForMixedArray()
    {
        $value1 = 'value';
        $value2 = 'value2';

        $array = [new StringObject($value1), $value2];

        $entity = new \stdClass();
        $field = new StringField('title', 'd_title', false, 10, false);

        $entity->title = $array;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals(join(',', [$value1, $value2]), $fieldValue);
    }
}