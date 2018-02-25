<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Suggest\Result;

class SuggestionResult
{
    /**
     * @var string
     */
    private $term;

    /**
     * @var float
     */
    private $weight;

    /**
     * @var string
     */
    private $payload;

    /**
     * SuggestionResult constructor.
     * @param string $term
     * @param float $weight
     * @param string $payload
     */
    public function __construct($term, $weight, $payload)
    {
        $this->term = $term;
        $this->weight = $weight;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

}