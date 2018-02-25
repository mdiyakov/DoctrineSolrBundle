<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Suggest\Result;

class FieldResult
{

    /**
     * @var string
     */
    private $entityFieldName;

    /**
     * @var string
     */
    private $initialTerm;

    /**
     * @var SuggestionResult[]
     */
    private $suggestions;

    /**
     * FieldResult constructor.
     * @param string $entityFieldName
     * @param string $initialTerm
     * @param [][] $suggestions
     */
    public function __construct($entityFieldName, $initialTerm, array $suggestions)
    {
        $this->entityFieldName = $entityFieldName;
        $this->initialTerm = $initialTerm;

        foreach ($suggestions as $suggestion) {
            $this->suggestions[] = new SuggestionResult($suggestion['term'], $suggestion['weight'], $suggestion['payload']);
        }

    }

    /**
     * @return SuggestionResult[]
     */
    public function getSuggestions()
    {
        return $this->suggestions;
    }

    /**
     * @return string
     */
    public function getInitialTerm()
    {
        return $this->initialTerm;
    }

    /**
     * @return string
     */
    public function getEntityFieldName()
    {
        return $this->entityFieldName;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this->suggestions);
    }
}