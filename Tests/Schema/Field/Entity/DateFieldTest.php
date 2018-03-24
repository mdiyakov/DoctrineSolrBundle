<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Schema\Field\Entity;

use Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\DateField;

class DateFieldTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider scalarProvider
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\InvalidFieldValueException
     */
    public function testGetDocumentValueForScalar($value)
    {
        $entity = new \stdClass();
        $field = new DateField('date', 'd_date', false, 10, false);

        $entity->date = $value;
        $field->getDocumentFieldValue($entity);
    }

    public function testGetDocumentValueForDateTime()
    {
        $value = new \DateTime('2018-01-04 09:30:00');

        $entity = new \stdClass();
        $field = new DateField('date', 'd_date', false, 10, false);

        $entity->date = $value;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals('2018-01-04T09:30:00Z',$fieldValue);
    }

    public function testGetDocumentValueForNull()
    {
        $value = null;

        $entity = new \stdClass();
        $field = new DateField('date', 'd_date', false, 10, false);

        $entity->date = $value;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals(null,$fieldValue);
    }

    /**
     * @return array
     */
    public function scalarProvider()
    {
        return [
            ['string'],
            [false],
            [123],
            [2.3],
        ];
    }
}