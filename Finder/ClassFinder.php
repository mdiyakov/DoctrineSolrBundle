<?php

namespace Mdiyakov\DoctrineSolrBundle\Finder;

use Mdiyakov\DoctrineSolrBundle\Query\Select\ClassSelectQuery;

class ClassFinder extends AbstractFinder
{
    /**
     * @var ClassSelectQuery
     */
    private $selectQuery;

    /**
     * @var string
     */
    private $class;

    /**
     * @param string $class
     * @param ClassSelectQuery $selectQuery
     */
    public function __construct($class, ClassSelectQuery $selectQuery)
    {
        $this->selectQuery = $selectQuery;
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return ClassSelectQuery
     */
    protected function getQuery()
    {
        return $this->selectQuery->reset();
    }
}