<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Schema\Field\Entity;

use Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\IntField;

class IntFieldTest extends \PHPUnit_Framework_TestCase
{

    public function testGetDocumentValueForNumbers()
    {
        $int = 123;
        $double = 123.83;
        $null = null;

        $entity = new \stdClass();
        $field = new IntField('price', 'd_price', false, 10, false);

        $entity->price = $double;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals(123,$fieldValue);

        $entity->price = $int;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals(123,$fieldValue);

        $entity->price = $null;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals(0,$fieldValue);
    }

    /**
     * @dataProvider notNumberProvider
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\InvalidFieldValueException
     */
    public function testGetDocumentValueForNotNumber($value)
    {
        $entity = new \stdClass();
        $field = new IntField('price', 'd_price', false, 10, false);

        $entity->price = $value;
        $field->getDocumentFieldValue($entity);
    }

    /**
     * @return array
     */
    public function notNumberProvider()
    {
        return [
            ['urytryry453'],
            [true],
            [new \stdClass()]
        ];
    }
}