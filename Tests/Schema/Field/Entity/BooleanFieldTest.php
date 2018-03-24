<?php


namespace Mdiyakov\DoctrineSolrBundle\Tests\Schema\Field\Entity;


use Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\BooleanField;

class BooleanFieldTest extends \PHPUnit_Framework_TestCase
{

    public function testGetDocumentValueForScalar()
    {
        $string = 'string';
        $int = 123;
        $bool = false;

        $entity = new \stdClass();
        $field = new BooleanField('enabled', 'd_enabled', false, 10, false);

        $entity->enabled = $string;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals(true ,$fieldValue);

        $entity->enabled = $int;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals(true ,$fieldValue);


        $entity->enabled = $bool;
        $fieldValue = $field->getDocumentFieldValue($entity);
        $this->assertEquals(false, $fieldValue);
    }
}