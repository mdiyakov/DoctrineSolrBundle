<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Suggest\Solarium;

use Solarium\Core\Query\ResponseParser as ResponseParserAbstract;
use Solarium\Core\Query\ResponseParserInterface as ResponseParserInterface;
use Solarium\QueryType\Suggester\Result\Result;

class ResponseParser  extends ResponseParserAbstract implements ResponseParserInterface
{
    /**
     * Get result data for the response
     *
     * @param  Result $result
     * @return array
     */
    public function parse($result)
    {
        $data = $result->getData();
        $query = $result->getQuery();
        $suggestions = array();

        if (isset($data['suggest']) && is_array($data['suggest'])) {

            $suggestResults = $data['suggest'];
            $termClass = $query->getOption('termclass');
            foreach ($suggestResults as $dictionary => $termData) {

                foreach ($termData as $term => $suggestionData) {
                    $suggestions[$dictionary][$term] = $this->createTerm($termClass, $suggestionData);
                }
            }
        }

        return $this->addHeaderInfo(
            $data,
            array(
                'results' => $suggestions,
            )
        );
    }

    /**
     * @param $termClass
     * @param array $suggestionData
     * @return mixed
     */
    private function createTerm($termClass, array $suggestionData)
    {
        return new $termClass(
            $suggestionData['numFound'],
            $suggestionData['suggestions']
        );
    }
}