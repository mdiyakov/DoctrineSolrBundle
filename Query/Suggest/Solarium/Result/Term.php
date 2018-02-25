<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Suggest\Solarium\Result;

class Term implements \IteratorAggregate, \Countable
{

    /**
     * @var int
     */
    private $numFound;

    /**
     * @var array
     */
    private $suggestions;

    public function __construct($numFound, $suggestions)
    {
        $this->numFound = $numFound;
        $this->suggestions = $suggestions;
    }


    /**
     * IteratorAggregate implementation
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->suggestions);
    }

    /**
     * Countable implementation
     *
     * @return int
     */
    public function count()
    {
        return count($this->suggestions);
    }

    /**
     * Get suggestions
     *
     * @return array
     */
    public function getSuggestions()
    {
        return $this->suggestions;
    }

}