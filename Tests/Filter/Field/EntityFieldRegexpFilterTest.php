<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Filter\Field;

use Mdiyakov\DoctrineSolrBundle\Filter\Field\EntityFieldRegexpFilter;

class EntityFieldRegexpFilterTest extends \PHPUnit_Framework_TestCase
{

    public function testIsFilterValidForScalar()
    {
        $string = 'abcd 3874';

        $entity = new \stdClass();
        $filter = new EntityFieldRegexpFilter();
        $filter->setEntityFieldName('title');

        $entity->title = $string;
        $filter->setEntityFieldValue('/^[a-z]{1,4}\s?[\d]*$/');
        $this->assertEquals(true, $filter->isFilterValid($entity));
    }
}